<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\StripeService;
use App\Models\Company;
use App\Models\Agent;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\User;
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

        if (!$user->isCompany() && !$user->isAgent()) {
            return response()->json(['error' => 'Invalid user type for onboarding'], 400);
        }

        $result = $this->stripeService->createOnboardingLink($user);

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

        $result = $this->stripeService->checkOnboardingStatus($user);
        
        if ($result['success'] && $result['onboarding_complete']) {
            return redirect()->route($user->role . '.dashboard')
                ->with('success', 'Stripe Connect onboarding completed successfully!');
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
     * Add payment method for user (for super admin)
     */
    public function addPaymentMethod(Request $request)
    {
        $request->validate([
            'entity_type' => 'required|in:company,agent',
            'entity_id' => 'required|integer',
            'payment_method_data' => 'required|array',
        ]);

        // Get the user from the entity
        if ($request->entity_type === 'company') {
            $company = Company::findOrFail($request->entity_id);
            $user = $company->user;
        } else {
            $agent = Agent::findOrFail($request->entity_id);
            $user = $agent->user;
        }

        $result = $this->stripeService->addPaymentMethod($user, $request->payment_method_data);

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

        $invoice = Invoice::with(['company.user', 'agent.user'])->findOrFail($request->invoice_id);
        $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);

        // Ensure the payment method belongs to the company user
        if ($paymentMethod->user_id !== $invoice->company->user_id) {
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

        Log::info('Stripe webhook received', ['payload' => $payload, 'sig_header' => $sig_header, 'endpoint_secret' => $endpoint_secret]);

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

        if (!$user->isCompany() && !$user->isAgent()) {
            return response()->json(['onboarding_complete' => false]);
        }

        $result = $this->stripeService->checkOnboardingStatus($user);

        return response()->json([
            'onboarding_complete' => $result['onboarding_complete'] ?? false,
            'account_id' => $user->stripe_connect_account_id,
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

        // Get the user from the entity
        if ($request->entity_type === 'company') {
            $company = Company::findOrFail($request->entity_id);
            $user = $company->user;
        } else {
            $agent = Agent::findOrFail($request->entity_id);
            $user = $agent->user;
        }

        $result = $this->stripeService->createOnboardingLink($user);

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

    /**
     * Show public invoice payment page (no auth required)
     */
    public function showPublicInvoicePayment(string $token)
    {
        $invoice = Invoice::where('payment_token', $token)
            ->with(['company', 'agent.user'])
            ->firstOrFail();

        // Validate token
        if (!$invoice->isPaymentTokenValid($token)) {
            if ($invoice->isPaid()) {
                return view('public.invoice-paid', compact('invoice'));
            }
            return view('public.invoice-expired');
        }

        // Get Stripe publishable key
        $stripeKey = config('cashier.key');

        return view('public.invoice-payment', compact('invoice', 'stripeKey'));
    }

    /**
     * Attach payment method for public invoice payment (card)
     */
    public function attachPaymentMethodToPublicInvoice(Request $request, string $token)
    {
        $invoice = Invoice::where('payment_token', $token)
            ->with(['company.user'])
            ->firstOrFail();

        // Validate token
        if (!$invoice->isPaymentTokenValid($token)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid or expired payment link.',
            ], 400);
        }

        $request->validate([
            'payment_method_id' => 'required|string|starts_with:pm_',
        ]);

        // Get the company user (payer for public invoice)
        $user = $invoice->company->user;

        // Attach payment method to user
        $result = $this->stripeService->attachPaymentMethod(
            $user, 
            $request->payment_method_id, 
            false // Not setting as default
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Payment method added successfully!',
                'payment_method' => $result['payment_method'],
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'],
        ], 400);
    }

    /**
     * Create Financial Connections session for public invoice payment (ACH)
     */
    public function createFinancialConnectionsSessionForPublicPayment(Request $request, string $token)
    {
        $invoice = Invoice::where('payment_token', $token)
            ->with(['company', 'agent'])
            ->firstOrFail();

        // Validate token
        if (!$invoice->isPaymentTokenValid($token)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid or expired payment link.',
            ], 400);
        }

        // Create a temporary guest customer for this payment only
        $result = $this->stripeService->createGuestFinancialConnectionsSession($invoice);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'client_secret' => $result['client_secret'],
                'session_id' => $result['session']->id,
                'customer_id' => $result['customer_id'],
                'message' => $result['message'],
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'],
        ], 400);
    }

    /**
     * Check Financial Connections session status for public invoice payment
     */
    public function checkPublicInvoiceFinancialConnectionsStatus(Request $request, string $token)
    {
        $invoice = Invoice::where('payment_token', $token)
            ->with(['company', 'agent'])
            ->firstOrFail();

        // Validate token
        if (!$invoice->isPaymentTokenValid($token)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid or expired payment link.',
            ], 400);
        }

        $request->validate([
            'session_id' => 'required|string',
            'customer_id' => 'required|string',
        ]);

        // Complete guest Financial Connections session
        $result = $this->stripeService->completeGuestFinancialConnectionsSession(
            $request->session_id, 
            $request->customer_id,
            $invoice
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'complete' => true,
                'message' => $result['message'],
                'payment_method_id' => $result['payment_method_id'],
            ]);
        }

        return response()->json([
            'success' => false,
            'complete' => false,
            'error' => $result['error'] ?? 'Failed to complete bank account addition.',
        ], 400);
    }

    /**
     * Process public invoice payment
     */
    public function processPublicInvoicePayment(Request $request, string $token)
    {
        $invoice = Invoice::where('payment_token', $token)
            ->with(['company.user', 'agent.user'])
            ->firstOrFail();

        // Validate token
        if (!$invoice->isPaymentTokenValid($token)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid or expired payment link.',
            ], 400);
        }

        $request->validate([
            'stripe_payment_method_id' => 'required|string|starts_with:pm_',
            'customer_id' => 'nullable|string|starts_with:cus_',
        ]);

        // Process guest payment directly with Stripe payment method ID
        // customer_id is optional (only needed for ACH if already created)
        $result = $this->stripeService->createGuestPaymentIntent(
            $invoice, 
            $request->stripe_payment_method_id,
            $request->customer_id
        );

        if ($result['success']) {
            // Invalidate payment token after successful payment
            $invoice->invalidatePaymentToken();

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully!',
                'transaction' => $result['transaction'],
                'payment_intent' => $result['payment_intent'],
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'],
        ], 400);
    }
}
