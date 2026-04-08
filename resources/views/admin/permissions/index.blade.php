@cannot('manage roles')
    @php abort(403) @endphp
@endcannot

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Permissions</h2>
            <a href="{{ route('admin.permissions.create') }}"
               class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                + New Permission
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4">

        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white shadow rounded-xl overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-6 py-4">#</th>
                    <th class="px-6 py-4">Permission Name</th>
                    <th class="px-6 py-4">Used by Roles</th>
                    <th class="px-6 py-4">Core</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
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
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-gray-400">{{ $loop->iteration }}</td>

                        <td class="px-6 py-4 font-medium text-gray-800">
                            {{ $permission->name }}
                        </td>

                        <td class="px-6 py-4 text-gray-500">
                            {{ $permission->roles_count }}
                            {{ Str::plural('role', $permission->roles_count) }}
                        </td>

                        {{-- Core badge --}}
                        <td class="px-6 py-4">
                            @if($isCore)
                                <span class="px-2 py-0.5 bg-orange-100 text-orange-700
                                                 text-xs rounded-full font-medium">
                                        Core
                                    </span>
                            @else
                                <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-right space-x-2">
                            {{--
                            | Edit and Delete are disabled for core permissions.
                            | Renaming or deleting them would silently break the app.
                            --}}
                            @if(!$isCore)
                                <a href="{{ route('admin.permissions.edit', $permission) }}"
                                   class="text-indigo-600 hover:underline text-xs font-medium">
                                    Edit
                                </a>

                                <form action="{{ route('admin.permissions.destroy', $permission) }}"
                                      method="POST" class="inline"
                                      onsubmit="return confirm('Delete this permission?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-500 hover:underline text-xs font-medium">
                                        Delete
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-gray-300">Protected</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-400">
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
