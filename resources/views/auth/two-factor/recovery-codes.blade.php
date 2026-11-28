<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Save your recovery codes</h2>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Shown only once</p>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto px-4">
        <div class="bg-white dark:bg-gray-800 shadow rounded-xl border border-gray-200 dark:border-gray-700 p-6">

            <div class="mb-5 px-4 py-3 rounded-xl bg-amber-50 dark:bg-amber-950 border border-amber-200 dark:border-amber-800
                        text-sm text-amber-800 dark:text-amber-300">
                If you lose your phone, each of these codes lets you sign in <strong>once</strong>.
                Store them somewhere safe, such as a password manager. They will not be shown again.
            </div>

            <div class="grid grid-cols-2 gap-2 mb-5">
                @foreach($codes as $code)
                    <code class="px-3 py-2 rounded-lg bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200
                                 text-sm text-center tracking-wider select-all">{{ $code }}</code>
                @endforeach
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="button" id="copy-codes"
                        class="px-4 py-2 text-sm font-medium rounded-xl border border-gray-300 dark:border-gray-600
                               text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    Copy all
                </button>
                <button type="button" id="download-codes"
                        class="px-4 py-2 text-sm font-medium rounded-xl border border-gray-300 dark:border-gray-600
                               text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    Download .txt
                </button>
                <a href="{{ auth()->user()->can('access admin panel') ? route('admin.profile.edit') : route('frontend.profile.edit') }}"
                   class="ml-auto px-5 py-2 text-sm font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white transition">
                    I have saved them
                </a>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var codes = @json($codes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
            var text  = codes.join('\n');

            document.getElementById('copy-codes').addEventListener('click', function () {
                navigator.clipboard.writeText(text).then(function () {
                    toast.success('Recovery codes copied.');
                }, function () {
                    toast.error('Could not copy. Select the codes and copy them by hand.');
                });
            });

            document.getElementById('download-codes').addEventListener('click', function () {
                var blob = new Blob(['{{ config('app.name') }} recovery codes\n\n' + text + '\n'], { type: 'text/plain' });
                var link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'recovery-codes.txt';
                link.click();
                URL.revokeObjectURL(link.href);
            });
        })();
    </script>
</x-app-layout>
