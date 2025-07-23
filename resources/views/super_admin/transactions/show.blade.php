@extends('layouts.app')

@section('title', 'Transaction Details')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold">Transaction {{ $transaction->transaction_id }}</h2>
                        <p class="text-gray-600">{{ ucfirst($transaction->type) }} Transaction</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('super_admin.transactions') }}" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            ← Back to Transactions
                        </a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Transaction Status Banner -->
                <div class="mb-6">
                    @if($transaction->status === 'completed')
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800">Transaction Completed</h3>
                                    <p class="text-sm text-green-700">This transaction was processed successfully on {{ $transaction->processed_at ? $transaction->processed_at->format('M d, Y H:i') : $transaction->created_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    @elseif($transaction->status === 'pending')
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">Transaction Pending</h3>
                                    <p class="text-sm text-yellow-700">This transaction is being processed</p>
                                </div>
                            </div>
                        </div>
                    @elseif($transaction->status === 'failed')
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Transaction Failed</h3>
                                    <p class="text-sm text-red-700">This transaction failed to process</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-7-9a1 1 0 112 0v3a1 1 0 11-2 0V9zm8-4a1 1 0 10-2 0v7a1 1 0 102 0V5zm4 2a1 1 0 10-2 0v5a1 1 0 102 0V7z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-gray-800">Transaction {{ ucfirst($transaction->status) }}</h3>
                                    <p class="text-sm text-gray-600">Status: {{ ucfirst($transaction->status) }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Transaction Details -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Main Transaction Information -->
                    <div class="lg:col-span-2">
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-medium mb-4">Transaction Information</h3>
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Transaction ID</dt>
                                    <dd class="text-sm text-gray-900 font-mono">{{ $transaction->transaction_id }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Type</dt>
                                    <dd class="text-sm text-gray-900">{{ ucfirst($transaction->type) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Invoice</dt>
                                    <dd class="text-sm text-gray-900">
                                        <a href="{{ route('super_admin.invoices.show', $transaction->invoice) }}" class="text-blue-600 hover:text-blue-900">
                                            {{ $transaction->invoice->invoice_number }}
                                        </a>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Company</dt>
                                    <dd class="text-sm text-gray-900">
                                        <a href="{{ route('super_admin.companies.show', $transaction->company) }}" class="text-blue-600 hover:text-blue-900">
                                            {{ $transaction->company->company_name }}
                                        </a>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Agent</dt>
                                    <dd class="text-sm text-gray-900">
                                        <a href="{{ route('super_admin.agents.show', $transaction->agent) }}" class="text-blue-600 hover:text-blue-900">
                                            {{ $transaction->agent->user->name }} ({{ $transaction->agent->agent_code }})
                                        </a>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Payment Method Type</dt>
                                    <dd class="text-sm text-gray-900">{{ $transaction->payment_method_type ?: 'Not specified' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Created Date</dt>
                                    <dd class="text-sm text-gray-900">{{ $transaction->created_at->format('M d, Y H:i') }}</dd>
                                </div>
                                @if($transaction->processed_at)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Processed Date</dt>
                                    <dd class="text-sm text-gray-900">{{ $transaction->processed_at->format('M d, Y H:i') }}</dd>
                                </div>
                                @endif
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="text-sm">
                                        @if($transaction->status === 'completed')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                                        @elseif($transaction->status === 'pending')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                        @elseif($transaction->status === 'failed')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Failed</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($transaction->status) }}</span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>

                            @if($transaction->notes)
                            <div class="mt-6">
                                <dt class="text-sm font-medium text-gray-500 mb-2">Notes</dt>
                                <dd class="text-sm text-gray-900">{{ $transaction->notes }}</dd>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Amount Breakdown -->
                    <div>
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-medium mb-4">Amount Breakdown</h3>
                            <dl class="space-y-3">
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Total Amount</dt>
                                    <dd class="text-sm text-gray-900">${{ number_format($transaction->amount, 2) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Admin Commission</dt>
                                    <dd class="text-sm text-red-600">-${{ number_format($transaction->admin_commission, 2) }}</dd>
                                </div>
                                <div class="border-t border-gray-200 pt-3">
                                    <div class="flex justify-between">
                                        <dt class="text-base font-medium text-gray-900">Net Amount to Agent</dt>
                                        <dd class="text-base font-medium text-gray-900">${{ number_format($transaction->net_amount, 2) }}</dd>
                                    </div>
                                </div>
                            </dl>
                        </div>

                        <!-- Stripe Information -->
                        @if($transaction->stripe_payment_intent_id || $transaction->stripe_transfer_id)
                        <div class="bg-blue-50 p-6 rounded-lg mt-6">
                            <h3 class="text-lg font-medium mb-4">Stripe Information</h3>
                            <dl class="space-y-3">
                                @if($transaction->stripe_payment_intent_id)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Payment Intent ID</dt>
                                    <dd class="text-sm text-gray-900 font-mono break-all">{{ $transaction->stripe_payment_intent_id }}</dd>
                                </div>
                                @endif
                                @if($transaction->stripe_transfer_id)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Transfer ID</dt>
                                    <dd class="text-sm text-gray-900 font-mono break-all">{{ $transaction->stripe_transfer_id }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                        @endif

                        <!-- Commission Info -->
                        <div class="bg-green-50 p-6 rounded-lg mt-6">
                            <h3 class="text-lg font-medium mb-4">Commission Details</h3>
                            <dl class="space-y-3">
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Commission Rate</dt>
                                    <dd class="text-sm text-gray-900">Fixed $2.00</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Commission Amount</dt>
                                    <dd class="text-sm text-green-600">${{ number_format($transaction->admin_commission, 2) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Agent Commission Rate</dt>
                                    <dd class="text-sm text-gray-900">{{ $transaction->agent->commission_rate }}%</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Stripe Metadata -->
                @if($transaction->stripe_metadata)
                <div class="mb-8">
                    <h3 class="text-lg font-medium mb-4">Stripe Metadata</h3>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <pre class="text-sm text-gray-900 whitespace-pre-wrap">{{ json_encode(json_decode($transaction->stripe_metadata), JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
                @endif

                <!-- Related Invoice Details -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium mb-4">Related Invoice</h3>
                    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h4 class="text-lg font-medium text-gray-900">{{ $transaction->invoice->invoice_number }}</h4>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($transaction->invoice->status === 'paid') bg-green-100 text-green-800
                                    @elseif($transaction->invoice->status === 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($transaction->invoice->status) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">{{ $transaction->invoice->title }}</p>
                        </div>
                        <div class="px-6 py-4">
                            <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Amount</dt>
                                    <dd class="text-sm text-gray-900">${{ number_format($transaction->invoice->total_amount, 2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                                    <dd class="text-sm text-gray-900">{{ $transaction->invoice->due_date->format('M d, Y') }}</dd>
                                </div>
                                @if($transaction->invoice->paid_date)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Paid Date</dt>
                                    <dd class="text-sm text-gray-900">{{ $transaction->invoice->paid_date->format('M d, Y') }}</dd>
                                </div>
                                @endif
                            </dl>
                            <div class="mt-4">
                                <a href="{{ route('super_admin.invoices.show', $transaction->invoice) }}" class="text-primary-600 hover:text-primary-900 text-sm font-medium">
                                    View Full Invoice →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transaction Timeline -->
                <div>
                    <h3 class="text-lg font-medium mb-4">Transaction Timeline</h3>
                    <div class="flow-root">
                        <ul class="-mb-8">
                            <li>
                                <div class="relative pb-8">
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">Transaction created</p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                <time datetime="{{ $transaction->created_at->toISOString() }}">{{ $transaction->created_at->format('M d, Y H:i') }}</time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @if($transaction->processed_at)
                            <li>
                                <div class="relative pb-8">
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full 
                                                @if($transaction->status === 'completed') bg-green-500
                                                @elseif($transaction->status === 'failed') bg-red-500
                                                @else bg-yellow-500 @endif
                                                flex items-center justify-center ring-8 ring-white">
                                                @if($transaction->status === 'completed')
                                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                @elseif($transaction->status === 'failed')
                                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                    </svg>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">Transaction {{ $transaction->status }}</p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                <time datetime="{{ $transaction->processed_at->toISOString() }}">{{ $transaction->processed_at->format('M d, Y H:i') }}</time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 