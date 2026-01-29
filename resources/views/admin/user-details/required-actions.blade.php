@extends('vendor.tyro-dashboard.layouts.admin')

@section('title', 'User Required Actions - ' . $user['username'])

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
        <p class="text-gray-600 mt-1">Manage required actions for this user</p>
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
            <a href="{{ route('admin.users.details.role-mappings', $user['id']) }}" class="border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Role Mappings
            </a>
            <a href="{{ route('admin.users.details.groups', $user['id']) }}" class="border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Groups
            </a>
            <a href="{{ route('admin.users.details.sessions', $user['id']) }}" class="border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Sessions
            </a>
            <a href="{{ route('admin.users.details.required-actions', $user['id']) }}" class="border-b-2 border-indigo-500 py-4 px-1 text-sm font-medium text-indigo-600">
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

    <!-- Info Box -->
    <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">Required actions force users to perform specific tasks during their next login, such as verifying their email or updating their password.</p>
            </div>
        </div>
    </div>

    <!-- Required Actions Form -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Required Actions</h2>
        </div>
        <form action="{{ route('admin.users.details.required-actions.update', $user['id']) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Verify Email -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" name="required_actions[]" value="VERIFY_EMAIL" id="verify_email"
                               {{ in_array('VERIFY_EMAIL', $user['required_actions'] ?? []) ? 'checked' : '' }}
                               class="h-5 w-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    </div>
                    <div class="ml-3">
                        <label for="verify_email" class="font-medium text-gray-700">Verify Email</label>
                        <p class="text-sm text-gray-500">User must verify their email address before they can login.</p>
                    </div>
                </div>

                <!-- Update Password -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" name="required_actions[]" value="UPDATE_PASSWORD" id="update_password"
                               {{ in_array('UPDATE_PASSWORD', $user['required_actions'] ?? []) ? 'checked' : '' }}
                               class="h-5 w-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    </div>
                    <div class="ml-3">
                        <label for="update_password" class="font-medium text-gray-700">Update Password</label>
                        <p class="text-sm text-gray-500">User must change their password on their next login.</p>
                    </div>
                </div>

                <!-- Configure TOTP -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" name="required_actions[]" value="CONFIGURE_TOTP" id="configure_totp"
                               {{ in_array('CONFIGURE_TOTP', $user['required_actions'] ?? []) ? 'checked' : '' }}
                               class="h-5 w-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    </div>
                    <div class="ml-3">
                        <label for="configure_totp" class="font-medium text-gray-700">Configure TOTP</label>
                        <p class="text-sm text-gray-500">User must configure Time-Based One-Time Password (2FA) authentication.</p>
                    </div>
                </div>

                <!-- Update Profile -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" name="required_actions[]" value="UPDATE_PROFILE" id="update_profile"
                               {{ in_array('UPDATE_PROFILE', $user['required_actions'] ?? []) ? 'checked' : '' }}
                               class="h-5 w-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    </div>
                    <div class="ml-3">
                        <label for="update_profile" class="font-medium text-gray-700">Update Profile</label>
                        <p class="text-sm text-gray-500">User must update their profile information on next login.</p>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" name="required_actions[]" value="TERMS_AND_CONDITIONS" id="terms_and_conditions"
                               {{ in_array('TERMS_AND_CONDITIONS', $user['required_actions'] ?? []) ? 'checked' : '' }}
                               class="h-5 w-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    </div>
                    <div class="ml-3">
                        <label for="terms_and_conditions" class="font-medium text-gray-700">Accept Terms and Conditions</label>
                        <p class="text-sm text-gray-500">User must accept terms and conditions before they can proceed.</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-6 mt-6 border-t border-gray-200">
                <button type="button" onclick="uncheckAll()" class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    Uncheck All
                </button>
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Current Status -->
    @if(!empty($user['required_actions']))
        <div class="mt-6 bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Current Required Actions</h2>
            </div>
            <div class="p-6">
                <div class="space-y-2">
                    @foreach($user['required_actions'] as $action)
                        <div class="flex items-center justify-between p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-sm font-medium text-gray-900">{{ str_replace('_', ' ', ucfirst(strtolower($action))) }}</span>
                            </div>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                Pending
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="mt-6 bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No Required Actions</h3>
                <p class="mt-1 text-sm text-gray-500">This user has no pending required actions.</p>
            </div>
        </div>
    @endif
</div>

<script>
function uncheckAll() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
}
</script>
@endsection
