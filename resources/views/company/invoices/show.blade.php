@extends('layouts.app')

@section('title', 'Invoice Details')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $invoice->invoice_number }}</h1>
                    <p class="text-gray-600">{{ $invoice->title }}</p>
                </div>
                <div class="flex gap-3">
                    @if($invoice->status !== 'paid')
                        <a href="{{ route('company.invoices.pay', $invoice) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            💳 Pay Invoice
                        </a>
                        <a href="{{ route('company.invoices.edit', $invoice) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Edit Invoice
                        </a>
                    @endif
                    <a href="{{ route('company.invoices') }}" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        ← Back to Invoices
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        <!-- Invoice Details -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <!-- Status Banner -->
            <div class="px-6 py-4 border-b border-gray-200 {{ $invoice->status === 'paid' ? 'bg-green-50' : ($invoice->isOverdue() ? 'bg-red-50' : 'bg-yellow-50') }}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : ($invoice->isOverdue() ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ $invoice->isOverdue() ? 'Overdue' : ucfirst($invoice->status) }}
                        </span>
                        @if($invoice->paid_date)
                            <span class="ml-3 text-sm text-gray-600">Paid on {{ $invoice->paid_date->format('M d, Y') }}</span>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-gray-900">${{ number_format($invoice->total_amount, 2) }}</p>
                        <p class="text-sm text-gray-500">Due {{ $invoice->due_date->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Invoice Information -->
            <div class="px-6 py-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Invoice Details -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Details</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Invoice Number</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->invoice_number }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Title</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->title }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Description</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->description ?: 'No description provided' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created Date</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->created_at->format('M d, Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->due_date->format('M d, Y') }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Agent Information -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Agent Information</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Agent Name</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->agent->user->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Agent Code</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->agent->agent_code }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Department</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->agent->department ?: 'Not specified' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Commission Rate</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->agent->commission_rate ? $invoice->agent->commission_rate . '%' : 'Not set' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Amount Breakdown -->
                <div class="mt-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Amount Breakdown</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex justify-between items-center py-2">
                            <span class="text-sm text-gray-600">Subtotal</span>
                            <span class="text-sm text-gray-900">${{ number_format($invoice->amount, 2) }}</span>
                        </div>
                        @if($invoice->tax_amount > 0)
                            <div class="flex justify-between items-center py-2">
                                <span class="text-sm text-gray-600">Tax</span>
                                <span class="text-sm text-gray-900">${{ number_format($invoice->tax_amount, 2) }}</span>
                            </div>
                        @endif
                        <div class="border-t border-gray-200 pt-2 mt-2">
                            <div class="flex justify-between items-center">
                                <span class="text-base font-medium text-gray-900">Total</span>
                                <span class="text-base font-medium text-gray-900">${{ number_format($invoice->total_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Items (if any) -->
                @if($invoice->invoice_items && count($invoice->invoice_items) > 0)
                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Items</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            @foreach($invoice->invoice_items as $item)
                                <div class="flex justify-between items-center py-2 {{ !$loop->last ? 'border-b border-gray-200' : '' }}">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $item['description'] ?? 'Item ' . ($loop->index + 1) }}</p>
                                        @if(isset($item['quantity']) && isset($item['unit_price']))
                                            <p class="text-xs text-gray-500">{{ $item['quantity'] }} × ${{ number_format($item['unit_price'], 2) }}</p>
                                        @endif
                                    </div>
                                    <span class="text-sm text-gray-900">${{ number_format($item['amount'] ?? 0, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Transactions -->
        @if($invoice->transactions->count() > 0)
            <div class="mt-8">
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Payment History</h3>
                    </div>
                    <div class="px-6 py-4">
                        <div class="space-y-4">
                            @foreach($invoice->transactions as $transaction)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $transaction->transaction_id }}</p>
                                        <p class="text-sm text-gray-500">{{ $transaction->created_at->format('M d, Y g:i A') }}</p>
                                        <p class="text-xs text-gray-400">{{ ucfirst($transaction->type) }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900">${{ number_format($transaction->amount, 2) }}</p>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                            {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : ($transaction->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection 