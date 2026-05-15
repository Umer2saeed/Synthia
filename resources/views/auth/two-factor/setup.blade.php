<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
            Set Up Two-Factor Authentication
        </h2>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
            Secure your account with Google Authenticator
        </p>
    </x-slot>

    <div class="py-10 max-w-2xl mx-auto px-4">

        @if(session('warning'))
            <div class="mb-6 px-4 py-3 bg-amber-50 dark:bg-amber-950
                        border border-amber-200 dark:border-amber-800
                        text-amber-800 dark:text-amber-300 text-sm rounded-xl">
                ⚠ {{ session('warning') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800
                    rounded-2xl border border-gray-200 dark:border-gray-700
                    overflow-hidden">

            {{-- Step indicators --}}
            <div class="grid grid-cols-3 border-b border-gray-100 dark:border-gray-700">
                @foreach(['Scan QR Code', 'Verify Code', 'Save Recovery Codes'] as $i => $step)
                    <div class="flex items-center gap-2 px-5 py-4
                                {{ $i === 0
                                    ? 'border-b-2 border-indigo-500'
                                    : 'opacity-40' }}">
                        <span class="w-6 h-6 rounded-full text-xs font-bold flex items-center justify-center
                                     {{ $i === 0
                                         ? 'bg-indigo-600 text-white'
                                         : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }}">
                            {{ $i + 1 }}
                        </span>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300
                                     hidden sm:block">
                            {{ $step }}
                        </span>
                    </div>
                @endforeach
            </div>

            <div class="p-8">

                <h3 class="text-base font-semibold text-gray-800 dark:text-white mb-2">
                    Step 1: Scan this QR code
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                    Open <strong>Google Authenticator</strong>, <strong>Authy</strong>, or any TOTP app,
                    tap the + button, and scan the QR code below.
                </p>

                {{-- QR Code --}}
                <div class="flex justify-center mb-6">
                    <div class="p-4 bg-white rounded-2xl border border-gray-200 dark:border-gray-700
                                shadow-sm inline-block">
                        <img src="data:image/svg+xml;base64,{{ $qrSvg }}"
                             alt="2FA QR Code"
                             class="w-48 h-48">
                    </div>
                </div>

                {{-- Manual entry key --}}
                <div class="bg-gray-50 dark:bg-gray-900
                            border border-gray-200 dark:border-gray-700
                            rounded-xl p-4 mb-8">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5">
                        Can't scan? Enter this key manually:
                    </p>
                    <code class="text-sm font-mono font-bold text-indigo-600 dark:text-indigo-400
                                  tracking-widest break-all">
                        {{ chunk_split($secret, 4, ' ') }}
                    </code>
                </div>

                <h3 class="text-base font-semibold text-gray-800 dark:text-white mb-2">
                    Step 2: Enter the 6-digit code
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Enter the code shown in your authenticator app to confirm setup.
                </p>

                <form action="{{ route('two-factor.confirm') }}" method="POST">
                    @csrf

                    <div class="mb-5">
                        <input type="text"
                               name="code"
                               inputmode="numeric"
                               autocomplete="one-time-code"
                               maxlength="6"
                               placeholder="000000"
                               autofocus
                               class="w-full border border-gray-300 dark:border-gray-600
                                      bg-white dark:bg-gray-900
                                      text-gray-800 dark:text-gray-200
                                      placeholder-gray-300 dark:placeholder-gray-600
                                      rounded-xl px-4 py-3 text-2xl font-mono
                                      text-center tracking-[0.5em]
                                      focus:outline-none focus:ring-2 focus:ring-indigo-400
                                      @error('code') border-red-400 @enderror">

                        @error('code')
                        <p class="text-red-500 text-sm mt-2 text-center">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                            class="w-full py-3 bg-indigo-600 hover:bg-indigo-700
                                   text-white font-semibold rounded-xl transition">
                        Verify & Enable 2FA
                    </button>

                </form>

            </div>
        </div>

    </div>
</x-app-layout>
