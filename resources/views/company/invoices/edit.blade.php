@extends('layouts.app')

@section('title', 'Edit Invoice')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit Invoice</h1>
                    <p class="text-gray-600">{{ $invoice->invoice_number }}</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('company.invoices.show', $invoice) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        ← Back to Invoice
                    </a>
                </div>
            </div>
        </div>

        @if($invoice->isPaid())
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-6">
                This invoice has been paid and cannot be edited.
            </div>
        @endif

        <!-- Edit Invoice Form -->
        <div class="bg-white shadow rounded-lg">
            <form action="{{ route('company.invoices.update', $invoice) }}" method="POST" class="space-y-6 p-6">
                @csrf
                @method('PUT')

                <!-- Agent Selection -->
                <div>
                    <label for="agent_id" class="block text-sm font-medium text-gray-700">Select Agent</label>
                    <select name="agent_id" id="agent_id" required {{ $invoice->isPaid() ? 'disabled' : '' }}
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ old('agent_id', $invoice->agent_id) == $agent->id ? 'selected' : '' }}>
                                {{ $agent->user->name }} ({{ $agent->agent_code }})
                            </option>
                        @endforeach
                    </select>
                    @error('agent_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Invoice Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Invoice Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $invoice->title) }}" required 
                           {{ $invoice->isPaid() ? 'readonly' : '' }}
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="3" {{ $invoice->isPaid() ? 'readonly' : '' }}
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">{{ old('description', $invoice->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Amount and Tax -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" step="0.01" name="amount" id="amount" value="{{ old('amount', $invoice->amount) }}" required
                                   {{ $invoice->isPaid() ? 'readonly' : '' }}
                                   class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        @error('amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tax_amount" class="block text-sm font-medium text-gray-700">Tax Amount</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" step="0.01" name="tax_amount" id="tax_amount" value="{{ old('tax_amount', $invoice->tax_amount) }}"
                                   {{ $invoice->isPaid() ? 'readonly' : '' }}
                                   class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        @error('tax_amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Due Date -->
                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date</label>
                    <input type="date" name="due_date" id="due_date" value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}" required
                           {{ $invoice->isPaid() ? 'readonly' : '' }}
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('due_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Buttons -->
                @if(!$invoice->isPaid())
                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                        <a href="{{ route('company.invoices.show', $invoice) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm font-medium">
                            Cancel
                        </a>
                        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Update Invoice
                        </button>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection 