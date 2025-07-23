@extends('layouts.app')

@section('title', 'Verify Payment Method')

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold">Verify Payment Method</h2>
                        <p class="text-gray-600">Enter the micro-deposit amounts to verify your bank account</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('company.payment-methods') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            ← Back to Payment Methods
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

                <!-- Payment Method Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Bank Account Information</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p><strong>Bank Account:</strong> {{ $paymentMethod->bank_name ?? 'Bank Account' }} ending in {{ $paymentMethod->last_four }}</p>
                                <p><strong>Account Type:</strong> {{ ucfirst($paymentMethod->account_holder_type ?? 'individual') }} {{ $paymentMethod->account_type ?? 'checking' }} account</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Verification Instructions -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Micro-Deposit Verification</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p class="mb-2"><strong>Please check your bank account for two small deposits (usually under $1.00).</strong></p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li>These deposits typically appear within 1-2 business days</li>
                                    <li>Look for deposits from "STRIPE" or similar</li>
                                    <li>Enter the exact amounts below to complete verification</li>
                                    <li>The deposits will be automatically reversed</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Verification Form -->
                <form id="verification-form" method="POST" action="{{ route('company.payment-methods.verify.submit', $paymentMethod) }}">
                    @csrf

                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Enter Micro-Deposit Amounts</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="amount_1" class="block text-sm font-medium text-gray-700">First Deposit Amount</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">$</span>
                                        </div>
                                        <input type="number" 
                                               id="amount_1" 
                                               name="amount_1" 
                                               step="0.01" 
                                               min="0" 
                                               max="1" 
                                               required
                                               class="focus:ring-primary-500 focus:border-primary-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md"
                                               placeholder="0.32"
                                               autocomplete="off">
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">USD</span>
                                        </div>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">Enter the exact amount of the first deposit</p>
                                </div>

                                <div>
                                    <label for="amount_2" class="block text-sm font-medium text-gray-700">Second Deposit Amount</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">$</span>
                                        </div>
                                        <input type="number" 
                                               id="amount_2" 
                                               name="amount_2" 
                                               step="0.01" 
                                               min="0" 
                                               max="1" 
                                               required
                                               class="focus:ring-primary-500 focus:border-primary-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md"
                                               placeholder="0.47"
                                               autocomplete="off">
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">USD</span>
                                        </div>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">Enter the exact amount of the second deposit</p>
                                </div>
                            </div>
                            <div id="verification-errors" class="mt-2 text-sm text-red-600"></div>
                        </div>

                        <!-- Security Notice -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-gray-800">Security & Privacy</h3>
                                    <p class="mt-1 text-sm text-gray-600">
                                        This verification process is securely handled by Stripe. We never store your banking credentials, and the micro-deposits are automatically reversed after verification.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="h-4 w-4 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm text-gray-500">Verification attempts: {{ $paymentMethod->verification_attempts ?? 0 }}/3</span>
                            </div>
                            <div class="flex gap-3">
                                <a href="{{ route('company.payment-methods') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-md text-sm font-medium">
                                    Cancel
                                </a>
                                <button type="submit" id="submit-button" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded-md text-sm font-medium disabled:opacity-50">
                                    <span id="button-text">Verify Bank Account</span>
                                    <span id="spinner" class="hidden">
                                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Verifying...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('verification-form');
    const submitButton = document.getElementById('submit-button');
    const buttonText = document.getElementById('button-text');
    const spinner = document.getElementById('spinner');
    const errorsDiv = document.getElementById('verification-errors');

    // Form validation
    function validateAmounts() {
        const amount1 = parseFloat(document.getElementById('amount_1').value);
        const amount2 = parseFloat(document.getElementById('amount_2').value);
        
        let errors = [];
        
        if (!amount1 || amount1 <= 0 || amount1 > 1) {
            errors.push('First deposit amount must be between $0.01 and $1.00');
        }
        
        if (!amount2 || amount2 <= 0 || amount2 > 1) {
            errors.push('Second deposit amount must be between $0.01 and $1.00');
        }
        
        if (amount1 && amount2 && amount1 === amount2) {
            errors.push('The two deposit amounts should be different');
        }
        
        if (errors.length > 0) {
            errorsDiv.textContent = errors.join('. ');
            return false;
        } else {
            errorsDiv.textContent = '';
            return true;
        }
    }

    // Add validation to amount inputs
    document.getElementById('amount_1').addEventListener('input', validateAmounts);
    document.getElementById('amount_2').addEventListener('input', validateAmounts);

    // Handle form submission
    form.addEventListener('submit', async function(event) {
        event.preventDefault();
        
        if (!validateAmounts()) {
            return;
        }

        // Disable submit button and show loading
        submitButton.disabled = true;
        buttonText.classList.add('hidden');
        spinner.classList.remove('hidden');

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Show success message and redirect
                alert('✅ Bank account verified successfully!\n\nYour payment method is now ready to use.');
                window.location.href = data.redirect || '{{ route('company.payment-methods') }}';
            } else {
                // Show error message
                errorsDiv.textContent = data.error || 'Verification failed. Please check your amounts and try again.';
                
                // Re-enable submit button
                submitButton.disabled = false;
                buttonText.classList.remove('hidden');
                spinner.classList.add('hidden');
            }
        } catch (error) {
            console.error('Verification error:', error);
            errorsDiv.textContent = 'An unexpected error occurred. Please try again.';
            
            // Re-enable submit button
            submitButton.disabled = false;
            buttonText.classList.remove('hidden');
            spinner.classList.add('hidden');
        }
    });
});
</script>
@endsection 