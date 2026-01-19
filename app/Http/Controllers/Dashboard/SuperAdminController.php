<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use App\Models\Agent;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\PaymentMethod;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuperAdminController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Super Admin Dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_companies' => Company::count(),
            'total_agents' => Agent::count(),
            'total_invoices' => Invoice::count(),
            'pending_invoices' => Invoice::where('status', 'pending')->count(),
            'paid_invoices' => Invoice::where('status', 'paid')->count(),
            'total_revenue' => Transaction::where('status', 'completed')->sum('amount'),
            'admin_commission' => Transaction::where('status', 'completed')->sum('admin_commission'),
            'recent_transactions' => Transaction::with(['invoice', 'company', 'agent'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
        ];

        $recentInvoices = Invoice::with(['company', 'agent'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('super_admin.dashboard', compact('stats', 'recentInvoices'));
    }

    /**
     * Companies Management
     */
    public function companies()
    {
        $companies = Company::with(['user', 'agents'])
            ->withCount(['agents', 'invoices', 'transactions'])
            ->paginate(15);

        return view('super_admin.companies.index', compact('companies'));
    }

    public function showCompany(Company $company)
    {
        $company->load(['user', 'agents.user', 'invoices.agent', 'transactions', 'paymentMethods']);
        
        $stats = [
            'total_agents' => $company->agents->count(),
            'total_invoices' => $company->invoices->count(),
            'pending_invoices' => $company->invoices->where('status', 'pending')->count(),
            'total_paid' => $company->transactions->where('status', 'completed')->sum('amount'),
        ];

        return view('super_admin.companies.show', compact('company', 'stats'));
    }

    public function editCompany(Company $company)
    {
        return view('super_admin.companies.edit', compact('company'));
    }

    public function updateCompany(Request $request, Company $company)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url',
            'tax_id' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $company->update($request->all());

        return redirect()->route('super_admin.companies.show', $company)
            ->with('success', 'Company updated successfully!');
    }

    /**
     * Agents Management
     */
    public function agents()
    {
        $agents = Agent::with(['user', 'company'])
            ->withCount(['invoices', 'transactions'])
            ->paginate(15);

        return view('super_admin.agents.index', compact('agents'));
    }

    public function showAgent(Agent $agent)
    {
        $agent->load(['user', 'company.user', 'invoices', 'transactions', 'paymentMethods']);
        
        $stats = [
            'total_invoices' => $agent->invoices->count(),
            'pending_invoices' => $agent->invoices->where('status', 'pending')->count(),
            'total_earned' => $agent->transactions->where('status', 'completed')->sum('net_amount'),
        ];

        return view('super_admin.agents.show', compact('agent', 'stats'));
    }

    public function editAgent(Agent $agent)
    {
        return view('super_admin.agents.edit', compact('agent'));
    }

    public function updateAgent(Request $request, Agent $agent)
    {
        $request->validate([
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'department' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $agent->update($request->all());

        return redirect()->route('super_admin.agents.show', $agent)
            ->with('success', 'Agent updated successfully!');
    }

    /**
     * Invoices Management
     */
    public function invoices()
    {
        $invoices = Invoice::with(['company', 'agent'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('super_admin.invoices.index', compact('invoices'));
    }

    public function showInvoice(Invoice $invoice)
    {
        $invoice->load(['company.user', 'agent.user', 'transactions']);
        
        return view('super_admin.invoices.show', compact('invoice'));
    }

    public function createInvoice()
    {
        $companies = Company::with('user')->where('is_active', true)->get();
        
        return view('super_admin.invoices.create', compact('companies'));
    }

    public function storeInvoice(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'agent_id' => 'required|exists:agents,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'due_date' => 'required|date|after:today',
            'invoice_items' => 'nullable|array',
        ]);

        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'company_id' => $request->company_id,
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

        return redirect()->route('super_admin.invoices.show', $invoice)
            ->with('success', 'Invoice created successfully!');
    }

    /**
     * Process payment for invoice
     */
    public function processPayment(Request $request, Invoice $invoice)
    {
        // If GET request, show the payment form
        if ($request->isMethod('GET')) {
            // Get available payment methods for the company user
            $paymentMethods = $invoice->company->user->paymentMethods()
                ->where('is_active', true)
                ->get();

            return view('super_admin.invoices.process-payment', compact('invoice', 'paymentMethods'));
        }

        // If POST request, process the payment
        $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
        ]);

        $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);

        // Ensure the payment method belongs to the company user
        if ($paymentMethod->user_id !== $invoice->company->user_id) {
            return back()->with('error', 'Invalid payment method for this invoice.');
        }

        $result = $this->stripeService->createPaymentIntent($invoice, $paymentMethod);

        if ($result['success']) {
            return redirect()->route('super_admin.invoices.show', $invoice)
                ->with('success', 'Payment processed successfully!');
        }

        return back()->with('error', 'Payment failed: ' . $result['error']);
    }

    /**
     * Send invoice via email
     */
    public function sendInvoice(Request $request, Invoice $invoice)
    {
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

    /**
     * Transactions
     */
    public function transactions()
    {
        $transactions = Transaction::with(['invoice', 'company', 'agent'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('super_admin.transactions.index', compact('transactions'));
    }

    public function showTransaction(Transaction $transaction)
    {
        $transaction->load(['invoice', 'company.user', 'agent.user']);
        
        return view('super_admin.transactions.show', compact('transaction'));
    }

    /**
     * Payment Methods Management
     */
    public function addPaymentMethodToEntity(Request $request)
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
            $entity = $company;
        } else {
            $agent = Agent::findOrFail($request->entity_id);
            $user = $agent->user;
            $entity = $agent;
        }

        $result = $this->stripeService->addPaymentMethod($user, $request->payment_method_data);

        if ($result['success']) {
            $redirectRoute = $request->entity_type === 'company' 
                ? 'super_admin.companies.show' 
                : 'super_admin.agents.show';
                
            return redirect()->route($redirectRoute, $entity)
                ->with('success', 'Payment method added successfully!');
        }

        return back()->with('error', 'Failed to add payment method: ' . $result['error']);
    }

    /**
     * Get agents for a company (AJAX)
     */
    public function getCompanyAgents(Company $company)
    {
        $agents = $company->agents()->with('user')->where('is_active', true)->get();
        
        return response()->json($agents);
    }

    /**
     * Analytics & Reports
     */
    public function analytics()
    {
        $monthlyRevenue = Transaction::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('SUM(admin_commission) as total_commission')
            )
            ->where('status', 'completed')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        $topCompanies = Company::withSum(['transactions' => function($query) {
                $query->where('status', 'completed');
            }], 'amount')
            ->orderBy('transactions_sum_amount', 'desc')
            ->limit(10)
            ->get();

        $topAgents = Agent::withSum(['transactions' => function($query) {
                $query->where('status', 'completed');
            }], 'net_amount')
            ->orderBy('transactions_sum_net_amount', 'desc')
            ->limit(10)
            ->get();

        return view('super_admin.analytics', compact('monthlyRevenue', 'topCompanies', 'topAgents'));
    }

    /**
     * Settings
     */
    public function settings()
    {
        return view('super_admin.settings');
    }

    /**
     * View pending transactions (including those awaiting 3DS)
     */
    public function pendingTransactions()
    {
        $pendingTransactions = Transaction::where('status', 'pending')
            ->with(['invoice', 'company', 'agent'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('super_admin.pending_transactions', compact('pendingTransactions'));
    }

    /**
     * Reconcile pending transactions with Stripe
     */
    public function reconcilePendingTransactions(Request $request)
    {
        $olderThanMinutes = $request->input('older_than_minutes', 10);
        
        $result = $this->stripeService->reconcilePendingTransactions($olderThanMinutes);
        
        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Reconciliation completed successfully.',
                'checked' => $result['checked'],
                'reconciled' => $result['reconciled'],
                'failed' => $result['failed'],
            ]);
        }
        
        return response()->json([
            'success' => false,
            'error' => 'Reconciliation failed',
        ], 500);
    }

    /**
     * Check specific transaction status with Stripe
     */
    public function checkTransactionStatus(Transaction $transaction)
    {
        if (!$transaction->stripe_payment_intent_id) {
            return response()->json([
                'success' => false,
                'error' => 'No payment intent associated with this transaction',
            ], 400);
        }

        try {
            $paymentIntent = \Stripe\PaymentIntent::retrieve($transaction->stripe_payment_intent_id);
            
            return response()->json([
                'success' => true,
                'transaction_id' => $transaction->id,
                'payment_intent_id' => $paymentIntent->id,
                'payment_intent_status' => $paymentIntent->status,
                'local_status' => $transaction->status,
                'requires_action' => $paymentIntent->status === 'requires_action',
                'next_action' => $paymentIntent->next_action,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve payment intent: ' . $e->getMessage(),
            ], 500);
        }
    }
}
