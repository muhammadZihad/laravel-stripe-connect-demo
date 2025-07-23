<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\PaymentMethod;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Agent;

class AgentController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Agent Dashboard
     */
    public function dashboard()
    {
        $agent = Auth::user()->agent;
        
        $stats = [
            'total_invoices' => $agent->invoices()->count(),
            'pending_invoices' => $agent->invoices()->where('status', 'pending')->count(),
            'paid_invoices' => $agent->invoices()->where('status', 'paid')->count(),
            'overdue_invoices' => $agent->invoices()
                ->where('status', '!=', 'paid')
                ->where('due_date', '<', now())
                ->count(),
            'total_earned' => $agent->transactions()->where('status', 'completed')->sum('net_amount'),
            'pending_amount' => $agent->invoices()->where('status', 'pending')->sum('total_amount'),
            'this_month_earned' => $agent->transactions()
                ->where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('net_amount'),
        ];

        $recentInvoices = $agent->invoices()
            ->with('company.user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentTransactions = $agent->transactions()
            ->with(['invoice', 'company.user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $stripeOnboardingComplete = Auth::user()->isStripeOnboardingComplete();

        return view('agent.dashboard', compact(
            'agent', 'stats', 'recentInvoices', 'recentTransactions', 'stripeOnboardingComplete'
        ));
    }

    /**
     * Invoices
     */
    public function invoices()
    {
        $agent = Auth::user()->agent;
        $invoices = $agent->invoices()
            ->with('company.user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('agent.invoices.index', compact('invoices'));
    }

    public function showInvoice(Invoice $invoice)
    {
        $agent = Auth::user()->agent;
        
        // Ensure invoice belongs to this agent
        if ($invoice->agent_id !== $agent->id) {
            abort(403, 'Unauthorized.');
        }
        
        $invoice->load(['company.user', 'transactions']);
        
        return view('agent.invoices.show', compact('invoice'));
    }

    /**
     * Transactions
     */
    public function transactions()
    {
        $agent = Auth::user()->agent;
        $transactions = $agent->transactions()
            ->with(['invoice', 'company.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('agent.transactions.index', compact('transactions'));
    }

    public function showTransaction(Transaction $transaction)
    {
        $agent = Auth::user()->agent;
        
        // Ensure transaction belongs to this agent
        if ($transaction->agent_id !== $agent->id) {
            abort(403, 'Unauthorized.');
        }
        
        $transaction->load(['invoice', 'company.user']);
        
        return view('agent.transactions.show', compact('transaction'));
    }

    /**
     * Payment Methods
     */
    public function paymentMethods()
    {
        $user = Auth::user();
        $paymentMethods = $user->paymentMethods()->get();

        return view('agent.payment-methods.index', compact('paymentMethods'));
    }

    public function addPaymentMethod()
    {
        return view('agent.payment-methods.create');
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
     * Initiate micro-deposit verification
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
        if (!$paymentMethod->isPendingVerification()) {
            return redirect()->route('agent.payment-methods')
                ->with('error', 'This payment method is not available for verification.');
        }

        return view('agent.payment-methods.verify', compact('paymentMethod'));
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
                'redirect' => route('agent.payment-methods'),
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'],
        ], 400);
    }

    /**
     * Agent Profile
     */
    public function profile()
    {
        $agent = Auth::user()->agent;
        $agent->load(['user', 'company.user']);
        
        return view('agent.profile', compact('agent'));
    }

    public function updateProfile(Request $request)
    {
        $agent = Auth::user()->agent;
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $agent->user->id,
            'department' => 'nullable|string|max:255',
        ]);

        // Update user info
        $agent->user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Update agent info
        $agent->update([
            'department' => $request->department,
        ]);

        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Performance Reports
     */
    public function performance()
    {
        $agent = Auth::user()->agent;
        
        $monthlyStats = $agent->transactions()
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count, SUM(net_amount) as total')
            ->where('status', 'completed')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        $yearlyTotal = $agent->transactions()
            ->where('status', 'completed')
            ->whereYear('created_at', now()->year)
            ->sum('net_amount');

        $topInvoices = $agent->invoices()
            ->with('company.user')
            ->where('status', 'paid')
            ->orderBy('total_amount', 'desc')
            ->limit(10)
            ->get();

        $paymentStats = [
            'average_invoice_amount' => $agent->invoices()->avg('total_amount'),
            'highest_invoice_amount' => $agent->invoices()->max('total_amount'),
            'average_processing_time' => $agent->invoices()
                ->whereNotNull('paid_date')
                ->selectRaw('AVG(DATEDIFF(paid_date, created_at)) as avg_days')
                ->value('avg_days'),
        ];

        return view('agent.performance', compact(
            'monthlyStats', 'yearlyTotal', 'topInvoices', 'paymentStats'
        ));
    }

    /**
     * Company Information (read-only for agent)
     */
    public function company()
    {
        $agent = Auth::user()->agent;
        $company = $agent->company;
        $company->load('user');
        
        return view('agent.company', compact('company', 'agent'));
    }

    /**
     * Earnings Summary
     */
    public function earnings()
    {
        $agent = Auth::user()->agent;
        
        $currentYear = now()->year;
        $monthlyEarnings = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $monthlyEarnings[] = [
                'month' => $month,
                'month_name' => now()->month($month)->format('F'),
                'earnings' => $agent->transactions()
                    ->where('status', 'completed')
                    ->whereYear('created_at', $currentYear)
                    ->whereMonth('created_at', $month)
                    ->sum('net_amount'),
                'invoices_count' => $agent->invoices()
                    ->where('status', 'paid')
                    ->whereYear('paid_date', $currentYear)
                    ->whereMonth('paid_date', $month)
                    ->count(),
            ];
        }

        $totalEarnings = collect($monthlyEarnings)->sum('earnings');
        $avgMonthlyEarnings = $totalEarnings / 12;

        return view('agent.earnings', compact(
            'monthlyEarnings', 'totalEarnings', 'avgMonthlyEarnings', 'currentYear'
        ));
    }
}
