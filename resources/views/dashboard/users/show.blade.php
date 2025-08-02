@extends('layouts.app')

@section('title', 'User Details - MedicoThink Dashboard')
@section('page-title', 'User Details')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- User Profile Card -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6">
            <div class="flex items-center">
                <div class="w-20 h-20 bg-teal-100 rounded-full flex items-center justify-center">
                    <span class="text-teal-600 font-bold text-2xl">{{ $user->initials }}</span>
                </div>
                <div class="ml-6">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $user->username }}</h1>
                    <p class="text-gray-600">{{ $user->email }}</p>
                    <p class="text-sm text-gray-500">{{ $user->specialization ?? 'No specialization' }}</p>
                </div>
                <div class="ml-auto">
                    <span class="px-3 py-1 rounded-full text-sm font-medium 
                        {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Information -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">User Information</h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Phone Number</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->phone_number ?? 'Not provided' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Age</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->age ?? 'Not provided' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">City</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->city ?? 'Not provided' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nationality</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->nationality ?? 'Not provided' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Education Level</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->education_level ?? 'Not provided' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Joined Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('M d, Y') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Recent Conversations -->
            <div class="bg-white rounded-lg shadow mt-6">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Conversations</h3>
                </div>
                <div class="p-6">
                    @if($user->conversations->count() > 0)
                        <div class="space-y-4">
                            @foreach($user->conversations->take(5) as $conversation)
                            <div class="flex items-center justify-between p-4 border rounded-lg">
                                <div>
                                    <h4 class="font-medium text-gray-900">{{ $conversation->title }}</h4>
                                    <p class="text-sm text-gray-500">{{ $conversation->messages->count() }} messages</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-500">{{ $conversation->created_at->diffForHumans() }}</p>
                                    <a href="{{ route('admin.conversations.show', $conversation->id) }}" 
                                       class="text-teal-600 hover:text-teal-900 text-sm">View</a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">No conversations yet</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Subscription & Stats -->
        <div class="space-y-6">
            <!-- Subscription Status -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Subscription</h3>
                </div>
                <div class="p-6">
                    @if($user->subscription)
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm font-medium text-gray-500">Plan:</span>
                                <span class="ml-2 text-sm text-gray-900">{{ $user->subscription->plan_name }}</span>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Status:</span>
                                <span class="ml-2 px-2 py-1 rounded-full text-xs font-medium 
                                    {{ $user->subscription->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $user->subscription->status_text }}
                                </span>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Expires:</span>
                                <span class="ml-2 text-sm text-gray-900">{{ $user->subscription->expires_at->format('M d, Y') }}</span>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Days Remaining:</span>
                                <span class="ml-2 text-sm text-gray-900">{{ $user->subscription->days_remaining }}</span>
                            </div>
                        </div>
                    @else
                        <p class="text-gray-500">No active subscription</p>
                    @endif
                </div>
            </div>

            <!-- User Stats -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Statistics</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Total Conversations</span>
                            <span class="text-sm font-medium text-gray-900">{{ $user->conversations->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Total Messages</span>
                            <span class="text-sm font-medium text-gray-900">{{ $user->conversations->sum(function($c) { return $c->messages->count(); }) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Last Activity</span>
                            <span class="text-sm font-medium text-gray-900">{{ $user->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection