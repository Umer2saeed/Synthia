<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
            Save Your Recovery Codes
        </h2>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
            These codes will not be shown again
        </p>
    </x-slot>

    <div class="py-10 max-w-xl mx-auto px-4">

        {{-- Warning banner --}}
        <div class="mb-6 flex items-start gap-3 px-4 py-4
                    bg-amber-50 dark:bg-amber-950
                    border border-amber-200 dark:border-amber-800
                    text-amber-800 dark:text-amber-300 text-sm rounded-xl">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <p class="font-semibold mb-1">Save these codes now</p>
                <p>These recovery codes are shown only once. Store them somewhere safe — in a password manager, printed paper, or secure notes. Each code can be used once if you lose access to your authenticator app.</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800
                    rounded-2xl border border-gray-200 dark:border-gray-700
                    overflow-hidden">

            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700
                        flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                    Recovery Codes
                </h3>
                <button type="button"
                        onclick="copyRecoveryCodes(this)"
                        class="text-xs text-indigo-500 dark:text-indigo-400 hover:underline">
                    Copy All
                </button>
            </div>

            <div class="p-6 grid grid-cols-2 gap-2" id="recovery-codes">
                @foreach($codes as $code)
                    <code class="font-mono text-sm font-bold
                                  text-gray-800 dark:text-gray-200
                                  bg-gray-50 dark:bg-gray-900
                                  border border-gray-200 dark:border-gray-700
                                  rounded-lg px-3 py-2 text-center
                                  tracking-widest">
                        {{ $code }}
                    </code>
                @endforeach
            </div>

            <div class="px-6 pb-6">
                <a href="{{ route('admin.profile.edit') }}"
                   class="block w-full py-2.5 text-center
                          bg-indigo-600 hover:bg-indigo-700
                          text-white text-sm font-semibold
                          rounded-xl transition">
                    I've saved my codes — Continue
                </a>
            </div>

        </div>

    </div>

    <script>
        function copyRecoveryCodes(btn) {
            const codes = Array.from(
                document.querySelectorAll('#recovery-codes code')
            ).map(el => el.textContent.trim()).join('\n');

            const fallback = () => {
                const ta = document.createElement('textarea');
                ta.value = codes;
                ta.style.position = 'fixed'; ta.style.left = '-9999px';
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(codes).catch(fallback);
            } else {
                fallback();
            }

            btn.textContent = 'Copied!';
            setTimeout(() => btn.textContent = 'Copy All', 2000);
        }
    </script>

</x-app-layout>
