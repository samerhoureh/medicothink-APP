@extends('layouts.app')

@section('title', 'App Versions - MedicoThink Dashboard')
@section('page-title', 'App Version Management')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Create New Version -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Create New Version</h3>
            </div>
            <div class="p-6">
                <form action="{{ route('admin.app-versions.store') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Version Name</label>
                            <input type="text" name="version_name" placeholder="1.2.0" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Version Code</label>
                            <input type="number" name="version_code" placeholder="120" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Platform</label>
                            <select name="platform" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="android">Android</option>
                                <option value="ios">iOS</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Download URL</label>
                            <input type="url" name="download_url" placeholder="https://..." 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="is_required" id="is_required" 
                                   class="h-4 w-4 text-teal-600 border-gray-300 rounded">
                            <label for="is_required" class="ml-2 block text-sm text-gray-900">
                                Force Update Required
                            </label>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Release Notes</label>
                            <textarea name="release_notes" rows="3" 
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                      placeholder="What's new in this version..."></textarea>
                        </div>
                        <button type="submit" 
                                class="w-full bg-teal-600 text-white py-2 px-4 rounded-md hover:bg-teal-700">
                            Create Version
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Version List -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-900">App Versions</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($versions as $version)
                    <div class="border rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-medium text-gray-900">
                                    {{ $version->version_name }} 
                                    <span class="text-sm text-gray-500">({{ $version->version_code }})</span>
                                </h4>
                                <p class="text-sm text-gray-600">{{ ucfirst($version->platform) }}</p>
                                <p class="text-xs text-gray-500">{{ $version->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                @if($version->is_required)
                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">Required</span>
                                @endif
                                <span class="px-2 py-1 text-xs rounded-full 
                                    {{ $version->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $version->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                        @if($version->release_notes)
                            <div class="mt-3">
                                <p class="text-sm text-gray-700">{{ $version->release_notes }}</p>
                            </div>
                        @endif
                        <div class="mt-3 flex space-x-2">
                            <a href="{{ $version->download_url }}" target="_blank" 
                               class="text-teal-600 hover:text-teal-900 text-sm">Download</a>
                            <button class="text-indigo-600 hover:text-indigo-900 text-sm">Edit</button>
                            <button class="text-red-600 hover:text-red-900 text-sm">Delete</button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection