<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Create New Post</h2>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ now()->format('l, d F Y') }}</p>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4">

        {{-- =============================================
     AUTOSAVE RESTORE BANNER
     Shown only when an unsaved draft exists
============================================= --}}
        @if(isset($autosaveDraft) && $autosaveDraft)
            <div id="autosave-banner"
                 class="mb-5 flex items-center justify-between gap-4
                px-4 py-3 rounded-xl
                bg-amber-50 dark:bg-amber-950
                border border-amber-200 dark:border-amber-800
                text-amber-800 dark:text-amber-300 text-sm">

                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Unsaved draft found from {{ $autosaveDraft->saved_at->diffForHumans() }}.
                    Would you like to restore it?
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <button type="button"
                            id="restore-draft-btn"
                            data-title="{{ $autosaveDraft->title }}"
                            data-content="{{ e($autosaveDraft->content) }}"
                            class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700
                           text-white text-xs font-medium rounded-lg transition">
                        Restore
                    </button>
                    <button type="button"
                            id="dismiss-draft-btn"
                            class="text-xs text-amber-700 dark:text-amber-400 hover:underline">
                        Dismiss
                    </button>
                </div>
            </div>
        @endif

        {{-- Autosave status indicator --}}
        <div id="autosave-status"
             class="mb-3 text-xs text-gray-400 dark:text-gray-500 text-right hidden">
            <span id="autosave-status-text">Saving...</span>
        </div>

        <form action="{{ route('admin.posts.store') }}"
              method="POST"
              enctype="multipart/form-data"
              class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- MAIN COLUMN --}}
                <div class="lg:col-span-2 space-y-5">

                    <div class="bg-white dark:bg-gray-800
                                shadow rounded-xl
                                border border-gray-200 dark:border-gray-700
                                p-6 space-y-5">

                        {{-- Title --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="title" value="{{ old('title') }}"
                                   id="title-input"
                                   class="w-full border border-gray-300 dark:border-gray-600
                                          bg-white dark:bg-gray-900
                                          text-gray-800 dark:text-gray-200
                                          placeholder-gray-400 dark:placeholder-gray-500
                                          rounded-lg px-4 py-2 text-sm
                                          focus:outline-none focus:ring-2 focus:ring-indigo-400
                                          @error('title') border-red-400 @enderror"
                                   placeholder="Enter post title...">
                            @error('title')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Slug --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Slug
                                <span class="text-gray-400 dark:text-gray-500 font-normal text-xs">
                                    (auto-generated if left blank)
                                </span>
                            </label>
                            <input type="text" name="slug" value="{{ old('slug') }}"
                                   id="slug-input"
                                   class="w-full border border-gray-300 dark:border-gray-600
                                          bg-white dark:bg-gray-900
                                          text-gray-800 dark:text-gray-200
                                          placeholder-gray-400 dark:placeholder-gray-500
                                          rounded-lg px-4 py-2 text-sm font-mono
                                          focus:outline-none focus:ring-2 focus:ring-indigo-400
                                          @error('slug') border-red-400 @enderror"
                                   placeholder="post-url-slug">
                            @error('slug')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Content --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Content <span class="text-red-500">*</span>
                            </label>
                            <x-rich-editor name="content" :value="old('content', '')" />
                            @error('content')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- AI Summary --}}
                    <div class="bg-white dark:bg-gray-800
                                shadow rounded-xl
                                border border-gray-200 dark:border-gray-700
                                p-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            AI Summary
                            <span class="text-gray-400 dark:text-gray-500 font-normal text-xs">
                                (optional — max 500 chars)
                            </span>
                        </label>
                        <textarea name="ai_summary" rows="3" maxlength="500"
                                  class="w-full border border-gray-300 dark:border-gray-600
                                         bg-white dark:bg-gray-900
                                         text-gray-800 dark:text-gray-200
                                         placeholder-gray-400 dark:placeholder-gray-500
                                         rounded-lg px-4 py-2 text-sm
                                         focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                  placeholder="A short AI-generated or manually written summary...">{{ old('ai_summary') }}</textarea>
                    </div>

                </div>

                {{-- SIDEBAR --}}
                <div class="space-y-5">

                    {{-- Publish Settings --}}
                    <div class="bg-white dark:bg-gray-800
                                shadow rounded-xl
                                border border-gray-200 dark:border-gray-700
                                p-5 space-y-4">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200
                                   border-b border-gray-100 dark:border-gray-700 pb-2">
                            Publish Settings
                        </h3>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                Status
                            </label>
                            @can('publish posts')
                                <select name="status"
                                        class="w-full border border-gray-300 dark:border-gray-600
                                               bg-white dark:bg-gray-900
                                               text-gray-800 dark:text-gray-200
                                               rounded-lg px-3 py-2 text-sm
                                               focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                    <option value="draft"     {{ old('status') === 'draft'     ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="scheduled" {{ old('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                </select>
                            @else
                                <input type="hidden" name="status" value="draft">
                                <div class="px-3 py-2 bg-gray-50 dark:bg-gray-900
                                            border border-gray-200 dark:border-gray-700
                                            rounded-lg text-sm text-gray-500 dark:text-gray-400">
                                    Draft
                                    <span class="text-xs text-gray-400 dark:text-gray-500 block mt-0.5">
                                        An editor will publish your post.
                                    </span>
                                </div>
                            @endcan
                        </div>

                        @can('publish posts')
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                    Publish Date
                                </label>
                                <input type="datetime-local" name="published_at"
                                       value="{{ old('published_at') }}"
                                       class="w-full border border-gray-300 dark:border-gray-600
                                              bg-white dark:bg-gray-900
                                              text-gray-800 dark:text-gray-200
                                              rounded-lg px-3 py-2 text-sm
                                              focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            </div>

                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="is_featured" value="1" id="is_featured"
                                       {{ old('is_featured') ? 'checked' : '' }}
                                       class="rounded border-gray-300 dark:border-gray-600
                                              text-indigo-600 focus:ring-indigo-400">
                                <label for="is_featured"
                                       class="text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                    Mark as Featured
                                </label>
                            </div>
                        @endcan
                    </div>

                    {{-- Category --}}
                    <div class="bg-white dark:bg-gray-800
                                shadow rounded-xl
                                border border-gray-200 dark:border-gray-700
                                p-5">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200
                                   border-b border-gray-100 dark:border-gray-700 pb-2 mb-3">
                            Category
                        </h3>
                        <select name="category_id"
                                class="w-full border border-gray-300 dark:border-gray-600
                                       bg-white dark:bg-gray-900
                                       text-gray-800 dark:text-gray-200
                                       rounded-lg px-3 py-2 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-indigo-400
                                       @error('category_id') border-red-400 @enderror">
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

                    {{-- Tags --}}
                    <div class="bg-white dark:bg-gray-800
                                shadow rounded-xl
                                border border-gray-200 dark:border-gray-700
                                p-5">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200
                                   border-b border-gray-100 dark:border-gray-700 pb-2 mb-3">
                            Tags
                        </h3>
                        @if($tags->isEmpty())
                            <p class="text-xs text-gray-400 dark:text-gray-500">
                                No tags yet.
                                <a href="{{ route('admin.tags.create') }}"
                                   class="text-indigo-500 dark:text-indigo-400 hover:underline">
                                    Create tags first
                                </a>.
                            </p>
                        @else
                            <div class="space-y-1 max-h-48 overflow-y-auto">
                                @foreach($tags as $tag)
                                    <label class="flex items-center gap-2 cursor-pointer
                                                  hover:bg-gray-50 dark:hover:bg-gray-700
                                                  px-2 py-1 rounded">
                                        <input type="checkbox"
                                               name="tags[]"
                                               value="{{ $tag->id }}"
                                               {{ in_array($tag->id, old('tags', [])) ? 'checked' : '' }}
                                               class="rounded border-gray-300 dark:border-gray-600
                                                      text-indigo-600 focus:ring-indigo-400">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            {{ $tag->name }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Cover Image --}}
                    <div class="bg-white dark:bg-gray-800
                                shadow rounded-xl
                                border border-gray-200 dark:border-gray-700
                                p-5">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200
                                   border-b border-gray-100 dark:border-gray-700 pb-2 mb-3">
                            Cover Image
                        </h3>

                        <div id="image-preview" class="hidden mb-3">
                            <img id="preview-img" src="" alt="Preview"
                                 class="w-full h-36 object-cover rounded-lg
                                        border border-gray-200 dark:border-gray-700">
                        </div>

                        <input type="file" name="cover_image" id="cover_image"
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

                    {{-- Submit --}}
                    <div class="flex flex-col gap-2">
                        <button type="submit"
                                class="w-full px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700
                                       text-white text-sm font-medium rounded-lg transition">
                            Create Post
                        </button>
                        <a href="{{ route('admin.posts.index') }}"
                           class="text-center text-sm text-gray-500 dark:text-gray-400 hover:underline">
                            Cancel
                        </a>
                    </div>

                </div>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('title-input').addEventListener('input', function () {
            const slugInput = document.getElementById('slug-input');
            if (!slugInput.dataset.manuallyEdited) {
                slugInput.value = this.value
                    .toLowerCase().trim()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
            }
        });

        document.getElementById('slug-input').addEventListener('input', function () {
            this.dataset.manuallyEdited = 'true';
        });

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
