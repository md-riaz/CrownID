@extends('admin.realm-settings.index')

@section('tab-content')
<div class="p-6">
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900">Token Settings</h2>
        <p class="mt-1 text-sm text-gray-600">Configure token lifespans and session timeouts for this realm.</p>
    </div>

    <form action="{{ route('admin.realms.settings.tokens.update', $realm) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Access Token Lifespan -->
            <div>
                <label for="access_token_lifespan" class="block text-sm font-medium text-gray-700 mb-2">
                    <span class="flex items-center">
                        Access Token Lifespan
                        <svg class="w-4 h-4 ml-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Time until access tokens expire">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                </label>
                <div class="mt-1 flex rounded-md shadow-sm">
                    <input type="number" 
                           name="access_token_lifespan" 
                           id="access_token_lifespan" 
                           value="{{ old('access_token_lifespan', $realm->access_token_lifespan ?? 300) }}"
                           min="60"
                           required
                           class="flex-1 block w-full rounded-l-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('access_token_lifespan') border-red-300 @enderror">
                    <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                        seconds
                    </span>
                </div>
                @error('access_token_lifespan')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Maximum time before an access token expires. Default: 300 seconds (5 minutes).</p>
            </div>

            <!-- Refresh Token Lifespan -->
            <div>
                <label for="refresh_token_lifespan" class="block text-sm font-medium text-gray-700 mb-2">
                    <span class="flex items-center">
                        Refresh Token Lifespan
                        <svg class="w-4 h-4 ml-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Time until refresh tokens expire">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                </label>
                <div class="mt-1 flex rounded-md shadow-sm">
                    <input type="number" 
                           name="refresh_token_lifespan" 
                           id="refresh_token_lifespan" 
                           value="{{ old('refresh_token_lifespan', $realm->refresh_token_lifespan ?? 1800) }}"
                           min="60"
                           required
                           class="flex-1 block w-full rounded-l-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('refresh_token_lifespan') border-red-300 @enderror">
                    <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                        seconds
                    </span>
                </div>
                @error('refresh_token_lifespan')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Maximum time before a refresh token expires. Default: 1800 seconds (30 minutes).</p>
            </div>

            <!-- SSO Session Idle -->
            <div>
                <label for="sso_session_idle" class="block text-sm font-medium text-gray-700 mb-2">
                    <span class="flex items-center">
                        SSO Session Idle Timeout
                        <svg class="w-4 h-4 ml-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Time of inactivity before SSO session expires">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                </label>
                <div class="mt-1 flex rounded-md shadow-sm">
                    <input type="number" 
                           name="sso_session_idle" 
                           id="sso_session_idle" 
                           value="{{ old('sso_session_idle', $realm->sso_session_idle ?? 1800) }}"
                           min="60"
                           required
                           class="flex-1 block w-full rounded-l-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('sso_session_idle') border-red-300 @enderror">
                    <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                        seconds
                    </span>
                </div>
                @error('sso_session_idle')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Maximum time an SSO session can be idle before expiring. Default: 1800 seconds (30 minutes).</p>
            </div>

            <!-- SSO Session Max -->
            <div>
                <label for="sso_session_max" class="block text-sm font-medium text-gray-700 mb-2">
                    <span class="flex items-center">
                        SSO Session Max Lifespan
                        <svg class="w-4 h-4 ml-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Maximum time an SSO session can exist">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                </label>
                <div class="mt-1 flex rounded-md shadow-sm">
                    <input type="number" 
                           name="sso_session_max" 
                           id="sso_session_max" 
                           value="{{ old('sso_session_max', $realm->sso_session_max ?? 36000) }}"
                           min="60"
                           required
                           class="flex-1 block w-full rounded-l-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('sso_session_max') border-red-300 @enderror">
                    <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                        seconds
                    </span>
                </div>
                @error('sso_session_max')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Maximum time an SSO session can exist, regardless of activity. Default: 36000 seconds (10 hours).</p>
            </div>

            <!-- Client Session Idle -->
            <div>
                <label for="client_session_idle" class="block text-sm font-medium text-gray-700 mb-2">
                    <span class="flex items-center">
                        Client Session Idle Timeout
                        <svg class="w-4 h-4 ml-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Time of inactivity before client session expires">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                </label>
                <div class="mt-1 flex rounded-md shadow-sm">
                    <input type="number" 
                           name="client_session_idle" 
                           id="client_session_idle" 
                           value="{{ old('client_session_idle', $realm->client_session_idle ?? 0) }}"
                           min="0"
                           class="flex-1 block w-full rounded-l-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('client_session_idle') border-red-300 @enderror">
                    <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                        seconds
                    </span>
                </div>
                @error('client_session_idle')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Maximum time a client session can be idle. Set to 0 to use SSO Session Idle value.</p>
            </div>

            <!-- Client Session Max -->
            <div>
                <label for="client_session_max" class="block text-sm font-medium text-gray-700 mb-2">
                    <span class="flex items-center">
                        Client Session Max Lifespan
                        <svg class="w-4 h-4 ml-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Maximum time a client session can exist">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                </label>
                <div class="mt-1 flex rounded-md shadow-sm">
                    <input type="number" 
                           name="client_session_max" 
                           id="client_session_max" 
                           value="{{ old('client_session_max', $realm->client_session_max ?? 0) }}"
                           min="0"
                           class="flex-1 block w-full rounded-l-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('client_session_max') border-red-300 @enderror">
                    <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                        seconds
                    </span>
                </div>
                @error('client_session_max')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Maximum time a client session can exist. Set to 0 to use SSO Session Max value.</p>
            </div>
        </div>

        <!-- Info Box -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>Important:</strong> Shorter token lifespans improve security but may require users to login more frequently. Adjust these values based on your security requirements and user experience needs.
                    </p>
                </div>
            </div>
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
