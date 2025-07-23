@extends('layouts.app')

@section('title', 'Add Payment Method')

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold">Add Payment Method</h2>
                        <p class="text-gray-600">Add a new way to pay invoices</p>
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

                <!-- Demo Notice -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Stripe Payment Method</h3>
                            <p class="mt-1 text-sm text-blue-700">
                                Payment methods are securely processed through Stripe. Use test card numbers for demo purposes (4242424242424242).
                            </p>
                        </div>
                    </div>
                </div>

                <form id="payment-method-form">
                    @csrf

                    <!-- Billing Information -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Billing Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="billing_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                                <input id="billing_name" name="billing_name" type="text" required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                       value="{{ Auth::user()->name }}" placeholder="John Doe">
                            </div>
                            <div>
                                <label for="billing_email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input id="billing_email" name="billing_email" type="email" required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                       value="{{ Auth::user()->email }}" placeholder="john@example.com">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label for="billing_phone" class="block text-sm font-medium text-gray-700">Phone Number (Optional)</label>
                            <input id="billing_phone" name="billing_phone" type="tel"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                   placeholder="+1 (555) 123-4567">
                        </div>
                    </div>

                    <!-- Payment Method Type -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Payment Method Type</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="relative">
                                <input type="radio" name="type" value="card" class="sr-only peer" checked>
                                <div class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-primary-500 peer-checked:bg-primary-50">
                                    <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-lg mr-3">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">Credit/Debit Card</div>
                                        <div class="text-sm text-gray-500">Visa, Mastercard, AMEX</div>
                                    </div>
                                </div>
                            </label>

                            <label class="relative">
                                <input type="radio" name="type" value="us_bank_account" class="sr-only peer">
                                <div class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-primary-500 peer-checked:bg-primary-50">
                                    <div class="flex items-center justify-center w-10 h-10 bg-green-100 rounded-lg mr-3">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">Bank Account</div>
                                        <div class="text-sm text-gray-500">ACH transfers</div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Card Payment Section -->
                    <div id="card-section" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Card Information</label>
                            <div id="card-element" class="p-3 border border-gray-300 rounded-md bg-white">
                                <!-- Stripe Elements will mount here -->
                            </div>
                            <div id="card-errors" class="mt-2 text-sm text-red-600"></div>
                        </div>
                    </div>

                    <!-- Bank Account Section -->
                    <div id="bank-section" class="space-y-4" style="display: none;">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Bank Account Information</label>
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                                <p class="text-sm text-yellow-700">
                                    <strong>Note:</strong> For this demo, we'll collect bank details manually. In production, you would integrate with Stripe's ACH verification process.
                                </p>
                            </div>
                        </div>
                        
                        <!-- Manual Bank Info -->
                        <div id="manual-bank-info">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="routing_number" class="block text-sm font-medium text-gray-700">Routing Number</label>
                                    <input id="routing_number" name="routing_number" type="text" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                           placeholder="110000000" maxlength="9" pattern="[0-9]{9}">
                                    <p class="mt-1 text-sm text-gray-500">9-digit bank routing number</p>
                                </div>
                                <div>
                                    <label for="account_number" class="block text-sm font-medium text-gray-700">Account Number</label>
                                    <input id="account_number" name="account_number" type="text"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                           placeholder="000123456789" maxlength="17">
                                    <p class="mt-1 text-sm text-gray-500">Bank account number</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label for="account_holder_type" class="block text-sm font-medium text-gray-700">Account Holder Type</label>
                                    <select id="account_holder_type" name="account_holder_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                        <option value="individual">Individual</option>
                                        <option value="company">Company</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="account_type" class="block text-sm font-medium text-gray-700">Account Type</label>
                                    <select id="account_type" name="account_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                        <option value="checking">Checking</option>
                                        <option value="savings">Savings</option>
                                    </select>
                                </div>
                            </div>
                            <div id="bank-errors" class="mt-2 text-sm text-red-600"></div>
                        </div>
                    </div>

                    <!-- Make Default -->
                    <div class="mt-6">
                        <div class="flex items-center">
                            <input id="is_default" name="is_default" type="checkbox" value="1"
                                   class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                            <label for="is_default" class="ml-2 block text-sm text-gray-900">
                                Set as default payment method
                            </label>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">Your default payment method will be used for all new payments.</p>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-8 flex items-center justify-between">
                        <p class="text-sm text-gray-500">
                            This information will be securely stored and processed through Stripe.
                        </p>
                        <div class="flex gap-3">
                            <a href="{{ route('company.payment-methods') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-md text-sm font-medium">
                                Cancel
                            </a>
                            <button type="submit" id="submit-button" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded-md text-sm font-medium disabled:opacity-50" disabled>
                                <span id="button-text">Add Payment Method</span>
                                <span id="spinner" class="hidden">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Processing...
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Stripe.js -->
<script src="https://js.stripe.com/v3/"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Stripe
    const stripe = Stripe('{{ config('cashier.key') }}'); // Use your publishable key
    const elements = stripe.elements();
    
    // Create card element
    const cardElement = elements.create('card', {
        style: {
            base: {
                fontSize: '16px',
                color: '#424770',
                '::placeholder': {
                    color: '#aab7c4',
                },
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: 'antialiased',
            },
            invalid: {
                color: '#9e2146',
            },
        },
    });
    
    cardElement.mount('#card-element');
    
    // Handle payment method type switching
    const typeRadios = document.querySelectorAll('input[name="type"]');
    const cardSection = document.getElementById('card-section');
    const bankSection = document.getElementById('bank-section');
    const submitButton = document.getElementById('submit-button');
    
    function togglePaymentMethodFields() {
        const selectedType = document.querySelector('input[name="type"]:checked')?.value;
        
        if (selectedType === 'card') {
            cardSection.style.display = 'block';
            bankSection.style.display = 'none';
            submitButton.disabled = false;
        } else if (selectedType === 'us_bank_account') {
            cardSection.style.display = 'none';
            bankSection.style.display = 'block';
            submitButton.disabled = false;
        }
    }
    
    typeRadios.forEach(radio => {
        radio.addEventListener('change', togglePaymentMethodFields);
    });
    
    // Initialize on page load
    togglePaymentMethodFields();
    
    // Handle card element events
    cardElement.on('ready', () => {
        submitButton.disabled = false;
    });
    
    cardElement.on('change', (event) => {
        const cardErrors = document.getElementById('card-errors');
        if (event.error) {
            cardErrors.textContent = event.error.message;
        } else {
            cardErrors.textContent = '';
        }
    });
    
    // Bank account validation
    function validateBankAccount() {
        const routingNumber = document.getElementById('routing_number').value;
        const accountNumber = document.getElementById('account_number').value;
        const bankErrors = document.getElementById('bank-errors');
        
        let errors = [];
        
        if (!routingNumber || routingNumber.length !== 9 || !/^\d{9}$/.test(routingNumber)) {
            errors.push('Routing number must be exactly 9 digits');
        }
        
        if (!accountNumber || accountNumber.length < 4 || accountNumber.length > 17 || !/^\d+$/.test(accountNumber)) {
            errors.push('Account number must be 4-17 digits');
        }
        
        if (errors.length > 0) {
            bankErrors.textContent = errors.join(', ');
            return false;
        } else {
            bankErrors.textContent = '';
            return true;
        }
    }
    
    // Billing validation
    function validateBillingDetails() {
        const name = document.getElementById('billing_name').value.trim();
        const email = document.getElementById('billing_email').value.trim();
        
        if (!name) {
            alert('Please enter your full name');
            return false;
        }
        
        if (!email || !email.includes('@')) {
            alert('Please enter a valid email address');
            return false;
        }
        
        return true;
    }
    
    // Add validation to bank fields
    document.getElementById('routing_number').addEventListener('input', validateBankAccount);
    document.getElementById('account_number').addEventListener('input', validateBankAccount);
    
    // Handle form submission
    const form = document.getElementById('payment-method-form');
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        
        // Validate billing details first
        if (!validateBillingDetails()) {
            return;
        }
        
        const submitButton = document.getElementById('submit-button');
        const buttonText = document.getElementById('button-text');
        const spinner = document.getElementById('spinner');
        
        // Disable submit button and show loading
        submitButton.disabled = true;
        buttonText.classList.add('hidden');
        spinner.classList.remove('hidden');
        
        const selectedType = document.querySelector('input[name="type"]:checked')?.value;
        const isDefault = document.getElementById('is_default').checked;
        
        // Get billing details
        const billingDetails = {
            name: document.getElementById('billing_name').value.trim(),
            email: document.getElementById('billing_email').value.trim(),
        };
        
        const phone = document.getElementById('billing_phone').value.trim();
        if (phone) {
            billingDetails.phone = phone;
        }
        
        try {
            let result;
            
            if (selectedType === 'card') {
                // Create payment method with card
                result = await stripe.createPaymentMethod({
                    type: 'card',
                    card: cardElement,
                    billing_details: billingDetails,
                });
            } else if (selectedType === 'us_bank_account') {
                // Validate bank account first
                if (!validateBankAccount()) {
                    // Re-enable submit button
                    submitButton.disabled = false;
                    buttonText.classList.remove('hidden');
                    spinner.classList.add('hidden');
                    return;
                }
                
                // Create payment method with bank account using manual details
                const routingNumber = document.getElementById('routing_number').value;
                const accountNumber = document.getElementById('account_number').value;
                const accountHolderType = document.getElementById('account_holder_type').value;
                const accountType = document.getElementById('account_type').value;
                
                result = await stripe.createPaymentMethod({
                    type: 'us_bank_account',
                    us_bank_account: {
                        routing_number: routingNumber,
                        account_number: accountNumber,
                        account_holder_type: accountHolderType,
                        account_type: accountType,
                    },
                    billing_details: billingDetails,
                });
            }
            
            if (result.error) {
                // Show error to customer
                const errorElement = selectedType === 'card' ? 
                    document.getElementById('card-errors') : 
                    document.getElementById('bank-errors');
                errorElement.textContent = result.error.message;
                
                // Re-enable submit button
                submitButton.disabled = false;
                buttonText.classList.remove('hidden');
                spinner.classList.add('hidden');
            } else {
                // Send payment method to backend
                const response = await fetch('{{ route('company.payment-methods.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({
                        payment_method_id: result.paymentMethod.id,
                        is_default: isDefault,
                    }),
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Check if this was a bank account with verification requirements
                    if (data.verification_required) {
                        // Show verification notice for bank accounts
                        alert('✅ Bank account added successfully!\n\n' + 
                              data.message + '\n\n' +
                              'Next steps:\n' +
                              '1. Go to your Payment Methods page\n' +
                              '2. Click "Start Verification" for this bank account\n' +
                              '3. Wait 1-2 business days for micro-deposits\n' +
                              '4. Enter the deposit amounts to complete verification');
                    }
                    
                    // Redirect to payment methods page
                    window.location.href = '{{ route('company.payment-methods') }}';
                } else {
                    // Handle different types of errors
                    let errorMessage = data.error || 'Failed to save payment method';
                    
                    if (data.verification_required) {
                        errorMessage = 'Bank Account Verification Required\n\n' +
                                     'US bank accounts must be verified before they can be used for payments. ' +
                                     'This typically involves:\n' +
                                     '• Micro-deposit verification (1-2 business days)\n' +
                                     '• Or instant verification through your online banking\n\n' +
                                     'For this demo, we\'ve created a placeholder entry.';
                        
                        alert(errorMessage);
                        
                        // Still redirect since demo entry was created
                        setTimeout(() => {
                            window.location.href = '{{ route('company.payment-methods') }}';
                        }, 1000);
                        return;
                    }
                    
                    // Show error
                    const errorElement = selectedType === 'card' ? 
                        document.getElementById('card-errors') : 
                        document.getElementById('bank-errors');
                    errorElement.textContent = errorMessage;
                    
                    // Re-enable submit button
                    submitButton.disabled = false;
                    buttonText.classList.remove('hidden');
                    spinner.classList.add('hidden');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            const errorElement = selectedType === 'card' ? 
                document.getElementById('card-errors') : 
                document.getElementById('bank-errors');
            errorElement.textContent = 'An unexpected error occurred';
            
            // Re-enable submit button
            submitButton.disabled = false;
            buttonText.classList.remove('hidden');
            spinner.classList.add('hidden');
        }
    });
});
</script>
@endsection 