@extends('layouts.app')

@section('title', 'Analytics - MedicoThink Dashboard')
@section('page-title', 'Analytics & Reports')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <!-- Daily Stats -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">{{ number_format($analytics['daily_active_users']) }}</h3>
                <p class="text-sm text-gray-600">Daily Active Users</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">{{ number_format($analytics['conversations_today']) }}</h3>
                <p class="text-sm text-gray-600">Conversations Today</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">${{ number_format($analytics['revenue_today'], 2) }}</h3>
                <p class="text-sm text-gray-600">Revenue Today</p>
            </div>
        </div>
    </div>
</div>

<!-- Charts and Detailed Analytics -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- User Growth Chart -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-900">User Growth</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Daily Active Users</span>
                    <span class="text-lg font-semibold text-gray-900">{{ number_format($analytics['daily_active_users']) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Weekly Active Users</span>
                    <span class="text-lg font-semibold text-gray-900">{{ number_format($analytics['weekly_active_users']) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Monthly Active Users</span>
                    <span class="text-lg font-semibold text-gray-900">{{ number_format($analytics['monthly_active_users']) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Analytics -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Revenue Analytics</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Today's Revenue</span>
                    <span class="text-lg font-semibold text-green-600">${{ number_format($analytics['revenue_today'], 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Messages Today</span>
                    <span class="text-lg font-semibold text-gray-900">{{ number_format($analytics['messages_today']) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">AI API Usage</span>
                    <span class="text-lg font-semibold text-gray-900">{{ number_format($analytics['messages_today'] * 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection