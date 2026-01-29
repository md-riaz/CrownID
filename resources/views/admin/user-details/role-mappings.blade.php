@extends('vendor.tyro-dashboard.layouts.admin')

@section('title', 'User Role Mappings - ' . $user['username'])

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Breadcrumbs -->
    <nav class="text-sm mb-6">
        <ol class="list-none p-0 inline-flex">
            <li class="flex items-center">
                <a href="{{ route('admin.users.index') }}" class="text-indigo-600 hover:text-indigo-800">Users</a>
                <svg class="w-3 h-3 mx-3 fill-current text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/></svg>
            </li>
            <li>
                <span class="text-gray-500">{{ $user['username'] }}</span>
            </li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ $user['username'] }}</h1>
        <p class="text-gray-600 mt-1">Manage user role assignments</p>
    </div>

    <!-- Tab Navigation -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8">
            <a href="{{ route('admin.users.details.info', $user['id']) }}" class="border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Details
            </a>
            <a href="{{ route('admin.users.details.credentials', $user['id']) }}" class="border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Credentials
            </a>
            <a href="{{ route('admin.users.details.role-mappings', $user['id']) }}" class="border-b-2 border-indigo-500 py-4 px-1 text-sm font-medium text-indigo-600">
                Role Mappings
            </a>
            <a href="{{ route('admin.users.details.groups', $user['id']) }}" class="border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Groups
            </a>
            <a href="{{ route('admin.users.details.sessions', $user['id']) }}" class="border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Sessions
            </a>
            <a href="{{ route('admin.users.details.required-actions', $user['id']) }}" class="border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Required Actions
            </a>
        </nav>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left Column - Available Roles -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Available Roles</h2>
                <p class="text-sm text-gray-600 mt-1">Roles that can be assigned to this user</p>
            </div>
            <div class="p-6">
                <!-- Search Box -->
                <div class="mb-4">
                    <input type="text" id="searchAvailable" placeholder="Search available roles..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Available Roles List -->
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @forelse($realmRoles ?? [] as $role)
                        @if(!in_array($role['name'], array_column($user['realm_roles'] ?? [], 'name')))
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 available-role">
                                <div class="flex-1">
                                    <h3 class="text-sm font-medium text-gray-900 role-name">{{ $role['name'] }}</h3>
                                    @if(!empty($role['description']))
                                        <p class="text-xs text-gray-500 mt-1">{{ $role['description'] }}</p>
                                    @endif
                                </div>
                                <form action="{{ route('admin.users.details.role-mappings.add', $user['id']) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="role_id" value="{{ $role['id'] }}">
                                    <input type="hidden" name="role_name" value="{{ $role['name'] }}">
                                    <button type="submit" class="ml-3 px-3 py-1 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        Add
                                    </button>
                                </form>
                            </div>
                        @endif
                    @empty
                        <p class="text-sm text-gray-500 text-center py-8">No available roles</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Column - Assigned Roles -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Assigned Roles</h2>
                <p class="text-sm text-gray-600 mt-1">Roles currently assigned to this user</p>
            </div>
            <div class="p-6">
                <!-- Search Box -->
                <div class="mb-4">
                    <input type="text" id="searchAssigned" placeholder="Search assigned roles..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Assigned Roles List -->
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @forelse($user['realm_roles'] ?? [] as $role)
                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 assigned-role">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2">
                                    <h3 class="text-sm font-medium text-gray-900 role-name">{{ $role['name'] }}</h3>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                        Direct
                                    </span>
                                </div>
                                @if(!empty($role['description']))
                                    <p class="text-xs text-gray-500 mt-1">{{ $role['description'] }}</p>
                                @endif
                            </div>
                            <form action="{{ route('admin.users.details.role-mappings.remove', $user['id']) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="role_id" value="{{ $role['id'] }}">
                                <input type="hidden" name="role_name" value="{{ $role['name'] }}">
                                <button type="submit" class="ml-3 px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                    Remove
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-8">No assigned roles</p>
                    @endforelse

                    <!-- Roles Inherited from Groups -->
                    @if(!empty($user['inherited_roles']))
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-700 mb-3">Inherited from Groups</h3>
                            @foreach($user['inherited_roles'] as $inherited)
                                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 mb-2 assigned-role">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2">
                                            <h3 class="text-sm font-medium text-gray-900 role-name">{{ $inherited['role_name'] }}</h3>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                                Inherited
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">From group: {{ $inherited['group_name'] }}</p>
                                    </div>
                                    <span class="ml-3 text-xs text-gray-400">Via Group</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality for available roles
document.getElementById('searchAvailable').addEventListener('input', function(e) {
    const search = e.target.value.toLowerCase();
    document.querySelectorAll('.available-role').forEach(function(role) {
        const name = role.querySelector('.role-name').textContent.toLowerCase();
        role.style.display = name.includes(search) ? 'flex' : 'none';
    });
});

// Search functionality for assigned roles
document.getElementById('searchAssigned').addEventListener('input', function(e) {
    const search = e.target.value.toLowerCase();
    document.querySelectorAll('.assigned-role').forEach(function(role) {
        const name = role.querySelector('.role-name').textContent.toLowerCase();
        role.style.display = name.includes(search) ? 'flex' : 'none';
    });
});
</script>
@endsection
