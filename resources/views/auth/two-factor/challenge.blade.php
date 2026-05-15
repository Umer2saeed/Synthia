<x-guest-layout>
    <div class="mb-6 text-center">
        <div class="w-14 h-14 rounded-2xl bg-indigo-600 flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
            Two-Factor Authentication
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Enter the 6-digit code from your authenticator app,
            or enter a recovery code.
        </p>
    </div>

    <form method="POST" action="{{ route('two-factor.verify') }}">
        @csrf

        <div class="mb-5">
            <input type="text"
                   name="code"
                   inputmode="numeric"
                   autocomplete="one-time-code"
                   placeholder="000000"
                   autofocus
                   class="w-full border border-gray-300 dark:border-gray-600
                          bg-white dark:bg-gray-900
                          text-gray-800 dark:text-gray-200
                          placeholder-gray-300 dark:placeholder-gray-600
                          rounded-xl px-4 py-3
                          text-center text-2xl font-mono tracking-[0.5em]
                          focus:outline-none focus:ring-2 focus:ring-indigo-400
                          @error('code') border-red-400 @enderror">

            @error('code')
            <p class="text-red-500 text-sm mt-2 text-center">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="w-full py-3 bg-indigo-600 hover:bg-indigo-700
                       text-white font-semibold rounded-xl transition">
            Verify
        </button>

    </form>

    <div class="mt-5 text-center">
        <details class="text-sm">
            <summary class="text-gray-500 dark:text-gray-400 cursor-pointer
                            hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                Use a recovery code instead
            </summary>
            <form method="POST" action="{{ route('two-factor.verify') }}" class="mt-3">
                @csrf
                <input type="text"
                       name="code"
                       placeholder="XXXX-XXXX-XXXX"
                       class="w-full border border-gray-300 dark:border-gray-600
                              bg-white dark:bg-gray-900
                              text-gray-800 dark:text-gray-200
                              rounded-xl px-4 py-2.5 text-sm font-mono text-center
                              focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <button type="submit"
                        class="mt-2 w-full py-2.5 bg-gray-700 hover:bg-gray-800
                               text-white text-sm font-medium rounded-xl transition">
                    Use Recovery Code
                </button>
            </form>
        </details>
    </div>

</x-guest-layout>
