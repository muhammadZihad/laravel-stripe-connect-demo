@extends('layouts.app')

@section('title', 'Add New Company')

@section('content')
<div class="py-12">
    <div class="max-w-md mx-auto">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-2xl font-bold text-center text-gray-900 mb-6">Add New Company</h2>

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

                <form method="POST" action="{{ route('auth.create-company') }}">
                    @csrf

                    <!-- User Details -->
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Contact Name</label>
                        <input id="name" name="name" type="text" required 
                               class="mt-1 block w-full rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('name') border-red-300 @else border-gray-300 @enderror" 
                               value="{{ old('name') }}">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input id="email" name="email" type="email" required 
                               class="mt-1 block w-full rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('email') border-red-300 @else border-gray-300 @enderror" 
                               value="{{ old('email') }}">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input id="password" name="password" type="password" required 
                               class="mt-1 block w-full rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('password') border-red-300 @else border-gray-300 @enderror">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Company Details -->
                    <div class="mb-4">
                        <label for="company_name" class="block text-sm font-medium text-gray-700">Company Name</label>
                        <input id="company_name" name="company_name" type="text" required 
                               class="mt-1 block w-full rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('company_name') border-red-300 @else border-gray-300 @enderror" 
                               value="{{ old('company_name') }}">
                        @error('company_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="business_type" class="block text-sm font-medium text-gray-700">Business Type</label>
                        <input id="business_type" name="business_type" type="text" 
                               class="mt-1 block w-full rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('business_type') border-red-300 @else border-gray-300 @enderror" 
                               value="{{ old('business_type') }}">
                        @error('business_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                        <input id="phone" name="phone" type="text" 
                               class="mt-1 block w-full rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('phone') border-red-300 @else border-gray-300 @enderror" 
                               value="{{ old('phone') }}">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea id="address" name="address" rows="3" 
                                  class="mt-1 block w-full rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('address') border-red-300 @else border-gray-300 @enderror">{{ old('address') }}</textarea>
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <a href="{{ route('super_admin.companies') }}" class="text-gray-600 hover:text-gray-900">
                            ← Back to Companies
                        </a>
                        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                            Create Company
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 