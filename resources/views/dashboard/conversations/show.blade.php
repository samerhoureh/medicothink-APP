@extends('layouts.app')

@section('title', 'Conversation Details - MedicoThink Dashboard')
@section('page-title', 'Conversation Details')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Conversation Header -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $conversation->title }}</h1>
                    <p class="text-gray-600">Conversation with {{ $conversation->user->username }}</p>
                    <p class="text-sm text-gray-500">Started {{ $conversation->created_at->diffForHumans() }}</p>
                </div>
                <div class="flex space-x-2">
                    <span class="px-3 py-1 rounded-full text-sm font-medium 
                        {{ $conversation->is_archived ? 'bg-gray-100 text-gray-800' : 'bg-green-100 text-green-800' }}">
                        {{ $conversation->is_archived ? 'Archived' : 'Active' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Messages -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Messages ({{ $conversation->messages->count() }})</h3>
                </div>
                <div class="p-6 max-h-96 overflow-y-auto">
                    <div class="space-y-4">
                        @foreach($conversation->messages as $message)
                        <div class="flex {{ $message->is_user ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg 
                                {{ $message->is_user ? 'bg-teal-600 text-white' : 'bg-gray-100 text-gray-900' }}">
                                @if($message->message_type === 'image' && $message->image_path)
                                    <img src="{{ asset('storage/' . $message->image_path) }}" 
                                         alt="Medical Image" class="rounded mb-2 max-w-full">
                                @endif
                                <p class="text-sm">{{ $message->content }}</p>
                                <p class="text-xs mt-1 {{ $message->is_user ? 'text-teal-100' : 'text-gray-500' }}">
                                    {{ $message->created_at->format('M d, H:i') }}
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Conversation Info -->
        <div class="space-y-6">
            <!-- Summary -->
            @if($conversation->summary)
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">AI Summary</h3>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-700">{{ Str::limit($conversation->summary->summary, 200) }}</p>
                    @if($conversation->summary->diagnosis)
                        <div class="mt-4">
                            <h4 class="font-medium text-gray-900">Diagnosis:</h4>
                            <p class="text-sm text-gray-700">{{ $conversation->summary->diagnosis }}</p>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- User Info -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">User Info</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-teal-100 rounded-full flex items-center justify-center">
                            <span class="text-teal-600 font-semibold">{{ $conversation->user->initials }}</span>
                        </div>
                        <div class="ml-3">
                            <p class="font-medium text-gray-900">{{ $conversation->user->username }}</p>
                            <p class="text-sm text-gray-500">{{ $conversation->user->specialization ?? 'No specialization' }}</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.users.show', $conversation->user->id) }}" 
                       class="text-teal-600 hover:text-teal-900 text-sm">View Full Profile</a>
                </div>
            </div>

            <!-- Stats -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Statistics</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Total Messages</span>
                            <span class="text-sm font-medium text-gray-900">{{ $conversation->messages->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">User Messages</span>
                            <span class="text-sm font-medium text-gray-900">{{ $conversation->messages->where('is_user', true)->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">AI Responses</span>
                            <span class="text-sm font-medium text-gray-900">{{ $conversation->messages->where('is_user', false)->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Images Shared</span>
                            <span class="text-sm font-medium text-gray-900">{{ $conversation->messages->where('message_type', 'image')->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection