<x-app-layout>
    @cannot('manage categories')
        @php abort(403) @endphp
    @endcannot

    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
            Edit Tag: {{ $tag->name }}
        </h2>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ now()->format('l, d F Y') }}</p>
    </x-slot>

    <div class="py-8 max-w-xl mx-auto px-4">
        <div class="bg-white dark:bg-gray-800
                    shadow rounded-xl
                    border border-gray-200 dark:border-gray-700
                    p-6">
            <form action="{{ route('admin.tags.update', $tag) }}"
                  method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Tag Name
                    </label>
                    <input type="text" name="name"
                           value="{{ old('name', $tag->name) }}"
                           class="w-full border border-gray-300 dark:border-gray-600
                                  bg-white dark:bg-gray-900
                                  text-gray-800 dark:text-gray-200
                                  rounded-lg px-4 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400
                                  @error('name') border-red-400 @enderror">
                    @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Slug
                    </label>
                    <input type="text" name="slug"
                           value="{{ old('slug', $tag->slug) }}"
                           class="w-full border border-gray-300 dark:border-gray-600
                                  bg-white dark:bg-gray-900
                                  text-gray-800 dark:text-gray-200
                                  rounded-lg px-4 py-2 text-sm font-mono
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400
                                  @error('slug') border-red-400 @enderror">
                    @error('slug')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900
                            border border-gray-100 dark:border-gray-700
                            rounded-lg text-xs text-gray-500 dark:text-gray-400">
                    This tag is currently used in
                    <span class="font-semibold text-gray-700 dark:text-gray-300">
                        {{ $tag->posts()->count() }} {{ Str::plural('post', $tag->posts()->count()) }}
                    </span>.
                    Renaming it will update everywhere automatically.
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700
                                   text-white text-sm rounded-lg transition">
                        Update Tag
                    </button>
                    <a href="{{ route('admin.tags.index') }}"
                       class="text-sm text-gray-500 dark:text-gray-400 hover:underline">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
