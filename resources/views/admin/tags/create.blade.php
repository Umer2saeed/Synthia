<x-app-layout>
    {{-- Controller already blocks authors but this is a clear safety net --}}
    @cannot('manage categories')
        @php abort(403) @endphp
    @endcannot
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Create Tag</h2>
    </x-slot>

    <div class="py-8 max-w-xl mx-auto px-4">
        <div class="bg-white shadow rounded-xl p-6">
            <form action="{{ route('admin.tags.store') }}" method="POST" class="space-y-5">
                @csrf

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Tag Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           id="tag-name"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 @error('name') border-red-400 @enderror"
                           placeholder="e.g. Laravel">
                    @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Slug --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Slug
                        <span class="text-gray-400 font-normal text-xs">(auto-generated if empty)</span>
                    </label>
                    <input type="text" name="slug" value="{{ old('slug') }}"
                           id="tag-slug"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-400 @error('slug') border-red-400 @enderror"
                           placeholder="e.g. laravel">
                    @error('slug')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="px-5 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                        Create Tag
                    </button>
                    <a href="{{ route('admin.tags.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-generate slug from name — same pattern as Post create form
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
