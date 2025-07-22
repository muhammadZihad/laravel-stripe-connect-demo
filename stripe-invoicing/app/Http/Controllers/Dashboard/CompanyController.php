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

        $stripeOnboardingComplete = $company->isStripeOnboardingComplete();

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
        $company = Auth::user()->company;
        $paymentMethods = $company->paymentMethods()->get();

        return view('company.payment-methods.index', compact('paymentMethods'));
    }

    public function addPaymentMethod()
    {
        return view('company.payment-methods.create');
    }

    public function storePaymentMethod(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required|string|starts_with:pm_',
            'is_default' => 'sometimes|boolean',
        ]);

        $company = Auth::user()->company;
        $result = $this->stripeService->attachPaymentMethod($company, $request->payment_method_id, $request->boolean('is_default', false));

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
        $company = Auth::user()->company;
        
        // Ensure payment method belongs to this company
        if ($paymentMethod->payable_type !== get_class($company) || 
            $paymentMethod->payable_id !== $company->id) {
            abort(403, 'Unauthorized.');
        }

        $paymentMethod->setAsDefault();

        return back()->with('success', 'Default payment method updated successfully!');
    }

    public function deletePaymentMethod(PaymentMethod $paymentMethod)
    {
        $company = Auth::user()->company;
        
        // Ensure payment method belongs to this company
        if ($paymentMethod->payable_type !== get_class($company) || 
            $paymentMethod->payable_id !== $company->id) {
            abort(403, 'Unauthorized.');
        }

        $paymentMethod->delete();

        return back()->with('success', 'Payment method deleted successfully!');
    }

    /**
     * Initiate micro-deposit verification
     */
    public function initiateVerification(Request $request, PaymentMethod $paymentMethod)
    {
        // Ensure this payment method belongs to the current company
        if ($paymentMethod->payable_id !== Auth::user()->company->id || $paymentMethod->payable_type !== Company::class) {
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
                'estimated_arrival' => $result['estimated_arrival'],
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
        // Ensure this payment method belongs to the current company
        if ($paymentMethod->payable_id !== Auth::user()->company->id || $paymentMethod->payable_type !== Company::class) {
            abort(403, 'Unauthorized access to payment method.');
        }

        // Check if verification is available
        if (!$paymentMethod->isPendingVerification()) {
            return redirect()->route('company.payment-methods')
                ->with('error', 'This payment method is not available for verification.');
        }

        return view('company.payment-methods.verify', compact('paymentMethod'));
    }

    /**
     * Verify micro-deposit amounts
     */
    public function verifyMicroDeposits(Request $request, PaymentMethod $paymentMethod)
    {
        // Ensure this payment method belongs to the current company
        if ($paymentMethod->payable_id !== Auth::user()->company->id || $paymentMethod->payable_type !== Company::class) {
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

        $result = $this->stripeService->verifyMicroDepositAmounts($paymentMethod, $amounts);

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

        // Get available payment methods for the company
        $paymentMethods = $company->paymentMethods()
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

        // Ensure the payment method belongs to the company
        if ($paymentMethod->payable_type !== get_class($company) || 
            $paymentMethod->payable_id !== $company->id) {
            return back()->with('error', 'Invalid payment method for this invoice.');
        }

        $result = $this->stripeService->createPaymentIntent($invoice, $paymentMethod);

        if ($result['success']) {
            return redirect()->route('company.invoices.show', $invoice)
                ->with('success', 'Payment processed successfully!');
        }

        return back()->with('error', 'Payment failed: ' . $result['error']);
    }
}
