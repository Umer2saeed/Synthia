<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Edit Post</h2>
        <p class="text-xs text-gray-400 mt-0.5">{{ now()->format('l, d F Y') }}
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">{{ Str::limit($post->title, 60) }}</h2>
        </div>

        <form action="{{ route('admin.posts.update', $post) }}"
              method="POST"
              enctype="multipart/form-data"
              class="space-y-6">
            @csrf
            @method('PUT') {{-- Laravel needs PUT for update --}}

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- MAIN COLUMN --}}
                <div class="lg:col-span-2 space-y-5">

                    <div class="bg-white shadow rounded-xl p-6 space-y-5">

                        {{-- Title --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="title"
                                   value="{{ old('title', $post->title) }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 @error('title') border-red-400 @enderror">
                            @error('title')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Slug --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                            <input type="text" name="slug"
                                   value="{{ old('slug', $post->slug) }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-400 @error('slug') border-red-400 @enderror">
                            @error('slug')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Content --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Content <span class="text-red-500">*</span>
                            </label>
                            <textarea name="content" rows="14"
                                      class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 @error('content') border-red-400 @enderror">{{ old('content', $post->content) }}</textarea>
                            @error('content')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- AI Summary --}}
                    <div class="bg-white shadow rounded-xl p-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">AI Summary</label>
                        <textarea name="ai_summary" rows="3" maxlength="500"
                                  class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('ai_summary', $post->ai_summary) }}</textarea>
                    </div>

                </div>

                {{-- SIDEBAR --}}
                <div class="space-y-5">

                    {{-- Publish Settings --}}
                    <div class="bg-white shadow rounded-xl p-5 space-y-4">
                        <h3 class="text-sm font-semibold text-gray-700 border-b pb-2">Publish Settings</h3>

                        {{-- Status --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>

                            @can('publish posts')
                                <select name="status"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                    @foreach(['draft', 'published', 'scheduled'] as $s)
                                        <option value="{{ $s }}"
                                            {{ old('status', $post->status) === $s ? 'selected' : '' }}>
                                            {{ ucfirst($s) }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                {{--
                                | Author sees the current status as read-only.
                                | Hidden input preserves the existing status on submit
                                | so the controller's enforcement keeps it as-is.
                                --}}
                                <input type="hidden" name="status" value="{{ $post->status }}">
                                <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-500">
                                    {{ ucfirst($post->status) }}
                                    @if($post->status === 'draft')
                                        <span class="text-xs text-gray-400 block mt-0.5">
                        An editor will publish your post.
                    </span>
                                    @endif
                                </div>
                            @endcan
                        </div>

                        {{-- Published At --}}
                        @can('publish posts')
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Publish Date</label>
                                <input type="datetime-local" name="published_at"
                                       value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            </div>
                        @endcan

                        {{-- Featured --}}
                        @can('publish posts')
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="is_featured" value="1" id="is_featured"
                                       {{ old('is_featured', $post->is_featured) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-400">
                                <label for="is_featured" class="text-sm text-gray-700 cursor-pointer">
                                    Mark as Featured
                                </label>
                            </div>
                        @endcan

                    </div>

                    {{-- Category --}}
                    <div class="bg-white shadow rounded-xl p-5">
                        <h3 class="text-sm font-semibold text-gray-700 border-b pb-2 mb-3">Category</h3>
                        <select name="category_id"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 @error('category_id') border-red-400 @enderror">
                            <option value="">— Select Category —</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id', $post->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tags Multi-Select --}}
                    <div class="bg-white shadow rounded-xl p-5">
                        <h3 class="text-sm font-semibold text-gray-700 border-b pb-2 mb-3">Tags</h3>

                        @if($tags->isEmpty())
                            <p class="text-xs text-gray-400">
                                No tags yet.
                                <a href="{{ route('admin.tags.create') }}" class="text-indigo-500 hover:underline">Create tags first</a>.
                            </p>
                        @else
                            <div class="space-y-1 max-h-48 overflow-y-auto">
                                @foreach($tags as $tag)
                                    <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 px-2 py-1 rounded">
                                        <input type="checkbox"
                                               name="tags[]"
                                               value="{{ $tag->id }}"
                                               {{--
                                                   old() handles repopulation after validation failure.
                                                   $postTagIds handles the initial pre-check on page load.
                                               --}}
                                               {{ in_array($tag->id, old('tags', $postTagIds)) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-400">
                                        <span class="text-sm text-gray-700">{{ $tag->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Cover Image --}}
                    <div class="bg-white shadow rounded-xl p-5">
                        <h3 class="text-sm font-semibold text-gray-700 border-b pb-2 mb-3">Cover Image</h3>

                        {{-- Show current image if it exists --}}
                        @if($post->cover_image)
                            <div class="mb-3">
                                <p class="text-xs text-gray-400 mb-1">Current image:</p>
                                <img src="{{ $post->cover_image_url }}" alt="Current Cover"
                                     class="w-full h-36 object-cover rounded-lg border border-gray-200">
                            </div>
                        @endif

                        {{-- Preview new image before upload --}}
                        <div id="image-preview" class="hidden mb-3">
                            <p class="text-xs text-gray-400 mb-1">New image:</p>
                            <img id="preview-img" src="" alt="Preview"
                                 class="w-full h-36 object-cover rounded-lg border border-gray-200">
                        </div>

                        <input type="file" name="cover_image" id="cover_image"
                               accept="image/jpg,image/jpeg,image/png,image/webp"
                               class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                        <p class="text-xs text-gray-400 mt-1">Upload new image to replace current. JPG, PNG, WEBP — max 2MB</p>

                        @error('cover_image')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Buttons --}}
                    <div class="flex flex-col gap-2">
                        <button type="submit"
                                class="w-full px-5 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                            Update Post
                        </button>
                        <a href="{{ route('admin.posts.index') }}"
                           class="text-center text-sm text-gray-500 hover:underline">Cancel</a>
                    </div>

                </div>
            </div>
        </form>
    </div>

    <script>
        // Live image preview for new upload
        document.getElementById('cover_image').addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    document.getElementById('preview-img').src = e.target.result;
                    document.getElementById('image-preview').classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</x-app-layout>
