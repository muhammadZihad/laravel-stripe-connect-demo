@extends('layouts.app')

@section('title', 'Payment Methods')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Payment Methods</h1>
                    <p class="text-gray-600">Manage your company's payment methods</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('company.payment-methods.create') }}" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Add Payment Method
                    </a>
                    <a href="{{ route('company.dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        ← Back to Dashboard
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

        <!-- Payment Methods List -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            @if($paymentMethods->count() > 0)
                <ul class="divide-y divide-gray-200">
                    @foreach($paymentMethods as $paymentMethod)
                        <li class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
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
                                    <div class="ml-4">
                                        <div class="flex items-center">
                                            <p class="text-sm font-medium text-gray-900">
                                                @if($paymentMethod->type === 'card')
                                                    {{ ucfirst($paymentMethod->brand ?? 'Card') }} ending in {{ $paymentMethod->last_four }}
                                                @else
                                                    {{ $paymentMethod->bank_name ?? 'Bank Account' }} ending in {{ $paymentMethod->last_four }}
                                                @endif
                                            </p>
                                            @if($paymentMethod->is_default)
                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Default
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-500">
                                            @if($paymentMethod->type === 'card')
                                                Expires {{ $paymentMethod->exp_month }}/{{ $paymentMethod->exp_year }}
                                            @else
                                                {{ ucfirst($paymentMethod->account_holder_type ?? 'individual') }} account
                                            @endif
                                        </p>
                                        <div class="flex items-center mt-1">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                                {{ $paymentMethod->verification_status === 'verified' ? 'bg-green-100 text-green-800' : 
                                                   ($paymentMethod->verification_status === 'pending_verification' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                {{ $paymentMethod->verification_badge['text'] ?? ucfirst(str_replace('_', ' ', $paymentMethod->verification_status)) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    @if(!$paymentMethod->is_default && $paymentMethod->is_active)
                                        <form action="{{ route('company.payment-methods.set-default', $paymentMethod) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                                Set Default
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('company.payment-methods.delete', $paymentMethod) }}" method="POST" style="display: inline;" 
                                          onsubmit="return confirm('Are you sure you want to delete this payment method?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No payment methods</h3>
                    <p class="mt-1 text-sm text-gray-500">Add a payment method to start processing payments.</p>
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
</div>
@endsection 