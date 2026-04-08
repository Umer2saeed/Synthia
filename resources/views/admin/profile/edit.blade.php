{{--<x-app-layout>--}}
{{--    <x-slot name="header">--}}
{{--        <h2 class="font-semibold text-xl text-gray-800 leading-tight">--}}
{{--            {{ __('Profile') }}--}}
{{--        </h2>--}}
{{--    </x-slot>--}}

{{--    <div class="py-12">--}}
{{--        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">--}}
{{--            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">--}}
{{--                <div class="max-w-xl">--}}
{{--                    @include('profile.partials.update-profile-information-form')--}}
{{--                </div>--}}
{{--            </div>--}}

{{--            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">--}}
{{--                <div class="max-w-xl">--}}
{{--                    @include('profile.partials.update-password-form')--}}
{{--                </div>--}}
{{--            </div>--}}

{{--            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">--}}
{{--                <div class="max-w-xl">--}}
{{--                    @include('profile.partials.delete-user-form')--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</x-app-layout>--}}


<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">My Profile</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 space-y-6">

        @if(session('success'))
            <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        {{-- =============================================
             SECTION 1: Profile Information
             ============================================= --}}
        <div class="bg-white shadow rounded-xl p-6">
            <h3 class="text-sm font-semibold text-gray-700 border-b pb-3 mb-5">Profile Information</h3>

            <form action="{{ route('admin.profile.update') }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="space-y-5">
                @csrf
                @method('PUT')

                {{-- Avatar --}}
                <div class="flex items-center gap-5">
                    <img src="{{ $user->avatar_url }}"
                         id="avatar-preview"
                         alt="Your Avatar"
                         class="w-16 h-16 rounded-full object-cover border-2 border-indigo-100 shrink-0">

                    <div class="flex-1 space-y-1">
                        <label class="block text-sm font-medium text-gray-700">Profile Photo</label>
                        <input type="file" name="avatar" id="avatar-input"
                               accept="image/jpg,image/jpeg,image/png,image/webp"
                               class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                        <p class="text-xs text-gray-400">JPG, PNG, WEBP — max 2MB</p>
                        @error('avatar')
                        <p class="text-red-500 text-xs">{{ $message }}</p>
                        @enderror

                        {{-- Remove avatar link (only if they have one) --}}
                        @if($user->avatar)
                            <form action="{{ route('admin.profile.avatar.remove') }}"
                                  method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-xs text-red-500 hover:underline"
                                        onclick="return confirm('Remove your avatar?')">
                                    Remove photo
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 @error('name') border-red-400 @enderror">
                    @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Username --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">@</span>
                        <input type="text" name="username"
                               value="{{ old('username', $user->username) }}"
                               class="w-full border border-gray-300 rounded-lg pl-7 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 @error('username') border-red-400 @enderror"
                               placeholder="your_username">
                    </div>
                    @error('username')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Bio --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
                    <textarea name="bio" rows="3" maxlength="300"
                              id="bio-textarea"
                              class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                              placeholder="Tell readers a little about yourself...">{{ old('bio', $user->bio) }}</textarea>
                    <p class="text-xs text-gray-400 mt-1 text-right">
                        <span id="bio-count">{{ strlen(old('bio', $user->bio ?? '')) }}</span>/300
                    </p>
                    @error('bio')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <button type="submit"
                            class="px-5 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                        Save Profile
                    </button>
                </div>
            </form>
        </div>

        {{-- =============================================
             SECTION 2: Change Password
             ============================================= --}}
        <div class="bg-white shadow rounded-xl p-6">
            <h3 class="text-sm font-semibold text-gray-700 border-b pb-3 mb-5">Change Password</h3>

            <form action="{{ route('admin.profile.password') }}"
                  method="POST"
                  class="space-y-5">
                @csrf
                @method('PUT')

                {{-- Current Password --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Current Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="current_password"
                           autocomplete="current-password"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 @error('current_password') border-red-400 @enderror">
                    @error('current_password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- New Password --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        New Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password"
                           autocomplete="new-password"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 @error('password') border-red-400 @enderror">
                    @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirm New Password --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Confirm New Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password_confirmation"
                           autocomplete="new-password"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>

                <div>
                    <button type="submit"
                            class="px-5 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-900 transition">
                        Update Password
                    </button>
                </div>
            </form>
        </div>

        {{-- =============================================
             SECTION 3: Account Info (read-only)
             ============================================= --}}
        <div class="bg-white shadow rounded-xl p-6">
            <h3 class="text-sm font-semibold text-gray-700 border-b pb-3 mb-4">Account Info</h3>

            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Email</span>
                    <span class="text-gray-800">{{ $user->email }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Role</span>
                    <span class="text-gray-800">
                        {{ $user->roles->pluck('name')->map('ucfirst')->join(', ') ?: 'No role assigned' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Member Since</span>
                    <span class="text-gray-800">{{ $user->created_at->format('d F Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Last Login</span>
                    <span class="text-gray-800">
                        {{ $user->last_login_at?->diffForHumans() ?? 'Not recorded' }}
                    </span>
                </div>
            </div>
        </div>

    </div>

    <script>
        // Live avatar preview
        document.getElementById('avatar-input').addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => document.getElementById('avatar-preview').src = e.target.result;
                reader.readAsDataURL(file);
            }
        });

        // Bio character counter
        const bioTextarea = document.getElementById('bio-textarea');
        const bioCount    = document.getElementById('bio-count');
        bioTextarea.addEventListener('input', function () {
            bioCount.textContent = this.value.length;
        });
    </script>
</x-app-layout>
