@extends('layouts.app')

@section('title', 'Company Invoices')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Invoices</h1>
                    <p class="text-gray-600">Manage all invoices for your company</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('company.invoices.create') }}" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Create New Invoice
                    </a>
                    <a href="{{ route('company.dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        ← Back to Dashboard
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

        <!-- Invoices Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            @if($invoices->count() > 0)
                <ul class="divide-y divide-gray-200">
                    @foreach($invoices as $invoice)
                        <li>
                            <div class="px-4 py-4 flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 {{ $invoice->status === 'paid' ? 'bg-green-100' : ($invoice->isOverdue() ? 'bg-red-100' : 'bg-yellow-100') }} rounded-full flex items-center justify-center">
                                            @if($invoice->status === 'paid')
                                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            @elseif($invoice->isOverdue())
                                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
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
                                            <p class="text-sm font-medium text-gray-900">{{ $invoice->invoice_number }}</p>
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : ($invoice->isOverdue() ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ $invoice->isOverdue() ? 'Overdue' : ucfirst($invoice->status) }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-500">{{ $invoice->title }}</p>
                                        <p class="text-sm text-gray-500">Agent: {{ $invoice->agent->user->name }}</p>
                                        <p class="text-xs text-gray-400">Due: {{ $invoice->due_date->format('M d, Y') }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-semibold text-gray-900">${{ number_format($invoice->total_amount, 2) }}</p>
                                    <div class="flex items-center mt-2 space-x-2">
                                        <a href="{{ route('company.invoices.show', $invoice) }}" class="text-primary-600 hover:text-primary-900 text-sm font-medium">
                                            View
                                        </a>
                                        @if($invoice->status !== 'paid')
                                            <a href="{{ route('company.invoices.edit', $invoice) }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium">
                                                Edit
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <!-- Pagination -->
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $invoices->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No invoices</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating your first invoice.</p>
                    <div class="mt-6">
                        <a href="{{ route('company.invoices.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Create Invoice
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 