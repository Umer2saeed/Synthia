@cannot('manage roles')
    @php abort(403) @endphp
@endcannot

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
            Manage Roles — {{ $user->name }}
        </h2>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ now()->format('l, d F Y') }}</p>
    </x-slot>

    <div class="py-8 max-w-xl mx-auto px-4">
        <div class="bg-white dark:bg-gray-800
                    shadow rounded-xl
                    border border-gray-200 dark:border-gray-700
                    p-6">

            <div class="mb-5 p-4 bg-gray-50 dark:bg-gray-900
                        border border-gray-100 dark:border-gray-700
                        rounded-lg">
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    <span class="font-medium">Name:</span> {{ $user->name }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    <span class="font-medium">Email:</span> {{ $user->email }}
                </p>
            </div>

            @if(session('success'))
                <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900
                            border border-green-200 dark:border-green-700
                            text-green-800 dark:text-green-300 text-sm rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('admin.users.roles.update', $user) }}"
                  method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Assign Roles
                    </label>
                    <div class="space-y-2">
                        @foreach($roles as $role)
                            @php
                                $color = match($role->name) {
                                    'admin'  => 'text-red-600 dark:text-red-400',
                                    'editor' => 'text-blue-600 dark:text-blue-400',
                                    'author' => 'text-green-600 dark:text-green-400',
                                    default  => 'text-gray-700 dark:text-gray-300',
                                };
                            @endphp
                            <label class="flex items-center gap-3 cursor-pointer
                                          border border-gray-100 dark:border-gray-700
                                          rounded-lg px-4 py-3
                                          hover:bg-gray-50 dark:hover:bg-gray-700
                                          transition">
                                <input type="checkbox"
                                       name="roles[]"
                                       value="{{ $role->id }}"
                                       {{ in_array($role->id, $userRoles) ? 'checked' : '' }}
                                       class="rounded border-gray-300 dark:border-gray-600
                                              text-indigo-600 focus:ring-indigo-400">
                                <div>
                                    <span class="text-sm font-medium {{ $color }}">
                                        {{ ucfirst($role->name) }}
                                    </span>
                                    <span class="text-xs text-gray-400 dark:text-gray-500 ml-2">
                                        {{ $role->permissions_count ?? $role->permissions->count() }} permissions
                                    </span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700
                                   text-white text-sm rounded-lg transition">
                        Save Roles
                    </button>
                    <a href="{{ route('admin.roles.index') }}"
                       class="text-sm text-gray-500 dark:text-gray-400 hover:underline">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
