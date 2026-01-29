@php
    $indent = $level * 2.5; // 2.5rem per level for indentation
    $hasChildren = isset($group->children) && count($group->children) > 0;
    $membersCount = $group->members_count ?? 0;
@endphp

<div class="group-item" x-data="{ open: isExpanded({{ $group->id }}) }">
    <div class="px-6 py-4 hover:bg-gray-50 flex items-center transition" style="padding-left: {{ $indent + 1.5 }}rem;">
        <!-- Expand/Collapse Icon -->
        @if($hasChildren)
            <button @click="toggleGroup({{ $group->id }}); open = !open" 
                    class="mr-2 text-gray-400 hover:text-gray-600 transition focus:outline-none"
                    aria-label="Toggle children">
                <svg class="w-5 h-5 transform transition-transform" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        @else
            <span class="w-5 mr-2"></span>
        @endif
        
        <!-- Folder Icon -->
        <svg class="w-5 h-5 mr-3 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
        </svg>
        
        <!-- Group Name -->
        <span class="flex-1 font-medium text-gray-900">{{ $group->name }}</span>
        
        <!-- Member Count Badge -->
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 mr-4">
            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
            </svg>
            {{ $membersCount }} {{ $membersCount === 1 ? 'member' : 'members' }}
        </span>
        
        <!-- Actions Dropdown -->
        <div class="relative" x-data="{ dropdownOpen: false }">
            <button @click="dropdownOpen = !dropdownOpen" 
                    class="text-gray-400 hover:text-gray-600 p-2 rounded-lg hover:bg-gray-100 transition focus:outline-none"
                    aria-label="Actions">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                </svg>
            </button>
            <div x-show="dropdownOpen" 
                 @click.away="dropdownOpen = false"
                 x-transition
                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-10">
                <a href="{{ route('admin.groups.edit', $group) }}" 
                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit
                </a>
                <a href="{{ route('admin.groups.create', ['parent_id' => $group->id]) }}" 
                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Create Subgroup
                </a>
                <form action="{{ route('admin.groups.destroy', $group) }}" 
                      method="POST" 
                      onsubmit="return confirm('Are you sure you want to delete this group? This action cannot be undone.');" 
                      class="block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 transition">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Render Children -->
    @if($hasChildren)
        <div x-show="open" x-transition>
            @foreach($group->children as $child)
                @include('admin.groups.partials.tree-item', ['group' => $child, 'level' => $level + 1])
            @endforeach
        </div>
    @endif
</div>
