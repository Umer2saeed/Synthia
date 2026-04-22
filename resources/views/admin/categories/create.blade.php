<x-app-layout>
    {{-- Safety net: should never be reached by authors due to controller check --}}
    @cannot('manage categories')
        @php abort(403) @endphp
    @endcannot
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Create Category</h2>
        <p class="text-xs text-gray-400 mt-0.5">{{ now()->format('l, d F Y') }}</p>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto px-4">
        <div class="bg-white shadow rounded-xl p-6">

            <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 @error('name') border-red-400 @enderror"
                           placeholder="e.g. Technology">
                    @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug
                        <span class="text-gray-400 font-normal">(auto-generated if empty)</span>
                    </label>
                    <input type="text" name="slug" value="{{ old('slug') }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-400 @error('slug') border-red-400 @enderror"
                           placeholder="e.g. technology">
                    @error('slug')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="4"
                              class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                              placeholder="Optional short description...">{{ old('description') }}</textarea>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="px-5 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                        Create Category
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
