@extends('layouts.app')

@section('title', 'Verify Bank Account')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Verify Your Bank Account</h1>
            <p class="mt-2 text-gray-600">Enter the micro-deposit amounts to complete verification</p>
        </div>

        <!-- Payment Method Info -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Bank Account Information</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Bank Name:</span>
                    <span class="font-medium">{{ $paymentMethod->bank_name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Account Type:</span>
                    <span class="font-medium">{{ ucfirst($paymentMethod->account_holder_type) ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Last 4 Digits:</span>
                    <span class="font-medium">•••• {{ $paymentMethod->last_four }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Verification Status:</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $paymentMethod->verification_badge['class'] }}">
                        {{ $paymentMethod->verification_badge['text'] }}
                    </span>
                </div>
                @if($paymentMethod->verification_initiated_at)
                <div class="flex justify-between">
                    <span class="text-gray-600">Verification Initiated:</span>
                    <span class="font-medium">{{ $paymentMethod->verification_initiated_at->format('M j, Y g:i A') }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Instructions -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-blue-900 mb-3">Verification Instructions</h3>
            <div class="text-sm text-blue-800 space-y-2">
                <p><strong>Step 1:</strong> Check your bank account for two small deposits from Stripe (usually within 1-2 business days).</p>
                <p><strong>Step 2:</strong> Enter the exact amounts of these deposits below (in dollars and cents).</p>
                <p><strong>Step 3:</strong> Click "Verify Account" to complete the process.</p>
                <p class="mt-4 text-xs"><strong>Note:</strong> The deposits will typically be amounts like $0.32 and $0.45. Enter them exactly as they appear in your bank statement.</p>
            </div>
        </div>

        <!-- Verification Form -->
        <div class="bg-white shadow rounded-lg p-6">
            <form id="verification-form" class="space-y-6">
                @csrf
                
                <!-- Error Display -->
                <div id="error-message" class="hidden bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-800" id="error-text"></p>
                        </div>
                    </div>
                </div>

                <!-- Amount Inputs -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="amount_1" class="block text-sm font-medium text-gray-700 mb-2">
                            First Deposit Amount
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" 
                                   id="amount_1" 
                                   name="amount_1" 
                                   step="0.01" 
                                   min="0" 
                                   max="1" 
                                   placeholder="0.32"
                                   class="block w-full pl-7 pr-12 py-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                   required>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Enter amount like 0.32 (32 cents)</p>
                    </div>

                    <div>
                        <label for="amount_2" class="block text-sm font-medium text-gray-700 mb-2">
                            Second Deposit Amount
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" 
                                   id="amount_2" 
                                   name="amount_2" 
                                   step="0.01" 
                                   min="0" 
                                   max="1" 
                                   placeholder="0.45"
                                   class="block w-full pl-7 pr-12 py-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                   required>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Enter amount like 0.45 (45 cents)</p>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-between pt-6">
                    <a href="{{ route('agent.payment-methods') }}" 
                       class="text-gray-600 hover:text-gray-900 font-medium">
                        ← Back to Payment Methods
                    </a>
                    
                    <button type="submit" 
                            id="verify-button" 
                            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span id="button-text">Verify Account</span>
                        <div id="spinner" class="hidden ml-2 w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    </button>
                </div>
            </form>
        </div>

        <!-- Help Section -->
        <div class="mt-8 bg-gray-50 border border-gray-200 rounded-lg p-6">
            <h3 class="text-sm font-medium text-gray-900 mb-3">Need Help?</h3>
            <div class="text-sm text-gray-600 space-y-2">
                <p><strong>Don't see the deposits?</strong> They typically arrive within 1-2 business days. Check your account again tomorrow.</p>
                <p><strong>Can't find the exact amounts?</strong> Look for very small deposits (usually under $1.00) from "Stripe" or "STRIPE".</p>
                <p><strong>Still having trouble?</strong> Contact support for assistance with your account verification.</p>
                <p class="mt-4 text-xs"><strong>Security:</strong> These micro-deposits will be automatically reversed after verification.</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('verification-form');
    const verifyButton = document.getElementById('verify-button');
    const buttonText = document.getElementById('button-text');
    const spinner = document.getElementById('spinner');
    const errorMessage = document.getElementById('error-message');
    const errorText = document.getElementById('error-text');

    function showError(message) {
        errorText.textContent = message;
        errorMessage.classList.remove('hidden');
        errorMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function hideError() {
        errorMessage.classList.add('hidden');
    }

    function setLoading(loading) {
        verifyButton.disabled = loading;
        if (loading) {
            buttonText.classList.add('hidden');
            spinner.classList.remove('hidden');
        } else {
            buttonText.classList.remove('hidden');
            spinner.classList.add('hidden');
        }
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        hideError();
        setLoading(true);

        const formData = new FormData(form);
        const amount1 = parseFloat(formData.get('amount_1'));
        const amount2 = parseFloat(formData.get('amount_2'));

        // Validate amounts
        if (isNaN(amount1) || isNaN(amount2)) {
            showError('Please enter valid amounts for both deposits.');
            setLoading(false);
            return;
        }

        if (amount1 <= 0 || amount1 > 1 || amount2 <= 0 || amount2 > 1) {
            showError('Deposit amounts must be between $0.01 and $1.00.');
            setLoading(false);
            return;
        }

        try {
            const response = await fetch('{{ route('agent.payment-methods.verify.submit', $paymentMethod) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({
                    amount_1: amount1,
                    amount_2: amount2,
                }),
            });

            const data = await response.json();

            if (data.success) {
                // Show success message
                alert('✅ ' + data.message);
                // Redirect to payment methods page
                window.location.href = data.redirect;
            } else {
                showError(data.error || 'Verification failed. Please try again.');
                setLoading(false);
            }
        } catch (error) {
            console.error('Verification error:', error);
            showError('An unexpected error occurred. Please try again.');
            setLoading(false);
        }
    });

    // Format amount inputs
    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('input', function() {
            const value = parseFloat(this.value);
            if (!isNaN(value) && value > 1) {
                this.value = '1.00';
            }
        });
    });
});
</script>
@endsection 