<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Create Series</h2>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
            {{ now()->format('l, d F Y') }}
        </p>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto px-4">
        <div class="bg-white dark:bg-gray-800
                    shadow rounded-xl
                    border border-gray-200 dark:border-gray-700 p-6">

            <form action="{{ route('admin.series.store') }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium
                                   text-gray-700 dark:text-gray-300 mb-1">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title"
                           value="{{ old('title') }}"
                           class="w-full border border-gray-300 dark:border-gray-600
                                  bg-white dark:bg-gray-900
                                  text-gray-800 dark:text-gray-200
                                  rounded-lg px-4 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400
                                  @error('title') border-red-400 @enderror"
                           placeholder="e.g. Mastering Laravel from Zero">
                    @error('title')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium
                                   text-gray-700 dark:text-gray-300 mb-1">
                        Slug
                        <span class="text-gray-400 dark:text-gray-500 font-normal text-xs">
                            (auto-generated if empty)
                        </span>
                    </label>
                    <input type="text" name="slug"
                           value="{{ old('slug') }}"
                           class="w-full border border-gray-300 dark:border-gray-600
                                  bg-white dark:bg-gray-900
                                  text-gray-800 dark:text-gray-200
                                  rounded-lg px-4 py-2 text-sm font-mono
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400
                                  @error('slug') border-red-400 @enderror"
                           placeholder="mastering-laravel-from-zero">
                    @error('slug')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium
                                   text-gray-700 dark:text-gray-300 mb-1">
                        Description
                    </label>
                    <textarea name="description" rows="4"
                              class="w-full border border-gray-300 dark:border-gray-600
                                     bg-white dark:bg-gray-900
                                     text-gray-800 dark:text-gray-200
                                     placeholder-gray-400 dark:placeholder-gray-500
                                     rounded-lg px-4 py-2 text-sm
                                     focus:outline-none focus:ring-2 focus:ring-indigo-400"
                              placeholder="What will readers learn from this series?">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium
                                   text-gray-700 dark:text-gray-300 mb-1">
                        Cover Image
                    </label>
                    <input type="file" name="cover_image"
                           accept="image/jpg,image/jpeg,image/png,image/webp"
                           class="w-full text-sm text-gray-500 dark:text-gray-400
                                  file:mr-3 file:py-2 file:px-3 file:rounded-lg
                                  file:border-0 file:text-sm file:font-medium
                                  file:bg-indigo-50 dark:file:bg-indigo-950
                                  file:text-indigo-700 dark:file:text-indigo-400
                                  hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900
                                  cursor-pointer">
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                        JPG, PNG, WEBP — max 2MB
                    </p>
                    @error('cover_image')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_complete" value="1"
                           id="is_complete"
                           {{ old('is_complete') ? 'checked' : '' }}
                           class="rounded border-gray-300 dark:border-gray-600
                                  text-indigo-600 focus:ring-indigo-400">
                    <label for="is_complete"
                           class="text-sm text-gray-700 dark:text-gray-300">
                        Mark as complete
                    </label>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700
                                   text-white text-sm rounded-lg transition">
                        Create Series
                    </button>
                    <a href="{{ route('admin.series.index') }}"
                       class="text-sm text-gray-500 dark:text-gray-400 hover:underline">
                        Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>
