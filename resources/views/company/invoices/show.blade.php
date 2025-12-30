@extends('layouts.app')

@section('title', 'Invoice Details')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $invoice->invoice_number }}</h1>
                    <p class="text-gray-600">{{ $invoice->title }}</p>
                </div>
                <div class="flex gap-3">
                    @if($invoice->status !== 'paid')
                        <button onclick="openSendInvoiceModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            📧 Send Invoice
                        </button>
                        <a href="{{ route('company.invoices.pay', $invoice) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            💳 Pay Invoice
                        </a>
                        <a href="{{ route('company.invoices.edit', $invoice) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Edit Invoice
                        </a>
                    @endif
                    <a href="{{ route('company.invoices') }}" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        ← Back to Invoices
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

        <!-- Invoice Details -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <!-- Status Banner -->
            <div class="px-6 py-4 border-b border-gray-200 {{ $invoice->status === 'paid' ? 'bg-green-50' : ($invoice->isOverdue() ? 'bg-red-50' : 'bg-yellow-50') }}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : ($invoice->isOverdue() ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ $invoice->isOverdue() ? 'Overdue' : ucfirst($invoice->status) }}
                        </span>
                        @if($invoice->paid_date)
                            <span class="ml-3 text-sm text-gray-600">Paid on {{ $invoice->paid_date->format('M d, Y') }}</span>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-gray-900">${{ number_format($invoice->total_amount, 2) }}</p>
                        <p class="text-sm text-gray-500">Due {{ $invoice->due_date->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Invoice Information -->
            <div class="px-6 py-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Invoice Details -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Details</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Invoice Number</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->invoice_number }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Title</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->title }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Description</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->description ?: 'No description provided' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created Date</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->created_at->format('M d, Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->due_date->format('M d, Y') }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Agent Information -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Agent Information</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Agent Name</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->agent->user->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Agent Code</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->agent->agent_code }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Department</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->agent->department ?: 'Not specified' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Commission Rate</dt>
                                <dd class="text-sm text-gray-900">{{ $invoice->agent->commission_rate ? $invoice->agent->commission_rate . '%' : 'Not set' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Amount Breakdown -->
                <div class="mt-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Amount Breakdown</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex justify-between items-center py-2">
                            <span class="text-sm text-gray-600">Subtotal</span>
                            <span class="text-sm text-gray-900">${{ number_format($invoice->amount, 2) }}</span>
                        </div>
                        @if($invoice->tax_amount > 0)
                            <div class="flex justify-between items-center py-2">
                                <span class="text-sm text-gray-600">Tax</span>
                                <span class="text-sm text-gray-900">${{ number_format($invoice->tax_amount, 2) }}</span>
                            </div>
                        @endif
                        <div class="border-t border-gray-200 pt-2 mt-2">
                            <div class="flex justify-between items-center">
                                <span class="text-base font-medium text-gray-900">Total</span>
                                <span class="text-base font-medium text-gray-900">${{ number_format($invoice->total_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Items (if any) -->
                @if($invoice->invoice_items && count($invoice->invoice_items) > 0)
                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Items</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            @foreach($invoice->invoice_items as $item)
                                <div class="flex justify-between items-center py-2 {{ !$loop->last ? 'border-b border-gray-200' : '' }}">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $item['description'] ?? 'Item ' . ($loop->index + 1) }}</p>
                                        @if(isset($item['quantity']) && isset($item['unit_price']))
                                            <p class="text-xs text-gray-500">{{ $item['quantity'] }} × ${{ number_format($item['unit_price'], 2) }}</p>
                                        @endif
                                    </div>
                                    <span class="text-sm text-gray-900">${{ number_format($item['amount'] ?? 0, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Transactions -->
        @if($invoice->transactions->count() > 0)
            <div class="mt-8">
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Payment History</h3>
                    </div>
                    <div class="px-6 py-4">
                        <div class="space-y-4">
                            @foreach($invoice->transactions as $transaction)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $transaction->transaction_id }}</p>
                                        <p class="text-sm text-gray-500">{{ $transaction->created_at->format('M d, Y g:i A') }}</p>
                                        <p class="text-xs text-gray-400">{{ ucfirst($transaction->type) }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900">${{ number_format($transaction->amount, 2) }}</p>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                            {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : ($transaction->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Send Invoice Modal -->
<div id="sendInvoiceModal" style="display: none;" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" style="max-width: 90vw;">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Send Invoice</h3>
                <button onclick="closeSendInvoiceModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form id="sendInvoiceForm" onsubmit="sendInvoice(event)">
                <div class="mb-4">
                    <label for="recipientEmail" class="block text-sm font-medium text-gray-700 mb-2">
                        Recipient Email Address
                    </label>
                    <input 
                        type="email" 
                        id="recipientEmail" 
                        name="email"
                        required
                        value="{{ $invoice->agent->user->email }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="recipient@example.com"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        A secure payment link will be sent to this email address
                    </p>
                </div>

                <div class="bg-blue-50 border-l-4 border-blue-400 p-3 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-xs text-blue-700">
                                The payment link will expire in 30 days. Recipients can pay using card or ACH transfer.
                            </p>
                        </div>
                    </div>
                </div>

                <div id="sendSuccessMessage" class="hidden bg-green-50 border border-green-200 rounded-md p-3 mb-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p id="sendSuccessText" class="text-sm text-green-700"></p>
                    </div>
                </div>

                <div id="sendErrorMessage" class="hidden bg-red-50 border border-red-200 rounded-md p-3 mb-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <p id="sendErrorText" class="text-sm text-red-700"></p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button 
                        type="submit" 
                        id="sendButton"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                    >
                        Send Invoice
                    </button>
                    <button 
                        type="button" 
                        onclick="closeSendInvoiceModal()"
                        class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm font-medium"
                    >
                        Cancel
                    </button>
                </div>
            </form>

            <div id="paymentLinkDisplay" class="hidden mt-4 p-3 bg-gray-50 rounded-md border border-gray-200">
                <label class="block text-xs font-medium text-gray-700 mb-1">Payment Link (for reference)</label>
                <div class="flex">
                    <input 
                        type="text" 
                        id="paymentLinkInput" 
                        readonly 
                        class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded-l-md bg-white"
                    />
                    <button 
                        onclick="copyPaymentLink()"
                        class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs rounded-r-md"
                    >
                        Copy
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openSendInvoiceModal() {
        const modal = document.getElementById('sendInvoiceModal');
        modal.style.display = 'block';
        document.getElementById('sendSuccessMessage').classList.add('hidden');
        document.getElementById('sendErrorMessage').classList.add('hidden');
        document.getElementById('paymentLinkDisplay').classList.add('hidden');
    }

    function closeSendInvoiceModal() {
        const modal = document.getElementById('sendInvoiceModal');
        modal.style.display = 'none';
    }

    async function sendInvoice(event) {
        event.preventDefault();
        
        const sendButton = document.getElementById('sendButton');
        const email = document.getElementById('recipientEmail').value;
        const successMessage = document.getElementById('sendSuccessMessage');
        const errorMessage = document.getElementById('sendErrorMessage');
        
        // Hide previous messages
        successMessage.classList.add('hidden');
        errorMessage.classList.add('hidden');
        
        // Disable button and show loading
        sendButton.disabled = true;
        sendButton.textContent = 'Sending...';
        
        try {
            const response = await fetch('{{ route('company.invoices.send', $invoice) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ email: email })
            });
            
            const result = await response.json();
            
            if (result.success) {
                document.getElementById('sendSuccessText').textContent = result.message;
                successMessage.classList.remove('hidden');
                
                // Show payment link
                if (result.payment_url) {
                    document.getElementById('paymentLinkInput').value = result.payment_url;
                    document.getElementById('paymentLinkDisplay').classList.remove('hidden');
                }
                
                // Reset form after 3 seconds
                setTimeout(() => {
                    closeSendInvoiceModal();
                    document.getElementById('sendInvoiceForm').reset();
                }, 3000);
            } else {
                document.getElementById('sendErrorText').textContent = result.error;
                errorMessage.classList.remove('hidden');
            }
        } catch (error) {
            document.getElementById('sendErrorText').textContent = 'An error occurred. Please try again.';
            errorMessage.classList.remove('hidden');
        } finally {
            sendButton.disabled = false;
            sendButton.textContent = 'Send Invoice';
        }
    }

    function copyPaymentLink() {
        const linkInput = document.getElementById('paymentLinkInput');
        linkInput.select();
        document.execCommand('copy');
        
        // Show copied feedback
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Copied!';
        setTimeout(() => {
            button.textContent = originalText;
        }, 2000);
    }

    // Close modal when clicking outside
    document.getElementById('sendInvoiceModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeSendInvoiceModal();
        }
    });
</script>
@endsection 