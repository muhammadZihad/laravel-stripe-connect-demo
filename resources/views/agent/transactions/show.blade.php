@extends('layouts.app')

@section('title', 'Transaction Details')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold">Transaction {{ $transaction->transaction_id }}</h2>
                        <p class="text-gray-600">{{ ucfirst($transaction->type) }} Transaction</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('agent.transactions') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            ← Back to Transactions
                        </a>
                    </div>
                </div>

                <!-- Status Banner -->
                <div class="mb-6">
                    @if($transaction->status === 'completed')
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800">Transaction Completed</h3>
                                    <p class="mt-1 text-sm text-green-700">
                                        Your earnings of ${{ number_format($transaction->net_amount, 2) }} have been processed successfully.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @elseif($transaction->status === 'pending')
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">Transaction Pending</h3>
                                    <p class="mt-1 text-sm text-yellow-700">
                                        This transaction is being processed. Your earnings will be available once completed.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @elseif($transaction->status === 'failed')
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Transaction Failed</h3>
                                    <p class="mt-1 text-sm text-red-700">
                                        This transaction could not be processed. Please contact support if you need assistance.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Transaction Details -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Basic Information -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium mb-4">Transaction Details</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Transaction ID</dt>
                                <dd class="text-sm text-gray-900">{{ $transaction->transaction_id }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Type</dt>
                                <dd class="text-sm text-gray-900">{{ ucfirst($transaction->type) }}</dd>
                            </div>
                            <div class="flex justify-between">
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
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Company</dt>
                                <dd class="text-sm text-gray-900">{{ $transaction->company->company_name }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Agent</dt>
                                <dd class="text-sm text-gray-900">{{ $transaction->agent->user->name }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
                                <dd class="text-sm text-gray-900">{{ ucfirst($transaction->payment_method_type) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Created Date</dt>
                                <dd class="text-sm text-gray-900">{{ $transaction->created_at->format('M d, Y g:i A') }}</dd>
                            </div>
                            @if($transaction->processed_at)
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Processed Date</dt>
                                <dd class="text-sm text-gray-900">{{ $transaction->processed_at->format('M d, Y g:i A') }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>

                    <!-- Amount Breakdown -->
                    <div class="bg-green-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium mb-4">Payment Breakdown</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Total Invoice Amount</dt>
                                <dd class="text-sm text-gray-900">${{ number_format($transaction->amount, 2) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Admin Commission</dt>
                                <dd class="text-sm text-red-600">-${{ number_format($transaction->admin_commission, 2) }}</dd>
                            </div>
                            <div class="border-t border-green-200 pt-3">
                                <div class="flex justify-between">
                                    <dt class="text-base font-medium text-gray-900">Your Net Earnings</dt>
                                    <dd class="text-lg font-bold text-green-600">${{ number_format($transaction->net_amount, 2) }}</dd>
                                </div>
                            </div>
                            <div class="text-xs text-gray-600 pt-2">
                                <p>Platform commission is automatically deducted from each transaction.</p>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Related Invoice -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium mb-4">Related Invoice</h3>
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-base font-medium text-gray-900">{{ $transaction->invoice->invoice_number }}</h4>
                                <p class="text-sm text-gray-500">{{ $transaction->invoice->title }}</p>
                                <div class="mt-2 flex items-center text-sm text-gray-500">
                                    <span>Due: {{ $transaction->invoice->due_date->format('M d, Y') }}</span>
                                    <span class="mx-2">•</span>
                                    <span>Amount: ${{ number_format($transaction->invoice->total_amount, 2) }}</span>
                                    <span class="mx-2">•</span>
                                    @if($transaction->invoice->status === 'paid')
                                        <span class="text-green-600 font-medium">Paid</span>
                                    @else
                                        <span class="text-yellow-600 font-medium">{{ ucfirst($transaction->invoice->status) }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('agent.invoices.show', $transaction->invoice) }}" class="text-primary-600 hover:text-primary-900 text-sm font-medium">
                                    View Invoice
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stripe Information -->
                @if($transaction->stripe_payment_intent_id || $transaction->stripe_transfer_id)
                <div class="mb-8">
                    <h3 class="text-lg font-medium mb-4">Payment Processing Details</h3>
                    <div class="bg-blue-50 p-6 rounded-lg">
                        <dl class="space-y-3">
                            @if($transaction->stripe_payment_intent_id)
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Stripe Payment Intent</dt>
                                <dd class="text-sm text-gray-900 font-mono">{{ $transaction->stripe_payment_intent_id }}</dd>
                            </div>
                            @endif
                            @if($transaction->stripe_transfer_id)
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Stripe Transfer ID</dt>
                                <dd class="text-sm text-gray-900 font-mono">{{ $transaction->stripe_transfer_id }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>
                @endif

                <!-- Notes -->
                @if($transaction->notes)
                <div class="mb-8">
                    <h3 class="text-lg font-medium mb-4">Notes</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-gray-900 whitespace-pre-wrap">{{ $transaction->notes }}</p>
                    </div>
                </div>
                @endif

                <!-- Transaction Timeline -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium mb-4">Transaction Timeline</h3>
                    <div class="flow-root">
                        <ul class="-mb-8">
                            <li>
                                <div class="relative pb-8">
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">Transaction created</p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                {{ $transaction->created_at->format('M d, Y g:i A') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @if($transaction->processed_at)
                            <li>
                                <div class="relative">
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full 
                                                @if($transaction->status === 'completed') bg-green-500
                                                @elseif($transaction->status === 'failed') bg-red-500
                                                @else bg-yellow-500 @endif
                                                flex items-center justify-center ring-8 ring-white">
                                                @if($transaction->status === 'completed')
                                                    <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                @elseif($transaction->status === 'failed')
                                                    <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                    </svg>
                                                @else
                                                    <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                    </svg>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">
                                                    @if($transaction->status === 'completed')
                                                        Transaction completed successfully
                                                    @elseif($transaction->status === 'failed')
                                                        Transaction failed
                                                    @else
                                                        Transaction {{ $transaction->status }}
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                {{ $transaction->processed_at->format('M d, Y g:i A') }}
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