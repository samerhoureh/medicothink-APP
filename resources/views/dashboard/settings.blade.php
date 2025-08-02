@extends('layouts.app')

@section('title', 'Settings - MedicoThink Dashboard')
@section('page-title', 'System Settings')

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- General Settings -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">General Settings</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Application Name</label>
                        <input type="text" name="app_name" value="{{ $settings['app_name'] }}" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Application Version</label>
                        <input type="text" name="app_version" value="{{ $settings['app_version'] }}" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="maintenance_mode" id="maintenance_mode" 
                               {{ $settings['maintenance_mode'] ? 'checked' : '' }}
                               class="h-4 w-4 text-teal-600 border-gray-300 rounded">
                        <label for="maintenance_mode" class="ml-2 block text-sm text-gray-900">
                            Maintenance Mode
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="registration_enabled" id="registration_enabled" 
                               {{ $settings['registration_enabled'] ? 'checked' : '' }}
                               class="h-4 w-4 text-teal-600 border-gray-300 rounded">
                        <label for="registration_enabled" class="ml-2 block text-sm text-gray-900">
                            Allow New Registrations
                        </label>
                    </div>
                </div>
            </div>

            <!-- AI Settings -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">AI Features</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" name="ai_features_enabled" id="ai_features_enabled" 
                               {{ $settings['ai_features_enabled'] ? 'checked' : '' }}
                               class="h-4 w-4 text-teal-600 border-gray-300 rounded">
                        <label for="ai_features_enabled" class="ml-2 block text-sm text-gray-900">
                            Enable AI Features
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">OpenAI Model</label>
                        <select name="openai_model" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="gpt-4">GPT-4</option>
                            <option value="gpt-3.5-turbo">GPT-3.5 Turbo</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Max Tokens per Request</label>
                        <input type="number" name="max_tokens" value="1000" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Rate Limit (requests/minute)</label>
                        <input type="number" name="rate_limit" value="60" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                </div>
            </div>

            <!-- Payment Settings -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Payment Settings</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Currency</label>
                        <select name="default_currency" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="USD">USD - US Dollar</option>
                            <option value="EUR">EUR - Euro</option>
                            <option value="SAR">SAR - Saudi Riyal</option>
                        </select>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="stripe_enabled" id="stripe_enabled" checked
                               class="h-4 w-4 text-teal-600 border-gray-300 rounded">
                        <label for="stripe_enabled" class="ml-2 block text-sm text-gray-900">
                            Enable Stripe Payments
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="paypal_enabled" id="paypal_enabled" checked
                               class="h-4 w-4 text-teal-600 border-gray-300 rounded">
                        <label for="paypal_enabled" class="ml-2 block text-sm text-gray-900">
                            Enable PayPal Payments
                        </label>
                    </div>
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" name="email_notifications" id="email_notifications" checked
                               class="h-4 w-4 text-teal-600 border-gray-300 rounded">
                        <label for="email_notifications" class="ml-2 block text-sm text-gray-900">
                            Email Notifications
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="sms_notifications" id="sms_notifications" checked
                               class="h-4 w-4 text-teal-600 border-gray-300 rounded">
                        <label for="sms_notifications" class="ml-2 block text-sm text-gray-900">
                            SMS Notifications
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Admin Email</label>
                        <input type="email" name="admin_email" value="admin@medicothink.com" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="bg-teal-600 text-white py-2 px-6 rounded-md hover:bg-teal-700">
                Save Settings
            </button>
        </div>
    </form>
</div>
@endsection