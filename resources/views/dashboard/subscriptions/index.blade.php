@extends('layouts.app')

@section('title', 'Subscriptions - MedicoThink Dashboard')
@section('page-title', 'Subscriptions Management')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">All Subscriptions</h3>
            <div class="flex space-x-2">
                <select class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="expired">Expired</option>
                    <option value="expiring_soon">Expiring Soon</option>
                </select>
                <button class="px-4 py-2 bg-teal-600 text-white rounded-md text-sm hover:bg-teal-700">
                    Export
                </button>
            </div>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($subscriptions as $subscription)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-teal-100 rounded-full flex items-center justify-center">
                                <span class="text-teal-600 font-semibold text-sm">{{ $subscription->user->initials }}</span>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $subscription->user->username }}</div>
                                <div class="text-sm text-gray-500">{{ $subscription->user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $subscription->plan_name }}</div>
                        <div class="text-sm text-gray-500">{{ ucfirst($subscription->plan_type) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${{ number_format($subscription->price, 2) }} {{ $subscription->currency }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($subscription->is_expired) bg-red-100 text-red-800
                            @elseif($subscription->is_expiring_soon) bg-yellow-100 text-yellow-800
                            @elseif($subscription->is_active) bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $subscription->status_text }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $subscription->expires_at->format('M d, Y') }}
                        <div class="text-xs text-gray-400">{{ $subscription->days_remaining }} days left</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button class="text-teal-600 hover:text-teal-900 mr-3">Extend</button>
                        <button class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                        <button class="text-red-600 hover:text-red-900">Cancel</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="px-6 py-4 border-t">
        {{ $subscriptions->links() }}
    </div>
</div>
@endsection