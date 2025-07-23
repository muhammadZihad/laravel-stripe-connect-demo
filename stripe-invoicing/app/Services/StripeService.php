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
            // Get agent user for Connect account validation
            $agentUser = $invoice->agent->user;
            
            // Check if agent has completed Stripe Connect onboarding
            if (!$agentUser->stripe_connect_account_id) {
                return [
                    'success' => false,
                    'error' => 'Agent has not completed Stripe Connect onboarding. Please contact the agent to complete their account setup.',
                ];
            }

            // Verify agent's onboarding status
            $onboardingCheck = $this->checkOnboardingStatus($agentUser);
            if (!$onboardingCheck['success'] || !$onboardingCheck['onboarding_complete']) {
                return [
                    'success' => false,
                    'error' => 'Agent\'s Stripe Connect account is not fully set up. Please ask the agent to complete their onboarding process.',
                ];
            }

            // Calculate amounts
            $totalAmount = $invoice->total_amount * 100; // Convert to cents
            
            // Calculate application fee: 10% of total but between $1-$4
            $applicationFeeFloat = $invoice->total_amount * 0.1; // 10% of total
            $applicationFeeFloat = max(1.00, min(4.00, $applicationFeeFloat)); // Constrain between $1-$4
            $adminCommissionCents = round($applicationFeeFloat * 100); // Convert to cents

            $paymentIntent = PaymentIntent::create([
                'amount' => $totalAmount,
                'currency' => 'usd',
                'customer' => $this->getOrCreateCustomer($paymentMethod->user),
                'payment_method' => $paymentMethod->stripe_payment_method_id,
                'confirm' => true,
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never',
                ],
                'transfer_data' => [
                    'destination' => $agentUser->stripe_connect_account_id,
                ],
                'application_fee_amount' => $adminCommissionCents,
                'metadata' => [
                    'invoice_id' => $invoice->id,
                    'company_id' => $invoice->company_id,
                    'agent_id' => $invoice->agent_id,
                    'admin_commission' => $applicationFeeFloat,
                ],
            ]);

            // Create transaction record
            $transaction = Transaction::create([
                'transaction_id' => Transaction::generateTransactionId(),
                'invoice_id' => $invoice->id,
                'company_id' => $invoice->company_id,
                'agent_id' => $invoice->agent_id,
                'amount' => $invoice->total_amount,
                'admin_commission' => $applicationFeeFloat,
                'net_amount' => $invoice->total_amount - $applicationFeeFloat,
                'type' => 'payment',
                'status' => $paymentIntent->status === 'succeeded' ? 'completed' : 'pending',
                'stripe_payment_intent_id' => $paymentIntent->id,
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
            Log::error('Payment intent creation failed: ' . $e->getMessage());
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
                case 'account.updated':
                    $this->handleAccountUpdated($event['data']['object']);
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
} 