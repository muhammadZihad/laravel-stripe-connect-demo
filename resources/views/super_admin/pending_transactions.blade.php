@extends('layouts.app')

@section('title', 'Pending Transactions')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Pending Transactions</h1>
        <button onclick="reconcileTransactions()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
            Reconcile Pending Transactions
        </button>
    </div>

    @if($pendingTransactions->isEmpty())
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
            <p class="text-green-800 text-lg">No pending transactions found!</p>
        </div>
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-yellow-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div>
                    <h3 class="text-yellow-800 font-semibold mb-1">About Pending Transactions</h3>
                    <p class="text-yellow-700 text-sm">
                        Pending transactions are usually waiting for 3D Secure authentication or are still processing.
                        Use the reconcile button to sync with Stripe and update their status automatically.
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transaction ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Company</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Agent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Age</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($pendingTransactions as $transaction)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $transaction->transaction_id }}</div>
                                @if($transaction->stripe_payment_intent_id)
                                    <div class="text-xs text-gray-500">PI: {{ Str::limit($transaction->stripe_payment_intent_id, 20) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('super_admin.invoices.show', $transaction->invoice) }}" 
                                   class="text-blue-600 hover:text-blue-800 text-sm">
                                    {{ $transaction->invoice->invoice_number }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $transaction->company->company_name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $transaction->agent->user->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${{ number_format($transaction->amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                    $age = $transaction->created_at->diffForHumans();
                                    $isOld = $transaction->created_at->diffInHours() > 24;
                                @endphp
                                <span class="{{ $isOld ? 'text-red-600 font-medium' : 'text-gray-600' }}">
                                    {{ $age }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <button onclick="checkStatus('{{ $transaction->id }}')" 
                                        class="text-blue-600 hover:text-blue-800 mr-3">
                                    Check Status
                                </button>
                                <a href="{{ route('super_admin.transactions.show', $transaction) }}" 
                                   class="text-gray-600 hover:text-gray-800">
                                    View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $pendingTransactions->links() }}
        </div>
    @endif
</div>

<script>
function reconcileTransactions() {
    if (!confirm('This will check all pending transactions with Stripe and update their status. Continue?')) {
        return;
    }

    const button = event.target;
    button.disabled = true;
    button.textContent = 'Reconciling...';

    fetch('{{ route("super_admin.transactions.reconcile") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            older_than_minutes: 5
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Reconciliation Complete!\n\nChecked: ${data.checked}\nReconciled: ${data.reconciled.length}\nFailed: ${data.failed.length}`);
            location.reload();
        } else {
            alert('Reconciliation failed: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    })
    .finally(() => {
        button.disabled = false;
        button.textContent = 'Reconcile Pending Transactions';
    });
}

function checkStatus(transactionId) {
    fetch(`/super-admin/transactions/${transactionId}/check-status`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let message = `Transaction Status Check:\n\n`;
            message += `Payment Intent ID: ${data.payment_intent_id}\n`;
            message += `Payment Intent Status: ${data.payment_intent_status}\n`;
            message += `Local Status: ${data.local_status}\n`;
            message += `Requires Action: ${data.requires_action ? 'Yes (3DS pending)' : 'No'}\n`;
            
            if (data.next_action) {
                message += `\nNext Action: ${JSON.stringify(data.next_action, null, 2)}`;
            }
            
            alert(message);
        } else {
            alert('Failed to check status: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}
</script>
@endsection

