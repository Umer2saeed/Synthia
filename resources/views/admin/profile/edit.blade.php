<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">My Profile</h2>
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

        {{-- Profile Information --}}
        <div class="bg-white dark:bg-gray-800
                    shadow rounded-xl
                    border border-gray-200 dark:border-gray-700
                    p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200
                       border-b border-gray-100 dark:border-gray-700 pb-3 mb-5">
                Profile Information
            </h3>

            <form action="{{ route('admin.profile.update') }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="space-y-5">
                @csrf
                @method('PUT')

                <div class="flex items-center gap-5">
                    <img src="{{ $user->avatar_url }}"
                         id="avatar-preview"
                         alt="Your Avatar"
                         class="w-16 h-16 rounded-full object-cover
                                border-2 border-indigo-100 dark:border-indigo-900 shrink-0">

                    <div class="flex-1 space-y-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Profile Photo
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
                        <p class="text-xs text-gray-400 dark:text-gray-500">
                            JPG, PNG, WEBP — max 2MB
                        </p>
                        @error('avatar')
                        <p class="text-red-500 text-xs">{{ $message }}</p>
                        @enderror
                        @if($user->avatar)
                            <form action="{{ route('admin.profile.avatar.remove') }}"
                                  method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-xs text-red-500 dark:text-red-400 hover:underline"
                                        onclick="return confirm('Remove your avatar?')">
                                    Remove photo
                                </button>
                            </form>
                        @endif
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
                               placeholder="your_username">
                    </div>
                    @error('username')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Bio
                    </label>
                    <textarea name="bio" rows="3" maxlength="300"
                              id="bio-textarea"
                              class="w-full border border-gray-300 dark:border-gray-600
                                     bg-white dark:bg-gray-900
                                     text-gray-800 dark:text-gray-200
                                     placeholder-gray-400 dark:placeholder-gray-500
                                     rounded-lg px-4 py-2 text-sm
                                     focus:outline-none focus:ring-2 focus:ring-indigo-400"
                              placeholder="Tell readers a little about yourself...">{{ old('bio', $user->bio) }}</textarea>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 text-right">
                        <span id="bio-count">{{ strlen(old('bio', $user->bio ?? '')) }}</span>/300
                    </p>
                    @error('bio')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <button type="submit"
                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700
                                   text-white text-sm rounded-lg transition">
                        Save Profile
                    </button>
                </div>
            </form>
        </div>

        {{-- Change Password --}}
        <div class="bg-white dark:bg-gray-800
                    shadow rounded-xl
                    border border-gray-200 dark:border-gray-700
                    p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200
                       border-b border-gray-100 dark:border-gray-700 pb-3 mb-5">
                Change Password
            </h3>

            <form action="{{ route('admin.profile.password') }}"
                  method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Current Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="current_password"
                           autocomplete="current-password"
                           class="w-full border border-gray-300 dark:border-gray-600
                                  bg-white dark:bg-gray-900
                                  text-gray-800 dark:text-gray-200
                                  rounded-lg px-4 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400
                                  @error('current_password') border-red-400 @enderror">
                    @error('current_password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        New Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password"
                           autocomplete="new-password"
                           class="w-full border border-gray-300 dark:border-gray-600
                                  bg-white dark:bg-gray-900
                                  text-gray-800 dark:text-gray-200
                                  rounded-lg px-4 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400
                                  @error('password') border-red-400 @enderror">
                    @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Confirm New Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password_confirmation"
                           autocomplete="new-password"
                           class="w-full border border-gray-300 dark:border-gray-600
                                  bg-white dark:bg-gray-900
                                  text-gray-800 dark:text-gray-200
                                  rounded-lg px-4 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>

                <div>
                    <button type="submit"
                            class="px-5 py-2 bg-gray-800 dark:bg-gray-700
                                   hover:bg-gray-900 dark:hover:bg-gray-600
                                   text-white text-sm rounded-lg transition">
                        Update Password
                    </button>
                </div>
            </form>
        </div>

            {{-- =============================================
     TWO-FACTOR AUTHENTICATION
============================================= --}}
            <div class="bg-white dark:bg-gray-800
            shadow rounded-xl
            border border-gray-200 dark:border-gray-700
            p-6">

                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200
               border-b border-gray-100 dark:border-gray-700 pb-3 mb-5
               flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Two-Factor Authentication
                </h3>

                @if($user->hasTwoFactorEnabled())

                    {{-- 2FA is active --}}
                    <div class="flex items-center gap-3 mb-5 p-3
                    bg-green-50 dark:bg-green-950
                    border border-green-200 dark:border-green-800
                    rounded-xl">
                        <svg class="w-5 h-5 text-green-500 dark:text-green-400 shrink-0"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-green-700 dark:text-green-400">
                                2FA is enabled
                            </p>
                            <p class="text-xs text-green-600 dark:text-green-500">
                                Your account is protected with two-factor authentication.
                            </p>
                        </div>
                    </div>

                    <form action="{{ route('two-factor.disable') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Confirm your password to disable 2FA
                            </label>
                            <input type="password"
                                   name="password"
                                   class="w-full border border-gray-300 dark:border-gray-600
                              bg-white dark:bg-gray-900
                              text-gray-800 dark:text-gray-200
                              rounded-xl px-4 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-red-400
                              @error('password') border-red-400 @enderror"
                                   placeholder="Enter your current password">
                            @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit"
                                class="px-5 py-2 bg-red-600 hover:bg-red-700
                           text-white text-sm font-medium rounded-xl transition"
                                onclick="return confirm('Disable two-factor authentication? Your account will be less secure.')">
                            Disable 2FA
                        </button>
                    </form>

                @elseif($user->hasTwoFactorPending())

                    {{-- Setup started but not confirmed --}}
                    <p class="text-sm text-amber-600 dark:text-amber-400 mb-4">
                        ⚠ 2FA setup was started but not completed. Scan the QR code and verify your code.
                    </p>
                    <a href="{{ route('two-factor.setup') }}"
                       class="px-5 py-2 bg-amber-600 hover:bg-amber-700
                  text-white text-sm font-medium rounded-xl transition inline-block">
                        Continue Setup
                    </a>

                @else

                    {{-- 2FA not set up --}}
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
                        Two-factor authentication adds an extra layer of security. When enabled,
                        you will need your phone to log in.
                    </p>
                    <a href="{{ route('two-factor.setup') }}"
                       class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700
                  text-white text-sm font-medium rounded-xl transition inline-block">
                        Enable Two-Factor Authentication
                    </a>

                @endif

            </div>

        {{-- Account Info --}}
        <div class="bg-white dark:bg-gray-800
                    shadow rounded-xl
                    border border-gray-200 dark:border-gray-700
                    p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200
                       border-b border-gray-100 dark:border-gray-700 pb-3 mb-4">
                Account Info
            </h3>

            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Email</span>
                    <span class="text-gray-800 dark:text-gray-200">{{ $user->email }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Role</span>
                    <span class="text-gray-800 dark:text-gray-200">
                        {{ $user->roles->pluck('name')->map('ucfirst')->join(', ') ?: 'No role assigned' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Member Since</span>
                    <span class="text-gray-800 dark:text-gray-200">
                        {{ $user->created_at->format('d F Y') }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Last Login</span>
                    <span class="text-gray-800 dark:text-gray-200">
                        {{ $user->last_login_at?->diffForHumans() ?? 'Not recorded' }}
                    </span>
                </div>
            </div>
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
