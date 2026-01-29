@extends('admin.realm-settings.index')

@section('tab-content')
<div class="p-6">
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900">Security Settings</h2>
        <p class="mt-1 text-sm text-gray-600">Configure security measures and brute force protection for this realm.</p>
    </div>

    <form action="{{ route('admin.realms.settings.security.update', $realm) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- Brute Force Detection Section -->
        <div>
            <div class="border-b border-gray-200 pb-3 mb-6">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    Brute Force Detection
                </h3>
                <p class="mt-1 text-sm text-gray-600">Protect user accounts from password guessing attacks.</p>
            </div>

            <!-- Brute Force Enabled Toggle -->
            <div class="mb-6">
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" 
                               name="brute_force_enabled" 
                               id="brute_force_enabled" 
                               value="1"
                               {{ old('brute_force_enabled', $realm->brute_force_enabled ?? false) ? 'checked' : '' }}
                               class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    </div>
                    <div class="ml-3">
                        <label for="brute_force_enabled" class="font-medium text-gray-900 text-base">Enable Brute Force Protection</label>
                        <p class="text-sm text-gray-500">When enabled, accounts will be temporarily locked after multiple failed login attempts.</p>
                    </div>
                </div>
            </div>

            <div id="brute-force-settings" class="grid grid-cols-1 md:grid-cols-2 gap-6 ml-8 pl-4 border-l-2 border-gray-200">
                <!-- Max Login Failures -->
                <div>
                    <label for="max_login_failures" class="block text-sm font-medium text-gray-700 mb-2">
                        <span class="flex items-center">
                            Maximum Login Failures
                            <svg class="w-4 h-4 ml-1 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </span>
                    </label>
                    <input type="number" 
                           name="max_login_failures" 
                           id="max_login_failures" 
                           value="{{ old('max_login_failures', $realm->max_login_failures ?? 3) }}"
                           min="1"
                           max="100"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('max_login_failures') border-red-300 @enderror">
                    @error('max_login_failures')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Number of failed login attempts before account is locked. Default: 3</p>
                </div>

                <!-- Wait Increment -->
                <div>
                    <label for="wait_increment" class="block text-sm font-medium text-gray-700 mb-2">
                        <span class="flex items-center">
                            Wait Increment
                            <svg class="w-4 h-4 ml-1 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </span>
                    </label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <input type="number" 
                               name="wait_increment" 
                               id="wait_increment" 
                               value="{{ old('wait_increment', $realm->wait_increment ?? 60) }}"
                               min="1"
                               class="flex-1 block w-full rounded-l-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('wait_increment') border-red-300 @enderror">
                        <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                            seconds
                        </span>
                    </div>
                    @error('wait_increment')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Time added to lockout after each failed attempt. Default: 60 seconds</p>
                </div>

                <!-- Max Wait -->
                <div>
                    <label for="max_wait" class="block text-sm font-medium text-gray-700 mb-2">
                        <span class="flex items-center">
                            Maximum Wait Time
                            <svg class="w-4 h-4 ml-1 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </span>
                    </label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <input type="number" 
                               name="max_wait" 
                               id="max_wait" 
                               value="{{ old('max_wait', $realm->max_wait ?? 900) }}"
                               min="1"
                               class="flex-1 block w-full rounded-l-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('max_wait') border-red-300 @enderror">
                        <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                            seconds
                        </span>
                    </div>
                    @error('max_wait')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Maximum lockout duration between attempts. Default: 900 seconds (15 minutes)</p>
                </div>

                <!-- Failure Reset Time -->
                <div>
                    <label for="failure_reset_time" class="block text-sm font-medium text-gray-700 mb-2">
                        <span class="flex items-center">
                            Failure Reset Time
                            <svg class="w-4 h-4 ml-1 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </span>
                    </label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <input type="number" 
                               name="failure_reset_time" 
                               id="failure_reset_time" 
                               value="{{ old('failure_reset_time', $realm->failure_reset_time ?? 43200) }}"
                               min="1"
                               class="flex-1 block w-full rounded-l-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('failure_reset_time') border-red-300 @enderror">
                        <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                            seconds
                        </span>
                    </div>
                    @error('failure_reset_time')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Time after which failed login count is reset. Default: 43200 seconds (12 hours)</p>
                </div>

                <!-- Lockout Duration -->
                <div class="md:col-span-2">
                    <label for="lockout_duration" class="block text-sm font-medium text-gray-700 mb-2">
                        <span class="flex items-center">
                            Account Lockout Duration
                            <svg class="w-4 h-4 ml-1 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </span>
                    </label>
                    <div class="mt-1 flex rounded-md shadow-sm max-w-md">
                        <input type="number" 
                               name="lockout_duration" 
                               id="lockout_duration" 
                               value="{{ old('lockout_duration', $realm->lockout_duration ?? 30) }}"
                               min="1"
                               class="flex-1 block w-full rounded-l-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('lockout_duration') border-red-300 @enderror">
                        <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                            minutes
                        </span>
                    </div>
                    @error('lockout_duration')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">How long an account remains locked after reaching max failures. Default: 30 minutes</p>
                </div>
            </div>
        </div>

        <!-- Security Headers Section -->
        <div>
            <div class="border-b border-gray-200 pb-3 mb-6">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Security Headers
                </h3>
                <p class="mt-1 text-sm text-gray-600">Configure HTTP security headers sent with responses.</p>
            </div>

            <div class="space-y-6">
                <!-- X-Frame-Options -->
                <div>
                    <label for="x_frame_options" class="block text-sm font-medium text-gray-700 mb-2">
                        X-Frame-Options
                    </label>
                    <select name="x_frame_options" 
                            id="x_frame_options" 
                            class="mt-1 block w-full max-w-md rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('x_frame_options') border-red-300 @enderror">
                        <option value="DENY" {{ old('x_frame_options', $realm->x_frame_options ?? 'DENY') == 'DENY' ? 'selected' : '' }}>DENY</option>
                        <option value="SAMEORIGIN" {{ old('x_frame_options', $realm->x_frame_options ?? 'DENY') == 'SAMEORIGIN' ? 'selected' : '' }}>SAMEORIGIN</option>
                        <option value="ALLOW-FROM" {{ old('x_frame_options', $realm->x_frame_options ?? 'DENY') == 'ALLOW-FROM' ? 'selected' : '' }}>ALLOW-FROM</option>
                    </select>
                    @error('x_frame_options')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Controls whether the page can be embedded in frames. DENY prevents all framing.</p>
                </div>

                <!-- Content Security Policy -->
                <div>
                    <label for="content_security_policy" class="block text-sm font-medium text-gray-700 mb-2">
                        Content Security Policy
                    </label>
                    <textarea name="content_security_policy" 
                              id="content_security_policy" 
                              rows="3"
                              placeholder="default-src 'self'; script-src 'self' 'unsafe-inline';"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('content_security_policy') border-red-300 @enderror">{{ old('content_security_policy', $realm->content_security_policy ?? '') }}</textarea>
                    @error('content_security_policy')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Defines approved sources of content. Leave empty to use default policy.</p>
                </div>

                <!-- X-Content-Type-Options -->
                <div>
                    <label for="x_content_type_options" class="block text-sm font-medium text-gray-700 mb-2">
                        X-Content-Type-Options
                    </label>
                    <select name="x_content_type_options" 
                            id="x_content_type_options" 
                            class="mt-1 block w-full max-w-md rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('x_content_type_options') border-red-300 @enderror">
                        <option value="">None</option>
                        <option value="nosniff" {{ old('x_content_type_options', $realm->x_content_type_options ?? 'nosniff') == 'nosniff' ? 'selected' : '' }}>nosniff</option>
                    </select>
                    @error('x_content_type_options')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Prevents MIME type sniffing. Recommended to set to "nosniff".</p>
                </div>
            </div>
        </div>

        <!-- Warning Box -->
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>Security Warning:</strong> Changes to these settings affect all users in this realm. Test security configurations carefully before deploying to production. Overly restrictive brute force settings may lock out legitimate users.
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

<script>
    // Toggle brute force settings visibility
    document.getElementById('brute_force_enabled').addEventListener('change', function() {
        const settings = document.getElementById('brute-force-settings');
        if (this.checked) {
            settings.classList.remove('opacity-50');
            settings.querySelectorAll('input, select').forEach(el => el.disabled = false);
        } else {
            settings.classList.add('opacity-50');
            settings.querySelectorAll('input, select').forEach(el => el.disabled = true);
        }
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        const bruteForceEnabled = document.getElementById('brute_force_enabled');
        if (!bruteForceEnabled.checked) {
            const settings = document.getElementById('brute-force-settings');
            settings.classList.add('opacity-50');
            settings.querySelectorAll('input, select').forEach(el => el.disabled = true);
        }
    });
</script>
@endsection
