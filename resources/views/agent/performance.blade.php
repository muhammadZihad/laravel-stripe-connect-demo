@extends('layouts.app')

@section('title', 'Performance')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold">Performance Overview</h2>
                        <p class="text-gray-600">Track your performance metrics and achievements</p>
                    </div>
                </div>

                <!-- Performance Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Invoices -->
                    <div class="bg-blue-50 p-6 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5">
                                <p class="text-sm font-medium text-gray-500">Total Invoices</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_invoices'] }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Paid Invoices -->
                    <div class="bg-green-50 p-6 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5">
                                <p class="text-sm font-medium text-gray-500">Paid Invoices</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['paid_invoices'] }}</p>
                                @if($stats['total_invoices'] > 0)
                                    <p class="text-sm text-green-600">{{ number_format(($stats['paid_invoices'] / $stats['total_invoices']) * 100, 1) }}% success rate</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Total Earned -->
                    <div class="bg-purple-50 p-6 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5">
                                <p class="text-sm font-medium text-gray-500">Total Earned</p>
                                <p class="text-2xl font-bold text-gray-900">${{ number_format($stats['total_earned'], 2) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Average Invoice -->
                    <div class="bg-yellow-50 p-6 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5">
                                <p class="text-sm font-medium text-gray-500">Avg Invoice Value</p>
                                <p class="text-2xl font-bold text-gray-900">${{ number_format($stats['avg_invoice_amount'], 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Performance Chart -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Monthly Earnings -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Monthly Performance</h3>
                        <div class="space-y-4">
                            @foreach($monthlyStats as $month => $data)
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-900">{{ $month }}</span>
                                            <span class="text-sm text-gray-500">{{ $data['invoices'] }} invoices</span>
                                        </div>
                                        <div class="mt-1 flex items-center">
                                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                                <div class="bg-primary-600 h-2 rounded-full" style="width: {{ $data['invoices'] > 0 ? min(($data['earnings'] / max(array_column($monthlyStats, 'earnings'))) * 100, 100) : 0 }}%"></div>
                                            </div>
                                            <span class="ml-3 text-sm font-medium text-gray-900">${{ number_format($data['earnings'], 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Recent Performance -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h3>
                        <div class="space-y-4">
                            @if($recentInvoices->count() > 0)
                                @foreach($recentInvoices as $invoice)
                                    <div class="flex items-center justify-between py-2">
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-medium text-gray-900">{{ $invoice->invoice_number }}</span>
                                                @if($invoice->status === 'paid')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                                                @elseif($invoice->isOverdue())
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Overdue</span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                                @endif
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm text-gray-500">{{ $invoice->due_date->format('M d, Y') }}</span>
                                                <span class="text-sm font-medium text-gray-900">${{ number_format($invoice->total_amount, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-4">
                                    <p class="text-sm text-gray-500">No recent invoices found.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Payment Speed -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Speed</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Average Days to Payment</span>
                                <span class="text-sm font-medium text-gray-900">{{ number_format($stats['avg_days_to_payment'], 1) }} days</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Fastest Payment</span>
                                <span class="text-sm font-medium text-gray-900">{{ $stats['fastest_payment'] }} days</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Slowest Payment</span>
                                <span class="text-sm font-medium text-gray-900">{{ $stats['slowest_payment'] }} days</span>
                            </div>
                        </div>
                    </div>

                    <!-- Commission Breakdown -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Commission Details</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Commission Rate</span>
                                <span class="text-sm font-medium text-gray-900">{{ number_format($agent->commission_rate, 2) }}%</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Total Gross Revenue</span>
                                <span class="text-sm font-medium text-gray-900">${{ number_format($stats['total_revenue'], 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Platform Fees Paid</span>
                                <span class="text-sm font-medium text-gray-900">${{ number_format($stats['total_fees'], 2) }}</span>
                            </div>
                            <div class="border-t pt-3">
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-900">Net Earnings</span>
                                    <span class="text-sm font-bold text-green-600">${{ number_format($stats['total_earned'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Goals & Targets -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">This Month's Progress</h3>
                        <div class="space-y-4">
                            <!-- Monthly Target (example) -->
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm text-gray-500">Monthly Target</span>
                                    <span class="text-sm font-medium text-gray-900">${{ number_format($stats['this_month_earned'], 2) }} / $5,000</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ min(($stats['this_month_earned'] / 5000) * 100, 100) }}%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">{{ number_format(min(($stats['this_month_earned'] / 5000) * 100, 100), 1) }}% of monthly goal</p>
                            </div>

                            <!-- Invoice Count -->
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm text-gray-500">Invoices This Month</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $stats['this_month_invoices'] }} / 20</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(($stats['this_month_invoices'] / 20) * 100, 100) }}%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">{{ number_format(min(($stats['this_month_invoices'] / 20) * 100, 100), 1) }}% of target</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mt-8 bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="{{ route('agent.invoices') }}" class="flex items-center p-3 bg-white hover:bg-gray-50 rounded-lg border transition duration-150 ease-in-out">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">View All Invoices</p>
                                <p class="text-sm text-gray-500">Check your complete invoice list</p>
                            </div>
                        </a>

                        <a href="{{ route('agent.transactions') }}" class="flex items-center p-3 bg-white hover:bg-gray-50 rounded-lg border transition duration-150 ease-in-out">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2m0 0h10V9h-2M9 11h4m0 0V9m0 2v6"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">View Transactions</p>
                                <p class="text-sm text-gray-500">Track your earnings history</p>
                            </div>
                        </a>

                        <a href="{{ route('agent.earnings') }}" class="flex items-center p-3 bg-white hover:bg-gray-50 rounded-lg border transition duration-150 ease-in-out">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Detailed Earnings</p>
                                <p class="text-sm text-gray-500">Analyze earning patterns</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 