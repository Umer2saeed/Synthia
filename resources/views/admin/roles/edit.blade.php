@cannot('manage roles')
    @php abort(403) @endphp
@endcannot

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            Edit Role: {{ ucfirst($role->name) }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4">

        @if(session('error'))
            <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white shadow rounded-xl p-6">
            <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Role Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role Name</label>

                    @if($role->name === 'admin')
                        {{--
                        | Admin role name is locked — cannot be renamed.
                        | Show it as read-only with explanation.
                        --}}
                        <input type="hidden" name="name" value="admin">
                        <div class="flex items-center gap-2 px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg">
                            <span class="text-sm text-gray-700 font-medium">admin</span>
                            <span class="text-xs text-gray-400">🔒 This role name cannot be changed</span>
                        </div>
                    @else
                        <input type="text" name="name"
                               value="{{ old('name', $role->name) }}"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-indigo-400
                                      @error('name') border-red-400 @enderror">
                        @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    @endif
                </div>

                {{-- Permissions Grid --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-medium text-gray-700">
                            Permissions
                        </label>
                        <button type="button" id="toggle-all"
                                class="text-xs text-indigo-600 hover:underline">
                            Select All
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-80 overflow-y-auto
                                border border-gray-200 rounded-lg p-4">
                        @foreach($permissions as $permission)
                            <label class="flex items-center gap-2 cursor-pointer
                                          hover:bg-gray-50 px-2 py-1 rounded">
                                <input type="checkbox"
                                       name="permissions[]"
                                       value="{{ $permission->id }}"
                                       class="perm-checkbox rounded border-gray-300
                                              text-indigo-600 focus:ring-indigo-400"
                                    {{ in_array($permission->id, old('permissions', $rolePermissions))
                                        ? 'checked' : '' }}>
                                <span class="text-sm text-gray-700">
                                    {{ $permission->name }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="px-5 py-2 bg-indigo-600 text-white text-sm rounded-lg
                                   hover:bg-indigo-700 transition">
                        Update Role
                    </button>
                    <a href="{{ route('admin.roles.index') }}"
                       class="text-sm text-gray-500 hover:underline">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const toggleBtn  = document.getElementById('toggle-all');
        const checkboxes = document.querySelectorAll('.perm-checkbox');

        toggleBtn.addEventListener('click', function () {
            const allChecked = [...checkboxes].every(cb => cb.checked);
            checkboxes.forEach(cb => cb.checked = !allChecked);
            this.textContent = allChecked ? 'Select All' : 'Deselect All';
        });
    </script>

</x-app-layout>
