<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Agent;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\PaymentMethod;
use App\Models\User;
use Stripe\Stripe;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\PaymentIntent;
use Stripe\SetupIntent;
use Stripe\Transfer;
use Stripe\Customer;
use Stripe\PaymentMethod as StripePaymentMethod;
use Stripe\FinancialConnections\Session as FinancialConnectionsSession;
use Stripe\FinancialConnections\Account as FinancialConnectionsAccount;
use Illuminate\Support\Facades\Log;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('cashier.secret'));
    }

    /**
     * Create Stripe Connect account for user (agent or company)
     */
    public function createConnectAccount(User $user, string $type = 'express'): array
    {
        try {
            // Determine business type based on user role
            $businessType = $user->isCompany() ? 'company' : 'individual';
            
            // Get company or agent data for the account
            $company = $user->company;
            $agent = $user->agent;

            $account = Account::create([
                'type' => $type,
                'country' => 'US', // You may want to make this configurable
                'email' => $user->email,
                'capabilities' => [
                    'card_payments' => ['requested' => true],
                    'transfers' => ['requested' => true],
                    'us_bank_account_ach_payments' => ['requested' => true],
                    'us_bank_transfer_payments' => ['requested' => true],

                ],          
                'business_type' => $businessType,
                'company' => $company ? [
                    'name' => $company->company_name,
                    'phone' => $company->phone,
                    'tax_id' => $company->tax_id,
                ] : null,
                'individual' => $agent ? [
                    'email' => $user->email,
                    'first_name' => explode(' ', $user->name)[0] ?? '',
                    'last_name' => explode(' ', $user->name)[1] ?? '',
                ] : null,
            ]);

            // Update user with Stripe account ID
            $user->update([
                'stripe_connect_account_id' => $account->id,
                'stripe_capabilities' => $account->capabilities,
            ]);

            return [
                'success' => true,
                'account_id' => $account->id,
                'account' => $account,
            ];
        } catch (\Exception $e) {
            Log::error('Stripe Connect account creation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create onboarding link for Stripe Connect
     */
    public function createOnboardingLink(User $user): array
    {
        try {
            if (!$user->stripe_connect_account_id) {
                $result = $this->createConnectAccount($user);
                if (!$result['success']) {
                    return $result;
                }
            }

            $accountLink = AccountLink::create([
                'account' => $user->stripe_connect_account_id,
                'refresh_url' => config('app.url') . '/stripe/connect/refresh',
                'return_url' => config('app.url') . '/stripe/connect/return',
                'type' => 'account_onboarding',
            ]);

            return [
                'success' => true,
                'onboarding_url' => $accountLink->url,
            ];
        } catch (\Exception $e) {
            Log::error('Stripe onboarding link creation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check onboarding status for user
     */
    public function checkOnboardingStatus(User $user): array
    {
        try {
            if (!$user->stripe_connect_account_id) {
                return [
                    'success' => false,
                    'onboarding_complete' => false,
                    'account_id' => null,
                ];
            }

            $account = Account::retrieve($user->stripe_connect_account_id);
            $onboardingComplete = $account->details_submitted && 
                                 $account->charges_enabled && 
                                 $account->payouts_enabled;

            // Update local status
            $user->update([
                'stripe_onboarding_complete' => $onboardingComplete,
                'stripe_capabilities' => $account->capabilities,
            ]);

            return [
                'success' => true,
                'onboarding_complete' => $onboardingComplete,
                'account_id' => $account->id,
                'details_submitted' => $account->details_submitted,
                'charges_enabled' => $account->charges_enabled,
                'payouts_enabled' => $account->payouts_enabled,
                'capabilities' => $account->capabilities,
            ];
        } catch (\Exception $e) {
            Log::error('Onboarding status check failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create payment intent for invoice
     */
    public function createPaymentIntent(Invoice $invoice, PaymentMethod $paymentMethod, float $adminCommission = 2.00): array
    {
        try {
            // Get agent user for Connect account
            $agentUser = $invoice->agent->user;
            $agentConnectAccountId = $agentUser->stripe_connect_account_id;
            
            // Get company user for Connect account
            $companyUser = $paymentMethod->user;
            $companyConnectAccountId = $companyUser->stripe_connect_account_id;

            // Calculate amounts
            $totalAmount = $invoice->total_amount * 100; // Convert to cents
            $adminCommissionCents = 1000; // Convert to cents
            $totalPayableAmount = $totalAmount + $adminCommissionCents;
            $totalStripeChargeAmount = (($totalPayableAmount + 30) / (1 - 0.029)) - $totalPayableAmount;

            $totalPayableAmount += $totalStripeChargeAmount;

            $amounts = [
                'payable_amount_cents' => intval($totalPayableAmount),
                'stripe_charge_cents' => intval($totalStripeChargeAmount),
                'platform_fee_cents' => intval($adminCommissionCents),
                'payable_amount' => $totalPayableAmount / 100,
                'stripe_charge' => $totalStripeChargeAmount / 100,
                'platform_fee' => $adminCommissionCents / 100,
            ];

            $customerId = $this->getOrCreateCustomer($companyUser);
            $paymentMethodId = $paymentMethod->stripe_payment_method_id;

            // Create a transfer group to link payment and transfer together
            $transferGroup = 'invoice_' . $invoice->id;

            // Step 1: Create PaymentIntent on platform account
            $paymentIntent = PaymentIntent::create([
                'amount' => $amounts['payable_amount_cents'],
                'currency' => 'usd',
                'customer' => $customerId,
                'payment_method' => $paymentMethodId,
                'confirm' => true,
                'transfer_group' => $transferGroup, // Group transactions by invoice
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never',
                ],
                'metadata' => [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'company_id' => $invoice->company_id,
                    'agent_id' => $invoice->agent_id,
                    'admin_commission' => $amounts['platform_fee'],
                    'transfer_amount' => $invoice->total_amount,
                ],
            ]);

            // Initialize transfer ID variable
            $transferId = null;

            // Step 2: If payment succeeded, create separate transfer to agent
            if ($paymentIntent->status === 'succeeded') {
                try {
                    // Transfer only the invoice amount (excluding platform fee) to the agent
                    $transfer = Transfer::create([
                        'amount' => intval($invoice->total_amount * 100),
                        'currency' => 'usd',
                        'destination' => $agentConnectAccountId,
                        'transfer_group' => $transferGroup, // Same group as PaymentIntent
                        'metadata' => [
                            'invoice_id' => $invoice->id,
                            'invoice_number' => $invoice->invoice_number,
                            'company_id' => $invoice->company_id,
                            'agent_id' => $invoice->agent_id,
                            'payment_intent_id' => $paymentIntent->id,
                        ],
                    ]);
                    
                    $transferId = $transfer->id;
                    
                    Log::info('Transfer created successfully', [
                        'transfer_id' => $transfer->id,
                        'payment_intent_id' => $paymentIntent->id,
                        'amount' => $invoice->total_amount,
                        'invoice_id' => $invoice->id,
                        'destination' => $agentConnectAccountId,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Transfer creation failed: ' . $e->getMessage(), [
                        'payment_intent_id' => $paymentIntent->id,
                        'invoice_id' => $invoice->id,
                        'agent_connect_account_id' => $agentConnectAccountId,
                    ]);
                    
                    // Create transaction record with transfer_failed status
                    $transaction = Transaction::create([
                        'transaction_id' => Transaction::generateTransactionId(),
                        'invoice_id' => $invoice->id,
                        'company_id' => $invoice->company_id,
                        'agent_id' => $invoice->agent_id,
                        'amount' => $invoice->total_amount,
                        'admin_commission' => $amounts['platform_fee'],
                        'net_amount' => $invoice->total_amount,
                        'type' => 'payment',
                        'status' => 'transfer_failed',
                        'stripe_payment_intent_id' => $paymentIntent->id,
                        'payment_method_type' => $paymentMethod->type,
                        'stripe_metadata' => json_encode($paymentIntent->metadata->toArray()),
                        'notes' => 'Payment succeeded but transfer failed: ' . $e->getMessage(),
                    ]);
                    
                    return [
                        'success' => false,
                        'error' => 'Payment succeeded but transfer to agent failed. Support has been notified.',
                        'payment_intent' => $paymentIntent,
                        'transaction' => $transaction,
                    ];
                }
            }

            // Create transaction record
            $transaction = Transaction::create([
                'transaction_id' => Transaction::generateTransactionId(),
                'invoice_id' => $invoice->id,
                'company_id' => $invoice->company_id,
                'agent_id' => $invoice->agent_id,
                'amount' => $invoice->total_amount,
                'admin_commission' => $amounts['platform_fee'],
                'net_amount' => $invoice->total_amount,
                'type' => 'payment',
                'status' => $paymentIntent->status === 'succeeded' ? 'completed' : 'pending',
                'stripe_payment_intent_id' => $paymentIntent->id,
                'stripe_transfer_id' => $transferId,
                'payment_method_type' => $paymentMethod->type,
                'stripe_metadata' => json_encode($paymentIntent->metadata->toArray()),
            ]);

            if ($paymentIntent->status === 'succeeded') {
                $invoice->markAsPaid();
                $transaction->markAsCompleted();
            }

            return [
                'success' => true,
                'payment_intent' => $paymentIntent,
                'transaction' => $transaction,
            ];
        } catch (\Exception $e) {
            Log::error('Charge creation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Add payment method to user
     */
    public function addPaymentMethod(User $user, array $paymentMethodData): array
    {
        try {
            $customer = $this->getOrCreateCustomer($user);
            
            $stripePaymentMethod = StripePaymentMethod::create([
                'type' => $paymentMethodData['type'],
                'card' => $paymentMethodData['type'] === 'card' ? $paymentMethodData['card'] : null,
                'bank_account' => $paymentMethodData['type'] === 'bank_account' ? $paymentMethodData['bank_account'] : null,
            ]);

            $stripePaymentMethod->attach(['customer' => $customer]);

            // Store in database
            $paymentMethod = $user->paymentMethods()->create([
                'stripe_payment_method_id' => $stripePaymentMethod->id,
                'type' => $stripePaymentMethod->type,
                'brand' => $stripePaymentMethod->card->brand ?? null,
                'last_four' => $stripePaymentMethod->card->last4 ?? $stripePaymentMethod->bank_account->last4 ?? null,
                'exp_month' => $stripePaymentMethod->card->exp_month ?? null,
                'exp_year' => $stripePaymentMethod->card->exp_year ?? null,
                'bank_name' => $stripePaymentMethod->bank_account->bank_name ?? null,
                'account_holder_type' => $stripePaymentMethod->bank_account->account_holder_type ?? null,
                'is_default' => $user->paymentMethods()->count() === 0, // First payment method is default
                'stripe_metadata' => $stripePaymentMethod->metadata->toArray(),
            ]);

            return [
                'success' => true,
                'payment_method' => $paymentMethod,
                'stripe_payment_method' => $stripePaymentMethod,
            ];
        } catch (\Exception $e) {
            Log::error('Payment method creation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create Financial Connections session for adding new bank account
     */
    public function createFinancialConnectionsSessionForAddition(User $user): array
    {
        try {
            // Create Financial Connections session for adding new bank account
            $session = FinancialConnectionsSession::create([
                'account_holder' => [
                    'type' => 'customer',
                    'customer' => $this->getOrCreateCustomer($user),
                ],
                'permissions' => ['payment_method', 'balances'],
                'filters' => [
                    'countries' => ['US'],
                ],
                'return_url' => $this->getReturnUrlForAddition($user),
            ]);

            Log::info('Financial Connections session created for bank account addition', [
                'session_id' => $session->id,
                'user_id' => $user->id,
            ]);

            return [
                'success' => true,
                'session' => $session,
                'client_secret' => $session->client_secret,
                'message' => 'Financial Connections session created. Please connect your bank account.',
            ];
        } catch (\Exception $e) {
            Log::error('Financial Connections session creation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to create bank connection session: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Complete Financial Connections bank account addition
     */
    public function completeFinancialConnectionsBankAddition(User $user, string $sessionId): array
    {
        try {
            // Retrieve the session
            $session = FinancialConnectionsSession::retrieve($sessionId);
            
            Log::info('Financial Connections session retrieved for addition', [
                'session_id' => $sessionId,
                'user_id' => $user->id,
                'accounts_count' => count($session->accounts->data ?? []),
                'session_status' => $session->status ?? 'unknown',
            ]);
            
            if (empty($session->accounts->data)) {
                Log::warning('No accounts linked in Financial Connections session', [
                    'session_id' => $sessionId,
                    'user_id' => $user->id,
                ]);
                
                return [
                    'success' => false,
                    'error' => 'No accounts were linked during the connection process.',
                ];
            }

            $addedPaymentMethods = [];

            // Process each linked account
            foreach ($session->accounts->data as $account) {
                Log::info('Processing Financial Connections account for addition', [
                    'account_id' => $account->id,
                    'user_id' => $user->id,
                    'institution_name' => $account->institution_name,
                ]);
                
                // Create payment method from the linked account
                $stripePaymentMethod = StripePaymentMethod::create([
                    'type' => 'us_bank_account',
                    'us_bank_account' => [
                        'financial_connections_account' => $account->id,
                    ],
                    'billing_details' => [
                        'name' => $user->name ?: 'Unknown',
                        'email' => $user->email,
                    ],
                ]);

                // Attach to customer
                $customerId = $this->getOrCreateCustomer($user);
                $stripePaymentMethod->attach(['customer' => $customerId]);

                // Set as default if it's the first payment method
                $isFirstPaymentMethod = $user->paymentMethods()->count() === 0;

                // If setting as default, unset other defaults
                if ($isFirstPaymentMethod) {
                    $user->paymentMethods()->update(['is_default' => false]);
                }

                // Store in database as already verified
                $paymentMethod = $user->paymentMethods()->create([
                    'stripe_payment_method_id' => $stripePaymentMethod->id,
                    'type' => 'us_bank_account',
                    'brand' => null,
                    'last_four' => $stripePaymentMethod->us_bank_account->last4,
                    'exp_month' => null,
                    'exp_year' => null,
                    'bank_name' => $account->institution_name ?? 'Unknown Bank',
                    'account_holder_type' => $stripePaymentMethod->us_bank_account->account_holder_type,
                    'is_default' => $isFirstPaymentMethod,
                    'is_active' => true,
                    'verification_status' => 'verified', // Already verified via Financial Connections
                    'verification_method' => 'instant',
                    'verification_method_used' => 'instant',
                    'verified_at' => now(),
                    'verification_attempts' => 1,
                    'financial_connections_session_id' => $sessionId,
                    'financial_connections_account_id' => $account->id,
                    'financial_connections_metadata' => [
                        'session_id' => $sessionId,
                        'account_id' => $account->id,
                        'institution_name' => $account->institution_name,
                        'added_at' => now()->toISOString(),
                        'account_type' => $account->subcategory,
                        'balance' => $account->balance ?? null,
                    ],
                    'stripe_metadata' => $stripePaymentMethod->metadata->toArray(),
                ]);

                $addedPaymentMethods[] = $paymentMethod;

                Log::info('Bank account added successfully via Financial Connections', [
                    'payment_method_id' => $paymentMethod->id,
                    'stripe_payment_method_id' => $stripePaymentMethod->id,
                    'financial_connections_account_id' => $account->id,
                    'user_id' => $user->id,
                    'institution_name' => $account->institution_name,
                ]);
            }

            return [
                'success' => true,
                'payment_methods' => $addedPaymentMethods,
                'count' => count($addedPaymentMethods),
                'message' => count($addedPaymentMethods) === 1 
                    ? 'Bank account added and verified successfully!' 
                    : count($addedPaymentMethods) . ' bank accounts added and verified successfully!',
            ];
        } catch (\Exception $e) {
            Log::error('Financial Connections bank addition failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to add bank account: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get return URL for Financial Connections bank addition
     */
    private function getReturnUrlForAddition(User $user): string
    {
        $baseUrl = config('app.url');
        
        // Handle local development HTTPS requirement
        if (str_contains($baseUrl, 'localhost') || str_contains($baseUrl, '127.0.0.1')) {
            $baseUrl = 'https://your-app.test'; // Change to your local HTTPS domain
        }
        
        if ($user->isAgent()) {
            return $baseUrl . '/agent/payment-methods/add-complete';
        } else {
            return $baseUrl . '/company/payment-methods/add-complete';
        }
    }

    /**
     * Attach existing payment method to user
     */
    public function attachPaymentMethod(User $user, string $paymentMethodId, bool $isDefault = false): array
    {
        try {
            // Get or create customer for user
            $customer = $this->getOrCreateCustomer($user);
            
            // Retrieve the payment method from Stripe
            $stripePaymentMethod = StripePaymentMethod::retrieve($paymentMethodId);
            
            // Handle US bank account verification requirement
            if ($stripePaymentMethod->type === 'us_bank_account') {
                // Store as unverified and require verification
                return $this->storeUnverifiedBankAccount($user, $stripePaymentMethod, $isDefault);
            }
            
            // Attach to customer if not already attached (for cards)
            if (!$stripePaymentMethod->customer) {
                $stripePaymentMethod->attach(['customer' => $customer]);
            }

            // Set as default if requested or if it's the first payment method
            $isFirstPaymentMethod = $user->paymentMethods()->count() === 0;
            $shouldSetDefault = $isDefault || $isFirstPaymentMethod;

            // If setting as default, unset other defaults
            if ($shouldSetDefault) {
                $user->paymentMethods()->update(['is_default' => false]);
            }

            // Store in database
            $paymentMethod = $user->paymentMethods()->create([
                'stripe_payment_method_id' => $stripePaymentMethod->id,
                'type' => $stripePaymentMethod->type,
                'brand' => $stripePaymentMethod->card->brand ?? null,
                'last_four' => $stripePaymentMethod->card->last4 ?? null,
                'exp_month' => $stripePaymentMethod->card->exp_month ?? null,
                'exp_year' => $stripePaymentMethod->card->exp_year ?? null,
                'bank_name' => $stripePaymentMethod->us_bank_account->bank_name ?? null,
                'account_holder_type' => $stripePaymentMethod->us_bank_account->account_holder_type ?? null,
                'is_default' => $shouldSetDefault,
                'is_active' => true,
                'verification_status' => 'verified', // Cards are immediately verified
                'verified_at' => now(),
                'stripe_metadata' => $stripePaymentMethod->metadata->toArray(),
            ]);

            return [
                'success' => true,
                'payment_method' => $paymentMethod,
                'stripe_payment_method' => $stripePaymentMethod,
            ];
        } catch (\Exception $e) {
            // Check if this is a bank account verification error
            if (str_contains($e->getMessage(), 'must be verified before they can be attached')) {
                // Handle bank account verification requirement
                return $this->handleBankAccountVerification($user, $paymentMethodId, $isDefault, $e);
            }
            
            Log::error('Payment method attachment failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'payment_method_id' => $paymentMethodId,
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Store unverified bank account for verification
     */
    private function storeUnverifiedBankAccount(User $user, $stripePaymentMethod, bool $isDefault = false): array
    {
        try {
            // Set as default if requested or if it's the first payment method
            $isFirstPaymentMethod = $user->paymentMethods()->count() === 0;
            $shouldSetDefault = $isDefault || $isFirstPaymentMethod;

            // If setting as default, unset other defaults
            if ($shouldSetDefault) {
                $user->paymentMethods()->update(['is_default' => false]);
            }

            // Create bank account entry
            $paymentMethod = $user->paymentMethods()->create([
                'stripe_payment_method_id' => $stripePaymentMethod->id,
                'type' => 'us_bank_account',
                'brand' => null,
                'last_four' => $stripePaymentMethod->us_bank_account->last4 ?? null,
                'exp_month' => null,
                'exp_year' => null,
                'bank_name' => $stripePaymentMethod->us_bank_account->bank_name ?? null,
                'account_holder_type' => $stripePaymentMethod->us_bank_account->account_holder_type ?? null,
                'is_default' => false, // Don't set as default until verified
                'is_active' => false, // Not active until verified
                'verification_status' => 'verification_required',
                'stripe_metadata' => $stripePaymentMethod->metadata->toArray(),
            ]);

            Log::info('Bank account stored for verification', [
                'user_id' => $user->id,
                'payment_method_id' => $paymentMethod->id,
                'stripe_payment_method_id' => $stripePaymentMethod->id,
            ]);

            return [
                'success' => true,
                'payment_method' => $paymentMethod,
                'stripe_payment_method' => $stripePaymentMethod,
                'verification_required' => true,
                'message' => 'Bank account added successfully. Please verify using micro-deposits to activate.',
            ];
        } catch (\Exception $e) {
            Log::error('Bank account storage failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to store bank account: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle bank account verification requirement
     */
    private function handleBankAccountVerification(User $user, string $paymentMethodId, bool $isDefault, \Exception $originalException): array
    {
        try {
            // Retrieve the payment method details
            $stripePaymentMethod = StripePaymentMethod::retrieve($paymentMethodId);
            
            // Store unverified bank account for verification
            return $this->storeUnverifiedBankAccount($user, $stripePaymentMethod, $isDefault);
            
        } catch (\Exception $e) {
            Log::error('Bank account verification handling failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'Bank account verification required. US bank accounts must be verified before they can be used for payments.',
                'verification_required' => true,
                'original_error' => $originalException->getMessage(),
            ];
        }
    }

    /**
     * Initiate micro-deposit verification for bank account
     */
    public function initiateMicroDepositVerification(PaymentMethod $paymentMethod): array
    {
        try {
            if ($paymentMethod->type !== 'us_bank_account') {
                return [
                    'success' => false,
                    'error' => 'Only US bank accounts require micro-deposit verification.',
                ];
            }

            if (!$paymentMethod->canAttemptVerification()) {
                return [
                    'success' => false,
                    'error' => 'This payment method cannot be verified. Maximum attempts reached or already verified.',
                ];
            }

            // Create a SetupIntent for micro-deposit verification
            // Note: US bank accounts must be verified before they can be attached to customers
            $setupIntent = SetupIntent::create([
                'payment_method' => $paymentMethod->stripe_payment_method_id,
                'payment_method_types' => ['us_bank_account'],
                'mandate_data' => [
                    'customer_acceptance' => [
                        'type' => 'offline',
                    ],
                ],
                'confirm' => true,
                'usage' => 'off_session',
                'automatic_payment_methods' => [
                    'enabled' => false,
                ],
            ]);

            // Update payment method status
            $paymentMethod->update([
                'verification_status' => 'pending_verification',
                'verification_attempts' => $paymentMethod->verification_attempts + 1,
                'stripe_verification_session_id' => $setupIntent->id,
                'verification_initiated_at' => now(),
                'verification_metadata' => [
                    'setup_intent_id' => $setupIntent->id,
                    'status' => $setupIntent->status,
                    'next_action' => $setupIntent->next_action,
                ],
            ]);

            return [
                'success' => true,
                'setup_intent' => $setupIntent,
                'verification_initiated' => true,
                'message' => 'Micro-deposit verification initiated. Deposits should appear within 1-2 business days.',
            ];
        } catch (\Exception $e) {
            Log::error('Micro-deposit verification failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to initiate verification: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify micro-deposits for bank account
     */
    public function verifyMicroDeposits(PaymentMethod $paymentMethod, array $amounts): array
    {
        try {
            if (!$paymentMethod->stripe_verification_session_id) {
                return [
                    'success' => false,
                    'error' => 'No verification session found for this payment method.',
                ];
            }

            // Get the SetupIntent
            $setupIntent = SetupIntent::retrieve($paymentMethod->stripe_verification_session_id);
            
            // Verify the micro-deposits
            $setupIntent->verifyMicrodeposits(['amounts' => $amounts]);

            // Attach verified payment method to customer
            $customerId = $this->getOrCreateCustomer($paymentMethod->user);
            $stripePaymentMethod = StripePaymentMethod::retrieve($paymentMethod->stripe_payment_method_id);
            
            // Attach to customer now that it's verified
            if (!$stripePaymentMethod->customer) {
                $stripePaymentMethod->attach(['customer' => $customerId]);
                
                Log::info('Verified bank account attached to customer', [
                    'payment_method_id' => $paymentMethod->id,
                    'stripe_payment_method_id' => $paymentMethod->stripe_payment_method_id,
                    'customer_id' => $customerId,
                    'user_id' => $paymentMethod->user->id,
                ]);
            }

            // Update payment method as verified
            $paymentMethod->update([
                'verification_status' => 'verified',
                'verified_at' => now(),
                'is_active' => true,
                'verification_metadata' => array_merge(
                    $paymentMethod->verification_metadata ?? [],
                    ['verified_at' => now()->toISOString()]
                ),
            ]);

            return [
                'success' => true,
                'verified' => true,
                'message' => 'Bank account verified successfully and attached to customer!',
            ];
        } catch (\Exception $e) {
            // Update verification status to failed
            $paymentMethod->update([
                'verification_status' => 'failed',
                'verification_metadata' => array_merge(
                    $paymentMethod->verification_metadata ?? [],
                    [
                        'error' => $e->getMessage(),
                        'failed_at' => now()->toISOString(),
                    ]
                ),
            ]);

            Log::error('Micro-deposit verification failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Verification failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Create Financial Connections session for instant verification
     */
    public function createFinancialConnectionsSession(PaymentMethod $paymentMethod): array
    {
        try {
            if ($paymentMethod->type !== 'us_bank_account') {
                return [
                    'success' => false,
                    'error' => 'Financial Connections is only available for US bank accounts.',
                ];
            }

            if (!$paymentMethod->canAttemptVerification()) {
                return [
                    'success' => false,
                    'error' => 'This payment method cannot be verified. Maximum attempts reached or already verified.',
                ];
            }

            // Create Financial Connections session
            $session = FinancialConnectionsSession::create([
                'account_holder' => [
                    'type' => 'customer',
                    'customer' => $this->getOrCreateCustomer($paymentMethod->user),
                ],
                'permissions' => ['payment_method', 'balances'],
                'filters' => [
                    'countries' => ['US'],
                ],
                'return_url' => $this->getReturnUrl($paymentMethod),
            ]);

            // Update payment method with session info
            $paymentMethod->update([
                'verification_status' => 'pending_verification',
                'verification_attempts' => $paymentMethod->verification_attempts + 1,
                'verification_method' => 'instant',
                'financial_connections_session_id' => $session->id,
                'verification_initiated_at' => now(),
                'financial_connections_metadata' => [
                    'session_id' => $session->id,
                    'status' => $session->livemode ? 'live' : 'test',
                    'created_at' => now()->toISOString(),
                ],
            ]);

            return [
                'success' => true,
                'session' => $session,
                'client_secret' => $session->client_secret,
                'verification_initiated' => true,
                'message' => 'Financial Connections session created. Please complete bank account linking.',
            ];
        } catch (\Exception $e) {
            Log::error('Financial Connections session creation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to create verification session: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Complete Financial Connections verification
     */
    public function completeFinancialConnectionsVerification(PaymentMethod $paymentMethod): array
    {
        try {
            if (!$paymentMethod->hasFinancialConnectionsSession()) {
                return [
                    'success' => false,
                    'error' => 'No Financial Connections session found for this payment method.',
                ];
            }

            // Retrieve the session
            $session = FinancialConnectionsSession::retrieve($paymentMethod->financial_connections_session_id);
            
            if (empty($session->accounts->data)) {
                return [
                    'success' => false,
                    'error' => 'No accounts were linked during the verification process.',
                ];
            }

            // Get the first linked account
            $account = $session->accounts->data[0];
            
            // Create payment method from the linked account
            $user = $paymentMethod->user;
            
            Log::info('Creating payment method from Financial Connections account', [
                'account_id' => $account->id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
            ]);
            
            $stripePaymentMethod = StripePaymentMethod::create([
                'type' => 'us_bank_account',
                'us_bank_account' => [
                    'financial_connections_account' => $account->id,
                ],
                'billing_details' => [
                    'name' => $user->name ?: 'Unknown',
                    'email' => $user->email,
                ],
            ]);

            // Attach to customer
            $customerId = $this->getOrCreateCustomer($paymentMethod->user);
            $stripePaymentMethod->attach(['customer' => $customerId]);

            // Update payment method as verified
            $paymentMethod->update([
                'stripe_payment_method_id' => $stripePaymentMethod->id,
                'verification_status' => 'verified',
                'verification_method_used' => 'instant',
                'verified_at' => now(),
                'is_active' => true,
                'financial_connections_account_id' => $account->id,
                'bank_name' => $account->institution_name ?? 'Unknown Bank',
                'last_four' => $stripePaymentMethod->us_bank_account->last4,
                'account_holder_type' => $stripePaymentMethod->us_bank_account->account_holder_type,
                'financial_connections_metadata' => array_merge(
                    $paymentMethod->financial_connections_metadata ?? [],
                    [
                        'account_id' => $account->id,
                        'institution_name' => $account->institution_name,
                        'verified_at' => now()->toISOString(),
                        'account_type' => $account->subcategory,
                    ]
                ),
            ]);

            Log::info('Financial Connections verification completed successfully', [
                'payment_method_id' => $paymentMethod->id,
                'stripe_payment_method_id' => $stripePaymentMethod->id,
                'financial_connections_account_id' => $account->id,
                'user_id' => $paymentMethod->user->id,
            ]);

            return [
                'success' => true,
                'verified' => true,
                'message' => 'Bank account verified successfully via Financial Connections!',
                'account' => $account,
            ];
        } catch (\Exception $e) {
            // Update verification status to failed
            $paymentMethod->update([
                'verification_status' => 'failed',
                'financial_connections_metadata' => array_merge(
                    $paymentMethod->financial_connections_metadata ?? [],
                    [
                        'error' => $e->getMessage(),
                        'failed_at' => now()->toISOString(),
                    ]
                ),
            ]);

            Log::error('Financial Connections verification failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Verification failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check Financial Connections session status
     */
    public function checkFinancialConnectionsSessionStatus(PaymentMethod $paymentMethod): array
    {
        try {
            if (!$paymentMethod->hasFinancialConnectionsSession()) {
                return [
                    'success' => false,
                    'error' => 'No Financial Connections session found for this payment method.',
                ];
            }

            // Retrieve the session
            $session = FinancialConnectionsSession::retrieve($paymentMethod->financial_connections_session_id);
            
            return [
                'success' => true,
                'session' => $session,
                'status' => $session->status,
                'accounts_count' => count($session->accounts->data ?? []),
                'is_complete' => !empty($session->accounts->data),
            ];
        } catch (\Exception $e) {
            Log::error('Financial Connections session status check failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to check session status: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Initiate bank account verification with automatic method selection
     */
    public function initiateVerification(PaymentMethod $paymentMethod, string $preferredMethod = 'automatic'): array
    {
        try {
            if ($paymentMethod->type !== 'us_bank_account') {
                return [
                    'success' => false,
                    'error' => 'Only US bank accounts require verification.',
                ];
            }

            if (!$paymentMethod->canAttemptVerification()) {
                return [
                    'success' => false,
                    'error' => 'This payment method cannot be verified. Maximum attempts reached or already verified.',
                ];
            }

            // Determine verification method
            $verificationMethod = $this->determineVerificationMethod($preferredMethod);
            
            if ($verificationMethod === 'instant') {
                return $this->createFinancialConnectionsSession($paymentMethod);
            } else {
                return $this->initiateMicroDepositVerification($paymentMethod);
            }
        } catch (\Exception $e) {
            Log::error('Verification initiation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to initiate verification: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Determine the best verification method to use
     */
    private function determineVerificationMethod(string $preferredMethod): string
    {
        // For now, we'll prioritize Financial Connections if available
        // In the future, you could add logic to check if Financial Connections is enabled
        // in your Stripe account or based on other business rules
        
        if ($preferredMethod === 'instant') {
            return 'instant';
        } elseif ($preferredMethod === 'microdeposits') {
            return 'microdeposits';
        } else {
            // Automatic selection - prefer instant verification
            return 'instant';
        }
    }

    /**
     * Delete payment method
     */
    public function deletePaymentMethod(PaymentMethod $paymentMethod): array
    {
        try {
            // Delete from Stripe
            $stripePaymentMethod = StripePaymentMethod::retrieve($paymentMethod->stripe_payment_method_id);
            $stripePaymentMethod->detach();

            // Delete from database
            $paymentMethod->delete();

            return [
                'success' => true,
                'message' => 'Payment method deleted successfully.',
            ];
        } catch (\Exception $e) {
            Log::error('Payment method deletion failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get the appropriate return URL for Financial Connections
     */
    private function getReturnUrl(PaymentMethod $paymentMethod): string
    {
        $baseUrl = config('app.url');
        
        // For local development, we need HTTPS for Financial Connections
        // You can either:
        // 1. Use ngrok or similar service to get HTTPS locally
        // 2. Set up local HTTPS with Laravel Valet/Herd
        // 3. For testing, use a placeholder that won't redirect but satisfies Stripe
        
        if (str_contains($baseUrl, 'localhost') || str_contains($baseUrl, '127.0.0.1')) {
            // For local development, we'll handle completion manually via polling
            // This is a valid HTTPS URL that satisfies Stripe's requirements
            $baseUrl = 'https://your-app.test'; // Change this to your local HTTPS domain
        }
        
        // Determine the correct route based on user type
        $user = $paymentMethod->user;
        if ($user->isAgent()) {
            return $baseUrl . '/agent/payment-methods/verify-complete/' . $paymentMethod->id;
        } else {
            return $baseUrl . '/company/payment-methods/verify-complete/' . $paymentMethod->id;
        }
    }

    /**
     * Get or create Stripe customer for user
     */
    private function getOrCreateCustomer(User $user): string
    {
        // Check if user has a stripe_id field or create one
        if (!$user->stripe_id) {
            $customer = Customer::create([
                'email' => $user->email,
                'name' => $user->name,
                'metadata' => [
                    'user_id' => $user->id,
                    'user_role' => $user->role,
                ],
            ]);
            
            // Update user with Stripe customer ID
            $user->update(['stripe_id' => $customer->id]);
            
            return $customer->id;
        }

        return $user->stripe_id;
    }

    /**
     * Handle webhook events
     */
    public function handleWebhook(array $payload): array
    {
        try {
            $event = $payload;

            switch ($event['type']) {
                case 'payment_intent.succeeded':
                    $this->handlePaymentSucceeded($event['data']['object']);
                    break;
                case 'payment_intent.payment_failed':
                    $this->handlePaymentFailed($event['data']['object']);
                    break;
                case 'transfer.created':
                    $this->handleTransferCreated($event['data']['object']);
                    break;
                case 'transfer.failed':
                    $this->handleTransferFailed($event['data']['object']);
                    break;
                case 'transfer.reversed':
                    $this->handleTransferReversed($event['data']['object']);
                    break;
                case 'account.updated':
                    $this->handleAccountUpdated($event['data']['object']);
                    break;
                case 'financial_connections.account.created':
                    $this->handleFinancialConnectionsAccountCreated($event['data']['object']);
                    break;
                case 'financial_connections.account.disconnected':
                    $this->handleFinancialConnectionsAccountDisconnected($event['data']['object']);
                    break;
                case 'financial_connections.session.completed':
                    $this->handleFinancialConnectionsSessionCompleted($event['data']['object']);
                    break;
                // Add more webhook handlers as needed
            }

            return ['success' => true];
        } catch (\Exception $e) {
            Log::error('Webhook handling failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function handlePaymentSucceeded(array $paymentIntent): void
    {
        $transaction = Transaction::where('stripe_payment_intent_id', $paymentIntent['id'])->first();
        if ($transaction) {
            $transaction->markAsCompleted();
            $transaction->invoice->markAsPaid();
        }
    }

    private function handlePaymentFailed(array $paymentIntent): void
    {
        $transaction = Transaction::where('stripe_payment_intent_id', $paymentIntent['id'])->first();
        if ($transaction) {
            $transaction->markAsFailed('Payment failed');
        }
    }

    private function handleTransferCreated(array $transfer): void
    {
        Log::info('Transfer created webhook received', [
            'transfer_id' => $transfer['id'],
            'amount' => $transfer['amount'],
            'destination' => $transfer['destination'],
        ]);

        // Update transaction with transfer information
        $invoiceId = $transfer['metadata']['invoice_id'] ?? null;
        if ($invoiceId) {
            $transaction = Transaction::where('invoice_id', $invoiceId)
                ->where('stripe_payment_intent_id', $transfer['metadata']['payment_intent_id'] ?? null)
                ->first();
            
            if ($transaction && !$transaction->stripe_transfer_id) {
                $transaction->update([
                    'stripe_transfer_id' => $transfer['id'],
                ]);
                
                Log::info('Transaction updated with transfer ID', [
                    'transaction_id' => $transaction->id,
                    'transfer_id' => $transfer['id'],
                ]);
            }
        }
    }

    private function handleTransferFailed(array $transfer): void
    {
        Log::error('Transfer failed webhook received', [
            'transfer_id' => $transfer['id'],
            'failure_code' => $transfer['failure_code'] ?? 'unknown',
            'failure_message' => $transfer['failure_message'] ?? 'No message provided',
        ]);

        $transaction = Transaction::where('stripe_transfer_id', $transfer['id'])->first();
        if ($transaction) {
            $failureReason = ($transfer['failure_code'] ?? 'unknown') . ': ' . ($transfer['failure_message'] ?? 'Transfer failed');
            $transaction->markAsTransferFailed($failureReason);
            
            Log::warning('Transaction marked as transfer_failed', [
                'transaction_id' => $transaction->id,
                'invoice_id' => $transaction->invoice_id,
                'reason' => $failureReason,
            ]);
        }
    }

    private function handleTransferReversed(array $transfer): void
    {
        Log::warning('Transfer reversed webhook received', [
            'transfer_id' => $transfer['id'],
            'amount' => $transfer['amount'],
        ]);

        $transaction = Transaction::where('stripe_transfer_id', $transfer['id'])->first();
        if ($transaction) {
            $transaction->update([
                'status' => 'transfer_failed',
                'notes' => 'Transfer was reversed',
            ]);
            
            // Also update invoice status back to pending
            if ($transaction->invoice) {
                $transaction->invoice->update(['status' => 'pending']);
            }
            
            Log::warning('Transaction and invoice updated due to transfer reversal', [
                'transaction_id' => $transaction->id,
                'invoice_id' => $transaction->invoice_id,
            ]);
        }
    }

    private function handleAccountUpdated(array $account): void
    {
        // Update user based on account ID
        $user = User::where('stripe_connect_account_id', $account['id'])->first();
        
        if ($user) {
            $user->update([
                'stripe_capabilities' => $account['capabilities'],
                'stripe_onboarding_complete' => $account['details_submitted'] && 
                                                $account['charges_enabled'] && 
                                                $account['payouts_enabled'],
            ]);
        }
    }

    private function handleFinancialConnectionsAccountCreated(array $account): void
    {
        Log::info('Financial Connections account created', [
            'account_id' => $account['id'],
            'institution_name' => $account['institution_name'] ?? 'Unknown',
        ]);
        
        // Note: The account creation is handled in the completeFinancialConnectionsVerification method
        // This webhook is mainly for logging and monitoring purposes
    }

    private function handleFinancialConnectionsAccountDisconnected(array $account): void
    {
        Log::info('Financial Connections account disconnected', [
            'account_id' => $account['id'],
        ]);
        
        // Find and update payment method if it exists
        $paymentMethod = PaymentMethod::where('financial_connections_account_id', $account['id'])->first();
        
        if ($paymentMethod) {
            $paymentMethod->update([
                'is_active' => false,
                'verification_status' => 'failed',
                'financial_connections_metadata' => array_merge(
                    $paymentMethod->financial_connections_metadata ?? [],
                    [
                        'disconnected_at' => now()->toISOString(),
                        'reason' => 'Account disconnected via webhook',
                    ]
                ),
            ]);
            
            Log::warning('Payment method deactivated due to Financial Connections account disconnection', [
                'payment_method_id' => $paymentMethod->id,
                'user_id' => $paymentMethod->user_id,
                'financial_connections_account_id' => $account['id'],
            ]);
        }
    }

    private function handleFinancialConnectionsSessionCompleted(array $session): void
    {
        Log::info('Financial Connections session completed', [
            'session_id' => $session['id'],
            'accounts_count' => count($session['accounts']['data'] ?? []),
        ]);
        
        // Find payment method by session ID
        $paymentMethod = PaymentMethod::where('financial_connections_session_id', $session['id'])->first();
        
        if ($paymentMethod && empty($session['accounts']['data'])) {
            // Session completed but no accounts were linked
            $paymentMethod->update([
                'verification_status' => 'failed',
                'financial_connections_metadata' => array_merge(
                    $paymentMethod->financial_connections_metadata ?? [],
                    [
                        'session_completed_at' => now()->toISOString(),
                        'error' => 'No accounts linked during session',
                    ]
                ),
            ]);
            
            Log::warning('Financial Connections session completed without linking accounts', [
                'payment_method_id' => $paymentMethod->id,
                'user_id' => $paymentMethod->user_id,
                'session_id' => $session['id'],
            ]);
        }
    }
} 