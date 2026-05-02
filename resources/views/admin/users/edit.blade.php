@cannot('manage users')
    @php abort(403) @endphp
@endcannot

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
            Edit User: {{ $user->name }}
        </h2>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ now()->format('l, d F Y') }}</p>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 space-y-6">

        @if(session('success'))
            <div class="px-4 py-3 bg-green-50 dark:bg-green-900
                        border border-green-200 dark:border-green-700
                        text-green-800 dark:text-green-300 text-sm rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800
                    shadow rounded-xl
                    border border-gray-200 dark:border-gray-700
                    p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200
                       border-b border-gray-100 dark:border-gray-700 pb-3 mb-5">
                Profile Information
            </h3>

            <form action="{{ route('admin.users.update', $user) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="space-y-5">
                @csrf
                @method('PUT')

                <div class="flex items-center gap-5">
                    <img src="{{ $user->avatar_url }}"
                         id="avatar-preview"
                         alt="Avatar"
                         class="w-16 h-16 rounded-full object-cover
                                border-2 border-indigo-100 dark:border-indigo-900">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Avatar
                        </label>
                        <input type="file" name="avatar" id="avatar-input"
                               accept="image/jpg,image/jpeg,image/png,image/webp"
                               class="w-full text-sm text-gray-500 dark:text-gray-400
                                      file:mr-3 file:py-1.5 file:px-3 file:rounded-lg
                                      file:border-0 file:text-sm
                                      file:bg-indigo-50 dark:file:bg-indigo-950
                                      file:text-indigo-700 dark:file:text-indigo-400
                                      hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900
                                      cursor-pointer">
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                            JPG, PNG, WEBP — max 2MB
                        </p>
                        @error('avatar')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="w-full border border-gray-300 dark:border-gray-600
                                  bg-white dark:bg-gray-900
                                  text-gray-800 dark:text-gray-200
                                  rounded-lg px-4 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400
                                  @error('name') border-red-400 @enderror">
                    @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Username
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2
                                     text-gray-400 dark:text-gray-500 text-sm">@</span>
                        <input type="text" name="username"
                               value="{{ old('username', $user->username) }}"
                               class="w-full border border-gray-300 dark:border-gray-600
                                      bg-white dark:bg-gray-900
                                      text-gray-800 dark:text-gray-200
                                      placeholder-gray-400 dark:placeholder-gray-500
                                      rounded-lg pl-7 pr-4 py-2 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-indigo-400
                                      @error('username') border-red-400 @enderror"
                               placeholder="letters, numbers, hyphens, underscores">
                    </div>
                    @error('username')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="w-full border border-gray-300 dark:border-gray-600
                                  bg-white dark:bg-gray-900
                                  text-gray-800 dark:text-gray-200
                                  rounded-lg px-4 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400
                                  @error('email') border-red-400 @enderror">
                    @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Bio
                        <span class="text-gray-400 dark:text-gray-500 font-normal text-xs">
                            (max 300 characters)
                        </span>
                    </label>
                    <textarea name="bio" rows="3" maxlength="300"
                              id="bio-textarea"
                              class="w-full border border-gray-300 dark:border-gray-600
                                     bg-white dark:bg-gray-900
                                     text-gray-800 dark:text-gray-200
                                     placeholder-gray-400 dark:placeholder-gray-500
                                     rounded-lg px-4 py-2 text-sm
                                     focus:outline-none focus:ring-2 focus:ring-indigo-400
                                     @error('bio') border-red-400 @enderror"
                              placeholder="A short description about this user...">{{ old('bio', $user->bio) }}</textarea>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 text-right">
                        <span id="bio-count">{{ strlen(old('bio', $user->bio ?? '')) }}</span>/300
                    </p>
                    @error('bio')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Status
                    </label>
                    <select name="status"
                            class="w-full border border-gray-300 dark:border-gray-600
                                   bg-white dark:bg-gray-900
                                   text-gray-800 dark:text-gray-200
                                   rounded-lg px-4 py-2 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="active"   {{ old('status', $user->status) === 'active'   ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Roles
                    </label>

                    @if($user->id === auth()->id())
                        <div class="px-4 py-3 bg-yellow-50 dark:bg-yellow-950
                                    border border-yellow-200 dark:border-yellow-800
                                    rounded-lg text-xs text-yellow-700 dark:text-yellow-400 mb-2">
                            ⚠ You are editing your own account. Your admin role cannot be removed.
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-2">
                        @foreach($roles as $role)
                            @php
                                $isOwnAdminRole = $user->id === auth()->id() && $role->name === 'admin';
                            @endphp
                            <label class="flex items-center gap-2 cursor-pointer
                                          border border-gray-100 dark:border-gray-700
                                          rounded-lg px-3 py-2
                                          hover:bg-gray-50 dark:hover:bg-gray-700
                                          transition
                                          {{ $isOwnAdminRole ? 'opacity-60 cursor-not-allowed' : '' }}">
                                <input type="checkbox"
                                       name="roles[]"
                                       value="{{ $role->name }}"
                                       {{ in_array($role->name, old('roles', $user->roles->pluck('name')->toArray())) ? 'checked' : '' }}
                                       {{ $isOwnAdminRole ? 'disabled' : '' }}
                                       class="rounded border-gray-300 dark:border-gray-600
                                              text-indigo-600 focus:ring-indigo-400">
                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ ucfirst($role->name) }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700
                                   text-white text-sm rounded-lg transition">
                        Save Changes
                    </button>
                    <a href="{{ route('admin.users.index') }}"
                       class="text-sm text-gray-500 dark:text-gray-400 hover:underline">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('avatar-input').addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => document.getElementById('avatar-preview').src = e.target.result;
                reader.readAsDataURL(file);
            }
        });
        const bioTextarea = document.getElementById('bio-textarea');
        const bioCount    = document.getElementById('bio-count');
        bioTextarea.addEventListener('input', function () {
            bioCount.textContent = this.value.length;
        });
    </script>
</x-app-layout>
