@extends('layouts.app')

@section('title', 'Edit Agent')

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit Agent</h1>
                    <p class="text-gray-600">{{ $agent->user->name }} ({{ $agent->agent_code }})</p>
                </div>
                <a href="{{ route('company.agents.show', $agent) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    ← Back to Agent
                </a>
            </div>
        </div>

        <!-- Edit Agent Form -->
        <div class="bg-white shadow rounded-lg">
            <form action="{{ route('company.agents.update', $agent) }}" method="POST" class="space-y-6 p-6">
                @csrf
                @method('PUT')

                <!-- Commission Rate -->
                <div>
                    <label for="commission_rate" class="block text-sm font-medium text-gray-700">Commission Rate (%)</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <input type="number" step="0.01" min="0" max="100" name="commission_rate" id="commission_rate" 
                               value="{{ old('commission_rate', $agent->commission_rate) }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">%</span>
                        </div>
                    </div>
                    @error('commission_rate')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Department -->
                <div>
                    <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
                    <input type="text" name="department" id="department" value="{{ old('department', $agent->department) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('department')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Agent Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Agent Status</label>
                    <div class="mt-2">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $agent->is_active) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <span class="ml-2 text-sm text-gray-700">Active Agent</span>
                        </label>
                    </div>
                    @error('is_active')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Agent Information (Read-only) -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Agent Information (Read-only)</h4>
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Name:</dt>
                            <dd class="text-sm text-gray-900">{{ $agent->user->name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Email:</dt>
                            <dd class="text-sm text-gray-900">{{ $agent->user->email }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Agent Code:</dt>
                            <dd class="text-sm text-gray-900">{{ $agent->agent_code }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Hire Date:</dt>
                            <dd class="text-sm text-gray-900">{{ $agent->hire_date ? $agent->hire_date->format('M d, Y') : 'Not set' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('company.agents.show', $agent) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm font-medium">
                        Cancel
                    </a>
                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Update Agent
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 