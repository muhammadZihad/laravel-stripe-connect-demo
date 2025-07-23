@extends('layouts.app')

@section('title', 'Transaction Details')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Transaction {{ $transaction->transaction_id }}</h1>
                    <p class="text-gray-600">{{ $transaction->invoice->invoice_number }}</p>
                </div>
                <a href="{{ route('company.transactions') }}" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    ← Back to Transactions
                </a>
            </div>
        </div>

        <!-- Transaction Details -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <!-- Status Banner -->
            <div class="px-6 py-4 border-b border-gray-200 {{ $transaction->status === 'completed' ? 'bg-green-50' : ($transaction->status === 'failed' ? 'bg-red-50' : 'bg-yellow-50') }}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : ($transaction->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ ucfirst($transaction->status) }}
                        </span>
                        @if($transaction->processed_at)
                            <span class="ml-3 text-sm text-gray-600">Processed {{ $transaction->processed_at->format('M d, Y g:i A') }}</span>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-gray-900">${{ number_format($transaction->amount, 2) }}</p>
                        <p class="text-sm text-gray-500">{{ ucfirst($transaction->type) }}</p>
                    </div>
                </div>
            </div>

            <!-- Transaction Information -->
            <div class="px-6 py-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Transaction Details -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Transaction Details</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Transaction ID</dt>
                                <dd class="text-sm text-gray-900">{{ $transaction->transaction_id }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
                                <dd class="text-sm text-gray-900">{{ ucfirst($transaction->payment_method_type) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created Date</dt>
                                <dd class="text-sm text-gray-900">{{ $transaction->created_at->format('M d, Y g:i A') }}</dd>
                            </div>
                            @if($transaction->stripe_payment_intent_id)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Stripe Payment Intent</dt>
                                    <dd class="text-sm text-gray-900 font-mono">{{ $transaction->stripe_payment_intent_id }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    <!-- Related Information -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Related Information</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Invoice</dt>
                                <dd class="text-sm">
                                    <a href="{{ route('company.invoices.show', $transaction->invoice) }}" class="text-primary-600 hover:text-primary-900">
                                        {{ $transaction->invoice->invoice_number }}
                                    </a>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Agent</dt>
                                <dd class="text-sm">
                                    <a href="{{ route('company.agents.show', $transaction->agent) }}" class="text-primary-600 hover:text-primary-900">
                                        {{ $transaction->agent->user->name }}
                                    </a>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Agent Code</dt>
                                <dd class="text-sm text-gray-900">{{ $transaction->agent->agent_code }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Amount Breakdown -->
                <div class="mt-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Amount Breakdown</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex justify-between items-center py-2">
                            <span class="text-sm text-gray-600">Total Amount</span>
                            <span class="text-sm text-gray-900">${{ number_format($transaction->amount, 2) }}</span>
                        </div>
                        @if($transaction->admin_commission > 0)
                            <div class="flex justify-between items-center py-2">
                                <span class="text-sm text-gray-600">Platform Fee</span>
                                <span class="text-sm text-gray-900">${{ number_format($transaction->admin_commission, 2) }}</span>
                            </div>
                            <div class="border-t border-gray-200 pt-2 mt-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-base font-medium text-gray-900">Agent Net Amount</span>
                                    <span class="text-base font-medium text-gray-900">${{ number_format($transaction->net_amount ?? ($transaction->amount - $transaction->admin_commission), 2) }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Notes -->
                @if($transaction->notes)
                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Notes</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-900">{{ $transaction->notes }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 