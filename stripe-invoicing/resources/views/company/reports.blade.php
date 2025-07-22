@extends('layouts.app')

@section('title', 'Company Reports')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Reports & Analytics</h1>
                    <p class="text-gray-600">View performance metrics and financial reports</p>
                </div>
                <a href="{{ route('company.dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    ← Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Monthly Revenue Chart -->
        @if($monthlyStats->count() > 0)
            <div class="bg-white shadow rounded-lg p-6 mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Monthly Revenue Trends</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    @foreach($monthlyStats->take(4) as $month)
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">${{ number_format($month->total, 2) }}</div>
                            <div class="text-sm text-gray-500">{{ date('M Y', mktime(0, 0, 0, $month->month, 1, $month->year)) }}</div>
                            <div class="text-xs text-gray-400">{{ $month->count }} transactions</div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Simple Chart Representation -->
                <div class="space-y-2">
                    @php
                        $maxAmount = $monthlyStats->max('total');
                    @endphp
                    @foreach($monthlyStats as $month)
                        <div class="flex items-center">
                            <div class="w-20 text-xs text-gray-600">{{ date('M Y', mktime(0, 0, 0, $month->month, 1, $month->year)) }}</div>
                            <div class="flex-1 ml-4">
                                <div class="bg-gray-200 rounded-full h-4">
                                    <div class="bg-primary-500 h-4 rounded-full" style="width: {{ $maxAmount > 0 ? ($month->total / $maxAmount) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="w-24 text-sm text-gray-900 ml-4 text-right">${{ number_format($month->total, 2) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Agent Performance -->
        @if($agentPerformance->count() > 0)
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Agent Performance</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agent</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Invoices</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transactions</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Earned</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($agentPerformance as $agent)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-gray-700">{{ substr($agent->user->name, 0, 2) }}</span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $agent->user->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $agent->agent_code }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $agent->invoices_count }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $agent->transactions_count }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($agent->transactions_sum_net_amount ?? 0, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $agent->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $agent->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white shadow rounded-lg p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No data available</h3>
                <p class="mt-1 text-sm text-gray-500">Reports will appear here once you have transactions and agent activity.</p>
            </div>
        @endif
    </div>
</div>
@endsection 