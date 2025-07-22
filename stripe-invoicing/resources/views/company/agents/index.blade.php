@extends('layouts.app')

@section('title', 'Company Agents')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Agents</h1>
                    <p class="text-gray-600">Manage all agents in your company</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('company.agents.create') }}" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Add New Agent
                    </a>
                    <a href="{{ route('company.dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        ← Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        <!-- Agents Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            @if($agents->count() > 0)
                <ul class="divide-y divide-gray-200">
                    @foreach($agents as $agent)
                        <li>
                            <div class="px-4 py-4 flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 {{ $agent->is_active ? 'bg-green-100' : 'bg-gray-100' }} rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 {{ $agent->is_active ? 'text-green-600' : 'text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="flex items-center">
                                            <p class="text-sm font-medium text-gray-900">{{ $agent->user->name }}</p>
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $agent->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $agent->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                            @if($agent->stripe_onboarding_complete)
                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Verified
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-500">{{ $agent->agent_code }}</p>
                                        <p class="text-sm text-gray-500">{{ $agent->user->email }}</p>
                                        <p class="text-xs text-gray-400">{{ $agent->department ?: 'No department' }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-900">
                                        <p class="font-semibold">{{ $agent->invoices_count }} invoices</p>
                                        <p class="text-gray-500">{{ $agent->transactions_count }} transactions</p>
                                        @if($agent->commission_rate)
                                            <p class="text-xs text-gray-400">{{ $agent->commission_rate }}% commission</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center mt-2 space-x-2">
                                        <a href="{{ route('company.agents.show', $agent) }}" class="text-primary-600 hover:text-primary-900 text-sm font-medium">
                                            View
                                        </a>
                                        <a href="{{ route('company.agents.edit', $agent) }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium">
                                            Edit
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <!-- Pagination -->
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $agents->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No agents</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by adding your first agent.</p>
                    <div class="mt-6">
                        <a href="{{ route('company.agents.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Add Agent
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 