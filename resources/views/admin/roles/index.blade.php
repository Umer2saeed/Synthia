@cannot('manage roles')
    @php abort(403) @endphp
@endcannot

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Roles</h2>
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
            <a href="{{ route('admin.roles.create') }}"
               class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700
                      text-white text-sm rounded-lg transition">
                + New Role
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
                    <th class="px-6 py-4">Role Name</th>
                    <th class="px-6 py-4">Permissions</th>
                    <th class="px-6 py-4">Users</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($roles as $role)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="px-6 py-4 text-gray-400 dark:text-gray-500">
                            {{ $loop->iteration }}
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $color = match($role->name) {
                                    'admin'  => 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-400',
                                    'editor' => 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-400',
                                    'author' => 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400',
                                    'reader' => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400',
                                    default  => 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-400',
                                };
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $color }}">
                                    {{ ucfirst($role->name) }}
                                </span>
                            @if($role->name === 'admin')
                                <span class="ml-1 text-xs text-gray-400 dark:text-gray-500">
                                        🔒 Protected
                                    </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                            {{ $role->permissions_count }}
                            {{ Str::plural('permission', $role->permissions_count) }}
                        </td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                            {{ $role->users()->count() }}
                            {{ Str::plural('user', $role->users()->count()) }}
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('admin.roles.edit', $role) }}"
                               class="text-indigo-600 dark:text-indigo-400
                                          hover:underline text-xs font-medium">
                                Edit
                            </a>
                            @if($role->name === 'admin')
                                <span class="text-xs text-gray-300 dark:text-gray-600">
                                        🔒 Protected
                                    </span>
                            @elseif($role->users()->count() > 0)
                                <span class="text-xs text-gray-400 dark:text-gray-500">
                                        🚫 {{ $role->users()->count() }} {{ Str::plural('user', $role->users()->count()) }} assigned
                                    </span>
                            @else
                                <form action="{{ route('admin.roles.destroy', $role) }}"
                                      method="POST" class="inline"
                                      onsubmit="return confirm('Delete role {{ $role->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-500 dark:text-red-400
                                                       hover:underline text-xs font-medium">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5"
                            class="px-6 py-10 text-center text-gray-400 dark:text-gray-500">
                            No roles found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
