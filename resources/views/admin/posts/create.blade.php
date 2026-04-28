<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Create New Post</h2>
        <p class="text-xs text-gray-400 mt-0.5">{{ now()->format('l, d F Y') }}
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4">
        <form action="{{ route('admin.posts.store') }}"
              method="POST"
              enctype="multipart/form-data"  {{-- Required for file uploads --}}
              class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- =========================================
                     LEFT / MAIN COLUMN — Title, Slug, Content
                     ========================================= --}}
                <div class="lg:col-span-2 space-y-5">

                    {{-- Card wrapper --}}
                    <div class="bg-white shadow rounded-xl p-6 space-y-5">

                        {{-- Title --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="title" value="{{ old('title') }}"
                                   id="title-input"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 @error('title') border-red-400 @enderror"
                                   placeholder="Enter post title...">
                            @error('title')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Slug --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Slug
                                <span class="text-gray-400 font-normal text-xs">(auto-generated if left blank)</span>
                            </label>
                            <input type="text" name="slug" value="{{ old('slug') }}"
                                   id="slug-input"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-400 @error('slug') border-red-400 @enderror"
                                   placeholder="post-url-slug">
                            @error('slug')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Content --}}
{{--                        <div>--}}
{{--                            <label class="block text-sm font-medium text-gray-700 mb-1">--}}
{{--                                Content <span class="text-red-500">*</span>--}}
{{--                            </label>--}}
{{--                            <textarea name="content" rows="14"--}}
{{--                                      class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 @error('content') border-red-400 @enderror"--}}
{{--                                      placeholder="Write your post content here...">{{ old('content') }}</textarea>--}}
{{--                            @error('content')--}}
{{--                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>--}}
{{--                            @enderror--}}
{{--                        </div>--}}
                        {{-- ✅ NEW — TipTap rich text editor --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Content <span class="text-red-500">*</span>
                            </label>

                            <x-rich-editor
                                name="content"
                                :value="old('content', '')"
                            />

                            @error('content')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- AI Summary (separate card) --}}
                    <div class="bg-white shadow rounded-xl p-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            AI Summary
                            <span class="text-gray-400 font-normal text-xs">(optional — max 500 chars)</span>
                        </label>
                        <textarea name="ai_summary" rows="3" maxlength="500"
                                  class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                  placeholder="A short AI-generated or manually written summary...">{{ old('ai_summary') }}</textarea>
                    </div>

                </div>

                {{-- =========================================
                     RIGHT SIDEBAR — Meta, Image, Publish
                     ========================================= --}}
                <div class="space-y-5">

                    {{-- Publish Settings --}}
                    <div class="bg-white shadow rounded-xl p-5 space-y-4">
                        <h3 class="text-sm font-semibold text-gray-700 border-b pb-2">Publish Settings</h3>

                        {{-- Status --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>

                            @can('publish posts')
                                {{--
                                | Admin and editor see all three status options.
                                --}}
                                <select name="status"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                    <option value="draft"     {{ old('status') === 'draft'     ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="scheduled" {{ old('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                </select>
                            @else
                                {{--
                                | Author sees a read-only badge — their posts are always draft.
                                | The hidden input ensures 'draft' is always submitted.
                                --}}
                                <input type="hidden" name="status" value="draft">
                                <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-500">
                                    Draft
                                    <span class="text-xs text-gray-400 block mt-0.5">
                    An editor will publish your post.
                </span>
                                </div>
                            @endcan
                        </div>

                        {{-- Published At --}}
                        @can('publish posts')
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Publish Date</label>
                                <input type="datetime-local" name="published_at"
                                       value="{{ old('published_at') }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            </div>
                        @endcan

                        {{-- Featured — only admin and editor --}}
                        @can('publish posts')
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="is_featured" value="1" id="is_featured"
                                       {{ old('is_featured') ? 'checked' : '' }}
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
                                    {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                            {{--
                                Each checkbox sends tags[] with the tag ID as value.
                                in_array() checks if this tag was previously selected
                                (useful when form fails validation and repopulates via old()).
                            --}}
                            <div class="space-y-1 max-h-48 overflow-y-auto">
                                @foreach($tags as $tag)
                                    <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 px-2 py-1 rounded">
                                        <input type="checkbox"
                                               name="tags[]"
                                               value="{{ $tag->id }}"
                                               {{ in_array($tag->id, old('tags', [])) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-400">
                                        <span class="text-sm text-gray-700">{{ $tag->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>


                    {{-- Cover Image Upload --}}
                    <div class="bg-white shadow rounded-xl p-5">
                        <h3 class="text-sm font-semibold text-gray-700 border-b pb-2 mb-3">Cover Image</h3>

                        {{-- Preview area (JS-powered) --}}
                        <div id="image-preview" class="hidden mb-3">
                            <img id="preview-img" src="" alt="Preview"
                                 class="w-full h-36 object-cover rounded-lg border border-gray-200">
                        </div>

                        <input type="file" name="cover_image" id="cover_image"
                               accept="image/jpg,image/jpeg,image/png,image/webp"
                               class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                        <p class="text-xs text-gray-400 mt-1">JPG, PNG, WEBP — max 2MB</p>

                        @error('cover_image')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <div class="flex flex-col gap-2">
                        <button type="submit"
                                class="w-full px-5 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                            Create Post
                        </button>
                        <a href="{{ route('admin.posts.index') }}"
                           class="text-center text-sm text-gray-500 hover:underline">Cancel</a>
                    </div>

                </div>
            </div>

        </form>
    </div>

    {{-- JS: Live slug generation from title + image preview --}}
    <script>
        // Auto-generate slug from title as you type
        document.getElementById('title-input').addEventListener('input', function () {
            const slugInput = document.getElementById('slug-input');
            // Only auto-fill if user hasn't manually edited the slug
            if (!slugInput.dataset.manuallyEdited) {
                slugInput.value = this.value
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9\s-]/g, '')   // remove special chars
                    .replace(/\s+/g, '-')            // spaces to hyphens
                    .replace(/-+/g, '-');             // collapse multiple hyphens
            }
        });

        // Mark slug as manually edited so auto-gen stops
        document.getElementById('slug-input').addEventListener('input', function () {
            this.dataset.manuallyEdited = 'true';
        });

        // Live image preview before upload
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
