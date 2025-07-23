@extends('layouts.app')

@section('title', 'Invoice Details')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold">Invoice {{ $invoice->invoice_number }}</h2>
                        <p class="text-gray-600">{{ $invoice->title }}</p>
                    </div>
                    <div class="flex gap-3">
                        @if($invoice->status === 'pending')
                            <a href="{{ route('super_admin.invoices.process-payment', $invoice) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Process Payment
                            </a>
                        @endif
                        <a href="{{ route('super_admin.invoices') }}" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            ← Back to Invoices
                        </a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Invoice Status Banner -->
                <div class="mb-6">
                    @if($invoice->status === 'paid')
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800">Invoice Paid</h3>
                                    <p class="text-sm text-green-700">This invoice was paid on {{ $invoice->paid_date ? $invoice->paid_date->format('M d, Y') : 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    @elseif($invoice->status === 'pending' && $invoice->isOverdue())
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Invoice Overdue</h3>
                                    <p class="text-sm text-red-700">This invoice was due on {{ $invoice->due_date->format('M d, Y') }}</p>
                                </div>
                            </div>
                        </div>
                    @elseif($invoice->status === 'pending')
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">Payment Pending</h3>
                                    <p class="text-sm text-yellow-700">Due date: {{ $invoice->due_date->format('M d, Y') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Invoice Details -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Main Invoice Information -->
                    <div class="lg:col-span-2">
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-medium mb-4">Invoice Information</h3>
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Invoice Number</dt>
                                    <dd class="text-sm text-gray-900">{{ $invoice->invoice_number }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Title</dt>
                                    <dd class="text-sm text-gray-900">{{ $invoice->title }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Company</dt>
                                    <dd class="text-sm text-gray-900">
                                        <a href="{{ route('super_admin.companies.show', $invoice->company) }}" class="text-blue-600 hover:text-blue-900">
                                            {{ $invoice->company->company_name }}
                                        </a>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Agent</dt>
                                    <dd class="text-sm text-gray-900">
                                        <a href="{{ route('super_admin.agents.show', $invoice->agent) }}" class="text-blue-600 hover:text-blue-900">
                                            {{ $invoice->agent->user->name }} ({{ $invoice->agent->agent_code }})
                                        </a>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Created Date</dt>
                                    <dd class="text-sm text-gray-900">{{ $invoice->created_at->format('M d, Y') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                                    <dd class="text-sm text-gray-900">{{ $invoice->due_date->format('M d, Y') }}</dd>
                                </div>
                                @if($invoice->paid_date)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Paid Date</dt>
                                    <dd class="text-sm text-gray-900">{{ $invoice->paid_date->format('M d, Y') }}</dd>
                                </div>
                                @endif
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="text-sm">
                                        @if($invoice->status === 'paid')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                                        @elseif($invoice->status === 'pending')
                                            @if($invoice->isOverdue())
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Overdue</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                            @endif
                                        @elseif($invoice->status === 'draft')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Draft</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Cancelled</span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>

                            @if($invoice->description)
                            <div class="mt-6">
                                <dt class="text-sm font-medium text-gray-500 mb-2">Description</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->description }}</dd>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Amount Summary -->
                    <div>
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-medium mb-4">Amount Summary</h3>
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
                                <div class="border-t border-gray-200 pt-3">
                                    <div class="flex justify-between">
                                        <dt class="text-base font-medium text-gray-900">Total</dt>
                                        <dd class="text-base font-medium text-gray-900">${{ number_format($invoice->total_amount, 2) }}</dd>
                                    </div>
                                </div>
                            </dl>
                        </div>

                        @if($invoice->stripe_payment_intent_id)
                        <div class="bg-blue-50 p-6 rounded-lg mt-6">
                            <h3 class="text-lg font-medium mb-4">Payment Information</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Stripe Payment Intent</dt>
                                    <dd class="text-sm text-gray-900 font-mono">{{ $invoice->stripe_payment_intent_id }}</dd>
                                </div>
                            </dl>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Invoice Items -->
                @if($invoice->invoice_items)
                <div class="mb-8">
                    <h3 class="text-lg font-medium mb-4">Invoice Items</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($invoice->invoice_items as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item['description'] ?? 'Service' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item['quantity'] ?? 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($item['rate'] ?? 0, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format(($item['quantity'] ?? 1) * ($item['rate'] ?? 0), 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Related Transactions -->
                @if($invoice->transactions->count() > 0)
                <div>
                    <h3 class="text-lg font-medium mb-4">Related Transactions</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin Commission</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($invoice->transactions as $transaction)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $transaction->transaction_id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ ucfirst($transaction->type) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            ${{ number_format($transaction->amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            ${{ number_format($transaction->admin_commission, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            ${{ number_format($transaction->net_amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($transaction->status === 'completed')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                                            @elseif($transaction->status === 'pending')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                            @elseif($transaction->status === 'failed')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Failed</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($transaction->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $transaction->created_at->format('M d, Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('super_admin.transactions.show', $transaction) }}" class="text-primary-600 hover:text-primary-900">View</a>
                                        </td>
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