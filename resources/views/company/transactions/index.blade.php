@extends('layouts.app')

@section('title', 'Transactions')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Transactions</h1>
                    <p class="text-gray-600">View all payment transactions for your company</p>
                </div>
                <a href="{{ route('company.dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    ← Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            @if($transactions->count() > 0)
                <ul class="divide-y divide-gray-200">
                    @foreach($transactions as $transaction)
                        <li>
                            <div class="px-4 py-4 flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 {{ $transaction->status === 'completed' ? 'bg-green-100' : ($transaction->status === 'failed' ? 'bg-red-100' : 'bg-yellow-100') }} rounded-full flex items-center justify-center">
                                            @if($transaction->status === 'completed')
                                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            @elseif($transaction->status === 'failed')
                                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="flex items-center">
                                            <p class="text-sm font-medium text-gray-900">{{ $transaction->transaction_id }}</p>
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : ($transaction->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-500">{{ $transaction->invoice->invoice_number }}</p>
                                        <p class="text-sm text-gray-500">Agent: {{ $transaction->agent->user->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $transaction->created_at->format('M d, Y g:i A') }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-semibold text-gray-900">${{ number_format($transaction->amount, 2) }}</p>
                                    @if($transaction->admin_commission > 0)
                                        <p class="text-xs text-gray-500">Fee: ${{ number_format($transaction->admin_commission, 2) }}</p>
                                    @endif
                                    <div class="flex items-center mt-2 space-x-2">
                                        <a href="{{ route('company.transactions.show', $transaction) }}" class="text-primary-600 hover:text-primary-900 text-sm font-medium">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <!-- Pagination -->
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $transactions->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2m0 0h10V9h-2M9 11h4m0 0V9m0 2v6"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No transactions</h3>
                    <p class="mt-1 text-sm text-gray-500">Transactions will appear here once payments are processed.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 