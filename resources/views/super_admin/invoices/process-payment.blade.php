@extends('layouts.app')

@section('title', 'Process Payment')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold">Process Payment</h2>
                        <p class="text-gray-600">Invoice {{ $invoice->invoice_number }}</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('super_admin.invoices.show', $invoice) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            ← Back to Invoice
                        </a>
                    </div>
                </div>

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <div class="font-bold">Please correct the following errors:</div>
                        <ul class="mt-3 list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Invoice Summary -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Invoice Details -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium mb-4">Invoice Details</h3>
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
                                <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->due_date->format('M d, Y') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="text-sm">
                                    @if($invoice->status === 'pending')
                                        @if($invoice->isOverdue())
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Overdue</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                        @endif
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($invoice->status) }}</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Amount Summary -->
                    <div class="bg-blue-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium mb-4">Payment Summary</h3>
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
                            <div class="text-xs text-gray-500 mt-2">
                                <div class="flex justify-between">
                                    <span>Admin Commission:</span>
                                    <span>$2.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Net to Agent:</span>
                                    <span>${{ number_format($invoice->total_amount - 2, 2) }}</span>
                                </div>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Payment Method Selection -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium mb-4">Select Payment Method</h3>
                    
                    @if($paymentMethods->count() > 0)
                        <form method="POST" action="{{ route('super_admin.invoices.payment', $invoice) }}">
                            @csrf
                            
                            <div class="space-y-4">
                                @foreach($paymentMethods as $paymentMethod)
                                    <div class="relative">
                                        <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 @error('payment_method_id') border-red-300 @enderror">
                                            <input type="radio" name="payment_method_id" value="{{ $paymentMethod->id }}" 
                                                   class="sr-only peer" {{ old('payment_method_id') == $paymentMethod->id ? 'checked' : '' }}>
                                            <div class="w-4 h-4 border-2 border-gray-300 rounded-full mr-3 peer-checked:border-primary-600 peer-checked:bg-primary-600 flex items-center justify-center">
                                                <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <h4 class="text-sm font-medium text-gray-900">
                                                            @if($paymentMethod->type === 'card')
                                                                {{ ucfirst($paymentMethod->brand) }} ending in {{ $paymentMethod->last_four }}
                                                            @else
                                                                {{ ucfirst($paymentMethod->type) }} 
                                                                @if($paymentMethod->bank_name)
                                                                    ({{ $paymentMethod->bank_name }})
                                                                @endif
                                                                @if($paymentMethod->last_four)
                                                                    ending in {{ $paymentMethod->last_four }}
                                                                @endif
                                                            @endif
                                                        </h4>
                                                        <p class="text-sm text-gray-500">
                                                            {{ ucfirst($paymentMethod->type) }} 
                                                            @if($paymentMethod->type === 'card' && $paymentMethod->exp_month && $paymentMethod->exp_year)
                                                                • Expires {{ $paymentMethod->exp_month }}/{{ $paymentMethod->exp_year }}
                                                            @endif
                                                        </p>
                                                    </div>
                                                    @if($paymentMethod->is_default)
                                                        <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                                            Default
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            
                            @error('payment_method_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <div class="mt-6 flex items-center justify-between">
                                <div class="text-sm text-gray-500">
                                    <p>⚠️ This action will charge the selected payment method immediately.</p>
                                    <p>The transaction cannot be undone once processed.</p>
                                </div>
                                <div class="flex gap-3">
                                    <a href="{{ route('super_admin.invoices.show', $invoice) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-md text-sm font-medium">
                                        Cancel
                                    </a>
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md text-sm font-medium">
                                        Process Payment (${{ number_format($invoice->total_amount, 2) }})
                                    </button>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="text-center py-8">
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">
                                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L3.732 15c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                            </div>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No Payment Methods Available</h3>
                            <p class="mt-1 text-sm text-gray-500">The company {{ $invoice->company->company_name }} has no active payment methods.</p>
                            <div class="mt-6">
                                <p class="text-sm text-gray-600">To process this payment, the company needs to:</p>
                                <ul class="mt-2 text-sm text-gray-600 list-disc list-inside">
                                    <li>Complete Stripe Connect onboarding</li>
                                    <li>Add at least one payment method</li>
                                </ul>
                            </div>
                            <div class="mt-6">
                                <a href="{{ route('super_admin.companies.show', $invoice->company) }}" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                    View Company Details
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 