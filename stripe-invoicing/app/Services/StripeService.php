<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Agent;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\PaymentMethod;
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
     * Create Stripe Connect account for company or agent
     */
    public function createConnectAccount($entity, string $type = 'express'): array
    {
        try {
            $account = Account::create([
                'type' => $type,
                'country' => 'US', // You may want to make this configurable
                'email' => $entity->user->email,
                'capabilities' => [
                    'card_payments' => ['requested' => true],
                    'transfers' => ['requested' => true],
                ],
                'business_type' => $entity instanceof Company ? 'company' : 'individual',
                'company' => $entity instanceof Company ? [
                    'name' => $entity->company_name,
                    'phone' => $entity->phone,
                    'tax_id' => $entity->tax_id,
                ] : null,
                'individual' => $entity instanceof Agent ? [
                    'email' => $entity->user->email,
                    'first_name' => explode(' ', $entity->user->name)[0] ?? '',
                    'last_name' => explode(' ', $entity->user->name)[1] ?? '',
                ] : null,
            ]);

            // Update entity with Stripe account ID
            $entity->update([
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
    public function createOnboardingLink($entity): array
    {
        try {
            if (!$entity->stripe_connect_account_id) {
                $result = $this->createConnectAccount($entity);
                if (!$result['success']) {
                    return $result;
                }
            }

            $accountLink = AccountLink::create([
                'account' => $entity->stripe_connect_account_id,
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
     * Check onboarding status
     */
    public function checkOnboardingStatus($entity): array
    {
        try {
            if (!$entity->stripe_connect_account_id) {
                return [
                    'success' => false,
                    'onboarding_complete' => false,
                    'error' => 'No Stripe account found',
                ];
            }

            $account = Account::retrieve($entity->stripe_connect_account_id);
            
            $onboardingComplete = $account->details_submitted && 
                                 $account->charges_enabled && 
                                 $account->payouts_enabled;

            // Update entity status
            $entity->update([
                'stripe_onboarding_complete' => $onboardingComplete,
                'stripe_capabilities' => $account->capabilities,
            ]);

            return [
                'success' => true,
                'onboarding_complete' => $onboardingComplete,
                'account' => $account,
            ];
        } catch (\Exception $e) {
            Log::error('Stripe onboarding status check failed: ' . $e->getMessage());
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
            // Check if we're in demo mode (payment method starts with pm_demo_)
            $isDemoMode = str_starts_with($paymentMethod->stripe_payment_method_id, 'pm_demo_');
            
            if ($isDemoMode) {
                return $this->createDemoPayment($invoice, $paymentMethod, $adminCommission);
            }

            // Calculate amounts
            $totalAmount = $invoice->total_amount * 100; // Convert to cents
            $adminCommissionCents = $adminCommission * 100;
            $agentAmount = $totalAmount - $adminCommissionCents;

            $paymentIntent = PaymentIntent::create([
                'amount' => $totalAmount,
                'currency' => 'usd',
                'customer' => $this->getOrCreateCustomer($invoice->company),
                'payment_method' => $paymentMethod->stripe_payment_method_id,
                'confirmation_method' => 'manual',
                'confirm' => true,
                'transfer_data' => [
                    'destination' => $invoice->agent->stripe_connect_account_id,
                    'amount' => $agentAmount,
                ],
                'application_fee_amount' => $adminCommissionCents,
                'metadata' => [
                    'invoice_id' => $invoice->id,
                    'company_id' => $invoice->company_id,
                    'agent_id' => $invoice->agent_id,
                    'admin_commission' => $adminCommission,
                ],
            ]);

            // Create transaction record
            $transaction = Transaction::create([
                'transaction_id' => Transaction::generateTransactionId(),
                'invoice_id' => $invoice->id,
                'company_id' => $invoice->company_id,
                'agent_id' => $invoice->agent_id,
                'amount' => $invoice->total_amount,
                'admin_commission' => $adminCommission,
                'net_amount' => $invoice->total_amount - $adminCommission,
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
     * Create demo payment for testing purposes
     */
    private function createDemoPayment(Invoice $invoice, PaymentMethod $paymentMethod, float $adminCommission = 2.00): array
    {
        try {
            // Simulate successful payment
            $demoPaymentIntentId = 'pi_demo_' . time() . '_' . rand(1000, 9999);
            
            // Create transaction record
            $transaction = Transaction::create([
                'transaction_id' => Transaction::generateTransactionId(),
                'invoice_id' => $invoice->id,
                'company_id' => $invoice->company_id,
                'agent_id' => $invoice->agent_id,
                'amount' => $invoice->total_amount,
                'admin_commission' => $adminCommission,
                'net_amount' => $invoice->total_amount - $adminCommission,
                'type' => 'payment',
                'status' => 'completed',
                'stripe_payment_intent_id' => $demoPaymentIntentId,
                'stripe_transfer_id' => 'tr_demo_' . time() . '_' . rand(1000, 9999),
                'payment_method_type' => $paymentMethod->type,
                'stripe_metadata' => json_encode([
                    'demo' => true,
                    'invoice_id' => $invoice->id,
                    'company_id' => $invoice->company_id,
                    'agent_id' => $invoice->agent_id,
                    'admin_commission' => $adminCommission,
                    'processed_at' => now()->toISOString(),
                ]),
                'notes' => 'Demo payment processed successfully',
                'processed_at' => now(),
            ]);

            // Mark invoice as paid
            $invoice->update([
                'status' => 'paid',
                'paid_date' => now(),
                'stripe_payment_intent_id' => $demoPaymentIntentId,
            ]);

            $transaction->markAsCompleted();

            Log::info('Demo payment processed successfully', [
                'invoice_id' => $invoice->id,
                'transaction_id' => $transaction->id,
                'amount' => $invoice->total_amount,
            ]);

            return [
                'success' => true,
                'payment_intent' => (object) [
                    'id' => $demoPaymentIntentId,
                    'status' => 'succeeded',
                    'amount' => $invoice->total_amount * 100,
                    'currency' => 'usd',
                    'metadata' => [
                        'demo' => true,
                        'invoice_id' => $invoice->id,
                    ],
                ],
                'transaction' => $transaction,
                'demo_mode' => true,
            ];
        } catch (\Exception $e) {
            Log::error('Demo payment creation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Add payment method to company or agent
     */
    public function addPaymentMethod($entity, array $paymentMethodData): array
    {
        try {
            $customer = $this->getOrCreateCustomer($entity);
            
            $stripePaymentMethod = StripePaymentMethod::create([
                'type' => $paymentMethodData['type'],
                'card' => $paymentMethodData['type'] === 'card' ? $paymentMethodData['card'] : null,
                'bank_account' => $paymentMethodData['type'] === 'bank_account' ? $paymentMethodData['bank_account'] : null,
            ]);

            $stripePaymentMethod->attach(['customer' => $customer]);

            // Store in database
            $paymentMethod = $entity->paymentMethods()->create([
                'stripe_payment_method_id' => $stripePaymentMethod->id,
                'type' => $stripePaymentMethod->type,
                'brand' => $stripePaymentMethod->card->brand ?? null,
                'last_four' => $stripePaymentMethod->card->last4 ?? $stripePaymentMethod->bank_account->last4 ?? null,
                'exp_month' => $stripePaymentMethod->card->exp_month ?? null,
                'exp_year' => $stripePaymentMethod->card->exp_year ?? null,
                'bank_name' => $stripePaymentMethod->bank_account->bank_name ?? null,
                'account_holder_type' => $stripePaymentMethod->bank_account->account_holder_type ?? null,
                'is_default' => $entity->paymentMethods()->count() === 0, // First payment method is default
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
     * Attach existing payment method to entity
     */
    public function attachPaymentMethod($entity, string $paymentMethodId, bool $isDefault = false): array
    {
        try {
            // Get or create customer for entity
            $customer = $this->getOrCreateCustomer($entity);
            
            // Retrieve the payment method from Stripe
            $stripePaymentMethod = StripePaymentMethod::retrieve($paymentMethodId);
            
            // Handle US bank account verification requirement
            if ($stripePaymentMethod->type === 'us_bank_account') {
                // Store as unverified and require verification
                return $this->storeUnverifiedBankAccount($entity, $stripePaymentMethod, $isDefault);
            }
            
            // Attach to customer if not already attached (for cards)
            if (!$stripePaymentMethod->customer) {
                $stripePaymentMethod->attach(['customer' => $customer]);
            }

            // Set as default if requested or if it's the first payment method
            $isFirstPaymentMethod = $entity->paymentMethods()->count() === 0;
            $shouldSetDefault = $isDefault || $isFirstPaymentMethod;

            // If setting as default, unset other defaults
            if ($shouldSetDefault) {
                $entity->paymentMethods()->update(['is_default' => false]);
            }

            // Store in database
            $paymentMethod = $entity->paymentMethods()->create([
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
                return $this->handleBankAccountVerification($entity, $paymentMethodId, $isDefault, $e);
            }
            
            Log::error('Payment method attachment failed: ' . $e->getMessage(), [
                'entity_type' => get_class($entity),
                'entity_id' => $entity->id,
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
    private function storeUnverifiedBankAccount($entity, $stripePaymentMethod, bool $isDefault = false): array
    {
        try {
            // Set as default if requested or if it's the first payment method
            $isFirstPaymentMethod = $entity->paymentMethods()->count() === 0;
            $shouldSetDefault = $isDefault || $isFirstPaymentMethod;

            // If setting as default, unset other defaults
            if ($shouldSetDefault) {
                $entity->paymentMethods()->update(['is_default' => false]);
            }

            // Store bank account in verification_required state
            $paymentMethod = $entity->paymentMethods()->create([
                'stripe_payment_method_id' => $stripePaymentMethod->id,
                'type' => 'us_bank_account',
                'brand' => null,
                'last_four' => $stripePaymentMethod->us_bank_account->last4,
                'exp_month' => null,
                'exp_year' => null,
                'bank_name' => $stripePaymentMethod->us_bank_account->bank_name,
                'account_holder_type' => $stripePaymentMethod->us_bank_account->account_holder_type,
                'is_default' => false, // Don't set as default until verified
                'is_active' => false, // Not active until verified
                'verification_status' => 'verification_required',
                'stripe_metadata' => $stripePaymentMethod->metadata->toArray(),
            ]);

            Log::info('Bank account stored pending verification', [
                'entity_type' => get_class($entity),
                'entity_id' => $entity->id,
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
            Log::error('Unverified bank account storage failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to store bank account: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle bank account verification requirement
     */
    private function handleBankAccountVerification($entity, string $paymentMethodId, bool $isDefault, \Exception $originalException): array
    {
        try {
            // Retrieve the payment method details for demo purposes
            $stripePaymentMethod = StripePaymentMethod::retrieve($paymentMethodId);
            
            // Create demo entry since verification is complex for demo
            return $this->createDemoBankAccount($entity, $stripePaymentMethod, $isDefault);
            
        } catch (\Exception $e) {
            Log::error('Bank account verification handling failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'Bank account verification required. In a production environment, you would need to complete ACH verification before using this payment method.',
                'verification_required' => true,
                'original_error' => $originalException->getMessage(),
            ];
        }
    }

    /**
     * Initiate micro-deposit verification for a bank account
     */
    public function initiateMicroDepositVerification($paymentMethod): array
    {
        try {
            // Check if payment method is eligible for verification
            if ($paymentMethod->type !== 'us_bank_account') {
                return [
                    'success' => false,
                    'error' => 'Only US bank accounts can be verified with micro-deposits.',
                ];
            }

            if ($paymentMethod->verification_status === 'verified') {
                return [
                    'success' => false,
                    'error' => 'This payment method is already verified.',
                ];
            }

            // Check verification attempts limit
            if ($paymentMethod->verification_attempts >= 3) {
                return [
                    'success' => false,
                    'error' => 'Maximum verification attempts exceeded. Please contact support.',
                ];
            }

            // Get or create customer for the payment method owner
            $entity = $paymentMethod->payable;
            $customer = $this->getOrCreateCustomer($entity);

            // Create a SetupIntent for micro-deposit verification
            $setupIntent = SetupIntent::create([
                'customer' => $customer,
                'payment_method' => $paymentMethod->stripe_payment_method_id,
                'payment_method_types' => ['us_bank_account'],
                'confirm' => true,
                'usage' => 'off_session',
            ]);

            // Update local payment method record
            $paymentMethod->update([
                'verification_status' => 'pending_verification',
                'verification_attempts' => $paymentMethod->verification_attempts + 1,
                'stripe_verification_session_id' => $setupIntent->id,
                'verification_initiated_at' => now(),
                'verification_metadata' => [
                    'setup_intent_id' => $setupIntent->id,
                    'initiated_at' => now()->toISOString(),
                    'attempt_number' => $paymentMethod->verification_attempts + 1,
                ],
            ]);

            Log::info('Micro-deposit verification initiated', [
                'payment_method_id' => $paymentMethod->id,
                'stripe_payment_method_id' => $paymentMethod->stripe_payment_method_id,
                'setup_intent_id' => $setupIntent->id,
                'attempt_number' => $paymentMethod->verification_attempts,
            ]);

            return [
                'success' => true,
                'setup_intent' => $setupIntent,
                'message' => 'Micro-deposits have been sent to your bank account. This typically takes 1-2 business days.',
                'estimated_arrival' => 'Micro-deposits should arrive within 1-2 business days.',
            ];

        } catch (\Exception $e) {
            Log::error('Micro-deposit verification initiation failed: ' . $e->getMessage(), [
                'payment_method_id' => $paymentMethod->id,
                'stripe_payment_method_id' => $paymentMethod->stripe_payment_method_id,
            ]);

            return [
                'success' => false,
                'error' => 'Failed to initiate verification: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify micro-deposit amounts
     */
    public function verifyMicroDepositAmounts($paymentMethod, array $amounts): array
    {
        try {
            // Check if payment method has a verification session
            if (!$paymentMethod->stripe_verification_session_id) {
                return [
                    'success' => false,
                    'error' => 'No verification session found. Please initiate verification first.',
                ];
            }

            if ($paymentMethod->verification_status === 'verified') {
                return [
                    'success' => false,
                    'error' => 'This payment method is already verified.',
                ];
            }

            // Verify the amounts with Stripe using SetupIntent
            $setupIntent = SetupIntent::retrieve($paymentMethod->stripe_verification_session_id);
            
            // Use the verify microdeposits method on SetupIntent
            $verificationResult = $setupIntent->verifyMicrodeposits([
                'amounts' => $amounts
            ]);

            // Check verification status
            if ($verificationResult->status === 'succeeded') {
                // Update payment method as verified and active
                $paymentMethod->update([
                    'verification_status' => 'verified',
                    'is_active' => true,
                    'verified_at' => now(),
                    'verification_metadata' => array_merge(
                        $paymentMethod->verification_metadata ?? [],
                        [
                            'verified_at' => now()->toISOString(),
                            'setup_intent_status' => $verificationResult->status,
                        ]
                    ),
                ]);

                // Set as default if it's the first active payment method
                $entity = $paymentMethod->payable;
                if (!$entity->paymentMethods()->where('is_default', true)->where('is_active', true)->exists()) {
                    $paymentMethod->update(['is_default' => true]);
                }

                Log::info('Micro-deposit verification successful', [
                    'payment_method_id' => $paymentMethod->id,
                    'stripe_payment_method_id' => $paymentMethod->stripe_payment_method_id,
                    'setup_intent_id' => $verificationResult->id,
                ]);

                return [
                    'success' => true,
                    'message' => 'Bank account verified successfully! You can now use this payment method.',
                    'payment_method' => $paymentMethod->fresh(),
                ];
            } else {
                // Verification failed
                $paymentMethod->update([
                    'verification_status' => 'failed',
                    'verification_metadata' => array_merge(
                        $paymentMethod->verification_metadata ?? [],
                        [
                            'failed_at' => now()->toISOString(),
                            'setup_intent_status' => $verificationResult->status,
                            'failure_reason' => 'Incorrect micro-deposit amounts',
                        ]
                    ),
                ]);

                return [
                    'success' => false,
                    'error' => 'Verification failed. The amounts you entered do not match our records.',
                ];
            }

        } catch (\Exception $e) {
            Log::error('Micro-deposit verification failed: ' . $e->getMessage(), [
                'payment_method_id' => $paymentMethod->id,
                'stripe_payment_method_id' => $paymentMethod->stripe_payment_method_id,
                'amounts' => $amounts,
            ]);

            // Update verification status to failed
            $paymentMethod->update([
                'verification_status' => 'failed',
                'verification_metadata' => array_merge(
                    $paymentMethod->verification_metadata ?? [],
                    [
                        'failed_at' => now()->toISOString(),
                        'error' => $e->getMessage(),
                    ]
                ),
            ]);

            return [
                'success' => false,
                'error' => 'Verification failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get or create Stripe customer for entity
     */
    private function getOrCreateCustomer($entity): string
    {
        // Check if entity has a stripe_customer_id field or create one
        if (!$entity->stripe_id ?? null) {
            $customer = Customer::create([
                'email' => $entity->email,
                'name' => $entity->name,
                'metadata' => [
                    'user_id' => $entity->id,
                ],
            ]);

            // You might want to add stripe_id to your models
            $entity->update(['stripe_id' => $customer->id]);
            
            return $customer->id;
        }

        return $entity->stripe_id;
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
        // Update company or agent based on account ID
        $company = Company::where('stripe_connect_account_id', $account['id'])->first();
        if ($company) {
            $company->update([
                'stripe_capabilities' => $account['capabilities'],
                'stripe_onboarding_complete' => $account['details_submitted'] && 
                                                $account['charges_enabled'] && 
                                                $account['payouts_enabled'],
            ]);
            return;
        }

        $agent = Agent::where('stripe_connect_account_id', $account['id'])->first();
        if ($agent) {
            $agent->update([
                'stripe_capabilities' => $account['capabilities'],
                'stripe_onboarding_complete' => $account['details_submitted'] && 
                                                $account['charges_enabled'] && 
                                                $account['payouts_enabled'],
            ]);
        }
    }
} 