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

                <!-- Verification Method Selection -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Choose Your Verification Method</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>Select how you'd like to verify your bank account:</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Verification Options -->
                <div class="space-y-4 mb-6">
                    <!-- Instant Verification Option -->
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors cursor-pointer verification-option" data-method="instant">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="instant-verification" name="verification_method" type="radio" value="instant" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300" checked>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="instant-verification" class="font-medium text-gray-900 cursor-pointer">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-2">
                                        Recommended
                                    </span>
                                    Instant Verification
                                </label>
                                <p class="text-gray-500 mt-1">Connect your bank account securely through your online banking. Verification happens instantly.</p>
                                <div class="mt-2">
                                    <span class="inline-flex items-center text-xs text-green-600">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Instant • Secure • No waiting
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Micro-deposit Option -->
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors cursor-pointer verification-option" data-method="microdeposits">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="microdeposit-verification" name="verification_method" type="radio" value="microdeposits" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="microdeposit-verification" class="font-medium text-gray-900 cursor-pointer">Micro-deposit Verification</label>
                                <p class="text-gray-500 mt-1">We'll send small deposits to your account that you'll need to verify.</p>
                                <div class="mt-2">
                                    <span class="inline-flex items-center text-xs text-yellow-600">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                        </svg>
                                        Takes 1-2 business days
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Micro-Deposit Instructions (initially hidden) -->
                <div id="microdeposit-instructions" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6" style="display: none;">
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

                <!-- Start Verification Button -->
                <div id="start-verification-section" class="mb-6">
                    <button type="button" id="start-verification-btn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-md transition-colors">
                        Start Verification
                    </button>
                </div>

                <!-- Micro-Deposit Form (initially hidden) -->
                <div id="microdeposit-form-section" style="display: none;">
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
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Stripe
    const stripe = Stripe('{{ config('cashier.key') }}');

    // DOM elements
    const startVerificationBtn = document.getElementById('start-verification-btn');
    const startVerificationSection = document.getElementById('start-verification-section');
    const microdepositFormSection = document.getElementById('microdeposit-form-section');
    const microdepositInstructions = document.getElementById('microdeposit-instructions');
    const verificationOptions = document.querySelectorAll('input[name="verification_method"]');
    const form = document.getElementById('verification-form');

    // Handle verification method selection
    verificationOptions.forEach(option => {
        option.addEventListener('change', function() {
            if (this.value === 'microdeposits') {
                microdepositInstructions.style.display = 'block';
            } else {
                microdepositInstructions.style.display = 'none';
            }
        });
    });

    // Handle verification option clicks
    document.querySelectorAll('.verification-option').forEach(option => {
        option.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
            radio.dispatchEvent(new Event('change'));
        });
    });

    // Handle start verification button click
    startVerificationBtn.addEventListener('click', async function() {
        const selectedMethod = document.querySelector('input[name="verification_method"]:checked').value;
        
        // Disable button and show loading
        startVerificationBtn.disabled = true;
        startVerificationBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Starting Verification...
        `;

        try {
            const response = await fetch('{{ route('company.payment-methods.initiate-verification', $paymentMethod) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    verification_method: selectedMethod
                })
            });

            const data = await response.json();

            if (data.success) {
                if (data.verification_method === 'instant' && data.client_secret) {
                    // Handle Financial Connections
                    const isLocalDevelopment = window.location.protocol === 'http:' && 
                                             (window.location.hostname === 'localhost' || 
                                              window.location.hostname === '127.0.0.1');
                    
                    if (isLocalDevelopment) {
                        // For local development, open Financial Connections in a popup
                        // and poll for completion
                        try {
                            const { error } = await stripe.collectFinancialConnectionsAccounts({
                                clientSecret: data.client_secret
                            });

                            if (error) {
                                alert('❌ Error: ' + error.message);
                                startVerificationBtn.disabled = false;
                                startVerificationBtn.innerHTML = 'Start Verification';
                                return;
                            }

                            // Start polling for completion
                            startVerificationBtn.innerHTML = 'Checking verification status...';
                            await pollForVerificationCompletion();
                        } catch (error) {
                            console.error('Financial Connections error:', error);
                            alert('❌ An error occurred during verification');
                            startVerificationBtn.disabled = false;
                            startVerificationBtn.innerHTML = 'Start Verification';
                        }
                    } else {
                        // For production with HTTPS, use normal flow with return URL
                        const { error } = await stripe.collectFinancialConnectionsAccounts({
                            clientSecret: data.client_secret,
                            returnUrl: window.location.href
                        });

                        if (error) {
                            alert('❌ Error: ' + error.message);
                            startVerificationBtn.disabled = false;
                            startVerificationBtn.innerHTML = 'Start Verification';
                        }
                        // If no error, the user will be redirected to the return URL
                    }
                } else {
                    // Handle micro-deposits
                    alert('✅ ' + data.message);
                    startVerificationSection.style.display = 'none';
                    microdepositFormSection.style.display = 'block';
                }
            } else {
                alert('❌ Error: ' + data.error);
                // Reset button
                startVerificationBtn.disabled = false;
                startVerificationBtn.innerHTML = 'Start Verification';
            }
        } catch (error) {
            console.error('Verification error:', error);
            alert('❌ An unexpected error occurred. Please try again.');
            // Reset button
            startVerificationBtn.disabled = false;
            startVerificationBtn.innerHTML = 'Start Verification';
        }
    });

    // Polling function for local development
    async function pollForVerificationCompletion() {
        const maxAttempts = 30; // Poll for up to 5 minutes (30 * 10 seconds)
        let attempts = 0;

        const poll = async () => {
            attempts++;
            
            try {
                const response = await fetch('{{ route('company.payment-methods.check-status', $paymentMethod) }}', {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();

                if (data.success && data.complete) {
                    // Verification completed
                    alert('✅ ' + data.message);
                    window.location.href = data.redirect || '{{ route('company.payment-methods') }}';
                    return;
                }

                if (attempts >= maxAttempts) {
                    alert('⏱️ Verification is taking longer than expected. Please check back in a few minutes or try again.');
                    startVerificationBtn.disabled = false;
                    startVerificationBtn.innerHTML = 'Start Verification';
                    return;
                }

                // Continue polling
                setTimeout(poll, 10000); // Poll every 10 seconds
            } catch (error) {
                console.error('Polling error:', error);
                if (attempts >= maxAttempts) {
                    alert('❌ Unable to check verification status. Please refresh the page and try again.');
                    startVerificationBtn.disabled = false;
                    startVerificationBtn.innerHTML = 'Start Verification';
                } else {
                    setTimeout(poll, 10000); // Continue polling despite error
                }
            }
        };

        // Start polling
        setTimeout(poll, 5000); // Wait 5 seconds before first poll
    }

    // Micro-deposit form handling (if form exists)
    if (form) {
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
        const amount1Input = document.getElementById('amount_1');
        const amount2Input = document.getElementById('amount_2');
        if (amount1Input) amount1Input.addEventListener('input', validateAmounts);
        if (amount2Input) amount2Input.addEventListener('input', validateAmounts);

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
    }
});
</script>
@endsection 