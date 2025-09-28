@extends('layouts.app')

@section('title', 'My Payment Methods')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold">My Payment Methods</h2>
                        <p class="text-gray-600">Manage how you receive payments from companies</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('agent.payment-methods.create') }}" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Add Payment Method
                        </a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                @if($paymentMethods->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($paymentMethods as $paymentMethod)
                            <div class="bg-white border border-gray-200 rounded-lg p-6 {{ $paymentMethod->is_default ? 'ring-2 ring-primary-500' : '' }}">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        @if($paymentMethod->type === 'card')
                                            <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-lg">
                                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                                </svg>
                                            </div>
                                        @else
                                            <div class="flex items-center justify-center w-10 h-10 bg-green-100 rounded-lg">
                                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                </svg>
                                            </div>
                                        @endif
                                        @if($paymentMethod->is_default)
                                            <span class="ml-3 px-2 py-1 text-xs font-medium bg-primary-100 text-primary-800 rounded-full">
                                                Default
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        @if(!$paymentMethod->is_default)
                                            <form method="POST" action="{{ route('agent.payment-methods.set-default', $paymentMethod) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-primary-600 hover:text-primary-900 text-sm font-medium">
                                                    Set Default
                                                </button>
                                            </form>
                                        @endif
                                                                                    <form method="POST" action="{{ route('agent.payment-methods.delete', $paymentMethod) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this payment method?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">
                                            @if($paymentMethod->type === 'card')
                                                {{ ucfirst($paymentMethod->brand) }} •••• {{ $paymentMethod->last_four }}
                                            @else
                                                {{ ucfirst(str_replace('_', ' ', $paymentMethod->type)) }}
                                                @if($paymentMethod->bank_name)
                                                    ({{ $paymentMethod->bank_name }})
                                                @endif
                                                @if($paymentMethod->last_four)
                                                    •••• {{ $paymentMethod->last_four }}
                                                @endif
                                            @endif
                                        </h3>
                                        <p class="text-sm text-gray-500">
                                            @if($paymentMethod->type === 'card')
                                                Credit/Debit Card
                                                @if($paymentMethod->exp_month && $paymentMethod->exp_year)
                                                    • Expires {{ $paymentMethod->exp_month }}/{{ $paymentMethod->exp_year }}
                                                @endif
                                            @else
                                                Bank Account
                                                @if($paymentMethod->account_holder_type)
                                                    • {{ ucfirst($paymentMethod->account_holder_type) }} Account
                                                @endif
                                            @endif
                                            
                                            <!-- Verification Status Badge -->
                                            <br>
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full {{ $paymentMethod->verification_badge['class'] }}">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    @if($paymentMethod->verification_badge['icon'] === 'check-circle')
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    @elseif($paymentMethod->verification_badge['icon'] === 'exclamation-triangle')
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                    @elseif($paymentMethod->verification_badge['icon'] === 'clock')
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                    @elseif($paymentMethod->verification_badge['icon'] === 'x-circle')
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                    @else
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                                    @endif
                                                </svg>
                                                {{ $paymentMethod->verification_badge['text'] }}
                                            </span>
                                        </p>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            @if($paymentMethod->is_active)
                                                <span class="flex items-center text-sm text-green-600">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Active
                                                </span>
                                            @else
                                                <span class="flex items-center text-sm text-gray-500">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Inactive
                                                </span>
                                            @endif

                                            @if($paymentMethod->is_default)
                                                <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Default
                                                </span>
                                            @endif
                                        </div>

                                        <div class="flex items-center space-x-3">
                                            <!-- Verification Actions for Bank Accounts -->
                                            @if($paymentMethod->type === 'us_bank_account')
                                                @if($paymentMethod->requiresVerification())
                                                    <button onclick="initiateVerification({{ $paymentMethod->id }})" 
                                                            class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                                        Start Verification
                                                    </button>
                                                @elseif($paymentMethod->isPendingVerification())
                                                    <a href="{{ route('agent.payment-methods.verify', $paymentMethod) }}" 
                                                       class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                                        Enter Verification
                                                    </a>
                                                @elseif($paymentMethod->verificationFailed())
                                                    @if($paymentMethod->canAttemptVerification())
                                                        <button onclick="initiateVerification({{ $paymentMethod->id }})" 
                                                                class="text-orange-600 hover:text-orange-900 text-sm font-medium">
                                                            Retry Verification
                                                        </button>
                                                    @else
                                                        <span class="text-red-600 text-sm">Max attempts reached</span>
                                                    @endif
                                                @endif
                                            @endif

                                            <!-- Default Payment Method Action -->
                                            @if(!$paymentMethod->is_default && $paymentMethod->is_active)
                                                <form action="{{ route('agent.payment-methods.set-default', $paymentMethod) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                                        Set as Default
                                                    </button>
                                                </form>
                                            @endif

                                            <!-- Delete Action -->
                                            <form action="{{ route('agent.payment-methods.delete', $paymentMethod) }}" method="POST" class="inline" 
                                                  onsubmit="return confirm('Are you sure you want to delete this payment method?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No payment methods</h3>
                        <p class="mt-1 text-sm text-gray-500">You haven't added any payment methods yet. Add one to start receiving payments.</p>
                        <div class="mt-6">
                            <a href="{{ route('agent.payment-methods.create') }}" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Add Your First Payment Method
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Information Panel -->
                <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">About Payment Methods</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Payment methods are used to receive your earnings from completed invoices</li>
                                    <li>Your default payment method will be used for all new payments</li>
                                    <li>All payments are processed securely through Stripe</li>
                                    <li>A $2 platform commission is automatically deducted from each transaction</li>
                                    <li>Earnings are typically available within 2-7 business days</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bank Account Verification Notice -->
                <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Bank Account Verification</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p class="mb-2"><strong>US bank accounts require verification before use:</strong></p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li><strong>Step 1:</strong> Click "Start Verification" to initiate micro-deposits</li>
                                    <li><strong>Step 2:</strong> Wait 1-2 business days for deposits to arrive</li>
                                    <li><strong>Step 3:</strong> Click "Enter Verification" and input the deposit amounts</li>
                                    <li><strong>Step 4:</strong> Your bank account will be verified and activated</li>
                                </ul>
                                <p class="mt-2 text-xs">Micro-deposits are typically amounts under $1.00 and will be automatically reversed.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function initiateVerification(paymentMethodId) {
    // Redirect to verification page instead of initiating directly
    window.location.href = `/agent/payment-methods/${paymentMethodId}/verify`;
}
</script>
@endsection 