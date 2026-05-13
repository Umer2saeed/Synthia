<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Edit Post</h2>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ now()->format('l, d F Y') }}</p>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4">

        {{-- =============================================
             AUTOSAVE RESTORE BANNER
             Shown only when an unsaved draft exists
        ============================================= --}}
        @if(isset($autosaveDraft) && $autosaveDraft)
            <div data-post-id="{{ $post->id }}" id="autosave-banner" class="mb-5 flex items-center justify-between gap-4
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

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                    {{ Str::limit($post->title, 60) }}
                </h2>
            </div>

            <div>
                {{-- Revisions link --}}
                @if($post->revisions()->exists())
                    <a href="{{ route('admin.posts.revisions.index', $post) }}"
                       class="flex items-center gap-1.5 px-3 py-2 text-sm
                  text-gray-500 dark:text-gray-400
                  bg-gray-50 dark:bg-gray-800
                  border border-gray-200 dark:border-gray-700
                  rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700
                  transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Revisions
                        <span class="ml-1 px-1.5 py-0.5 text-xs rounded-full
                         bg-gray-200 dark:bg-gray-600
                         text-gray-600 dark:text-gray-300">
                            {{ $post->revisions()->count() }}
                        </span>
                    </a>
                @endif
            </div>
        </div>

        <form action="{{ route('admin.posts.update', $post) }}"
              method="POST"
              enctype="multipart/form-data"
              class="space-y-6">
            @csrf
            @method('PUT')

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
                            <input type="text" name="title"
                                   value="{{ old('title', $post->title) }}"
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

                        {{-- Slug --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Slug
                            </label>
                            <input type="text" name="slug"
                                   value="{{ old('slug', $post->slug) }}"
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

                        {{-- Content --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Content <span class="text-red-500">*</span>
                            </label>
                            <x-rich-editor
                                name="content"
                                :value="old('content', $post->content ?? '')"
                            />
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
                        </label>
                        <textarea name="ai_summary" rows="3" maxlength="500"
                                  class="w-full border border-gray-300 dark:border-gray-600
                                         bg-white dark:bg-gray-900
                                         text-gray-800 dark:text-gray-200
                                         rounded-lg px-4 py-2 text-sm
                                         focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('ai_summary', $post->ai_summary) }}</textarea>
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
                                    @foreach(['draft', 'published', 'scheduled'] as $s)
                                        <option value="{{ $s }}"
                                            {{ old('status', $post->status) === $s ? 'selected' : '' }}>
                                            {{ ucfirst($s) }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="hidden" name="status" value="{{ $post->status }}">
                                <div class="px-3 py-2 bg-gray-50 dark:bg-gray-900
                                            border border-gray-200 dark:border-gray-700
                                            rounded-lg text-sm text-gray-500 dark:text-gray-400">
                                    {{ ucfirst($post->status) }}
                                    @if($post->status === 'draft')
                                        <span class="text-xs text-gray-400 dark:text-gray-500 block mt-0.5">
                                            An editor will publish your post.
                                        </span>
                                    @endif
                                </div>
                            @endcan
                        </div>

                        @can('publish posts')
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                    Publish Date
                                </label>
                                <input type="datetime-local" name="published_at"
                                       value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}"
                                       class="w-full border border-gray-300 dark:border-gray-600
                                              bg-white dark:bg-gray-900
                                              text-gray-800 dark:text-gray-200
                                              rounded-lg px-3 py-2 text-sm
                                              focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            </div>

                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="is_featured" value="1" id="is_featured"
                                       {{ old('is_featured', $post->is_featured) ? 'checked' : '' }}
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
                                    {{ old('category_id', $post->category_id) == $category->id ? 'selected' : '' }}>
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
                                               {{ in_array($tag->id, old('tags', $postTagIds)) ? 'checked' : '' }}
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

                    {{-- Series Assignment --}}
                    <div class="bg-white dark:bg-gray-800
                        shadow rounded-xl
                        border border-gray-200 dark:border-gray-700
                        p-5">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200
                            border-b border-gray-100 dark:border-gray-700 pb-2 mb-3">
                            Series
                        </h3>

                        @if(isset($seriesList) && $seriesList->isNotEmpty())
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-medium
                               text-gray-600 dark:text-gray-400 mb-1">
                                        Assign to Series
                                    </label>
                                    <select name="series_id"
                                            class="w-full border border-gray-300 dark:border-gray-600
                               bg-white dark:bg-gray-900
                               text-gray-800 dark:text-gray-200
                               rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                        <option value="">— No Series —</option>
                                        @foreach($seriesList as $s)
                                            <option value="{{ $s->id }}"
                                                {{ old('series_id', $postSeries?->id) == $s->id ? 'selected' : '' }}>
                                                {{ $s->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium
                               text-gray-600 dark:text-gray-400 mb-1">
                                        Order in Series
                                    </label>
                                    <input type="number"
                                           name="series_order"
                                           value="{{ old('series_order', $postSeriesOrder ?? 1) }}"
                                           min="1"
                                           class="w-full border border-gray-300 dark:border-gray-600
                              bg-white dark:bg-gray-900
                              text-gray-800 dark:text-gray-200
                              rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                        Position of this post in the series.
                                    </p>
                                </div>
                            </div>
                        @else
                            <p class="text-xs text-gray-400 dark:text-gray-500">
                                No series yet.
                                <a href="{{ route('admin.series.create') }}"
                                   class="text-indigo-500 dark:text-indigo-400 hover:underline">
                                    Create one first
                                </a>.
                            </p>
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

                        @if($post->cover_image)
                            <div class="mb-3">
                                <p class="text-xs text-gray-400 dark:text-gray-500 mb-1">Current image:</p>
                                <img src="{{ $post->cover_image_url }}" alt="Current Cover"
                                     class="w-full h-36 object-cover rounded-lg
                                            border border-gray-200 dark:border-gray-700">
                            </div>
                        @endif

                        <div id="image-preview" class="hidden mb-3">
                            <p class="text-xs text-gray-400 dark:text-gray-500 mb-1">New image:</p>
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
                            Upload new image to replace current. JPG, PNG, WEBP — max 2MB
                        </p>

                        @error('cover_image')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Buttons --}}
                    <div class="flex flex-col gap-2">
                        <button type="submit"
                                class="w-full px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700
                                       text-white text-sm font-medium rounded-lg transition">
                            Update Post
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

    {{-- =============================================
     MEDIA LIBRARY PICKER MODAL
============================================= --}}
    <div id="media-modal"
         class="hidden fixed inset-0 z-50 flex items-center justify-center p-4
            bg-black/70 backdrop-blur-sm">

        <div class="bg-white dark:bg-gray-900
                rounded-2xl w-full max-w-3xl
                flex flex-col shadow-2xl
                border border-gray-200 dark:border-gray-700
                max-h-[85vh]">

            {{-- Modal header --}}
            <div class="flex items-center gap-3 px-5 py-4 shrink-0
                    border-b border-gray-100 dark:border-gray-800">

                <h3 class="text-sm font-semibold text-gray-800 dark:text-white flex-1">
                    Media Library
                </h3>

                <input type="text"
                       id="modal-search"
                       placeholder="Search..."
                       class="border border-gray-200 dark:border-gray-700
                          bg-gray-50 dark:bg-gray-800
                          text-gray-800 dark:text-gray-200
                          placeholder-gray-400 dark:placeholder-gray-500
                          rounded-lg px-3 py-1.5 text-sm w-40
                          focus:outline-none focus:ring-2 focus:ring-indigo-400">

                <button type="button"
                        id="modal-close"
                        class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800
                           flex items-center justify-center
                           hover:bg-gray-200 dark:hover:bg-gray-700
                           transition shrink-0">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

            </div>

            {{-- Scrollable image grid --}}
            <div class="flex-1 overflow-y-auto p-4">

                <div id="modal-grid"
                     class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                    <div class="col-span-full flex items-center justify-center py-12">
                        <p class="text-sm text-gray-400 dark:text-gray-500">
                            Loading...
                        </p>
                    </div>
                </div>

                {{-- Load more --}}
                <div id="modal-load-more" class="hidden text-center mt-4 pt-4
                                              border-t border-gray-100 dark:border-gray-800">
                    <button type="button"
                            class="px-5 py-2 text-sm font-medium
                               text-indigo-600 dark:text-indigo-400
                               border border-indigo-200 dark:border-indigo-800
                               rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950
                               transition">
                        Load more
                    </button>
                </div>

            </div>

            {{-- Modal footer --}}
            <div class="px-5 py-3 shrink-0
                    border-t border-gray-100 dark:border-gray-800
                    flex items-center justify-between">
                <p class="text-xs text-gray-400 dark:text-gray-500">
                    Click an image to insert it at the cursor
                </p>
                <a href="{{ route('admin.media.index') }}"
                   target="_blank"
                   class="text-xs text-indigo-500 dark:text-indigo-400 hover:underline">
                    Manage Library →
                </a>
            </div>

        </div>
    </div>

    <script>
        (function () {

            const modal       = document.getElementById('media-modal');
            const openBtn     = document.getElementById('media-picker-btn');
            const closeBtn    = document.getElementById('modal-close');
            const searchInput = document.getElementById('modal-search');
            const gridEl      = document.getElementById('modal-grid');
            const loadMoreEl  = document.getElementById('modal-load-more');

            if (!modal || !openBtn) return;

            let nextPageUrl  = null;
            let searchTimer  = null;
            let isLoading    = false;

            // ── Open / close ────────────────────────────────────────────────────
            openBtn.addEventListener('click', function () {
                modal.classList.remove('hidden');
                loadImages('{{ route('admin.media.api') }}', true);
            });

            closeBtn?.addEventListener('click',  () => modal.classList.add('hidden'));
            modal.addEventListener('click', e => { if (e.target === modal) modal.classList.add('hidden'); });

            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') modal.classList.add('hidden');
            });

            // ── Live search ─────────────────────────────────────────────────────
            searchInput?.addEventListener('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    const q   = this.value.trim();
                    const url = '{{ route('admin.media.api') }}' + (q ? '?search=' + encodeURIComponent(q) : '');
                    loadImages(url, true);
                }, 300);
            });

            // ── Load more ────────────────────────────────────────────────────────
            loadMoreEl?.addEventListener('click', function () {
                if (nextPageUrl && !isLoading) loadImages(nextPageUrl, false);
            });

            // ── Fetch images from API ────────────────────────────────────────────
            async function loadImages(url, replace) {
                if (isLoading) return;
                isLoading = true;

                if (replace) {
                    gridEl.innerHTML = `
                <div class="col-span-full flex items-center justify-center py-12">
                    <p class="text-sm text-gray-400 dark:text-gray-500">Loading...</p>
                </div>`;
                    loadMoreEl.classList.add('hidden');
                }

                try {
                    const res  = await fetch(url, {
                        headers: {
                            'Accept':           'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const data = await res.json();

                    if (replace) gridEl.innerHTML = '';

                    if (!data.data || !data.data.length) {
                        if (replace) {
                            gridEl.innerHTML = `
                        <div class="col-span-full flex items-center justify-center py-12">
                            <p class="text-sm text-gray-400 dark:text-gray-500">No images found.</p>
                        </div>`;
                        }
                        loadMoreEl.classList.add('hidden');
                        return;
                    }

                    data.data.forEach(item => gridEl.appendChild(buildCard(item)));

                    nextPageUrl = data.next_page_url;
                    loadMoreEl.classList.toggle('hidden', !nextPageUrl);

                } catch (err) {
                    console.error('Media API error:', err);
                    if (replace) {
                        gridEl.innerHTML = `
                    <div class="col-span-full flex items-center justify-center py-12">
                        <p class="text-sm text-red-400">Failed to load images.</p>
                    </div>`;
                    }
                } finally {
                    isLoading = false;
                }
            }

            // ── Build a single image card ─────────────────────────────────────────
            function buildCard(item) {
                const div = document.createElement('div');

                div.className = [
                    'group relative cursor-pointer rounded-xl overflow-hidden',
                    'border-2 border-transparent',
                    'hover:border-indigo-500 dark:hover:border-indigo-400',
                    'transition-all duration-150',
                    'bg-gray-100 dark:bg-gray-800',
                ].join(' ');

                /*
                | aspect-square makes every card a perfect square.
                | object-cover fills it completely — consistent thumbnail sizes.
                */
                div.innerHTML = `
            <div class="aspect-square overflow-hidden relative">
                <img src="${item.url}"
                     alt="${escapeHtml(item.original_name)}"
                     class="w-full h-full object-cover
                            group-hover:scale-105 transition-transform duration-300"
                     loading="lazy">
                <div class="absolute inset-0 bg-black/0
                            group-hover:bg-black/10 transition-colors pointer-events-none">
                </div>
            </div>
            <div class="p-1.5">
                <p class="text-xs font-medium
                           text-gray-700 dark:text-gray-300
                           truncate leading-tight">
                    ${escapeHtml(item.original_name)}
                </p>
                <p class="text-xs text-gray-400 dark:text-gray-500">
                    ${item.formatted_size}
                    ${item.width ? '· ' + item.width + '×' + item.height : ''}
                </p>
            </div>
        `;

                /*
                | INSERT into TipTap on click.
                |
                | window.tiptapEditor must be set in editor.js.
                | We call setImage() which is built into TipTap's Image extension.
                | If the extension is not configured, we fall back to inserting
                | an HTML string via insertContent().
                */
                div.addEventListener('click', function () {
                    const editor = window.tiptapEditor;

                    if (!editor) {
                        console.error(
                            'window.tiptapEditor is not defined. ' +
                            'Add "window.tiptapEditor = editor" in your editor.js after the Editor is created.'
                        );
                        alert('Editor not ready. Please ensure you are on the post create/edit page.');
                        return;
                    }

                    try {
                        if (editor.can().setImage({ src: item.url })) {
                            editor.chain().focus().setImage({
                                src: item.url,
                                alt: item.original_name,
                            }).run();
                        } else {
                            /*
                            | Fallback: insert as raw HTML.
                            | Works even if the Image extension uses a different command name.
                            */
                            editor.chain().focus().insertContent(
                                `<img src="${item.url}" alt="${escapeHtml(item.original_name)}">`
                            ).run();
                        }
                    } catch (err) {
                        console.error('TipTap insert error:', err);
                    }

                    modal.classList.add('hidden');
                });

                return div;
            }

            function escapeHtml(str) {
                return String(str)
                    .replace(/&/g,'&amp;').replace(/</g,'&lt;')
                    .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
            }

        })();
    </script>

    <script>
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
