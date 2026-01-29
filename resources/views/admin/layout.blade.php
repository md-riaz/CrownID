<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CrownID Admin')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
</head>
<body class="h-full">
    <div class="min-h-full">
        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-indigo-600 to-indigo-800 overflow-y-auto">
            <div class="flex items-center justify-center h-16 bg-indigo-900">
                <h1 class="text-white text-2xl font-bold">👑 CrownID</h1>
            </div>
            <nav class="mt-5 px-2">
                <a href="{{ route('admin.dashboard') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-white hover:bg-indigo-700 {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-700' : '' }}">
                    <i class="fas fa-dashboard mr-3"></i>
                    Dashboard
                </a>
                <a href="{{ route('admin.realms.index') }}" class="group flex items-center px-2 py-2 mt-1 text-sm font-medium rounded-md text-white hover:bg-indigo-700 {{ request()->routeIs('admin.realms.*') ? 'bg-indigo-700' : '' }}">
                    <i class="fas fa-globe mr-3"></i>
                    Realms
                </a>
                <a href="{{ route('admin.users.index') }}" class="group flex items-center px-2 py-2 mt-1 text-sm font-medium rounded-md text-white hover:bg-indigo-700 {{ request()->routeIs('admin.users.*') ? 'bg-indigo-700' : '' }}">
                    <i class="fas fa-users mr-3"></i>
                    Users
                </a>
                <a href="{{ route('admin.clients.index') }}" class="group flex items-center px-2 py-2 mt-1 text-sm font-medium rounded-md text-white hover:bg-indigo-700 {{ request()->routeIs('admin.clients.*') ? 'bg-indigo-700' : '' }}">
                    <i class="fas fa-key mr-3"></i>
                    Clients
                </a>
            </nav>
        </div>

        <!-- Main content -->
        <div class="pl-64 flex flex-col flex-1">
            <!-- Top bar -->
            <div class="sticky top-0 z-10 flex-shrink-0 flex h-16 bg-white shadow">
                <div class="flex-1 px-4 flex justify-between">
                    <div class="flex-1 flex items-center">
                        <h2 class="text-2xl font-bold text-gray-900">@yield('page-title', 'Dashboard')</h2>
                    </div>
                    <div class="ml-4 flex items-center md:ml-6">
                        <span class="text-gray-700 mr-4">Admin User</span>
                    </div>
                </div>
            </div>

            <!-- Page content -->
            <main class="flex-1 p-6">
                @if(session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
