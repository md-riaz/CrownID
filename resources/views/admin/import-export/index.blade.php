@extends('vendor.tyro-dashboard.layouts.admin')

@section('title', 'Import/Export - ' . $realm->name)

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
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="ml-1 text-gray-500 md:ml-2">Import/Export</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Import/Export Configuration</h1>
        <p class="mt-2 text-sm text-gray-600">Backup and restore realm configuration for: <span class="font-semibold">{{ $realm->name }}</span></p>
    </div>

    <!-- Flash Messages -->
    @include('vendor.tyro-dashboard.partials.flash-messages')

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Left Column: Export -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-xl font-semibold text-gray-900">Export Realm Configuration</h2>
                        <p class="text-sm text-gray-500 mt-1">Download realm data as JSON file</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <form id="exportForm" method="POST" action="{{ route('admin.import-export.export', $realm) }}">
                    @csrf
                    
                    <!-- Realm Selector (if multiple realms) -->
                    @if(isset($realms) && count($realms) > 1)
                    <div class="mb-6">
                        <label for="realm_id" class="block text-sm font-medium text-gray-700 mb-2">Select Realm</label>
                        <select id="realm_id" name="realm_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @foreach($realms as $realmOption)
                            <option value="{{ $realmOption->id }}" {{ $realmOption->id === $realm->id ? 'selected' : '' }}>
                                {{ $realmOption->display_name ?? $realmOption->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Export Options -->
                    <div class="space-y-4 mb-6">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="include_users" name="include_users" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" checked>
                            </div>
                            <div class="ml-3">
                                <label for="include_users" class="font-medium text-gray-700">Include Users</label>
                                <p class="text-sm text-gray-500">Include user accounts and credentials</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="include_credentials" name="include_credentials" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            </div>
                            <div class="ml-3">
                                <label for="include_credentials" class="font-medium text-gray-700">Include Client Secrets</label>
                                <p class="text-sm text-gray-500">Include OAuth client secrets</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="pretty_print" name="pretty_print" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" checked>
                            </div>
                            <div class="ml-3">
                                <label for="pretty_print" class="font-medium text-gray-700">Pretty Print JSON</label>
                                <p class="text-sm text-gray-500">Format JSON for readability</p>
                            </div>
                        </div>
                    </div>

                    <!-- Export Info -->
                    <div class="bg-indigo-50 rounded-lg p-4 mb-6">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-indigo-700 font-medium">Estimated Size:</span>
                            <span class="text-indigo-900" id="estimatedSize">~500 KB</span>
                        </div>
                        @if(isset($lastExport))
                        <div class="flex items-center justify-between text-sm mt-2">
                            <span class="text-indigo-700 font-medium">Last Export:</span>
                            <span class="text-indigo-900">{{ $lastExport }}</span>
                        </div>
                        @endif
                    </div>

                    <!-- Export Progress -->
                    <div id="exportProgress" class="hidden mb-6">
                        <div class="mb-2 flex justify-between items-center">
                            <span class="text-sm font-medium text-indigo-700">Exporting...</span>
                            <span class="text-sm text-indigo-600" id="exportPercentage">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div id="exportProgressBar" class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex space-x-3">
                        <button type="button" onclick="previewExport()" class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-indigo-600 rounded-md shadow-sm text-sm font-medium text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Preview
                        </button>
                        <button type="button" onclick="downloadExport()" class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Download
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column: Import -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-xl font-semibold text-gray-900">Import Realm Configuration</h2>
                        <p class="text-sm text-gray-500 mt-1">Upload and restore realm data from JSON</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <form id="importForm" method="POST" action="{{ route('admin.import-export.import', $realm) }}" enctype="multipart/form-data">
                    @csrf

                    <!-- File Upload Area -->
                    <div class="mb-6">
                        <div id="dropZone" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-indigo-400 transition-colors cursor-pointer">
                            <div id="dropZoneContent">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="mt-2 text-sm text-gray-600">
                                    <span class="font-semibold text-indigo-600">Click to upload</span> or drag and drop
                                </p>
                                <p class="text-xs text-gray-500 mt-1">JSON files only</p>
                            </div>
                            <div id="fileInfo" class="hidden">
                                <svg class="mx-auto h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="mt-2 text-sm font-medium text-gray-900" id="fileName"></p>
                                <p class="text-xs text-gray-500" id="fileSize"></p>
                                <button type="button" onclick="clearFile()" class="mt-2 text-xs text-red-600 hover:text-red-800">Remove</button>
                            </div>
                        </div>
                        <input type="file" id="fileInput" name="import_file" accept=".json" class="hidden">
                    </div>

                    <!-- Validation Errors -->
                    <div id="validationErrors" class="hidden mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Validation Errors</h3>
                                <div id="validationErrorsList" class="mt-2 text-sm text-red-700"></div>
                            </div>
                        </div>
                    </div>

                    <!-- JSON Preview -->
                    <div id="jsonPreview" class="hidden mb-6">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-700">JSON Preview</label>
                            <button type="button" onclick="togglePreview()" class="text-xs text-indigo-600 hover:text-indigo-800">Collapse</button>
                        </div>
                        <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto max-h-64 overflow-y-auto">
                            <pre id="jsonContent" class="text-xs text-green-400 font-mono"></pre>
                        </div>
                    </div>

                    <!-- Import Options -->
                    <div class="mb-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">If Realm Exists</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="radio" name="realm_exists" value="skip" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" checked>
                                    <span class="ml-2 text-sm text-gray-700">Skip import</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="realm_exists" value="overwrite" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700">Overwrite existing</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="realm_exists" value="fail" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700">Fail on conflict</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">User Handling</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="radio" name="user_handling" value="skip" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" checked>
                                    <span class="ml-2 text-sm text-gray-700">Skip existing users</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="user_handling" value="update" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700">Update existing users</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="user_handling" value="fail" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700">Fail on conflict</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Warning -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Warning</h3>
                                <p class="mt-1 text-sm text-yellow-700">Importing will modify or replace existing data. Make sure to backup before proceeding.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Import Progress -->
                    <div id="importProgress" class="hidden mb-6">
                        <div class="mb-2 flex justify-between items-center">
                            <span class="text-sm font-medium text-indigo-700">Importing...</span>
                            <span class="text-sm text-indigo-600" id="importPercentage">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div id="importProgressBar" class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                        <p class="mt-2 text-xs text-gray-600" id="importStatus">Processing...</p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex space-x-3">
                        <button type="button" onclick="validateImport()" id="validateBtn" class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-indigo-600 rounded-md shadow-sm text-sm font-medium text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Validate
                        </button>
                        <button type="button" onclick="confirmImport()" id="importBtn" class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Export Preview Modal -->
<div id="exportPreviewModal" class="hidden fixed z-50 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeExportPreview()"></div>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Export Preview</h3>
                    <button type="button" onclick="closeExportPreview()" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto max-h-96 overflow-y-auto">
                    <pre id="exportPreviewContent" class="text-xs text-green-400 font-mono"></pre>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="downloadFromPreview()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Download
                </button>
                <button type="button" onclick="closeExportPreview()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Import Confirmation Modal -->
<div id="importConfirmModal" class="hidden fixed z-50 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeImportConfirm()"></div>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Confirm Import</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Are you sure you want to import this configuration? This action may modify or replace existing data.
                            </p>
                            <div class="mt-4 bg-gray-50 rounded-lg p-3">
                                <p class="text-xs font-medium text-gray-700">Import Summary:</p>
                                <ul id="importSummary" class="mt-2 text-xs text-gray-600 space-y-1"></ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="executeImport()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Confirm Import
                </button>
                <button type="button" onclick="closeImportConfirm()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// File Upload Handler
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const validateBtn = document.getElementById('validateBtn');
const importBtn = document.getElementById('importBtn');

dropZone.addEventListener('click', () => fileInput.click());

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-indigo-500', 'bg-indigo-50');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-indigo-500', 'bg-indigo-50');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-indigo-500', 'bg-indigo-50');
    const files = e.dataTransfer.files;
    if (files.length > 0 && files[0].type === 'application/json') {
        fileInput.files = files;
        handleFileSelect(files[0]);
    }
});

