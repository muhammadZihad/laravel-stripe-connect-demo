@extends('layouts.app')

@section('title', 'Add Payment Method')

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Add Payment Method</h1>
                    <p class="text-gray-600">Add a new payment method for your company</p>
                </div>
                <a href="{{ route('company.payment-methods') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    ← Back to Payment Methods
                </a>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                <h3 class="mt-2 text-lg font-medium text-gray-900">Payment Method Integration</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Payment method creation requires Stripe Elements integration. This would typically include:
                </p>
                <div class="mt-4 text-left bg-gray-50 rounded-lg p-4">
                    <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                        <li>Stripe Elements for secure card input</li>
                        <li>Bank account setup form</li>
                        <li>Payment method validation</li>
                        <li>Secure tokenization before submission</li>
                    </ul>
                </div>
                <p class="mt-4 text-sm text-gray-500">
                    In a production environment, this page would contain the Stripe Elements form for secure payment method collection.
                </p>
                <div class="mt-6">
                    <a href="{{ route('company.payment-methods') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700">
                        Return to Payment Methods
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 