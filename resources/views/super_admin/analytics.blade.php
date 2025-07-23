@extends('layouts.app')

@section('title', 'Analytics & Reports')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Analytics & Reports</h1>
                    <p class="text-gray-600">Platform-wide performance metrics and analytics</p>
                </div>
                <a href="{{ route('super_admin.dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    ← Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Monthly Revenue Chart -->
        @if($monthlyRevenue->count() > 0)
            <div class="bg-white shadow rounded-lg p-6 mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Monthly Revenue & Commission Trends</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    @foreach($monthlyRevenue->take(4) as $month)
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">${{ number_format($month->total_amount, 2) }}</div>
                            <div class="text-sm text-gray-500">{{ date('M Y', mktime(0, 0, 0, $month->month, 1, $month->year)) }}</div>
                            <div class="text-xs text-gray-400">Commission: ${{ number_format($month->total_commission, 2) }}</div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Simple Chart Representation -->
                <div class="space-y-2">
                    @php
                        $maxAmount = $monthlyRevenue->max('total_amount');
                    @endphp
                    @foreach($monthlyRevenue as $month)
                        <div class="flex items-center">
                            <div class="w-20 text-xs text-gray-600">{{ date('M Y', mktime(0, 0, 0, $month->month, 1, $month->year)) }}</div>
                            <div class="flex-1 ml-4">
                                <div class="bg-gray-200 rounded-full h-4">
                                    <div class="bg-primary-500 h-4 rounded-full" style="width: {{ $maxAmount > 0 ? ($month->total_amount / $maxAmount) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="w-24 text-sm text-gray-900 ml-4 text-right">${{ number_format($month->total_amount, 2) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Top Companies -->
            @if($topCompanies->count() > 0)
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Top Performing Companies</h3>
                    <div class="space-y-4">
                        @foreach($topCompanies as $company)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <span class="text-sm font-medium text-blue-700">{{ substr($company->company_name, 0, 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $company->company_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $company->business_type }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-900">${{ number_format($company->transactions_sum_amount ?? 0, 2) }}</div>
                                    <div class="text-xs text-gray-500">Total Revenue</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Top Agents -->
            @if($topAgents->count() > 0)
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Top Performing Agents</h3>
                    <div class="space-y-4">
                        @foreach($topAgents as $agent)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                            <span class="text-sm font-medium text-green-700">{{ substr($agent->user->name, 0, 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $agent->user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $agent->agent_code }} • {{ $agent->company->company_name }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-900">${{ number_format($agent->transactions_sum_net_amount ?? 0, 2) }}</div>
                                    <div class="text-xs text-gray-500">Total Earned</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Summary Statistics -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Companies</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $topCompanies->count() }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Agents</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $topAgents->count() }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Revenue</dt>
                                <dd class="text-lg font-medium text-gray-900">${{ number_format($monthlyRevenue->sum('total_amount'), 2) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Platform Commission</dt>
                                <dd class="text-lg font-medium text-gray-900">${{ number_format($monthlyRevenue->sum('total_commission'), 2) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- No Data State -->
        @if($monthlyRevenue->count() === 0 && $topCompanies->count() === 0 && $topAgents->count() === 0)
            <div class="bg-white shadow rounded-lg p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No analytics data available</h3>
                <p class="mt-1 text-sm text-gray-500">Analytics will appear here once there are transactions and user activity.</p>
            </div>
        @endif
    </div>
</div>
@endsection 