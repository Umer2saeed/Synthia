@cannot('manage roles')
    @php abort(403) @endphp
@endcannot

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Roles</h2>
            <a href="{{ route('admin.roles.create') }}"
               class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                + New Role
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
                    <th class="px-6 py-4">Role Name</th>
                    <th class="px-6 py-4">Permissions</th>
                    <th class="px-6 py-4">Users</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($roles as $role)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-gray-400">{{ $loop->iteration }}</td>

                        {{-- Role name badge --}}
                        <td class="px-6 py-4">
                            @php
                                $color = match($role->name) {
                                    'admin'  => 'bg-red-100 text-red-700',
                                    'editor' => 'bg-blue-100 text-blue-700',
                                    'author' => 'bg-green-100 text-green-700',
                                    'reader' => 'bg-gray-100 text-gray-600',
                                    default  => 'bg-indigo-100 text-indigo-700',
                                };
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $color }}">
                                    {{ ucfirst($role->name) }}
                                </span>

                            {{-- Protected badge for admin role --}}
                            @if($role->name === 'admin')
                                <span class="ml-1 text-xs text-gray-400">🔒 Protected</span>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-gray-600">
                            {{ $role->permissions_count }}
                            {{ Str::plural('permission', $role->permissions_count) }}
                        </td>

                        {{-- User count assigned to this role --}}
                        <td class="px-6 py-4 text-gray-600">
                            {{ $role->users()->count() }}
                            {{ Str::plural('user', $role->users()->count()) }}
                        </td>

                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('admin.roles.edit', $role) }}"
                               class="text-indigo-600 hover:underline text-xs font-medium">
                                Edit
                            </a>

                            @if($role->name === 'admin')
                                {{-- Admin role is permanently protected --}}
                                <span class="text-xs text-gray-300" title="The admin role cannot be deleted">
                                    🔒 Protected
                                </span>

                            @elseif($role->users()->count() > 0)
                                {{--
                                | Role has users assigned — show count so admin knows
                                | exactly how many users need reassigning before delete
                                | is possible.
                                --}}
                                <span class="text-xs text-gray-400"
                                      title="Reassign {{ $role->users()->count() }} {{ Str::plural('user', $role->users()->count()) }} before deleting">
                                    🚫 {{ $role->users()->count() }} {{ Str::plural('user', $role->users()->count()) }} assigned
                                </span>

                            @else
                                {{-- Safe to delete — role exists but has no users --}}
                                <form action="{{ route('admin.roles.destroy', $role) }}"
                                      method="POST" class="inline"
                                      onsubmit="return confirm('Delete role {{ $role->name }}? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-500 hover:underline text-xs font-medium">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-400">
                            No roles found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
