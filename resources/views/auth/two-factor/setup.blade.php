<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Set up two-factor authentication</h2>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Protect your account with a code from your phone</p>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto px-4 space-y-4">

        <div class="bg-white dark:bg-gray-800 shadow rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-7">

            {{-- Step 1 --}}
            <div class="flex gap-4">
                <span class="shrink-0 w-7 h-7 rounded-full bg-indigo-600 text-white text-sm font-bold flex items-center justify-center">1</span>
                <div>
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Install an authenticator app</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Use Google Authenticator, Microsoft Authenticator or Authy on your phone.
                        If you already have a "{{ config('app.name') }}" entry from an earlier attempt, delete it first.
                    </p>
                </div>
            </div>

            {{-- Step 2 --}}
            <div class="flex gap-4">
                <span class="shrink-0 w-7 h-7 rounded-full bg-indigo-600 text-white text-sm font-bold flex items-center justify-center">2</span>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Scan this QR code</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mb-4">
                        In the app tap <strong>+</strong> then <strong>Scan a QR code</strong>. This works offline.
                    </p>

                    <div class="inline-block p-3 bg-white rounded-xl border border-gray-200">
                        <img src="data:image/svg+xml;base64,{{ $qrSvg }}" alt="QR code" class="w-44 h-44">
                    </div>

                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-4">Can't scan? Enter this key manually:</p>
                    <code class="mt-1 inline-block px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 text-sm tracking-wider select-all">
                        {{ trim(chunk_split($secret, 4, ' ')) }}
                    </code>
                </div>
            </div>

            {{-- Step 3 --}}
            <div class="flex gap-4">
                <span class="shrink-0 w-7 h-7 rounded-full bg-indigo-600 text-white text-sm font-bold flex items-center justify-center">3</span>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Enter the 6-digit code</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mb-3">
                        Type the code your app shows right now to finish.
                    </p>

                    <form action="{{ route('two-factor.confirm') }}" method="POST" class="flex flex-wrap items-start gap-3">
                        @csrf
                        <div>
                            <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code"
                                   maxlength="7" required autofocus placeholder="123456"
                                   class="w-40 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900
                                          text-gray-800 dark:text-gray-200 rounded-xl px-4 py-2.5 text-center text-lg
                                          font-semibold tracking-widest focus:outline-none focus:ring-2 focus:ring-indigo-400
                                          @error('code') border-red-400 @enderror">
                            @error('code')
                            <p class="text-red-500 text-xs mt-1 max-w-xs">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit"
                                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition">
                            Verify and enable
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Separate form, never nested inside the one above --}}
        <form action="{{ route('two-factor.restart') }}" method="POST"
              onsubmit="return confirm('Create a new QR code? Any entry you already scanned will stop working.')"
              class="text-center">
            @csrf
            <button type="submit" class="text-xs text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:underline">
                Codes not working? Create a new QR code
            </button>
        </form>
    </div>
</x-app-layout>
