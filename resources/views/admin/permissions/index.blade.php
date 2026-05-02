@cannot('manage roles')
    @php abort(403) @endphp
@endcannot

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Permissions</h2>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ now()->format('l, d F Y') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4">

        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900
                        border border-green-200 dark:border-green-700
                        text-green-800 dark:text-green-300 text-sm rounded-lg">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-950
                        border border-red-200 dark:border-red-800
                        text-red-700 dark:text-red-400 text-sm rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <div class="flex justify-end mb-2">
            <a href="{{ route('admin.permissions.create') }}"
               class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700
                      text-white text-sm rounded-lg transition">
                + New Permission
            </a>
        </div>

        <div class="bg-white dark:bg-gray-800
                    shadow rounded-xl overflow-hidden
                    border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-900
                              text-gray-500 dark:text-gray-400
                              uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-6 py-4">#</th>
                    <th class="px-6 py-4">Permission Name</th>
                    <th class="px-6 py-4">Used by Roles</th>
                    <th class="px-6 py-4">Core</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @php
                    $corePermissions = [
                        'access admin panel', 'manage users', 'manage roles',
                        'manage categories', 'create posts', 'edit own posts',
                        'edit all posts', 'delete own posts', 'delete all posts',
                        'publish posts', 'view posts', 'view categories',
                        'view comments', 'create comments', 'delete comments',
                    ];
                @endphp
                @forelse($permissions as $permission)
                    @php $isCore = in_array($permission->name, $corePermissions); @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="px-6 py-4 text-gray-400 dark:text-gray-500">
                            {{ $loop->iteration }}
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-800 dark:text-gray-200">
                            {{ $permission->name }}
                        </td>
                        <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                            {{ $permission->roles_count }}
                            {{ Str::plural('role', $permission->roles_count) }}
                        </td>
                        <td class="px-6 py-4">
                            @if($isCore)
                                <span class="px-2 py-0.5
                                                 bg-orange-100 dark:bg-orange-950
                                                 text-orange-700 dark:text-orange-400
                                                 text-xs rounded-full font-medium">
                                        Core
                                    </span>
                            @else
                                <span class="text-gray-300 dark:text-gray-600 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            @if(!$isCore)
                                <a href="{{ route('admin.permissions.edit', $permission) }}"
                                   class="text-indigo-600 dark:text-indigo-400
                                              hover:underline text-xs font-medium">
                                    Edit
                                </a>
                                <form action="{{ route('admin.permissions.destroy', $permission) }}"
                                      method="POST" class="inline"
                                      onsubmit="return confirm('Delete this permission?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-500 dark:text-red-400
                                                       hover:underline text-xs font-medium">
                                        Delete
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-gray-300 dark:text-gray-600">Protected</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5"
                            class="px-6 py-10 text-center text-gray-400 dark:text-gray-500">
                            No permissions found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $permissions->links() }}
        </div>
    </div>
</x-app-layout>
