@extends('vendor.tyro-dashboard.layouts.admin')

@section('title', 'Event Details - ' . $realm->name)

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Breadcrumbs -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('admin.dashboard') }}" class="text-gray-700 hover:text-indigo-600 inline-flex items-center">
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
                    <a href="{{ route('admin.realms.index') }}" class="ml-1 text-gray-700 hover:text-indigo-600 md:ml-2">Realms</a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <a href="{{ route('admin.realms.show', $realm) }}" class="ml-1 text-gray-700 hover:text-indigo-600 md:ml-2">{{ $realm->name }}</a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <a href="{{ route('admin.events.index', $realm) }}" class="ml-1 text-gray-700 hover:text-indigo-600 md:ml-2">Events</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="ml-1 text-gray-500 md:ml-2">Event #{{ $event->id }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Event Details</h1>
                <p class="mt-2 text-sm text-gray-600">Detailed view of audit event #{{ $event->id }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.events.index', $realm) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Events
                </a>
                <button type="button" onclick="copyToClipboard()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    Copy JSON
                </button>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @include('vendor.tyro-dashboard.partials.flash-messages')

    <!-- Event Summary -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">Event Summary</h2>
        </div>
        <div class="px-6 py-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Event Type -->
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500 mb-1">Event Type</dt>
                    <dd class="text-sm text-gray-900">
                        @php
                            $eventClass = 'bg-blue-100 text-blue-800';
                            if (str_contains(strtolower($event->type), 'error') || str_contains(strtolower($event->type), 'fail')) {
                                $eventClass = 'bg-red-100 text-red-800';
                            } elseif (str_contains(strtolower($event->type), 'success') || $event->type === 'LOGIN' || $event->type === 'REGISTER') {
                                $eventClass = 'bg-green-100 text-green-800';
                            }
                        @endphp
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $eventClass }}">
                            {{ $event->type }}
                        </span>
                    </dd>
                </div>

                <!-- Timestamp -->
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500 mb-1">Timestamp</dt>
                    <dd class="text-sm text-gray-900">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ $event->created_at->format('Y-m-d H:i:s') }}
                        </div>
                        <span class="text-xs text-gray-500 ml-7">{{ $event->created_at->diffForHumans() }}</span>
                    </dd>
                </div>

                <!-- Event ID -->
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500 mb-1">Event ID</dt>
                    <dd class="text-sm text-gray-900 font-mono">
                        {{ $event->id }}
                    </dd>
                </div>

                <!-- User -->
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500 mb-1">User</dt>
                    <dd class="text-sm text-gray-900">
                        @if($event->user_id)
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                @if($event->user)
                                    <a href="{{ route('admin.users.show', ['realm' => $realm, 'user' => $event->user]) }}" class="text-indigo-600 hover:text-indigo-900">
                                        {{ $event->user->username ?? $event->user->email }}
                                    </a>
                                @else
                                    <span>User #{{ $event->user_id }}</span>
                                @endif
                            </div>
                        @else
                            <span class="text-gray-500">Anonymous</span>
                        @endif
                    </dd>
                </div>

                <!-- IP Address -->
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500 mb-1">IP Address</dt>
                    <dd class="text-sm text-gray-900 font-mono">
                        {{ $event->ip_address ?? 'N/A' }}
                    </dd>
                </div>

                <!-- Client ID -->
                @if($event->client_id)
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500 mb-1">Client ID</dt>
                    <dd class="text-sm text-gray-900 font-mono">
                        {{ $event->client_id }}
                    </dd>
                </div>
                @endif

                <!-- Resource Type (for admin events) -->
                @if($event->resource_type)
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500 mb-1">Resource Type</dt>
                    <dd class="text-sm text-gray-900">
                        <div class="flex items-center">
                            @php
                                $iconMap = [
                                    'REALM' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                                    'USER' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                                    'CLIENT' => 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z',
                                    'ROLE' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                                    'GROUP' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                                ];
                                $icon = $iconMap[$event->resource_type] ?? 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01';
                            @endphp
                            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"></path>
                            </svg>
                            <span class="font-semibold">{{ $event->resource_type }}</span>
                        </div>
                    </dd>
                </div>
                @endif

                <!-- Operation (for admin events) -->
                @if($event->operation)
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500 mb-1">Operation</dt>
                    <dd class="text-sm text-gray-900">
                        @php
                            $operationClass = 'bg-gray-100 text-gray-800';
                            if ($event->operation === 'CREATE') {
                                $operationClass = 'bg-blue-100 text-blue-800';
                            } elseif ($event->operation === 'UPDATE') {
                                $operationClass = 'bg-yellow-100 text-yellow-800';
                            } elseif ($event->operation === 'DELETE') {
                                $operationClass = 'bg-red-100 text-red-800';
                            }
                        @endphp
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $operationClass }}">
                            {{ $event->operation }}
                        </span>
                    </dd>
                </div>
                @endif

                <!-- Resource ID -->
                @if($event->resource_id)
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500 mb-1">Resource ID</dt>
                    <dd class="text-sm text-gray-900 font-mono">
                        {{ $event->resource_id }}
                    </dd>
                </div>
                @endif

                <!-- Session ID -->
                @if($event->session_id)
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500 mb-1">Session ID</dt>
                    <dd class="text-sm text-gray-900 font-mono">
                        {{ $event->session_id }}
                    </dd>
                </div>
                @endif

                <!-- User Agent -->
                @if($event->user_agent)
                <div class="sm:col-span-2 lg:col-span-3">
                    <dt class="text-sm font-medium text-gray-500 mb-1">User Agent</dt>
                    <dd class="text-sm text-gray-900 font-mono break-all">
                        {{ $event->user_agent }}
                    </dd>
                </div>
                @endif
            </dl>
        </div>
    </div>

    <!-- Event Data / Details -->
    @if($event->details)
    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Event Data</h2>
            <button type="button" onclick="copyEventData()" class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md shadow-sm text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                Copy
            </button>
        </div>
        <div class="px-6 py-6">
            <div class="bg-gray-900 rounded-lg p-4 overflow-auto">
                <pre id="eventData" class="text-sm text-green-400 font-mono">{{ json_encode($event->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
        </div>
    </div>
    @endif

    <!-- Full Event JSON -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Complete Event JSON</h2>
            <button type="button" onclick="copyFullJSON()" class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md shadow-sm text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                Copy
            </button>
        </div>
        <div class="px-6 py-6">
            <div class="bg-gray-900 rounded-lg p-4 overflow-auto max-h-96">
                <pre id="fullJSON" class="text-sm text-green-400 font-mono">{{ json_encode($event->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
        </div>
    </div>

    <!-- Additional Actions -->
    <div class="flex justify-between items-center">
        <a href="{{ route('admin.events.index', $realm) }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Events List
        </a>
        
        <div class="text-sm text-gray-500">
            Event recorded {{ $event->created_at->diffForHumans() }}
        </div>
    </div>
</div>

<script>
function copyToClipboard() {
    const jsonText = document.getElementById('fullJSON').textContent;
    navigator.clipboard.writeText(jsonText).then(() => {
        showNotification('JSON copied to clipboard!');
    }).catch(err => {
        console.error('Failed to copy: ', err);
        showNotification('Failed to copy to clipboard', 'error');
    });
}

function copyEventData() {
    const jsonText = document.getElementById('eventData').textContent;
    navigator.clipboard.writeText(jsonText).then(() => {
        showNotification('Event data copied to clipboard!');
    }).catch(err => {
        console.error('Failed to copy: ', err);
        showNotification('Failed to copy to clipboard', 'error');
    });
}

function copyFullJSON() {
    const jsonText = document.getElementById('fullJSON').textContent;
    navigator.clipboard.writeText(jsonText).then(() => {
        showNotification('Complete JSON copied to clipboard!');
    }).catch(err => {
        console.error('Failed to copy: ', err);
        showNotification('Failed to copy to clipboard', 'error');
    });
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    } text-white font-medium transform transition-all duration-300 ease-in-out`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}
</script>
@endsection
