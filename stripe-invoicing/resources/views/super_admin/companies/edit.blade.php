@extends('layouts.app')

@section('title', 'Edit Company')

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit Company</h1>
                    <p class="text-gray-600">{{ $company->company_name }}</p>
                </div>
                <a href="{{ route('super_admin.companies.show', $company) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    ← Back to Company
                </a>
            </div>
        </div>

        <!-- Edit Company Form -->
        <div class="bg-white shadow rounded-lg">
            <form action="{{ route('super_admin.companies.update', $company) }}" method="POST" class="space-y-6 p-6">
                @csrf
                @method('PUT')

                <!-- Company Name -->
                <div>
                    <label for="company_name" class="block text-sm font-medium text-gray-700">Company Name</label>
                    <input type="text" name="company_name" id="company_name" value="{{ old('company_name', $company->company_name) }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('company_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Business Type -->
                <div>
                    <label for="business_type" class="block text-sm font-medium text-gray-700">Business Type</label>
                    <input type="text" name="business_type" id="business_type" value="{{ old('business_type', $company->business_type) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('business_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone', $company->phone) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Website -->
                <div>
                    <label for="website" class="block text-sm font-medium text-gray-700">Website</label>
                    <input type="url" name="website" id="website" value="{{ old('website', $company->website) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('website')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tax ID -->
                <div>
                    <label for="tax_id" class="block text-sm font-medium text-gray-700">Tax ID</label>
                    <input type="text" name="tax_id" id="tax_id" value="{{ old('tax_id', $company->tax_id) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('tax_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address -->
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                    <textarea name="address" id="address" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">{{ old('address', $company->address) }}</textarea>
                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Company Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Company Status</label>
                    <div class="mt-2">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $company->is_active) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <span class="ml-2 text-sm text-gray-700">Active Company</span>
                        </label>
                    </div>
                    @error('is_active')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Company Information (Read-only) -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Company Information (Read-only)</h4>
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">User Email:</dt>
                            <dd class="text-sm text-gray-900">{{ $company->user->email }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Total Agents:</dt>
                            <dd class="text-sm text-gray-900">{{ $company->agents->count() }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Stripe Status:</dt>
                            <dd class="text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $company->stripe_onboarding_complete ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $company->stripe_onboarding_complete ? 'Verified' : 'Pending' }}
                                </span>
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Member Since:</dt>
                            <dd class="text-sm text-gray-900">{{ $company->created_at->format('M d, Y') }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('super_admin.companies.show', $company) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm font-medium">
                        Cancel
                    </a>
                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Update Company
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 