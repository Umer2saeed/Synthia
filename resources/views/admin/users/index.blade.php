@cannot('manage users')
    @php abort(403) @endphp
@endcannot

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Users</h2>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                    {{ now()->format('l, d F Y') }} -
                    <span class="text-xs text-gray-400 dark:text-gray-500
                                 bg-gray-100 dark:bg-gray-800
                                 px-3 py-1 rounded-full">
                        Admin access only
                    </span>
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4">

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

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.users.index') }}"
              class="mb-5 flex flex-wrap gap-3 items-center">

            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search name, email, username..."
                   class="border border-gray-300 dark:border-gray-600
                          bg-white dark:bg-gray-800
                          text-gray-800 dark:text-gray-200
                          placeholder-gray-400 dark:placeholder-gray-500
                          rounded-lg px-4 py-2 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-400 w-72">

            <select name="role"
                    class="border border-gray-300 dark:border-gray-600
                           bg-white dark:bg-gray-800
                           text-gray-800 dark:text-gray-200
                           rounded-lg px-4 py-2 text-sm
                           focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">All Roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}"
                        {{ request('role') === $role->name ? 'selected' : '' }}>
                        {{ ucfirst($role->name) }}
                    </option>
                @endforeach
            </select>

            <select name="status"
                    class="border border-gray-300 dark:border-gray-600
                           bg-white dark:bg-gray-800
                           text-gray-800 dark:text-gray-200
                           rounded-lg px-4 py-2 text-sm
                           focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">All Statuses</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>

            <button type="submit"
                    class="px-4 py-2 bg-gray-700 dark:bg-gray-600
                           hover:bg-gray-800 dark:hover:bg-gray-500
                           text-white text-sm rounded-lg transition">
                Filter
            </button>

            @if(request('search') || request('role') || request('status'))
                <a href="{{ route('admin.users.index') }}"
                   class="text-sm text-red-500 dark:text-red-400 hover:underline">Clear</a>
            @endif
        </form>

        {{-- Users Table --}}
        <div class="bg-white dark:bg-gray-800
                    shadow rounded-xl overflow-hidden
                    border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-900
                              text-gray-500 dark:text-gray-400
                              uppercase text-xs tracking-wider">
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
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition align-middle">

                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                                     class="w-9 h-9 rounded-full object-cover
                                                border border-gray-200 dark:border-gray-700 shrink-0">
                                <div>
                                    <div class="font-medium text-gray-800 dark:text-gray-200">
                                        {{ $user->name }}
                                    </div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">
                                        {{ $user->email }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="px-5 py-3 text-gray-500 dark:text-gray-400 font-mono text-xs">
                            {{ $user->username ? '@'.$user->username : '—' }}
                        </td>

                        <td class="px-5 py-3">
                            @forelse($user->roles as $role)
                                @php
                                    $color = match($role->name) {
                                        'admin'  => 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-400',
                                        'editor' => 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-400',
                                        'author' => 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400',
                                        default  => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                        {{ ucfirst($role->name) }}
                                    </span>
                            @empty
                                <span class="text-xs text-gray-400 dark:text-gray-500">No role</span>
                            @endforelse
                        </td>

                        <td class="px-5 py-3">
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
                                                           ? 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 hover:bg-green-200 dark:hover:bg-green-800'
                                                           : 'bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-800' }}">
                                        {{ ucfirst($user->status) }}
                                    </button>
                                </form>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs font-medium
                                                 {{ $user->status === 'active'
                                                     ? 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400'
                                                     : 'bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400' }}">
                                        {{ ucfirst($user->status) }}
                                    </span>
                            @endif
                        </td>

                        <td class="px-5 py-3 text-gray-600 dark:text-gray-400">
                            {{ $user->posts_count }}
                        </td>

                        <td class="px-5 py-3 text-gray-400 dark:text-gray-500 text-xs">
                            {{ $user->last_login_at
                                ? $user->last_login_at->diffForHumans()
                                : 'Never' }}
                        </td>

                        <td class="px-5 py-3 text-gray-400 dark:text-gray-500 text-xs">
                            {{ $user->created_at->format('d M Y') }}
                        </td>

                        <td class="px-5 py-3 text-right space-x-2 whitespace-nowrap">
                            <a href="{{ route('admin.users.show', $user) }}"
                               class="text-gray-500 dark:text-gray-400
                                          hover:text-gray-800 dark:hover:text-white
                                          text-xs font-medium">
                                View
                            </a>
                            <a href="{{ route('admin.users.edit', $user) }}"
                               class="text-indigo-600 dark:text-indigo-400
                                          hover:underline text-xs font-medium">
                                Edit
                            </a>
                            <a href="{{ route('admin.users.roles.edit', $user) }}"
                               class="text-purple-600 dark:text-purple-400
                                          hover:underline text-xs font-medium">
                                Roles
                            </a>
                            @if($user->id !== auth()->id() && !$user->hasRole('admin'))
                                <form action="{{ route('admin.users.destroy', $user) }}"
                                      method="POST" class="inline"
                                      onsubmit="return confirm('Permanently delete {{ addslashes($user->name) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-500 dark:text-red-400
                                                       hover:underline text-xs font-medium">
                                        Delete
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-gray-300 dark:text-gray-600">
                                        {{ $user->id === auth()->id() ? 'You' : 'Admin' }}
                                    </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8"
                            class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
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
