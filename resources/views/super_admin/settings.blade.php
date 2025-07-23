@extends('layouts.app')

@section('title', 'Super Admin Settings')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">System Settings</h1>
                    <p class="text-gray-600">Manage platform configuration and settings</p>
                </div>
                <a href="{{ route('super_admin.dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    ← Back to Dashboard
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Platform Settings -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Platform Configuration</h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <!-- Commission Settings -->
                        <div class="border-b border-gray-200 pb-6">
                            <h4 class="text-base font-medium text-gray-900 mb-4">Commission Settings</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Default Agent Commission (%)</label>
                                    <input type="number" value="10" min="0" max="100" step="0.1" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Platform Fee (%)</label>
                                    <input type="number" value="2.5" min="0" max="100" step="0.1" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Settings -->
                        <div class="border-b border-gray-200 pb-6">
                            <h4 class="text-base font-medium text-gray-900 mb-4">Payment Settings</h4>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Stripe Public Key</label>
                                    <input type="text" value="pk_test_..." 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Webhook Endpoint</label>
                                    <input type="text" value="{{ url('/stripe/webhook') }}" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Settings -->
                        <div class="border-b border-gray-200 pb-6">
                            <h4 class="text-base font-medium text-gray-900 mb-4">Invoice Settings</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Default Due Days</label>
                                    <input type="number" value="30" min="1" max="365" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Late Fee (%)</label>
                                    <input type="number" value="5" min="0" max="50" step="0.1" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- System Settings -->
                        <div>
                            <h4 class="text-base font-medium text-gray-900 mb-4">System Settings</h4>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Email Notifications</label>
                                        <p class="text-sm text-gray-500">Send email notifications for important events</p>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" checked disabled
                                               class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Auto Backup</label>
                                        <p class="text-sm text-gray-500">Automatically backup database daily</p>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" checked disabled
                                               class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Maintenance Mode</label>
                                        <p class="text-sm text-gray-500">Enable maintenance mode for updates</p>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" disabled
                                               class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="space-y-6">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">System Information</h3>
                    </div>
                    <div class="p-6">
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Platform Version</dt>
                                <dd class="text-sm text-gray-900">v1.0.0</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Laravel Version</dt>
                                <dd class="text-sm text-gray-900">{{ app()->version() }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">PHP Version</dt>
                                <dd class="text-sm text-gray-900">{{ PHP_VERSION }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Environment</dt>
                                <dd class="text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ app()->environment('production') ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ ucfirst(app()->environment()) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Database</dt>
                                <dd class="text-sm text-gray-900">MySQL</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Last Backup</dt>
                                <dd class="text-sm text-gray-900">{{ now()->subHours(6)->format('M d, Y g:i A') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <button class="w-full bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Clear Cache
                        </button>
                        <button class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Run Backup
                        </button>
                        <button class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            View Logs
                        </button>
                        <button class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Export Data
                        </button>
                    </div>
                </div>

                <!-- Configuration Notice -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Configuration Note</h3>
                            <p class="mt-1 text-sm text-blue-700">
                                Settings are currently read-only. In a production environment, these would be editable through environment variables and database configuration.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 