{{--<x-guest-layout>--}}
{{--    <div class="mb-4 text-sm text-gray-600">--}}
{{--        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}--}}
{{--    </div>--}}

{{--    <!-- Session Status -->--}}
{{--    <x-auth-session-status class="mb-4" :status="session('status')" />--}}

{{--    <form method="POST" action="{{ route('password.email') }}">--}}
{{--        @csrf--}}

{{--        <!-- Email Address -->--}}
{{--        <div>--}}
{{--            <x-input-label for="email" :value="__('Email')" />--}}
{{--            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />--}}
{{--            <x-input-error :messages="$errors->get('email')" class="mt-2" />--}}
{{--        </div>--}}

{{--        <div class="flex items-center justify-end mt-4">--}}
{{--            <x-primary-button>--}}
{{--                {{ __('Email Password Reset Link') }}--}}
{{--            </x-primary-button>--}}
{{--        </div>--}}
{{--    </form>--}}
{{--</x-guest-layout>--}}

<x-guest-layout>

    <div class="mb-6 text-center">
        <div style="font-size: 48px; margin-bottom: 12px;">🔑</div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Forgot password?</h1>
    </div>

    <p class="mb-6 text-sm text-gray-600 leading-relaxed text-center">
        Enter your email address and we will send you a link to reset your password.
        The link expires in 60 minutes.
    </p>

    {{-- Success message after email sent --}}
    @if (session('status'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200
                    text-green-700 text-sm rounded-xl">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                Email Address
            </label>
            <input id="email" type="email" name="email"
                   value="{{ old('email') }}" required autofocus
                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-400
                          @error('email') border-red-400 @enderror">
            @error('email')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="w-full px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700
                       text-white text-sm font-medium rounded-xl transition-colors">
            Send Reset Link
        </button>

        <div class="mt-4 text-center">
            <a href="{{ route('login') }}"
               class="text-sm text-indigo-600 hover:underline">
                Back to login
            </a>
        </div>
    </form>

</x-guest-layout>
