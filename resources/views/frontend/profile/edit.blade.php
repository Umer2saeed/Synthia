@extends('frontend.layouts.app')

@section('content')

    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-12 space-y-6">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="px-4 py-3 bg-green-50 dark:bg-green-950 border border-green-200
                    dark:border-green-800 text-green-700 dark:text-green-400 text-sm rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="px-4 py-3 bg-red-50 dark:bg-red-950 border border-red-200
                    dark:border-red-800 text-red-700 dark:text-red-400 text-sm rounded-xl">
                {{ session('error') }}
            </div>
        @endif

        {{-- Page Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-display text-2xl font-bold text-gray-900 dark:text-white">
                    Edit Profile
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Update your public profile information.
                </p>
            </div>
            <a href="{{ route('frontend.profile.show') }}"
               class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700
                  dark:hover:text-gray-200 transition-colors">
                ← Back to Profile
            </a>
        </div>

        {{-- =============================================
             SECTION 1: Profile Information
             ============================================= --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100
                dark:border-gray-800 p-6">

            <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300
                   border-b border-gray-100 dark:border-gray-800 pb-3 mb-5">
                Profile Information
            </h2>

            <form action="{{ route('frontend.profile.update') }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="space-y-5">
                @csrf
                @method('PUT')

                {{-- Avatar --}}
                <div class="flex items-center gap-5">
                    <img src="{{ $user->avatar_url }}"
                         id="avatar-preview"
                         alt="{{ $user->name }}"
                         class="w-16 h-16 rounded-full object-cover border-2
                            border-indigo-100 dark:border-indigo-900 shrink-0">

                    <div class="flex-1 space-y-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Profile Photo
                        </label>
                        <input type="file"
                               name="avatar"
                               id="avatar-input"
                               accept="image/jpg,image/jpeg,image/png,image/webp"
                               class="w-full text-sm text-gray-500 dark:text-gray-400
                                  file:mr-3 file:py-1.5 file:px-3 file:rounded-lg
                                  file:border-0 file:text-sm file:font-medium
                                  file:bg-indigo-50 dark:file:bg-indigo-950
                                  file:text-indigo-700 dark:file:text-indigo-400
                                  hover:file:bg-indigo-100 cursor-pointer">
                        <p class="text-xs text-gray-400">JPG, PNG, WEBP — max 2MB</p>

                        @error('avatar')
                        <p class="text-red-500 text-xs">{{ $message }}</p>
                        @enderror

                        {{-- Remove avatar option --}}
                        @if($user->avatar)
                            <form action="{{ route('frontend.profile.avatar.remove') }}"
                                  method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-xs text-red-500 hover:text-red-700
                                           hover:underline transition-colors"
                                        onclick="return confirm('Remove your avatar?')">
                                    Remove photo
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Full Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="name"
                           value="{{ old('name', $user->name) }}"
                           class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5
                              text-sm bg-white dark:bg-gray-800
                              text-gray-700 dark:text-gray-300
                              focus:outline-none focus:ring-2 focus:ring-indigo-300
                              dark:focus:ring-indigo-700 transition
                              @error('name') border-red-400 @enderror">
                    @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Username --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Username
                        <span class="text-gray-400 font-normal text-xs">(optional)</span>
                    </label>
                    <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2
                                 text-gray-400 text-sm select-none">@</span>
                        <input type="text"
                               name="username"
                               value="{{ old('username', $user->username) }}"
                               class="w-full border border-gray-200 dark:border-gray-700 rounded-xl
                                  pl-7 pr-4 py-2.5 text-sm bg-white dark:bg-gray-800
                                  text-gray-700 dark:text-gray-300
                                  focus:outline-none focus:ring-2 focus:ring-indigo-300
                                  dark:focus:ring-indigo-700 transition
                                  @error('username') border-red-400 @enderror"
                               placeholder="your_username">
                    </div>
                    <p class="text-xs text-gray-400 mt-1">
                        Letters, numbers, hyphens and underscores only.
                    </p>
                    @error('username')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Bio --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Bio
                        <span class="text-gray-400 font-normal text-xs">(max 300 characters)</span>
                    </label>
                    <textarea name="bio"
                              id="bio-textarea"
                              rows="3"
                              maxlength="300"
                              class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5
                                 text-sm bg-white dark:bg-gray-800
                                 text-gray-700 dark:text-gray-300
                                 placeholder-gray-400 dark:placeholder-gray-600
                                 focus:outline-none focus:ring-2 focus:ring-indigo-300
                                 dark:focus:ring-indigo-700 resize-none transition"
                              placeholder="Tell readers a little about yourself...">{{ old('bio', $user->bio) }}</textarea>
                    <p class="text-xs text-gray-400 mt-1 text-right">
                        <span id="bio-count">{{ strlen(old('bio', $user->bio ?? '')) }}</span>/300
                    </p>
                    @error('bio')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white
                               text-sm font-medium rounded-xl transition-colors">
                        Save Profile
                    </button>
                </div>
            </form>
        </div>

        {{-- =============================================
             SECTION 2: Change Password
             ============================================= --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100
                dark:border-gray-800 p-6">

            <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300
                   border-b border-gray-100 dark:border-gray-800 pb-3 mb-5">
                Change Password
            </h2>

            <form action="{{ route('frontend.profile.password') }}"
                  method="POST"
                  class="space-y-5">
                @csrf
                @method('PUT')

                {{-- Current Password --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Current Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password"
                           name="current_password"
                           autocomplete="current-password"
                           class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5
                              text-sm bg-white dark:bg-gray-800
                              text-gray-700 dark:text-gray-300
                              focus:outline-none focus:ring-2 focus:ring-indigo-300
                              dark:focus:ring-indigo-700 transition
                              @error('current_password') border-red-400 @enderror">
                    @error('current_password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- New Password --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        New Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password"
                           name="password"
                           autocomplete="new-password"
                           class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5
                              text-sm bg-white dark:bg-gray-800
                              text-gray-700 dark:text-gray-300
                              focus:outline-none focus:ring-2 focus:ring-indigo-300
                              dark:focus:ring-indigo-700 transition
                              @error('password') border-red-400 @enderror">
                    @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirm New Password --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Confirm New Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password"
                           name="password_confirmation"
                           autocomplete="new-password"
                           class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5
                              text-sm bg-white dark:bg-gray-800
                              text-gray-700 dark:text-gray-300
                              focus:outline-none focus:ring-2 focus:ring-indigo-300
                              dark:focus:ring-indigo-700 transition">
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="px-6 py-2.5 bg-gray-800 dark:bg-gray-700 hover:bg-gray-900
                               dark:hover:bg-gray-600 text-white text-sm font-medium
                               rounded-xl transition-colors">
                        Update Password
                    </button>
                </div>
            </form>
        </div>

        {{-- =============================================
             SECTION 3: Account Info (read-only)
             ============================================= --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100
                dark:border-gray-800 p-6">

            <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300
                   border-b border-gray-100 dark:border-gray-800 pb-3 mb-4">
                Account Info
            </h2>

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Email</span>
                    <span class="text-sm text-gray-800 dark:text-gray-200">
                    {{ $user->email }}
                </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Role</span>
                    <span class="text-sm text-gray-800 dark:text-gray-200">
                    {{ $user->roles->pluck('name')->map('ucfirst')->join(', ') ?: 'No role assigned' }}
                </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Member Since</span>
                    <span class="text-sm text-gray-800 dark:text-gray-200">
                    {{ $user->created_at->format('d F Y') }}
                </span>
                </div>
            </div>
        </div>

    </div>

    <script>
        // Live avatar preview before upload
        document.getElementById('avatar-input')?.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => {
                    document.getElementById('avatar-preview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        // Bio character counter
        const bioTextarea = document.getElementById('bio-textarea');
        const bioCount    = document.getElementById('bio-count');
        bioTextarea?.addEventListener('input', function () {
            bioCount.textContent = this.value.length;
        });
    </script>

@endsection
