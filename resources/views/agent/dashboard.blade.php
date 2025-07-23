@extends('layouts.app')

@section('title', 'Agent Dashboard')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Welcome Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Welcome back, {{ $agent->user->name }}!</h1>
            <p class="text-gray-600">Agent Code: {{ $agent->agent_code }} • Company: {{ $agent->company->company_name }}</p>
        </div>

        <!-- Stripe Onboarding Alert -->
        @if(!$stripeOnboardingComplete)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Stripe Onboarding Required</h3>
                        <p class="mt-1 text-sm text-yellow-700">
                            You need to complete Stripe Connect onboarding to receive payments. This process verifies your identity and business information.
                        </p>
                        <div class="mt-4">
                            <a href="{{ route('stripe.start-onboarding') }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Complete Onboarding
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Invoices -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Invoices</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['total_invoices'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Invoices -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Pending Invoices</dt>
                                <dd class="flex items-baseline">
                                    <div class="text-lg font-medium text-gray-900">{{ $stats['pending_invoices'] }}</div>
                                    @if($stats['overdue_invoices'] > 0)
                                        <div class="ml-2 flex items-baseline text-sm font-semibold text-red-600">
                                            ({{ $stats['overdue_invoices'] }} overdue)
                                        </div>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Earned -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Earned</dt>
                                <dd class="text-lg font-medium text-gray-900">${{ number_format($stats['total_earned'], 2) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- This Month Earned -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">This Month</dt>
                                <dd class="text-lg font-medium text-gray-900">${{ number_format($stats['this_month_earned'], 2) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('agent.invoices') }}" class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition duration-150 ease-in-out">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-600">View All Invoices</p>
                    </div>
                </a>

                <a href="{{ route('agent.transactions') }}" class="flex items-center p-3 bg-green-50 hover:bg-green-100 rounded-lg transition duration-150 ease-in-out">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2m0 0h10V9h-2M9 11h4m0 0V9m0 2v6"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-600">View Transactions</p>
                    </div>
                </a>

                <a href="{{ route('agent.payment-methods') }}" class="flex items-center p-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition duration-150 ease-in-out">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-purple-600">Payment Methods</p>
                    </div>
                </a>

                <a href="{{ route('agent.profile') }}" class="flex items-center p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition duration-150 ease-in-out">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600">My Profile</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Invoices -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Recent Invoices</h3>
                        <a href="{{ route('agent.invoices') }}" class="text-sm text-primary-600 hover:text-primary-500">View all</a>
                    </div>
                </div>
                <div class="px-6 py-4">
                    @if($recentInvoices->count() > 0)
                        <div class="space-y-4">
                            @foreach($recentInvoices as $invoice)
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-medium text-gray-900">{{ $invoice->invoice_number }}</p>
                                            <div class="ml-2">
                                                @if($invoice->status === 'paid')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                                                @elseif($invoice->status === 'pending')
                                                    @if($invoice->isOverdue())
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Overdue</span>
                                                    @else
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                                    @endif
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($invoice->status) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-500">{{ $invoice->title }}</p>
                                        <div class="flex items-center justify-between mt-1">
                                            <p class="text-sm text-gray-500">Due: {{ $invoice->due_date->format('M d, Y') }}</p>
                                            <p class="text-sm font-medium text-gray-900">${{ number_format($invoice->total_amount, 2) }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No invoices yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Your invoices will appear here once created.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Recent Transactions</h3>
                        <a href="{{ route('agent.transactions') }}" class="text-sm text-primary-600 hover:text-primary-500">View all</a>
                    </div>
                </div>
                <div class="px-6 py-4">
                    @if($recentTransactions->count() > 0)
                        <div class="space-y-4">
                            @foreach($recentTransactions as $transaction)
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-medium text-gray-900">{{ $transaction->transaction_id }}</p>
                                            <div class="ml-2">
                                                @if($transaction->status === 'completed')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                                                @elseif($transaction->status === 'pending')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                                @elseif($transaction->status === 'failed')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Failed</span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($transaction->status) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-500">{{ $transaction->invoice->invoice_number }}</p>
                                        <div class="flex items-center justify-between mt-1">
                                            <p class="text-sm text-gray-500">{{ $transaction->created_at->format('M d, Y') }}</p>
                                            <div class="text-right">
                                                <p class="text-sm font-medium text-gray-900">${{ number_format($transaction->net_amount, 2) }}</p>
                                                <p class="text-xs text-gray-500">Net Amount</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2m0 0h10V9h-2M9 11h4m0 0V9m0 2v6"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No transactions yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Your payment transactions will appear here.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Pending Amount Alert -->
        @if($stats['pending_amount'] > 0)
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Pending Payments</h3>
                        <p class="mt-1 text-sm text-blue-700">
                            You have <strong>${{ number_format($stats['pending_amount'], 2) }}</strong> in pending invoices waiting for payment.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection 