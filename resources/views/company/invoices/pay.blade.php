@extends('layouts.app')

@section('title', 'Pay Invoice')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Pay Invoice</h1>
                    <p class="text-gray-600">{{ $invoice->invoice_number }} - {{ $invoice->title }}</p>
                </div>
                <a href="{{ route('company.invoices.show', $invoice) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    ← Back to Invoice
                </a>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Payment Form -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Select Payment Method</h3>
                    </div>
                    
                    @if($paymentMethods->count() > 0)
                        <form action="{{ route('company.invoices.pay', $invoice) }}" method="POST" class="p-6">
                            @csrf
                            
                            <div class="space-y-4">
                                @foreach($paymentMethods as $paymentMethod)
                                    <div class="relative">
                                        <input type="radio" name="payment_method_id" value="{{ $paymentMethod->id }}" 
                                               id="payment_{{ $paymentMethod->id }}" required
                                               class="sr-only peer" {{ $loop->first ? 'checked' : '' }}>
                                        <label for="payment_{{ $paymentMethod->id }}" 
                                               class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 peer-checked:border-primary-500 peer-checked:ring-2 peer-checked:ring-primary-500">
                                            <div class="flex items-center w-full">
                                                <div class="flex-shrink-0">
                                                    @if($paymentMethod->type === 'card')
                                                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                                            </svg>
                                                        </div>
                                                    @else
                                                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="ml-4 flex-1">
                                                    <div class="flex items-center justify-between">
                                                        <div>
                                                            <p class="text-sm font-medium text-gray-900">
                                                                @if($paymentMethod->type === 'card')
                                                                    {{ ucfirst($paymentMethod->brand ?? 'Card') }} ending in {{ $paymentMethod->last_four }}
                                                                @else
                                                                    {{ $paymentMethod->bank_name ?? 'Bank Account' }} ending in {{ $paymentMethod->last_four }}
                                                                @endif
                                                            </p>
                                                            <p class="text-sm text-gray-500">
                                                                @if($paymentMethod->type === 'card')
                                                                    Expires {{ $paymentMethod->exp_month }}/{{ $paymentMethod->exp_year }}
                                                                @else
                                                                    {{ ucfirst($paymentMethod->account_holder_type ?? 'individual') }} account
                                                                @endif
                                                            </p>
                                                        </div>
                                                        <div class="flex items-center space-x-2">
                                                            @if($paymentMethod->is_default)
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                    Default
                                                                </span>
                                                            @endif
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                                                {{ $paymentMethod->verification_status === 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                                {{ $paymentMethod->verification_status === 'verified' ? 'Verified' : 'Pending' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            @error('payment_method_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <!-- Submit Button -->
                            <div class="mt-6 flex justify-end space-x-3">
                                <a href="{{ route('company.invoices.show', $invoice) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm font-medium">
                                    Cancel
                                </a>
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                    💳 Pay ${{ number_format($invoice->total_amount, 2) }}
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="p-6 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No payment methods available</h3>
                            <p class="mt-1 text-sm text-gray-500">You need to add a payment method before you can pay this invoice.</p>
                            <div class="mt-6">
                                <a href="{{ route('company.payment-methods.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Add Payment Method
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Invoice Summary -->
            <div class="space-y-6">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Invoice Summary</h3>
                    </div>
                    <div class="p-6">
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Invoice Number</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->invoice_number }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Title</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->title }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Agent</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->agent->user->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                                <dd class="text-sm text-gray-900 {{ $invoice->isOverdue() ? 'text-red-600' : '' }}">
                                    {{ $invoice->due_date->format('M d, Y') }}
                                    @if($invoice->isOverdue())
                                        <span class="text-red-600 text-xs">(Overdue)</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Amount Breakdown -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Amount Breakdown</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Subtotal</span>
                                <span class="text-sm text-gray-900">${{ number_format($invoice->amount, 2) }}</span>
                            </div>
                            @if($invoice->tax_amount > 0)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Tax</span>
                                    <span class="text-sm text-gray-900">${{ number_format($invoice->tax_amount, 2) }}</span>
                                </div>
                            @endif
                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-medium text-gray-900">Total</span>
                                    <span class="text-lg font-medium text-gray-900">${{ number_format($invoice->total_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security Notice -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Secure Payment</h3>
                            <p class="mt-1 text-sm text-blue-700">
                                Your payment is processed securely through Stripe. Your card information is never stored on our servers.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 