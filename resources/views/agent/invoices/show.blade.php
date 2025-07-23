@extends('layouts.app')

@section('title', 'Invoice Details')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold">Invoice {{ $invoice->invoice_number }}</h2>
                        <p class="text-gray-600">{{ $invoice->title }}</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('agent.invoices') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            ← Back to Invoices
                        </a>
                    </div>
                </div>

                <!-- Status Banner -->
                <div class="mb-6">
                    @if($invoice->status === 'paid')
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800">Invoice Paid</h3>
                                    <p class="mt-1 text-sm text-green-700">
                                        This invoice was paid on {{ $invoice->paid_date ? $invoice->paid_date->format('M d, Y') : 'N/A' }}.
                                        Your earnings are being processed.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @elseif($invoice->isOverdue())
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Invoice Overdue</h3>
                                    <p class="mt-1 text-sm text-red-700">
                                        This invoice was due {{ $invoice->due_date->diffForHumans() }}. Please follow up with the company.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">Payment Pending</h3>
                                    <p class="mt-1 text-sm text-yellow-700">
                                        This invoice is awaiting payment from {{ $invoice->company->company_name }}.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Invoice Details -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Basic Information -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium mb-4">Invoice Information</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Invoice Number</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->invoice_number }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Title</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->title }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Company</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->company->company_name }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Agent</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->agent->user->name }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Created Date</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->created_at->format('M d, Y') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->due_date->format('M d, Y') }}</dd>
                            </div>
                            @if($invoice->paid_date)
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Paid Date</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->paid_date->format('M d, Y') }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>

                    <!-- Amount Details -->
                    <div class="bg-blue-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium mb-4">Amount Breakdown</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Subtotal</dt>
                                <dd class="text-sm text-gray-900">${{ number_format($invoice->amount, 2) }}</dd>
                            </div>
                            @if($invoice->tax_amount > 0)
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Tax</dt>
                                <dd class="text-sm text-gray-900">${{ number_format($invoice->tax_amount, 2) }}</dd>
                            </div>
                            @endif
                            <div class="border-t border-blue-200 pt-3">
                                <div class="flex justify-between">
                                    <dt class="text-base font-medium text-gray-900">Total Amount</dt>
                                    <dd class="text-lg font-bold text-gray-900">${{ number_format($invoice->total_amount, 2) }}</dd>
                                </div>
                            </div>
                            <div class="text-sm text-gray-600 pt-2 border-t border-blue-200">
                                <div class="flex justify-between">
                                    <span>Admin Commission (deducted):</span>
                                    <span>$2.00</span>
                                </div>
                                <div class="flex justify-between font-medium">
                                    <span>Your Net Earnings:</span>
                                    <span>${{ number_format($invoice->total_amount - 2, 2) }}</span>
                                </div>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Description -->
                @if($invoice->description)
                <div class="mb-8">
                    <h3 class="text-lg font-medium mb-4">Description</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-gray-900 whitespace-pre-wrap">{{ $invoice->description }}</p>
                    </div>
                </div>
                @endif

                <!-- Invoice Items -->
                @if($invoice->invoice_items)
                <div class="mb-8">
                    <h3 class="text-lg font-medium mb-4">Invoice Items</h3>
                    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($invoice->invoice_items as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item['description'] ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item['quantity'] ?? 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format(($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0), 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Related Transactions -->
                @if($invoice->transactions->count() > 0)
                <div class="mb-8">
                    <h3 class="text-lg font-medium mb-4">Related Transactions</h3>
                    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($invoice->transactions as $transaction)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <a href="{{ route('agent.transactions.show', $transaction) }}" class="text-primary-600 hover:text-primary-900">
                                                {{ $transaction->transaction_id }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ ucfirst($transaction->type) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($transaction->amount, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${{ number_format($transaction->net_amount, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($transaction->status === 'completed')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                                            @elseif($transaction->status === 'pending')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($transaction->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transaction->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 