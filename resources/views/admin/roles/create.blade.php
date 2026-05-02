@cannot('manage roles')
    @php abort(403) @endphp
@endcannot

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Create Role</h2>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ now()->format('l, d F Y') }}</p>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4">
        <div class="bg-white dark:bg-gray-800
                    shadow rounded-xl
                    border border-gray-200 dark:border-gray-700
                    p-6">
            <form action="{{ route('admin.roles.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Role Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="w-full border border-gray-300 dark:border-gray-600
                                  bg-white dark:bg-gray-900
                                  text-gray-800 dark:text-gray-200
                                  placeholder-gray-400 dark:placeholder-gray-500
                                  rounded-lg px-4 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400
                                  @error('name') border-red-400 @enderror"
                           placeholder="e.g. moderator">
                    @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Assign Permissions
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-80 overflow-y-auto
                                border border-gray-200 dark:border-gray-700
                                bg-white dark:bg-gray-900
                                rounded-lg p-4">
                        @foreach($permissions as $permission)
                            <label class="flex items-center gap-2 cursor-pointer
                                          hover:bg-gray-50 dark:hover:bg-gray-800
                                          px-2 py-1 rounded">
                                <input type="checkbox"
                                       name="permissions[]"
                                       value="{{ $permission->id }}"
                                       {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}
                                       class="rounded border-gray-300 dark:border-gray-600
                                              text-indigo-600 focus:ring-indigo-400">
                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ $permission->name }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700
                                   text-white text-sm rounded-lg transition">
                        Create Role
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
