@extends('layouts.app')

@section('title', 'My Company')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold">{{ $company->company_name }}</h2>
                        <p class="text-gray-600">Company information and details</p>
                    </div>
                </div>

                <!-- Company Overview -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Company Details -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium mb-4">Company Information</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Company Name</dt>
                                <dd class="text-sm text-gray-900">{{ $company->company_name }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Business Type</dt>
                                <dd class="text-sm text-gray-900">{{ $company->business_type ?: 'Not specified' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Tax ID</dt>
                                <dd class="text-sm text-gray-900">{{ $company->tax_id ?: 'Not specified' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                <dd class="text-sm text-gray-900">{{ $company->phone ?: 'Not specified' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Website</dt>
                                <dd class="text-sm text-gray-900">
                                    @if($company->website)
                                        <a href="{{ $company->website }}" target="_blank" class="text-primary-600 hover:text-primary-900">
                                            {{ $company->website }}
                                        </a>
                                    @else
                                        Not specified
                                    @endif
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Founded</dt>
                                <dd class="text-sm text-gray-900">{{ $company->created_at->format('M d, Y') }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Contact & Status -->
                    <div class="bg-blue-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium mb-4">Contact & Status</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Primary Contact</dt>
                                <dd class="text-sm text-gray-900">{{ $company->user->name }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Email</dt>
                                <dd class="text-sm text-gray-900">{{ $company->user->email }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Company Status</dt>
                                <dd class="text-sm">
                                    @if($company->is_active)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Stripe Onboarding</dt>
                                <dd class="text-sm">
                                    @if($company->isStripeOnboardingComplete())
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Complete</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Your Role</dt>
                                <dd class="text-sm text-gray-900">Agent ({{ $agent->agent_code }})</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Department</dt>
                                <dd class="text-sm text-gray-900">{{ $agent->department ?: 'Not specified' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Address -->
                @if($company->address)
                <div class="mb-8">
                    <h3 class="text-lg font-medium mb-4">Company Address</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-gray-900 whitespace-pre-wrap">{{ $company->address }}</p>
                    </div>
                </div>
                @endif

                <!-- Company Statistics -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium mb-4">Company Statistics</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg text-center">
                            <div class="text-2xl font-bold text-blue-600">{{ $company->agents()->count() }}</div>
                            <div class="text-sm text-gray-600">Total Agents</div>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg text-center">
                            <div class="text-2xl font-bold text-green-600">{{ $company->invoices()->count() }}</div>
                            <div class="text-sm text-gray-600">Total Invoices</div>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg text-center">
                            <div class="text-2xl font-bold text-purple-600">${{ number_format($company->transactions()->sum('amount'), 2) }}</div>
                            <div class="text-sm text-gray-600">Total Revenue</div>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg text-center">
                            <div class="text-2xl font-bold text-yellow-600">{{ $company->paymentMethods()->where('is_active', true)->count() }}</div>
                            <div class="text-sm text-gray-600">Payment Methods</div>
                        </div>
                    </div>
                </div>

                <!-- Other Agents -->
                @if($company->agents->count() > 1)
                <div class="mb-8">
                    <h3 class="text-lg font-medium mb-4">Other Agents</h3>
                    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agent</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agent Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($company->agents->where('id', '!=', $agent->id) as $otherAgent)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                        <span class="text-sm font-medium text-gray-700">
                                                            {{ substr($otherAgent->user->name, 0, 1) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $otherAgent->user->name }}</div>
                                                    <div class="text-sm text-gray-500">{{ $otherAgent->user->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">
                                            {{ $otherAgent->agent_code }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $otherAgent->department ?: 'Not specified' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($otherAgent->is_active)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $otherAgent->created_at->format('M d, Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Recent Company Activity -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Recent Invoices -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Company Invoices</h3>
                        @if($company->invoices()->orderBy('created_at', 'desc')->limit(5)->get()->count() > 0)
                            <div class="space-y-3">
                                @foreach($company->invoices()->with('agent.user')->orderBy('created_at', 'desc')->limit(5)->get() as $invoice)
                                    <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $invoice->invoice_number }}</div>
                                            <div class="text-sm text-gray-500">Agent: {{ $invoice->agent->user->name }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-medium text-gray-900">${{ number_format($invoice->total_amount, 2) }}</div>
                                            @if($invoice->status === 'paid')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                                            @elseif($invoice->isOverdue())
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Overdue</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500">No recent invoices found.</p>
                        @endif
                    </div>

                    <!-- Recent Transactions -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Company Transactions</h3>
                        @if($company->transactions()->orderBy('created_at', 'desc')->limit(5)->get()->count() > 0)
                            <div class="space-y-3">
                                @foreach($company->transactions()->with('agent.user')->orderBy('created_at', 'desc')->limit(5)->get() as $transaction)
                                    <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $transaction->transaction_id }}</div>
                                            <div class="text-sm text-gray-500">Agent: {{ $transaction->agent->user->name }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-medium text-gray-900">${{ number_format($transaction->amount, 2) }}</div>
                                            @if($transaction->status === 'completed')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                                            @elseif($transaction->status === 'pending')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($transaction->status) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500">No recent transactions found.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 