@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold">My Profile</h2>
                        <p class="text-gray-600">Manage your personal and agent information</p>
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

                <!-- Profile Overview -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Personal Info Card -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium mb-4">Personal Information</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                                <dd class="text-sm text-gray-900">{{ $agent->user->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Email</dt>
                                <dd class="text-sm text-gray-900">{{ $agent->user->email }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Role</dt>
                                <dd class="text-sm text-gray-900">{{ ucfirst($agent->user->role) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Account Created</dt>
                                <dd class="text-sm text-gray-900">{{ $agent->user->created_at->format('M d, Y') }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Agent Info Card -->
                    <div class="bg-blue-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium mb-4">Agent Details</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Agent Code</dt>
                                <dd class="text-sm text-gray-900 font-mono">{{ $agent->agent_code }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Company</dt>
                                <dd class="text-sm text-gray-900">{{ $agent->company->company_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Department</dt>
                                <dd class="text-sm text-gray-900">{{ $agent->department ?: 'Not specified' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Commission Rate</dt>
                                <dd class="text-sm text-gray-900">{{ number_format($agent->commission_rate, 2) }}%</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Hire Date</dt>
                                <dd class="text-sm text-gray-900">{{ $agent->hire_date ? $agent->hire_date->format('M d, Y') : 'Not specified' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Status Card -->
                    <div class="bg-green-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium mb-4">Account Status</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Agent Status</dt>
                                <dd class="text-sm">
                                    @if($agent->is_active)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Stripe Onboarding</dt>
                                <dd class="text-sm">
                                    @if($agent->isStripeOnboardingComplete())
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Complete</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Email Verified</dt>
                                <dd class="text-sm">
                                    @if($agent->user->email_verified_at)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Verified</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Unverified</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Stripe Onboarding Action -->
                @if(!$agent->isStripeOnboardingComplete())
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-medium text-yellow-800">Complete Stripe Onboarding</h3>
                                <p class="mt-1 text-sm text-yellow-700">
                                    You need to complete Stripe Connect onboarding to receive payments. This verifies your identity and business information.
                                </p>
                                <div class="mt-4">
                                    <a href="{{ route('stripe.start-onboarding') }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                        Complete Onboarding
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Edit Profile Form -->
                <form method="POST" action="{{ route('agent.profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        <!-- Personal Information Section -->
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-medium mb-4">Update Personal Information</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                                    <input id="name" name="name" type="text" required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('name') border-red-300 @enderror"
                                           value="{{ old('name', $agent->user->name) }}">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                                    <input id="email" name="email" type="email" required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('email') border-red-300 @enderror"
                                           value="{{ old('email', $agent->user->email) }}">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Agent Information Section -->
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-medium mb-4">Agent Information</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
                                    <input id="department" name="department" type="text"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('department') border-red-300 @enderror"
                                           value="{{ old('department', $agent->department) }}"
                                           placeholder="e.g., Sales, Marketing, Support">
                                    @error('department')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="hire_date" class="block text-sm font-medium text-gray-700">Hire Date</label>
                                    <input id="hire_date" name="hire_date" type="date"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('hire_date') border-red-300 @enderror"
                                           value="{{ old('hire_date', $agent->hire_date ? $agent->hire_date->format('Y-m-d') : '') }}">
                                    @error('hire_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Read-only fields -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Agent Code</label>
                                    <input type="text" disabled
                                           class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm"
                                           value="{{ $agent->agent_code }}">
                                    <p class="mt-1 text-sm text-gray-500">Agent code cannot be changed.</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Commission Rate</label>
                                    <input type="text" disabled
                                           class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm"
                                           value="{{ number_format($agent->commission_rate, 2) }}%">
                                    <p class="mt-1 text-sm text-gray-500">Contact your company to modify commission rate.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Password Change Section -->
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-medium mb-4">Change Password</h3>
                            <p class="text-sm text-gray-600 mb-4">Leave password fields empty if you don't want to change your password.</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                                    <input id="password" name="password" type="password"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('password') border-red-300 @enderror">
                                    @error('password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                    <input id="password_confirmation" name="password_confirmation" type="password"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-500">
                                Changes will be saved immediately after submission.
                            </p>
                            <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded-md text-sm font-medium">
                                Update Profile
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 