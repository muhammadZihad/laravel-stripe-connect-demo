<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\StripeService;
use App\Models\Company;
use App\Models\Agent;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StripeController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Start Stripe Connect onboarding
     */
    public function startOnboarding(Request $request)
    {
        $user = Auth::user();
        $entity = null;

        if ($user->isCompany()) {
            $entity = $user->company;
        } elseif ($user->isAgent()) {
            $entity = $user->agent;
        } else {
            return response()->json(['error' => 'Invalid user type for onboarding'], 400);
        }

        if (!$entity) {
            return response()->json(['error' => 'Profile not found'], 404);
        }

        $result = $this->stripeService->createOnboardingLink($entity);

        if ($result['success']) {
            return redirect($result['onboarding_url']);
        }

        return back()->with('error', 'Failed to start onboarding: ' . $result['error']);
    }

    /**
     * Handle Stripe Connect return
     */
    public function connectReturn(Request $request)
    {
        $user = Auth::user();
        $entity = $user->isCompany() ? $user->company : $user->agent;

        if ($entity) {
            $result = $this->stripeService->checkOnboardingStatus($entity);
            
            if ($result['success'] && $result['onboarding_complete']) {
                return redirect()->route($user->role . '.dashboard')
                    ->with('success', 'Stripe Connect onboarding completed successfully!');
            }
        }

        return redirect()->route($user->role . '.dashboard')
            ->with('warning', 'Onboarding is still in progress. Please complete all required steps.');
    }

    /**
     * Handle Stripe Connect refresh
     */
    public function connectRefresh(Request $request)
    {
        return $this->startOnboarding($request);
    }

    /**
     * Add payment method for entity (for super admin)
     */
    public function addPaymentMethod(Request $request)
    {
        $request->validate([
            'entity_type' => 'required|in:company,agent',
            'entity_id' => 'required|integer',
            'payment_method_data' => 'required|array',
        ]);

        $entityClass = $request->entity_type === 'company' ? Company::class : Agent::class;
        $entity = $entityClass::findOrFail($request->entity_id);

        $result = $this->stripeService->addPaymentMethod($entity, $request->payment_method_data);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Payment method added successfully',
                'payment_method' => $result['payment_method']
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error']
        ], 400);
    }

    /**
     * Process payment for invoice
     */
    public function processPayment(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
        ]);

        $invoice = Invoice::with(['company', 'agent'])->findOrFail($request->invoice_id);
        $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);

        // Ensure the payment method belongs to the company
        if ($paymentMethod->payable_type !== Company::class || 
            $paymentMethod->payable_id !== $invoice->company_id) {
            return response()->json(['error' => 'Invalid payment method for this invoice'], 400);
        }

        $result = $this->stripeService->createPaymentIntent($invoice, $paymentMethod);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'transaction' => $result['transaction'],
                'payment_intent' => $result['payment_intent']
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error']
        ], 400);
    }

    /**
     * Handle Stripe webhooks
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('cashier.webhook.secret');

        try {
            // Verify webhook signature
            \Stripe\Webhook::constructEvent(
                $request->getContent(),
                $sig_header,
                $endpoint_secret
            );

            $result = $this->stripeService->handleWebhook($payload);

            if ($result['success']) {
                return response()->json(['status' => 'success']);
            }

            return response()->json(['status' => 'error'], 400);

        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid webhook payload: ' . $e->getMessage());
            return response()->json(['status' => 'invalid_payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Invalid webhook signature: ' . $e->getMessage());
            return response()->json(['status' => 'invalid_signature'], 400);
        }
    }

    /**
     * Get onboarding status
     */
    public function getOnboardingStatus(Request $request)
    {
        $user = Auth::user();
        $entity = $user->isCompany() ? $user->company : $user->agent;

        if (!$entity) {
            return response()->json(['onboarding_complete' => false]);
        }

        $result = $this->stripeService->checkOnboardingStatus($entity);

        return response()->json([
            'onboarding_complete' => $result['onboarding_complete'] ?? false,
            'account_id' => $entity->stripe_connect_account_id,
        ]);
    }

    /**
     * Create onboarding link for super admin to help companies/agents
     */
    public function createOnboardingLinkForEntity(Request $request)
    {
        $request->validate([
            'entity_type' => 'required|in:company,agent',
            'entity_id' => 'required|integer',
        ]);

        $entityClass = $request->entity_type === 'company' ? Company::class : Agent::class;
        $entity = $entityClass::findOrFail($request->entity_id);

        $result = $this->stripeService->createOnboardingLink($entity);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'onboarding_url' => $result['onboarding_url']
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error']
        ], 400);
    }
}
