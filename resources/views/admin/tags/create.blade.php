<x-app-layout>
    @cannot('manage categories')
        @php abort(403) @endphp
    @endcannot

    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Create Tag</h2>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ now()->format('l, d F Y') }}</p>
    </x-slot>

    <div class="py-8 max-w-xl mx-auto px-4">
        <div class="bg-white dark:bg-gray-800
                    shadow rounded-xl
                    border border-gray-200 dark:border-gray-700
                    p-6">
            <form action="{{ route('admin.tags.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Tag Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           id="tag-name"
                           class="w-full border border-gray-300 dark:border-gray-600
                                  bg-white dark:bg-gray-900
                                  text-gray-800 dark:text-gray-200
                                  placeholder-gray-400 dark:placeholder-gray-500
                                  rounded-lg px-4 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400
                                  @error('name') border-red-400 @enderror"
                           placeholder="e.g. Laravel">
                    @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Slug
                        <span class="text-gray-400 dark:text-gray-500 font-normal text-xs">
                            (auto-generated if empty)
                        </span>
                    </label>
                    <input type="text" name="slug" value="{{ old('slug') }}"
                           id="tag-slug"
                           class="w-full border border-gray-300 dark:border-gray-600
                                  bg-white dark:bg-gray-900
                                  text-gray-800 dark:text-gray-200
                                  placeholder-gray-400 dark:placeholder-gray-500
                                  rounded-lg px-4 py-2 text-sm font-mono
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400
                                  @error('slug') border-red-400 @enderror"
                           placeholder="e.g. laravel">
                    @error('slug')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700
                                   text-white text-sm rounded-lg transition">
                        Create Tag
                    </button>
                    <a href="{{ route('admin.tags.index') }}"
                       class="text-sm text-gray-500 dark:text-gray-400 hover:underline">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const nameInput = document.getElementById('tag-name');
        const slugInput = document.getElementById('tag-slug');
        nameInput.addEventListener('input', function () {
            if (!slugInput.dataset.manuallyEdited) {
                slugInput.value = this.value
                    .toLowerCase().trim()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
            }
        });
        slugInput.addEventListener('input', function () {
            this.dataset.manuallyEdited = 'true';
        });
    </script>
</x-app-layout>
