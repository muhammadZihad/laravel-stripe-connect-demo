@extends('layouts.app')

@section('title', 'Add New Agent')

@section('content')
<div class="py-12">
    <div class="max-w-md mx-auto">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-2xl font-bold text-center text-gray-900 mb-6">Add New Agent</h2>

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

                <form method="POST" action="{{ route('auth.create-agent') }}">
                    @csrf

                    <!-- User Details -->
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
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

                    <!-- Company Selection -->
                    <div class="mb-4">
                        <label for="company_id" class="block text-sm font-medium text-gray-700">Company</label>
                        <select id="company_id" name="company_id" required 
                                class="mt-1 block w-full rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('company_id') border-red-300 @else border-gray-300 @enderror">
                            <option value="">Select Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->company_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('company_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Agent Details -->
                    <div class="mb-4">
                        <label for="commission_rate" class="block text-sm font-medium text-gray-700">Commission Rate (%)</label>
                        <input id="commission_rate" name="commission_rate" type="number" min="0" max="100" step="0.01" 
                               class="mt-1 block w-full rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('commission_rate') border-red-300 @else border-gray-300 @enderror" 
                               value="{{ old('commission_rate', '0.00') }}">
                        @error('commission_rate')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
                        <input id="department" name="department" type="text" 
                               class="mt-1 block w-full rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('department') border-red-300 @else border-gray-300 @enderror" 
                               value="{{ old('department') }}">
                        @error('department')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="hire_date" class="block text-sm font-medium text-gray-700">Hire Date</label>
                        <input id="hire_date" name="hire_date" type="date" 
                               class="mt-1 block w-full rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('hire_date') border-red-300 @else border-gray-300 @enderror" 
                               value="{{ old('hire_date') }}">
                        @error('hire_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <a href="{{ route('super_admin.agents') }}" class="text-gray-600 hover:text-gray-900">
                            ← Back to Agents
                        </a>
                        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                            Create Agent
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 