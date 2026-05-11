<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
            Edit Series: {{ $series->title }}
        </h2>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
            {{ now()->format('l, d F Y') }}
        </p>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto px-4 space-y-6">

        {{-- Series form --}}
        <div class="bg-white dark:bg-gray-800
                    shadow rounded-xl
                    border border-gray-200 dark:border-gray-700 p-6">

            <form action="{{ route('admin.series.update', $series) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium
                                   text-gray-700 dark:text-gray-300 mb-1">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title"
                           value="{{ old('title', $series->title) }}"
                           class="w-full border border-gray-300 dark:border-gray-600
                                  bg-white dark:bg-gray-900
                                  text-gray-800 dark:text-gray-200
                                  rounded-lg px-4 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400
                                  @error('title') border-red-400 @enderror">
                    @error('title')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium
                                   text-gray-700 dark:text-gray-300 mb-1">
                        Slug
                    </label>
                    <input type="text" name="slug"
                           value="{{ old('slug', $series->slug) }}"
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

                <div>
                    <label class="block text-sm font-medium
                                   text-gray-700 dark:text-gray-300 mb-1">
                        Description
                    </label>
                    <textarea name="description" rows="4"
                              class="w-full border border-gray-300 dark:border-gray-600
                                     bg-white dark:bg-gray-900
                                     text-gray-800 dark:text-gray-200
                                     rounded-lg px-4 py-2 text-sm
                                     focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('description', $series->description) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium
                                   text-gray-700 dark:text-gray-300 mb-1">
                        Cover Image
                    </label>
                    @if($series->cover_image)
                        <div class="mb-2">
                            <p class="text-xs text-gray-400 dark:text-gray-500 mb-1">
                                Current:
                            </p>
                            <img src="{{ $series->cover_image_url }}"
                                 alt="Current cover"
                                 class="w-24 h-24 object-cover rounded-lg
                                        border border-gray-200 dark:border-gray-700">
                        </div>
                    @endif
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
                        Upload new image to replace current.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_complete" value="1"
                           id="is_complete"
                           {{ old('is_complete', $series->is_complete) ? 'checked' : '' }}
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
                        Update Series
                    </button>
                    <a href="{{ route('admin.series.index') }}"
                       class="text-sm text-gray-500 dark:text-gray-400 hover:underline">
                        Cancel
                    </a>
                </div>

            </form>
        </div>

        {{-- Posts in this series --}}
        <div class="bg-white dark:bg-gray-800
                    shadow rounded-xl
                    border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">
                Posts in This Series
            </h3>

            @if($series->posts->isEmpty())
                <p class="text-sm text-gray-400 dark:text-gray-500">
                    No posts assigned to this series yet.
                    Assign posts from the post editor sidebar.
                </p>
            @else
                <div class="space-y-2">
                    @foreach($series->posts as $post)
                        <div class="flex items-center justify-between
                                    px-4 py-3 rounded-xl
                                    bg-gray-50 dark:bg-gray-700/50">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full
                                             bg-indigo-100 dark:bg-indigo-900
                                             text-indigo-600 dark:text-indigo-400
                                             text-xs font-bold
                                             flex items-center justify-center shrink-0">
                                    {{ $post->pivot->order }}
                                </span>
                                <div>
                                    <p class="text-sm font-medium
                                               text-gray-800 dark:text-gray-200">
                                        {{ Str::limit($post->title, 60) }}
                                    </p>
                                    <span class="text-xs
                                                 {{ $post->status === 'published'
                                                     ? 'text-green-600 dark:text-green-400'
                                                     : 'text-gray-400 dark:text-gray-500' }}">
                                        {{ ucfirst($post->status) }}
                                    </span>
                                </div>
                            </div>
                            <a href="{{ route('admin.posts.edit', $post) }}"
                               class="text-xs text-indigo-600 dark:text-indigo-400
                                      hover:underline">
                                Edit
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
