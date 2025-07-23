<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Stripe Invoicing') }} - Login</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-primary-600 mb-2">Stripe Invoicing Demo</h1>
                <p class="text-lg text-gray-600">Choose an account to login and explore the system</p>
            </div>

            <!-- Error Messages -->
            @if ($errors->any())
                <div class="max-w-2xl mx-auto mb-8">
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <div class="font-bold">Error:</div>
                        <ul class="mt-3 list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Traditional Login Form (Collapsible) -->
            <div class="max-w-md mx-auto mb-12">
                <div class="bg-white rounded-lg shadow-sm border">
                    <button type="button" 
                            onclick="toggleTraditionalLogin()"
                            class="w-full px-6 py-4 text-left text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:bg-gray-50 transition duration-150 ease-in-out">
                        <div class="flex items-center justify-between">
                            <span>Use Traditional Login</span>
                            <svg id="login-chevron" class="w-5 h-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>
                    
                    <div id="traditional-login" class="hidden px-6 pb-6">
                        <form class="space-y-4" action="{{ route('login') }}" method="POST">
                            @csrf
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                                <input id="email" name="email" type="email" autocomplete="email" required 
                                       value="{{ old('email') }}"
                                       class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                <input id="password" name="password" type="password" autocomplete="current-password" required 
                                       class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                            </div>

                            <button type="submit" 
                                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition duration-150 ease-in-out">
                                Sign in
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-7xl mx-auto">
                
                <!-- Companies Section -->
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            Companies ({{ $companies->count() }})
                        </h2>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        @forelse($companies as $company)
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition-colors">
                                <div class="flex items-start justify-between mb-3">
                                    <div>
                                        <h3 class="font-semibold text-lg text-gray-900">{{ $company['company_name'] }}</h3>
                                        <p class="text-sm text-gray-600">{{ $company['user']->email }}</p>
                                    </div>
                                    
                                    <!-- Stripe Connect Status -->
                                    <div class="flex flex-col items-end space-y-1">
                                        @if($company['stripe_onboarding_complete'])
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Stripe Connected
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                </svg>
                                                Setup Required
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Invoice Summary -->
                                <div class="mb-4">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">Total Invoices:</span>
                                        <span class="font-medium">{{ $company['invoices_count'] }}</span>
                                    </div>
                                    
                                    @if($company['recent_invoices']->count() > 0)
                                        <div class="mt-2">
                                            <p class="text-xs text-gray-500 mb-1">Recent Invoices:</p>
                                            <div class="space-y-1">
                                                @foreach($company['recent_invoices'] as $invoice)
                                                    <div class="flex justify-between text-xs">
                                                        <span class="text-gray-600 truncate">{{ $invoice->title }}</span>
                                                        <span class="text-gray-800 font-medium">${{ number_format($invoice->total_amount, 2) }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Quick Login Button -->
                                <form action="{{ route('quick-login') }}" method="POST" class="w-full">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $company['user']->id }}">
                                    <button type="submit" 
                                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md text-sm transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Login as {{ $company['company_name'] }}
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                <p class="mt-2">No companies available</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Agents Section -->
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Agents ({{ $agents->count() }})
                        </h2>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        @forelse($agents as $agent)
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition-colors">
                                <div class="flex items-start justify-between mb-3">
                                    <div>
                                        <h3 class="font-semibold text-lg text-gray-900">{{ $agent['name'] }}</h3>
                                        <p class="text-sm text-gray-600">{{ $agent['user']->email }}</p>
                                        <p class="text-xs text-gray-500">Code: {{ $agent['agent_code'] }} | Company: {{ $agent['company']->company_name }}</p>
                                    </div>
                                    
                                    <!-- Stripe Connect Status -->
                                    <div class="flex flex-col items-end space-y-1">
                                        @if($agent['stripe_onboarding_complete'])
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Stripe Connected
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                </svg>
                                                Setup Required
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Invoice Summary -->
                                <div class="mb-4">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">Total Invoices:</span>
                                        <span class="font-medium">{{ $agent['invoices_count'] }}</span>
                                    </div>
                                    
                                    @if($agent['recent_invoices']->count() > 0)
                                        <div class="mt-2">
                                            <p class="text-xs text-gray-500 mb-1">Recent Invoices:</p>
                                            <div class="space-y-1">
                                                @foreach($agent['recent_invoices'] as $invoice)
                                                    <div class="flex justify-between text-xs">
                                                        <span class="text-gray-600 truncate">{{ $invoice->title }}</span>
                                                        <span class="text-gray-800 font-medium">${{ number_format($invoice->total_amount, 2) }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Quick Login Button -->
                                <form action="{{ route('quick-login') }}" method="POST" class="w-full">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $agent['user']->id }}">
                                    <button type="submit" 
                                            class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md text-sm transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        Login as {{ $agent['name'] }}
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <p class="mt-2">No agents available</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Super Admin Access -->
            <div class="max-w-md mx-auto mt-8">
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-lg">
                    <div class="px-6 py-4">
                        <h3 class="text-lg font-semibold text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-2a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
                            </svg>
                            Super Admin Access
                        </h3>
                        <p class="text-purple-100 text-sm mt-1">Full system administration</p>
                        
                        @php
                            $superAdmin = \App\Models\User::where('role', 'super_admin')->first();
                        @endphp
                        
                        @if($superAdmin)
                            <form action="{{ route('quick-login') }}" method="POST" class="mt-4">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $superAdmin->id }}">
                                <button type="submit" 
                                        class="w-full bg-white text-purple-600 font-medium py-2 px-4 rounded-md text-sm hover:bg-purple-50 transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                    Login as Super Admin
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Footer Info -->
            <div class="text-center mt-12 text-sm text-gray-500">
                <p>This is a demo environment. All data is for demonstration purposes only.</p>
                <p class="mt-1">Traditional login credentials - Password for all accounts: <span class="font-mono bg-gray-100 px-2 py-1 rounded">password</span></p>
            </div>
        </div>
    </div>

    <script>
        function toggleTraditionalLogin() {
            const form = document.getElementById('traditional-login');
            const chevron = document.getElementById('login-chevron');
            
            if (form.classList.contains('hidden')) {
                form.classList.remove('hidden');
                chevron.style.transform = 'rotate(180deg)';
            } else {
                form.classList.add('hidden');
                chevron.style.transform = 'rotate(0deg)';
            }
        }
    </script>
</body>
</html> 