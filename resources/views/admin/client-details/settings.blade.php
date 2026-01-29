@extends('vendor.tyro-dashboard.layouts.admin')

@section('title', 'Client Settings - ' . $client->name)

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumbs -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-indigo-600">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ route('admin.clients.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-indigo-600 md:ml-2">Clients</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $client->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ $client->name }}</h1>
            <div class="mt-2 flex items-center space-x-3">
                <code class="px-3 py-1 bg-gray-100 rounded text-sm font-mono text-gray-800">{{ $client->client_id }}</code>
                <button onclick="copyToClipboard('{{ $client->client_id }}')" class="text-indigo-600 hover:text-indigo-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </button>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                    {{ $client->realm ?? 'master' }}
                </span>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-8">
                <a href="{{ route('admin.clients.details.settings', $client) }}" class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Settings
                </a>
                <a href="{{ route('admin.clients.details.credentials', $client) }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Credentials
                </a>
                <a href="{{ route('admin.clients.details.roles', $client) }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Roles
                </a>
                <a href="{{ route('admin.clients.details.sessions', $client) }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Sessions
                </a>
            </nav>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Settings Form -->
        <form method="POST" action="{{ route('admin.clients.details.settings.update', $client) }}" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- Basic Information -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h2>
                
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Client Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $client->name) }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('name') border-red-300 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('description') border-red-300 @enderror">{{ old('description', $client->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Optional description of the client application</p>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="enabled" id="enabled" value="1" {{ old('enabled', $client->enabled) ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="enabled" class="ml-2 block text-sm text-gray-900">
                            Enabled
                        </label>
                    </div>
                </div>
            </div>

            <!-- OAuth Configuration -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">OAuth Configuration</h2>
                
                <div class="space-y-4">
                    <div>
                        <label for="client_protocol" class="block text-sm font-medium text-gray-700">Client Protocol</label>
                        <select name="client_protocol" id="client_protocol" disabled
                            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm">
                            <option value="openid-connect" selected>openid-connect</option>
                        </select>
                        <p class="mt-1 text-sm text-gray-500">OpenID Connect protocol is used for OAuth 2.0 authentication</p>
                    </div>

                    <div>
                        <label for="access_type" class="block text-sm font-medium text-gray-700">Access Type</label>
                        <select name="access_type" id="access_type" disabled
                            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm">
                            <option value="confidential" {{ $client->access_type === 'confidential' ? 'selected' : '' }}>Confidential</option>
                            <option value="public" {{ $client->access_type === 'public' ? 'selected' : '' }}>Public</option>
                        </select>
                        <p class="mt-1 text-sm text-gray-500">Confidential clients require a secret, public clients do not</p>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="standard_flow_enabled" id="standard_flow_enabled" value="1" checked disabled
                            class="h-4 w-4 rounded border-gray-300 bg-gray-50 text-indigo-600">
                        <label for="standard_flow_enabled" class="ml-2 block text-sm text-gray-900">
                            Standard Flow Enabled
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="direct_access_grants_enabled" id="direct_access_grants_enabled" value="1" 
                            {{ old('direct_access_grants_enabled', $client->direct_access_grants_enabled ?? false) ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="direct_access_grants_enabled" class="ml-2 block text-sm text-gray-900">
                            Direct Access Grants Enabled
                        </label>
                    </div>
                </div>
            </div>

            <!-- URLs Configuration -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">URLs Configuration</h2>
                
                <div class="space-y-4">
                    <div>
                        <label for="root_url" class="block text-sm font-medium text-gray-700">Root URL</label>
                        <input type="url" name="root_url" id="root_url" value="{{ old('root_url', $client->root_url) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('root_url') border-red-300 @enderror">
                        @error('root_url')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Base URL for the client application</p>
                    </div>

                    <div>
                        <label for="redirect_uris" class="block text-sm font-medium text-gray-700">Valid Redirect URIs <span class="text-red-500">*</span></label>
                        <textarea name="redirect_uris" id="redirect_uris" rows="4" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm @error('redirect_uris') border-red-300 @enderror">{{ old('redirect_uris', is_array($client->redirect_uris) ? implode("\n", $client->redirect_uris) : $client->redirect_uris) }}</textarea>
                        @error('redirect_uris')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Enter one redirect URI per line. Wildcards (*) are allowed.</p>
                    </div>

                    <div>
                        <label for="web_origins" class="block text-sm font-medium text-gray-700">Web Origins</label>
                        <input type="text" name="web_origins" id="web_origins" value="{{ old('web_origins', $client->web_origins) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('web_origins') border-red-300 @enderror">
                        @error('web_origins')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Allowed CORS origins. Use * to allow all origins (not recommended for production)</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.clients.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Client ID copied to clipboard!');
    });
}
</script>
@endsection
