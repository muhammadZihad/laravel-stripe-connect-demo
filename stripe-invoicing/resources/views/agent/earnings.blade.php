@extends('layouts.app')

@section('title', 'Earnings Report')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold">Earnings Report</h2>
                        <p class="text-gray-600">Detailed breakdown of your earnings and payments</p>
                    </div>
                </div>

                <!-- Earnings Summary -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Lifetime Earnings -->
                    <div class="bg-green-50 p-6 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5">
                                <p class="text-sm font-medium text-gray-500">Total Lifetime Earnings</p>
                                <p class="text-2xl font-bold text-gray-900">${{ number_format($earningsStats['total_earnings'], 2) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- This Month -->
                    <div class="bg-blue-50 p-6 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5">
                                <p class="text-sm font-medium text-gray-500">This Month</p>
                                <p class="text-2xl font-bold text-gray-900">${{ number_format($earningsStats['this_month'], 2) }}</p>
                                @if($earningsStats['last_month'] > 0)
                                    @php
                                        $monthChange = (($earningsStats['this_month'] - $earningsStats['last_month']) / $earningsStats['last_month']) * 100;
                                    @endphp
                                    <p class="text-sm {{ $monthChange >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $monthChange >= 0 ? '+' : '' }}{{ number_format($monthChange, 1) }}% from last month
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Pending Earnings -->
                    <div class="bg-yellow-50 p-6 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5">
                                <p class="text-sm font-medium text-gray-500">Pending Earnings</p>
                                <p class="text-2xl font-bold text-gray-900">${{ number_format($earningsStats['pending_earnings'], 2) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Average Per Invoice -->
                    <div class="bg-purple-50 p-6 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5">
                                <p class="text-sm font-medium text-gray-500">Avg Per Invoice</p>
                                <p class="text-2xl font-bold text-gray-900">${{ number_format($earningsStats['avg_per_invoice'], 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Earnings Chart -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Monthly Breakdown -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Monthly Earnings Breakdown</h3>
                        <div class="space-y-4">
                            @foreach($monthlyEarnings as $month => $data)
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-900">{{ $month }}</span>
                                            <span class="text-sm text-gray-500">{{ $data['transactions'] }} transactions</span>
                                        </div>
                                        <div class="mt-1 flex items-center">
                                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                                <div class="bg-green-600 h-2 rounded-full" style="width: {{ $data['earnings'] > 0 ? min(($data['earnings'] / max(array_column($monthlyEarnings, 'earnings'))) * 100, 100) : 0 }}%"></div>
                                            </div>
                                            <span class="ml-3 text-sm font-medium text-gray-900">${{ number_format($data['earnings'], 2) }}</span>
                                        </div>
                                        <div class="mt-1 text-xs text-gray-500">
                                            Gross: ${{ number_format($data['gross'], 2) }} | Fees: ${{ number_format($data['fees'], 2) }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Commission Details -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Commission & Fees Breakdown</h3>
                        <div class="space-y-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700">Your Commission Rate</span>
                                    <span class="text-lg font-bold text-primary-600">{{ number_format($agent->commission_rate, 2) }}%</span>
                                </div>
                                <p class="text-xs text-gray-500">Applied to each completed invoice</p>
                            </div>

                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Total Gross Revenue Generated</span>
                                    <span class="text-sm font-medium text-gray-900">${{ number_format($earningsStats['total_gross_revenue'], 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Total Platform Fees Paid</span>
                                    <span class="text-sm font-medium text-red-600">${{ number_format($earningsStats['total_platform_fees'], 2) }}</span>
                                </div>
                                <div class="flex justify-between border-t pt-2">
                                    <span class="text-sm font-medium text-gray-900">Your Net Earnings</span>
                                    <span class="text-sm font-bold text-green-600">${{ number_format($earningsStats['total_earnings'], 2) }}</span>
                                </div>
                            </div>

                            <div class="bg-blue-50 p-3 rounded-lg">
                                <p class="text-xs text-blue-800">
                                    <strong>Platform Fee:</strong> $2 is deducted from each transaction to cover platform costs and services.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Earnings Transactions</h3>
                    @if($recentTransactions->count() > 0)
                        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gross Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Platform Fee</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Earnings</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($recentTransactions as $transaction)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $transaction->created_at->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <a href="{{ route('agent.invoices.show', $transaction->invoice) }}" class="text-primary-600 hover:text-primary-900">
                                                        {{ $transaction->invoice->invoice_number }}
                                                    </a>
                                                </div>
                                                <div class="text-sm text-gray-500">{{ $transaction->invoice->title }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                ${{ number_format($transaction->amount, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                                -${{ number_format($transaction->admin_commission, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                                ${{ number_format($transaction->net_amount, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($transaction->status === 'completed')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                                                @elseif($transaction->status === 'pending')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($transaction->status) }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $recentTransactions->links() }}
                        </div>
                    @else
                        <div class="text-center py-12 bg-gray-50 rounded-lg">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2m0 0h10V9h-2M9 11h4m0 0V9m0 2v6"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No earnings yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Your earnings from completed invoices will appear here.</p>
                        </div>
                    @endif
                </div>

                <!-- Payment Information -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Payment Information</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Earnings are automatically transferred to your default payment method</li>
                                    <li>Payments are typically processed within 2-7 business days</li>
                                    <li>Platform fee of $2 per transaction covers payment processing and platform maintenance</li>
                                    <li>All transactions are securely processed through Stripe</li>
                                    <li>You can view detailed transaction history in the Transactions section</li>
                                </ul>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('agent.payment-methods') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                    Manage Payment Methods
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 