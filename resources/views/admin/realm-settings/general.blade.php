@extends('admin.realm-settings.index')

@section('tab-content')
<div class="p-6">
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900">General Settings</h2>
        <p class="mt-1 text-sm text-gray-600">Configure basic realm settings and display properties.</p>
    </div>

    <form action="{{ route('admin.realms.settings.general.update', $realm) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Realm Name (Read-only) -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Realm Name
            </label>
            <div class="flex items-center">
                <span class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium bg-indigo-100 text-indigo-800">
                    {{ $realm->name }}
                </span>
            </div>
            <p class="mt-1 text-sm text-gray-500">The unique identifier for this realm. This cannot be changed.</p>
        </div>

        <!-- Display Name -->
        <div>
            <label for="display_name" class="block text-sm font-medium text-gray-700 mb-2">
                Display Name <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                   name="display_name" 
                   id="display_name" 
                   value="{{ old('display_name', $realm->display_name) }}"
                   required
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('display_name') border-red-300 @enderror">
            @error('display_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-sm text-gray-500">A human-friendly name for the realm that will be displayed to users.</p>
        </div>

        <!-- HTML Display Name -->
        <div>
            <label for="html_display_name" class="block text-sm font-medium text-gray-700 mb-2">
                HTML Display Name
            </label>
            <input type="text" 
                   name="html_display_name" 
                   id="html_display_name" 
                   value="{{ old('html_display_name', $realm->html_display_name) }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('html_display_name') border-red-300 @enderror">
            @error('html_display_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-sm text-gray-500">Optional HTML-formatted display name. Leave empty to use the plain display name.</p>
        </div>

        <!-- Enabled Toggle -->
        <div class="flex items-start">
            <div class="flex items-center h-5">
                <input type="checkbox" 
                       name="enabled" 
                       id="enabled" 
                       value="1"
                       {{ old('enabled', $realm->enabled) ? 'checked' : '' }}
                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            </div>
            <div class="ml-3">
                <label for="enabled" class="font-medium text-gray-700">Realm Enabled</label>
                <p class="text-sm text-gray-500">When disabled, users cannot login or access resources in this realm.</p>
            </div>
        </div>

        <!-- Frontend URL -->
        <div>
            <label for="frontend_url" class="block text-sm font-medium text-gray-700 mb-2">
                Frontend URL
            </label>
            <div class="mt-1 flex rounded-md shadow-sm">
                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                    </svg>
                </span>
                <input type="url" 
                       name="frontend_url" 
                       id="frontend_url" 
                       value="{{ old('frontend_url', $realm->frontend_url) }}"
                       placeholder="https://example.com"
                       class="flex-1 block w-full rounded-none rounded-r-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('frontend_url') border-red-300 @enderror">
            </div>
            @error('frontend_url')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-sm text-gray-500">Set a different base URL for frontend requests. Useful when running behind a reverse proxy.</p>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end pt-6 border-t border-gray-200">
            <button type="submit" 
                    class="inline-flex justify-center items-center px-6 py-2.5 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
