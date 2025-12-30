<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pay Invoice - {{ $invoice->invoice_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Invoice Payment</h1>
                <p class="mt-2 text-sm text-gray-600">Secure payment powered by Stripe</p>
            </div>

            <!-- Invoice Details Card -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
                <div class="bg-indigo-600 px-6 py-4">
                    <h2 class="text-xl font-semibold text-white">{{ $invoice->invoice_number }}</h2>
                    <p class="text-indigo-100 text-sm">{{ $invoice->title }}</p>
                </div>
                
                <div class="px-6 py-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-1">From</h3>
                            <p class="text-base text-gray-900">{{ $invoice->company->company_name }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-1">To</h3>
                            <p class="text-base text-gray-900">{{ $invoice->agent->user->name }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Due Date</h3>
                            <p class="text-base text-gray-900">{{ $invoice->due_date->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Invoice Date</h3>
                            <p class="text-base text-gray-900">{{ $invoice->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>

                    @if($invoice->description)
                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Description</h3>
                        <p class="text-base text-gray-900">{{ $invoice->description }}</p>
                    </div>
                    @endif

                    <div class="border-t border-gray-200 pt-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600">Subtotal</span>
                            <span class="text-sm text-gray-900">${{ number_format($invoice->amount, 2) }}</span>
                        </div>
                        @if($invoice->tax_amount > 0)
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600">Tax</span>
                            <span class="text-sm text-gray-900">${{ number_format($invoice->tax_amount, 2) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                            <span class="text-lg font-semibold text-gray-900">Total Amount</span>
                            <span class="text-2xl font-bold text-indigo-600">${{ number_format($invoice->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Method Selection -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Select Payment Method</h2>
                </div>
                
                <div class="px-6 py-6">
                    <!-- Payment Method Tabs -->
                    <div class="flex space-x-4 mb-6">
                        <button id="cardTab" class="payment-tab active flex-1 py-2 px-4 text-center border-b-2 border-indigo-600 text-indigo-600 font-medium">
                            Credit/Debit Card
                        </button>
                        <button id="achTab" class="payment-tab flex-1 py-2 px-4 text-center border-b-2 border-gray-300 text-gray-600 font-medium">
                            Bank Account (ACH)
                        </button>
                    </div>

                    <!-- Card Payment Form -->
                    <div id="cardPaymentForm" class="payment-form">
                        <div id="card-element" class="p-3 border border-gray-300 rounded-md mb-4"></div>
                        <div id="card-errors" class="text-red-600 text-sm mb-4"></div>
                        <button id="cardPayButton" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-md transition duration-200">
                            Pay ${{ number_format($invoice->total_amount, 2) }}
                        </button>
                    </div>

                    <!-- ACH Payment Form -->
                    <div id="achPaymentForm" class="payment-form hidden">
                        <p class="text-sm text-gray-600 mb-4">Connect your bank account securely using Stripe to pay via ACH transfer.</p>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-4">
                            <div class="flex items-start">
                                <input type="checkbox" id="achMandate" class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="achMandate" class="ml-2 text-sm text-gray-700">
                                    I authorize <strong>{{ $invoice->company->company_name }}</strong> to electronically debit my account for <strong>${{ number_format($invoice->total_amount, 2) }}</strong> and, if necessary, electronically credit my account to correct erroneous debits.
                                </label>
                            </div>
                        </div>
                        
                        <button id="achPayButton" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-md transition duration-200" disabled>
                            Connect Bank Account
                        </button>
                        <div id="ach-status" class="mt-4 text-sm text-gray-600"></div>
                    </div>

                    <!-- Loading Indicator -->
                    <div id="loadingIndicator" class="hidden text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
                        <p class="mt-4 text-gray-600">Processing payment...</p>
                    </div>

                    <!-- Success Message -->
                    <div id="successMessage" class="hidden bg-green-50 border border-green-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-green-800">Payment Successful!</h3>
                                <p class="mt-2 text-sm text-green-700">Your payment has been processed successfully. You can close this page.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div id="errorMessage" class="hidden bg-red-50 border border-red-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Payment Failed</h3>
                                <p id="errorText" class="mt-2 text-sm text-red-700"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="mt-6 text-center text-sm text-gray-500">
                <svg class="inline-block h-5 w-5 text-gray-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Your payment is secured with 256-bit SSL encryption
            </div>
        </div>
    </div>

    <script>
        const stripe = Stripe('{{ $stripeKey }}');
        const token = '{{ $invoice->payment_token }}';
        let cardElement;
        let financialConnectionsSession;
        let guestCustomerId = null;
        let guestPaymentMethodId = null;

        // Initialize Stripe Elements for Card
        const elements = stripe.elements();
        cardElement = elements.create('card', {
            style: {
                base: {
                    fontSize: '16px',
                    color: '#32325d',
                    fontFamily: '-apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif',
                    '::placeholder': {
                        color: '#aab7c4'
                    }
                },
                invalid: {
                    color: '#fa755a',
                    iconColor: '#fa755a'
                }
            }
        });
        cardElement.mount('#card-element');

        // Handle card errors
        cardElement.on('change', function(event) {
            const displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });

        // Tab switching
        document.getElementById('cardTab').addEventListener('click', function() {
            switchTab('card');
        });

        document.getElementById('achTab').addEventListener('click', function() {
            switchTab('ach');
        });

        function switchTab(tab) {
            const cardTab = document.getElementById('cardTab');
            const achTab = document.getElementById('achTab');
            const cardForm = document.getElementById('cardPaymentForm');
            const achForm = document.getElementById('achPaymentForm');

            if (tab === 'card') {
                cardTab.classList.add('active', 'border-indigo-600', 'text-indigo-600');
                cardTab.classList.remove('border-gray-300', 'text-gray-600');
                achTab.classList.remove('active', 'border-indigo-600', 'text-indigo-600');
                achTab.classList.add('border-gray-300', 'text-gray-600');
                cardForm.classList.remove('hidden');
                achForm.classList.add('hidden');
            } else {
                achTab.classList.add('active', 'border-indigo-600', 'text-indigo-600');
                achTab.classList.remove('border-gray-300', 'text-gray-600');
                cardTab.classList.remove('active', 'border-indigo-600', 'text-indigo-600');
                cardTab.classList.add('border-gray-300', 'text-gray-600');
                achForm.classList.remove('hidden');
                cardForm.classList.add('hidden');
                
                // Reset ACH mandate checkbox when switching to ACH tab
                const achMandate = document.getElementById('achMandate');
                achMandate.checked = false;
                document.getElementById('achPayButton').disabled = true;
            }
            hideMessages();
        }

        // Card Payment
        document.getElementById('cardPayButton').addEventListener('click', async function(e) {
            e.preventDefault();
            showLoading();

            try {
                // Create payment method
                const {paymentMethod, error} = await stripe.createPaymentMethod({
                    type: 'card',
                    card: cardElement,
                });

                if (error) {
                    throw new Error(error.message);
                }

                // Process payment directly without customer for card payments
                await processGuestPayment(paymentMethod.id, null);
            } catch (error) {
                showError(error.message);
            }
        });

        // Enable ACH button when mandate is accepted
        document.getElementById('achMandate').addEventListener('change', function(e) {
            document.getElementById('achPayButton').disabled = !e.target.checked;
        });

        // ACH Payment
        document.getElementById('achPayButton').addEventListener('click', async function(e) {
            e.preventDefault();
            
            // Check if mandate is accepted
            if (!document.getElementById('achMandate').checked) {
                showError('Please accept the ACH authorization agreement to continue.');
                return;
            }
            
            showLoading();

            try {
                // Create Financial Connections session
                const response = await fetch(`/invoice/pay/${token}/create-fc-session`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.error);
                }

                // Store customer ID for later use
                guestCustomerId = result.customer_id;

                hideLoading();

                // Collect bank account using Financial Connections
                const fcSession = await stripe.collectFinancialConnectionsAccounts({
                    clientSecret: result.client_secret,
                });

                if (fcSession.error) {
                    throw new Error(fcSession.error.message);
                }

                showLoading();

                // Poll to check if bank account was added
                await checkBankAccountAddition(result.session_id, guestCustomerId);

            } catch (error) {
                showError(error.message);
            }
        });

        async function checkBankAccountAddition(sessionId, customerId, attempts = 0) {
            const maxAttempts = 10;
            
            try {
                const response = await fetch(`/invoice/pay/${token}/check-fc-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ 
                        session_id: sessionId,
                        customer_id: customerId 
                    })
                });

                const result = await response.json();

                if (result.success && result.complete) {
                    // Bank account added successfully
                    if (result.payment_method_id) {
                        // Store payment method ID and automatically process payment
                        guestPaymentMethodId = result.payment_method_id;
                        await processGuestPayment(guestPaymentMethodId, customerId);
                    } else {
                        showError('Bank account connected but payment method ID not received.');
                    }
                } else if (attempts < maxAttempts) {
                    // Try again after 2 seconds
                    setTimeout(() => checkBankAccountAddition(sessionId, customerId, attempts + 1), 2000);
                } else {
                    throw new Error('Timeout waiting for bank account connection.');
                }
            } catch (error) {
                showError(error.message);
            }
        }

        async function processGuestPayment(stripePaymentMethodId, customerId = null) {
            try {
                showLoading();

                const paymentData = {
                    stripe_payment_method_id: stripePaymentMethodId
                };
                
                // Only add customer_id if it exists (for ACH payments)
                if (customerId) {
                    paymentData.customer_id = customerId;
                }

                const paymentResponse = await fetch(`/invoice/pay/${token}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(paymentData)
                });

                const paymentResult = await paymentResponse.json();

                if (!paymentResult.success) {
                    throw new Error(paymentResult.error);
                }

                showSuccess();
            } catch (error) {
                showError(error.message);
            }
        }

        function showLoading() {
            document.getElementById('cardPaymentForm').classList.add('hidden');
            document.getElementById('achPaymentForm').classList.add('hidden');
            document.getElementById('loadingIndicator').classList.remove('hidden');
            hideMessages();
        }

        function hideLoading() {
            document.getElementById('loadingIndicator').classList.add('hidden');
            const activeTab = document.querySelector('.payment-tab.active').id;
            if (activeTab === 'cardTab') {
                document.getElementById('cardPaymentForm').classList.remove('hidden');
            } else {
                document.getElementById('achPaymentForm').classList.remove('hidden');
            }
        }

        function showSuccess(message = null) {
            hideLoading();
            document.getElementById('cardPaymentForm').classList.add('hidden');
            document.getElementById('achPaymentForm').classList.add('hidden');
            document.getElementById('successMessage').classList.remove('hidden');
            if (message) {
                document.querySelector('#successMessage p').textContent = message;
            }
        }

        function showError(message) {
            hideLoading();
            document.getElementById('errorMessage').classList.remove('hidden');
            document.getElementById('errorText').textContent = message;
        }

        function hideMessages() {
            document.getElementById('successMessage').classList.add('hidden');
            document.getElementById('errorMessage').classList.add('hidden');
        }
    </script>
</body>
</html>

