<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\PaymentMethod;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;

class CompanyController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Company Dashboard
     */
    public function dashboard()
    {
        $company = Auth::user()->company;
        
        $stats = [
            'total_agents' => $company->agents()->count(),
            'active_agents' => $company->agents()->where('is_active', true)->count(),
            'total_invoices' => $company->invoices()->count(),
            'pending_invoices' => $company->invoices()->where('status', 'pending')->count(),
            'paid_invoices' => $company->invoices()->where('status', 'paid')->count(),
            'overdue_invoices' => $company->invoices()
                ->where('status', '!=', 'paid')
                ->where('due_date', '<', now())
                ->count(),
            'total_revenue' => $company->transactions()->where('status', 'completed')->sum('amount'),
            'pending_amount' => $company->invoices()->where('status', 'pending')->sum('total_amount'),
        ];

        $recentInvoices = $company->invoices()
            ->with('agent.user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentTransactions = $company->transactions()
            ->with(['invoice', 'agent.user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $stripeOnboardingComplete = Auth::user()->isStripeOnboardingComplete();

        return view('company.dashboard', compact(
            'company', 'stats', 'recentInvoices', 'recentTransactions', 'stripeOnboardingComplete'
        ));
    }

    /**
     * Agents Management
     */
    public function agents()
    {
        $company = Auth::user()->company;
        $agents = $company->agents()
            ->with('user')
            ->withCount(['invoices', 'transactions'])
            ->paginate(15);

        return view('company.agents.index', compact('agents'));
    }

    public function showAgent(Agent $agent)
    {
        $agent->load(['user', 'invoices', 'transactions', 'paymentMethods']);
        
        $stats = [
            'total_invoices' => $agent->invoices->count(),
            'pending_invoices' => $agent->invoices->where('status', 'pending')->count(),
            'paid_invoices' => $agent->invoices->where('status', 'paid')->count(),
            'total_earned' => $agent->transactions->where('status', 'completed')->sum('net_amount'),
        ];

        return view('company.agents.show', compact('agent', 'stats'));
    }

    public function editAgent(Agent $agent)
    {
        return view('company.agents.edit', compact('agent'));
    }

    public function updateAgent(Request $request, Agent $agent)
    {
        $request->validate([
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'department' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $agent->update($request->all());

        return redirect()->route('company.agents.show', $agent)
            ->with('success', 'Agent updated successfully!');
    }

    /**
     * Invoices Management
     */
    public function invoices()
    {
        $company = Auth::user()->company;
        $invoices = $company->invoices()
            ->with('agent.user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('company.invoices.index', compact('invoices'));
    }

    public function showInvoice(Invoice $invoice)
    {
        $invoice->load(['agent.user', 'transactions']);
        
        return view('company.invoices.show', compact('invoice'));
    }

    public function createInvoice()
    {
        $company = Auth::user()->company;
        $agents = $company->agents()->with('user')->where('is_active', true)->get();
        
        return view('company.invoices.create', compact('agents'));
    }

    public function storeInvoice(Request $request)
    {
        $company = Auth::user()->company;
        
        $request->validate([
            'agent_id' => 'required|exists:agents,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'due_date' => 'required|date|after:today',
            'invoice_items' => 'nullable|array',
        ]);

        // Ensure agent belongs to this company
        $agent = Agent::where('id', $request->agent_id)
            ->where('company_id', $company->id)
            ->firstOrFail();

        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'company_id' => $company->id,
            'agent_id' => $request->agent_id,
            'title' => $request->title,
            'description' => $request->description,
            'amount' => $request->amount,
            'tax_amount' => $request->tax_amount ?? 0,
            'total_amount' => $request->amount + ($request->tax_amount ?? 0),
            'due_date' => $request->due_date,
            'invoice_items' => $request->invoice_items,
            'status' => 'pending',
        ]);

        return redirect()->route('company.invoices.show', $invoice)
            ->with('success', 'Invoice created successfully!');
    }

    public function editInvoice(Invoice $invoice)
    {
        $company = Auth::user()->company;
        $agents = $company->agents()->with('user')->where('is_active', true)->get();
        
        return view('company.invoices.edit', compact('invoice', 'agents'));
    }

    public function updateInvoice(Request $request, Invoice $invoice)
    {
        // Only allow updates if invoice is not paid
        if ($invoice->isPaid()) {
            return back()->with('error', 'Cannot update a paid invoice.');
        }

        $request->validate([
            'agent_id' => 'required|exists:agents,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'due_date' => 'required|date',
            'invoice_items' => 'nullable|array',
        ]);

        $invoice->update([
            'agent_id' => $request->agent_id,
            'title' => $request->title,
            'description' => $request->description,
            'amount' => $request->amount,
            'tax_amount' => $request->tax_amount ?? 0,
            'total_amount' => $request->amount + ($request->tax_amount ?? 0),
            'due_date' => $request->due_date,
            'invoice_items' => $request->invoice_items,
        ]);

        return redirect()->route('company.invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully!');
    }

    /**
     * Transactions
     */
    public function transactions()
    {
        $company = Auth::user()->company;
        $transactions = $company->transactions()
            ->with(['invoice', 'agent.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('company.transactions.index', compact('transactions'));
    }

    public function showTransaction(Transaction $transaction)
    {
        $transaction->load(['invoice', 'agent.user']);
        
        return view('company.transactions.show', compact('transaction'));
    }

    /**
     * Payment Methods
     */
    public function paymentMethods()
    {
        $user = Auth::user();
        $paymentMethods = $user->paymentMethods()->get();

        return view('company.payment-methods.index', compact('paymentMethods'));
    }

    public function addPaymentMethod()
    {
        return view('company.payment-methods.create');
    }

    /**
     * Create Financial Connections session for adding bank account
     */
    public function createFinancialConnectionsSession(Request $request)
    {
        $user = Auth::user();
        
        $result = $this->stripeService->createFinancialConnectionsSessionForAddition($user);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'client_secret' => $result['client_secret'],
                'session_id' => $result['session']->id, // Include session ID for polling
                'message' => $result['message'],
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'],
        ], 400);
    }

    /**
     * Check Financial Connections session status for addition
     */
    public function checkFinancialConnectionsAdditionStatus(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'session_id' => 'required|string',
        ]);

        $result = $this->stripeService->completeFinancialConnectionsBankAddition($user, $request->session_id);

        return response()->json([
            'success' => $result['success'],
            'complete' => $result['success'],
            'message' => $result['success'] ? $result['message'] : $result['error'],
            'count' => $result['count'] ?? 0,
            'redirect' => route('company.payment-methods'),
        ]);
    }

    /**
     * Complete Financial Connections bank account addition
     */
    public function completeFinancialConnectionsAddition(Request $request)
    {
        $user = Auth::user();
        
        // Get session_id from query parameter for return URL
        $sessionId = $request->query('session_id') ?? $request->input('session_id');
        
        if (!$sessionId) {
            return redirect()->route('company.payment-methods')
                ->with('error', 'Missing session ID for Financial Connections completion.');
        }

        $result = $this->stripeService->completeFinancialConnectionsBankAddition($user, $sessionId);

        if ($result['success']) {
            return redirect()->route('company.payment-methods')
                ->with('success', $result['message']);
        }

        return redirect()->route('company.payment-methods')
            ->with('error', $result['error']);
    }

    public function storePaymentMethod(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required|string|starts_with:pm_',
            'is_default' => 'sometimes|boolean',
        ]);

        $user = Auth::user();
        $result = $this->stripeService->attachPaymentMethod($user, $request->payment_method_id, $request->boolean('is_default', false));

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

    public function setDefaultPaymentMethod(PaymentMethod $paymentMethod)
    {
        $user = Auth::user();
        
        // Ensure payment method belongs to this user
        if ($paymentMethod->user_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $paymentMethod->setAsDefault();

        return back()->with('success', 'Default payment method updated successfully!');
    }

    public function deletePaymentMethod(PaymentMethod $paymentMethod)
    {
        $user = Auth::user();
        
        // Ensure payment method belongs to this user
        if ($paymentMethod->user_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $result = $this->stripeService->deletePaymentMethod($paymentMethod);

        if ($result['success']) {
            return back()->with('success', 'Payment method deleted successfully!');
        }

        return back()->with('error', 'Failed to delete payment method: ' . $result['error']);
    }

    /**
     * Initiate micro-deposit verification (legacy method - deprecated)
     */
    public function initiateMicroDepositVerification(Request $request, PaymentMethod $paymentMethod)
    {
        $user = Auth::user();
        
        // Ensure this payment method belongs to the current user
        if ($paymentMethod->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized access to payment method.',
            ], 403);
        }

        $result = $this->stripeService->initiateMicroDepositVerification($paymentMethod);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'],
        ], 400);
    }

    /**
     * Show micro-deposit verification form
     */
    public function showVerifyForm(PaymentMethod $paymentMethod)
    {
        $user = Auth::user();
        
        // Ensure this payment method belongs to the current user
        if ($paymentMethod->user_id !== $user->id) {
            abort(403, 'Unauthorized access to payment method.');
        }

        // Check if verification is available
        if ($paymentMethod->isVerified()) {
            return redirect()->route('company.payment-methods')
                ->with('success', 'This payment method is already verified.');
        }

        // Only allow verification for US bank accounts
        if ($paymentMethod->type !== 'us_bank_account') {
            return redirect()->route('company.payment-methods')
                ->with('error', 'Only US bank accounts require verification.');
        }

        // Check if verification attempts are exhausted
        if (!$paymentMethod->canAttemptVerification()) {
            return redirect()->route('company.payment-methods')
                ->with('error', 'Maximum verification attempts reached for this payment method.');
        }

        return view('company.payment-methods.verify', compact('paymentMethod'));
    }

    /**
     * Initiate verification with method selection
     */
    public function initiateVerification(Request $request, PaymentMethod $paymentMethod)
    {
        $user = Auth::user();
        
        // Ensure this payment method belongs to the current user
        if ($paymentMethod->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized access to payment method.',
            ], 403);
        }

        $request->validate([
            'verification_method' => 'required|in:instant,microdeposits,automatic',
        ]);

        $result = $this->stripeService->initiateVerification($paymentMethod, $request->verification_method);

        if ($result['success']) {
            $response = [
                'success' => true,
                'message' => $result['message'],
            ];

            // If it's Financial Connections, return the client secret
            if (isset($result['client_secret'])) {
                $response['client_secret'] = $result['client_secret'];
                $response['verification_method'] = 'instant';
            } else {
                $response['redirect'] = route('company.payment-methods');
            }

            return response()->json($response);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'],
        ], 400);
    }

    /**
     * Check Financial Connections session status (for polling in local development)
     */
    public function checkFinancialConnectionsStatus(PaymentMethod $paymentMethod)
    {
        $user = Auth::user();
        
        // Ensure this payment method belongs to the current user
        if ($paymentMethod->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized access to payment method.',
            ], 403);
        }

        $result = $this->stripeService->checkFinancialConnectionsSessionStatus($paymentMethod);

        if ($result['success'] && $result['is_complete']) {
            // Session is complete, try to complete verification
            $completionResult = $this->stripeService->completeFinancialConnectionsVerification($paymentMethod);
            return response()->json([
                'success' => $completionResult['success'],
                'complete' => true,
                'message' => $completionResult['success'] ? $completionResult['message'] : $completionResult['error'],
                'redirect' => route('company.payment-methods'),
            ]);
        }

        return response()->json([
            'success' => $result['success'],
            'complete' => false,
            'status' => $result['status'] ?? 'unknown',
            'message' => $result['error'] ?? 'Session not yet complete',
        ]);
    }

    /**
     * Complete Financial Connections verification
     */
    public function completeFinancialConnectionsVerification(PaymentMethod $paymentMethod)
    {
        $user = Auth::user();
        
        // Ensure this payment method belongs to the current user
        if ($paymentMethod->user_id !== $user->id) {
            return redirect()->route('company.payment-methods')
                ->with('error', 'Unauthorized access to payment method.');
        }

        $result = $this->stripeService->completeFinancialConnectionsVerification($paymentMethod);

        if ($result['success']) {
            return redirect()->route('company.payment-methods')
                ->with('success', $result['message']);
        }

        return redirect()->route('company.payment-methods')
            ->with('error', $result['error']);
    }

    /**
     * Verify micro-deposit amounts
     */
    public function verifyMicroDeposits(Request $request, PaymentMethod $paymentMethod)
    {
        $user = Auth::user();
        
        // Ensure this payment method belongs to the current user
        if ($paymentMethod->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized access to payment method.',
            ], 403);
        }

        $request->validate([
            'amount_1' => 'required|numeric|min:0|max:1',
            'amount_2' => 'required|numeric|min:0|max:1',
        ]);

        // Convert amounts to cents (Stripe expects amounts in cents)
        $amounts = [
            intval($request->amount_1 * 100), // Convert dollars to cents
            intval($request->amount_2 * 100), // Convert dollars to cents
        ];

        $result = $this->stripeService->verifyMicroDeposits($paymentMethod, $amounts);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'redirect' => route('company.payment-methods'),
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'],
        ], 400);
    }

    /**
     * Company Profile
     */
    public function profile()
    {
        $company = Auth::user()->company;
        
        return view('company.profile', compact('company'));
    }

    public function updateProfile(Request $request)
    {
        $company = Auth::user()->company;
        
        $request->validate([
            'company_name' => 'required|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url',
            'tax_id' => 'nullable|string|max:50',
        ]);

        $company->update($request->all());

        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Reports
     */
    public function reports()
    {
        $company = Auth::user()->company;
        
        $monthlyStats = $company->transactions()
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count, SUM(amount) as total')
            ->where('status', 'completed')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        $agentPerformance = $company->agents()
            ->withSum(['transactions' => function($query) {
                $query->where('status', 'completed');
            }], 'net_amount')
            ->withCount(['invoices', 'transactions'])
            ->get();

        return view('company.reports', compact('monthlyStats', 'agentPerformance'));
    }

    /**
     * Show payment form for invoice
     */
    public function showPayInvoice(Invoice $invoice)
    {
        $company = Auth::user()->company;
        
        // Ensure invoice belongs to this company
        if ($invoice->company_id !== $company->id) {
            abort(403, 'Unauthorized.');
        }

        // Check if invoice is already paid
        if ($invoice->status === 'paid') {
            return redirect()->route('company.invoices.show', $invoice)
                ->with('error', 'This invoice has already been paid.');
        }

        // Get available payment methods for the user
        $paymentMethods = Auth::user()->paymentMethods()
            ->where('is_active', true)
            ->get();

        return view('company.invoices.pay', compact('invoice', 'paymentMethods'));
    }

    /**
     * Process payment for invoice
     */
    public function payInvoice(Request $request, Invoice $invoice)
    {
        $company = Auth::user()->company;
        
        // Ensure invoice belongs to this company
        if ($invoice->company_id !== $company->id) {
            abort(403, 'Unauthorized.');
        }

        // Check if invoice is already paid
        if ($invoice->status === 'paid') {
            return back()->with('error', 'This invoice has already been paid.');
        }

        $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
        ]);

        $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);

        // Ensure the payment method belongs to the current user
        if ($paymentMethod->user_id !== Auth::user()->id) {
            return back()->with('error', 'Invalid payment method for this invoice.');
        }

        $result = $this->stripeService->createPaymentIntent($invoice, $paymentMethod);

        if ($result['success']) {
            return redirect()->route('company.invoices.show', $invoice)
                ->with('success', 'Payment processed successfully!');
        }

        return back()->with('error', 'Payment failed: ' . $result['error']);
    }

    /**
     * Send invoice via email
     */
    public function sendInvoice(Request $request, Invoice $invoice)
    {
        $company = Auth::user()->company;
        
        // Ensure invoice belongs to this company
        if ($invoice->company_id !== $company->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized.',
            ], 403);
        }

        // Check if invoice is already paid
        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'error' => 'Cannot send payment link for a paid invoice.',
            ], 400);
        }

        $request->validate([
            'email' => 'required|email',
        ]);

        // Generate or regenerate payment token
        $token = $invoice->generatePaymentToken();
        $paymentUrl = $invoice->getPaymentUrl();

        // Update payment email
        $invoice->update(['payment_email' => $request->email]);

        try {
            // Send email
            \Mail::to($request->email)->send(new \App\Mail\InvoicePaymentMail($invoice, $paymentUrl));

            return response()->json([
                'success' => true,
                'message' => 'Invoice sent successfully to ' . $request->email,
                'payment_url' => $paymentUrl,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send invoice email: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to send email. Please try again later.',
            ], 500);
        }
    }
}