fileInput.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        handleFileSelect(e.target.files[0]);
    }
});

function handleFileSelect(file) {
    document.getElementById('dropZoneContent').classList.add('hidden');
    document.getElementById('fileInfo').classList.remove('hidden');
    document.getElementById('fileName').textContent = file.name;
    document.getElementById('fileSize').textContent = formatFileSize(file.size);
    validateBtn.disabled = false;
    importBtn.disabled = false;
    
    const reader = new FileReader();
    reader.onload = (e) => {
        try {
            const json = JSON.parse(e.target.result);
            document.getElementById('jsonContent').textContent = JSON.stringify(json, null, 2);
            document.getElementById('jsonPreview').classList.remove('hidden');
            document.getElementById('validationErrors').classList.add('hidden');
        } catch (error) {
            showValidationError('Invalid JSON file: ' + error.message);
        }
    };
    reader.readAsText(file);
}

function clearFile() {
    fileInput.value = '';
    document.getElementById('dropZoneContent').classList.remove('hidden');
    document.getElementById('fileInfo').classList.add('hidden');
    document.getElementById('jsonPreview').classList.add('hidden');
    document.getElementById('validationErrors').classList.add('hidden');
    validateBtn.disabled = true;
    importBtn.disabled = true;
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Export Functions
function previewExport() {
    const formData = new FormData(document.getElementById('exportForm'));
    showExportProgress(true);
    
    fetch('{{ route('admin.import-export.preview', $realm) }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        showExportProgress(false);
        document.getElementById('exportPreviewContent').textContent = JSON.stringify(data, null, 2);
        document.getElementById('exportPreviewModal').classList.remove('hidden');
    })
    .catch(error => {
        showExportProgress(false);
        alert('Error generating preview: ' + error.message);
    });
}

