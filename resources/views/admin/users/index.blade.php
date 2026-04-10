{{-- Safety net --}}
@cannot('manage users')
    @php abort(403) @endphp
@endcannot

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Dashboard</h2>
                <p class="text-xs text-gray-400 mt-0.5">{{ now()->format('l, d F Y') }} -
                    <span class="text-xs text-gray-400 bg-gray-100 px-3 py-1 rounded-full">Admin access only</span>
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4">

        {{-- Flash Messages --}}
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

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.users.index') }}"
              class="mb-5 flex flex-wrap gap-3 items-center">

            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search name, email, username..."
                   class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 w-72">

            <select name="role"
                    class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">All Roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}"
                        {{ request('role') === $role->name ? 'selected' : '' }}>
                        {{ ucfirst($role->name) }}
                    </option>
                @endforeach
            </select>

            <select name="status"
                    class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">All Statuses</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>

            <button type="submit"
                    class="px-4 py-2 bg-gray-700 text-white text-sm rounded-lg hover:bg-gray-800 transition">
                Filter
            </button>

            @if(request('search') || request('role') || request('status'))
                <a href="{{ route('admin.users.index') }}"
                   class="text-sm text-red-500 hover:underline">Clear</a>
            @endif
        </form>

        {{-- Users Table --}}
        <div class="bg-white shadow rounded-xl overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-5 py-4">User</th>
                    <th class="px-5 py-4">Username</th>
                    <th class="px-5 py-4">Role</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4">Posts</th>
                    <th class="px-5 py-4">Last Login</th>
                    <th class="px-5 py-4">Joined</th>
                    <th class="px-5 py-4 text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50 transition align-middle">

                        {{-- Avatar + Name + Email --}}
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <img src="{{ $user->avatar_url }}"
                                     alt="{{ $user->name }}"
                                     class="w-9 h-9 rounded-full object-cover border border-gray-200 shrink-0">
                                <div>
                                    <div class="font-medium text-gray-800">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-400">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- Username --}}
                        <td class="px-5 py-3 text-gray-500 font-mono text-xs">
                            {{ $user->username ? '@'.$user->username : '—' }}
                        </td>

                        {{-- Roles --}}
                        <td class="px-5 py-3">
                            @forelse($user->roles as $role)
                                @php
                                    $color = match($role->name) {
                                        'admin'  => 'bg-red-100 text-red-700',
                                        'editor' => 'bg-blue-100 text-blue-700',
                                        'author' => 'bg-green-100 text-green-700',
                                        default  => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                        {{ ucfirst($role->name) }}
                                    </span>
                            @empty
                                <span class="text-xs text-gray-400">No role</span>
                            @endforelse
                        </td>

                        {{-- Status Toggle --}}
                        <td class="px-5 py-3">
                            {{--
                            | Toggle button disabled for:
                            |   1. Yourself — cannot deactivate yourself
                            |   2. Other admins — cannot deactivate other admins
                            --}}
                            @php
                                $canToggle = $user->id !== auth()->id()
                                             && !$user->hasRole('admin');
                            @endphp

                            @if($canToggle)
                                <form action="{{ route('admin.users.toggle-status', $user) }}"
                                      method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="px-2 py-1 rounded-full text-xs font-medium cursor-pointer transition
                                                    {{ $user->status === 'active'
                                                        ? 'bg-green-100 text-green-700 hover:bg-green-200'
                                                        : 'bg-red-100 text-red-600 hover:bg-red-200' }}">
                                        {{ ucfirst($user->status) }}
                                    </button>
                                </form>
                            @else
                                {{-- Read-only badge for self or other admins --}}
                                <span class="px-2 py-1 rounded-full text-xs font-medium
                                        {{ $user->status === 'active'
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-red-100 text-red-600' }}">
                                        {{ ucfirst($user->status) }}
                                    </span>
                            @endif
                        </td>

                        {{-- Post Count --}}
                        <td class="px-5 py-3 text-gray-600">
                            {{ $user->posts_count }}
                        </td>

                        {{-- Last Login --}}
                        <td class="px-5 py-3 text-gray-400 text-xs">
                            {{ $user->last_login_at
                                ? $user->last_login_at->diffForHumans()
                                : 'Never' }}
                        </td>

                        {{-- Joined --}}
                        <td class="px-5 py-3 text-gray-400 text-xs">
                            {{ $user->created_at->format('d M Y') }}
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-3 text-right space-x-2 whitespace-nowrap">

                            <a href="{{ route('admin.users.show', $user) }}"
                               class="text-gray-500 hover:text-gray-800 text-xs font-medium">
                                View
                            </a>

                            <a href="{{ route('admin.users.edit', $user) }}"
                               class="text-indigo-600 hover:underline text-xs font-medium">
                                Edit
                            </a>

                            <a href="{{ route('admin.users.roles.edit', $user) }}"
                               class="text-purple-600 hover:underline text-xs font-medium">
                                Roles
                            </a>

                            {{--
                            | Delete: disabled for yourself and other admins.
                            --}}
                            @if($user->id !== auth()->id() && !$user->hasRole('admin'))
                                <form action="{{ route('admin.users.destroy', $user) }}"
                                      method="POST" class="inline"
                                      onsubmit="return confirm('Permanently delete {{ addslashes($user->name) }}? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-500 hover:underline text-xs font-medium">
                                        Delete
                                    </button>
                                </form>
                            @else
                                {{-- Show greyed out placeholder so the table stays aligned --}}
                                <span class="text-xs text-gray-300">
                                        {{ $user->id === auth()->id() ? 'You' : 'Admin' }}
                                    </span>
                            @endif

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8"
                            class="px-6 py-12 text-center text-gray-400">
                            No users found matching your filters.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>

    </div>
</x-app-layout>
