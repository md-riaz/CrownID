@extends('vendor.tyro-dashboard.layouts.admin')

@section('title', 'Create Role')

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
                        <a href="{{ route('admin.roles.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-indigo-600 md:ml-2">Roles</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Create</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Create New Role</h1>
            <p class="mt-2 text-sm text-gray-600">Add a new role to the realm or client</p>
        </div>

        <!-- Flash Messages -->
        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Create Form -->
        <div class="bg-white shadow rounded-lg">
            <form action="{{ route('admin.roles.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <!-- Role Type Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Role Type <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-4">
                        <label class="flex items-center">
                            <input type="radio" 
                                   name="role_type" 
                                   value="realm" 
                                   {{ old('role_type', request('client_id') ? 'client' : 'realm') === 'realm' ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                                   onchange="toggleClientSelector()">
                            <span class="ml-2 text-sm text-gray-700">Realm Role</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" 
                                   name="role_type" 
                                   value="client" 
                                   {{ old('role_type', request('client_id') ? 'client' : 'realm') === 'client' ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                                   onchange="toggleClientSelector()">
                            <span class="ml-2 text-sm text-gray-700">Client Role</span>
                        </label>
                    </div>
                </div>

                <!-- Client Selection (shown only for client roles) -->
                <div id="client-selector" style="display: {{ old('role_type', request('client_id') ? 'client' : 'realm') === 'client' ? 'block' : 'none' }};">
                    <label for="client_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Client <span class="text-red-500">*</span>
                    </label>
                    <select name="client_id" 
                            id="client_id" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('client_id') border-red-500 @enderror">
                        <option value="">-- Select a Client --</option>
                        @foreach($clients ?? [] as $client)
                            <option value="{{ $client->id }}" {{ old('client_id', request('client_id')) == $client->id ? 'selected' : '' }}>
                                {{ $client->client_id }}
                            </option>
                        @endforeach
                    </select>
                    @error('client_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Role Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           value="{{ old('name') }}"
                           required
                           placeholder="e.g., admin, user, moderator"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-500">Use lowercase letters, numbers, hyphens, and underscores only</p>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea name="description" 
                              id="description" 
                              rows="3"
                              placeholder="Describe the purpose and permissions of this role..."
                              class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Composite Role Checkbox -->
                <div>
                    <label class="flex items-start">
                        <input type="checkbox" 
                               name="composite" 
                               id="composite" 
                               value="1"
                               {{ old('composite') ? 'checked' : '' }}
                               class="h-4 w-4 mt-0.5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <div class="ml-3">
                            <span class="text-sm font-medium text-gray-700">Composite Role</span>
                            <p class="text-sm text-gray-500">A composite role is a role that has one or more associated roles. When a user is granted a composite role, they inherit the permissions of all associated roles.</p>
                        </div>
                    </label>
                </div>

                <!-- Info Box -->
                <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-indigo-800">About Roles</h3>
                            <div class="mt-2 text-sm text-indigo-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li><strong>Realm Roles:</strong> Available across all clients in the realm</li>
                                    <li><strong>Client Roles:</strong> Specific to a single client application</li>
                                    <li><strong>Composite Roles:</strong> Can include other roles, simplifying role management</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('admin.roles.index') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                        <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Create Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript for toggling client selector -->
<script>
function toggleClientSelector() {
    const roleType = document.querySelector('input[name="role_type"]:checked').value;
    const clientSelector = document.getElementById('client-selector');
    
    if (roleType === 'client') {
        clientSelector.style.display = 'block';
    } else {
        clientSelector.style.display = 'none';
    }
}
</script>
@endsection