function downloadExport() {
    const form = document.getElementById('exportForm');
    showExportProgress(true);
    
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.blob())
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'realm-{{ $realm->name }}-' + new Date().toISOString().split('T')[0] + '.json';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        showExportProgress(false);
    })
    .catch(error => {
        showExportProgress(false);
        alert('Error downloading export: ' + error.message);
    });
}

function showExportProgress(show) {
    const progressDiv = document.getElementById('exportProgress');
    if (show) {
        progressDiv.classList.remove('hidden');
        animateProgress('exportProgressBar', 'exportPercentage');
    } else {
        progressDiv.classList.add('hidden');
        document.getElementById('exportProgressBar').style.width = '0%';
        document.getElementById('exportPercentage').textContent = '0%';
    }
}

function closeExportPreview() {
    document.getElementById('exportPreviewModal').classList.add('hidden');
}

function downloadFromPreview() {
    closeExportPreview();
    downloadExport();
}

// Import Functions
function validateImport() {
    const formData = new FormData(document.getElementById('importForm'));
    
    fetch('{{ route('admin.import-export.validate', $realm) }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.valid) {
            document.getElementById('validationErrors').classList.add('hidden');
            alert('Validation successful! You can now proceed with the import.');
        } else {
            showValidationError(data.errors.join('\n'));
        }
    })
    .catch(error => {
        showValidationError('Validation error: ' + error.message);
    });
}

function confirmImport() {
    const file = fileInput.files[0];
    if (!file) {
        alert('Please select a file to import');
        return;
    }
    
    const reader = new FileReader();
    reader.onload = (e) => {
        try {
            const json = JSON.parse(e.target.result);
            const summary = document.getElementById('importSummary');
            summary.innerHTML = '';
            
            if (json.realm) summary.innerHTML += `<li>• Realm: ${json.realm.name || 'Unknown'}</li>`;
            if (json.users) summary.innerHTML += `<li>• Users: ${json.users.length || 0}</li>`;
            if (json.clients) summary.innerHTML += `<li>• Clients: ${json.clients.length || 0}</li>`;
            if (json.roles) summary.innerHTML += `<li>• Roles: ${json.roles.length || 0}</li>`;
            if (json.groups) summary.innerHTML += `<li>• Groups: ${json.groups.length || 0}</li>`;
            
            document.getElementById('importConfirmModal').classList.remove('hidden');
        } catch (error) {
            alert('Invalid JSON file');
        }
    };
    reader.readAsText(file);
}

function closeImportConfirm() {
    document.getElementById('importConfirmModal').classList.add('hidden');
}

function executeImport() {
    closeImportConfirm();
    const form = document.getElementById('importForm');
    const formData = new FormData(form);
    
    showImportProgress(true);
    document.getElementById('importStatus').textContent = 'Uploading file...';
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        showImportProgress(false);
        if (data.success) {
            window.location.href = '{{ route('admin.realms.show', $realm) }}?imported=1';
        } else {
            showValidationError(data.message || 'Import failed');
        }
    })
    .catch(error => {
        showImportProgress(false);
        showValidationError('Import error: ' + error.message);
    });
}

function showImportProgress(show) {
    const progressDiv = document.getElementById('importProgress');
    if (show) {
        progressDiv.classList.remove('hidden');
        animateProgress('importProgressBar', 'importPercentage');
    } else {
        progressDiv.classList.add('hidden');
        document.getElementById('importProgressBar').style.width = '0%';
        document.getElementById('importPercentage').textContent = '0%';
    }
}

function showValidationError(message) {
    const errorDiv = document.getElementById('validationErrors');
    const errorList = document.getElementById('validationErrorsList');
    errorList.innerHTML = '<ul class="list-disc list-inside space-y-1">' + 
        message.split('\n').map(m => '<li>' + m + '</li>').join('') + 
        '</ul>';
    errorDiv.classList.remove('hidden');
}

function togglePreview() {
    const preview = document.getElementById('jsonPreview');
    preview.classList.toggle('hidden');
}

function animateProgress(barId, percentId) {
    let progress = 0;
    const interval = setInterval(() => {
        progress += Math.random() * 30;
        if (progress >= 90) {
            progress = 90;
            clearInterval(interval);
        }
        document.getElementById(barId).style.width = progress + '%';
        document.getElementById(percentId).textContent = Math.round(progress) + '%';
    }, 200);
}
</script>
@endpush
@endsection
