<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                    Media Library
                </h2>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                    {{ number_format($totalFiles) }} {{ Str::plural('file', $totalFiles) }}
                    ·
                    {{ $totalSize >= 1048576
                        ? round($totalSize / 1048576, 1) . ' MB'
                        : round($totalSize / 1024, 1) . ' KB' }}
                    used
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4">

        @if(session('success'))
            <div class="mb-5 px-4 py-3 bg-green-100 dark:bg-green-900
                        border border-green-300 dark:border-green-700
                        text-green-800 dark:text-green-300 rounded-xl text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- =============================================
             TOOLBAR
        ============================================= --}}
        <div class="flex flex-wrap items-center gap-3 mb-6">

            <form method="GET" action="{{ route('admin.media.index') }}"
                  class="flex flex-wrap gap-3 flex-1 min-w-0">

                <input type="text" name="search"
                       value="{{ request('search') }}"
                       placeholder="Search filename..."
                       class="border border-gray-300 dark:border-gray-600
                              bg-white dark:bg-gray-800
                              text-gray-800 dark:text-gray-200
                              placeholder-gray-400 dark:placeholder-gray-500
                              rounded-xl px-4 py-2 text-sm w-52
                              focus:outline-none focus:ring-2 focus:ring-indigo-400">

                <input type="date" name="date"
                       value="{{ request('date') }}"
                       class="border border-gray-300 dark:border-gray-600
                              bg-white dark:bg-gray-800
                              text-gray-800 dark:text-gray-200
                              rounded-xl px-4 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-indigo-400">

                <button type="submit"
                        class="px-4 py-2 bg-gray-700 dark:bg-gray-600
                               hover:bg-gray-800 dark:hover:bg-gray-500
                               text-white text-sm rounded-xl transition">
                    Filter
                </button>

                @if(request('search') || request('date'))
                    <a href="{{ route('admin.media.index') }}"
                       class="text-sm text-red-500 dark:text-red-400
                              hover:underline self-center">
                        Clear
                    </a>
                @endif
            </form>

            {{-- Upload button --}}
            <label class="flex items-center gap-2 cursor-pointer
                           px-4 py-2 bg-indigo-600 hover:bg-indigo-700
                           text-white text-sm font-medium rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Upload
                <input type="file" id="upload-input" class="hidden"
                       accept="image/*" multiple>
            </label>

        </div>

        {{-- Upload progress bar --}}
        <div id="upload-area" class="hidden mb-5
                                     bg-white dark:bg-gray-800
                                     border border-gray-200 dark:border-gray-700
                                     rounded-xl p-4 space-y-2">
        </div>

        {{-- =============================================
             BULK ACTION BAR
        ============================================= --}}
        <form id="bulk-form"
              action="{{ route('admin.media.bulk-destroy') }}"
              method="POST">
            @csrf

            <div class="flex items-center gap-4 mb-4
                        px-4 py-3
                        bg-white dark:bg-gray-800
                        border border-gray-200 dark:border-gray-700
                        rounded-xl">

                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" id="select-all"
                           class="w-4 h-4 rounded border-gray-300 dark:border-gray-600
                                  text-indigo-600 focus:ring-indigo-400">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        Select All
                    </span>
                </label>

                <span id="selected-count"
                      class="text-xs text-gray-400 dark:text-gray-500 ml-1">
                    0 selected
                </span>

                <button type="button"
                        id="bulk-delete-btn"
                        class="ml-auto hidden px-3 py-1.5 text-xs font-medium
                               text-red-600 dark:text-red-400
                               border border-red-200 dark:border-red-800
                               rounded-lg hover:bg-red-50 dark:hover:bg-red-950
                               transition">
                    🗑 Delete Selected
                </button>

            </div>

            {{-- =============================================
                 MEDIA GRID
            ============================================= --}}
            @if($media->isEmpty())
                <div class="flex flex-col items-center justify-center py-28
                            bg-white dark:bg-gray-800
                            rounded-2xl border border-gray-200 dark:border-gray-700">
                    <svg class="w-14 h-14 text-gray-300 dark:text-gray-600 mb-4"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586
                                 a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2
                                 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-gray-400 dark:text-gray-500 text-sm font-medium">
                        No images yet
                    </p>
                    <p class="text-gray-300 dark:text-gray-600 text-xs mt-1">
                        Click Upload to add your first image
                    </p>
                </div>
            @else
                {{--
                | 8 columns on large screens — each thumbnail is compact.
                | aspect-square ensures every cell is a perfect square
                | regardless of the image's actual dimensions.
                --}}
                <div id="media-grid"
                     class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6
                            lg:grid-cols-8 gap-3">
                    @foreach($media as $item)
                        @include('admin.media._item', ['item' => $item])
                    @endforeach
                </div>
            @endif

        </form>

        @if($media->hasPages())
            <div class="mt-6">{{ $media->links() }}</div>
        @endif

    </div>

    {{-- =============================================
         LIGHTBOX
         Shown when user clicks an image thumbnail.
    ============================================= --}}
    <div id="lightbox"
         class="hidden fixed inset-0 z-50 flex items-center justify-center
                bg-black/90 backdrop-blur-sm">

        {{-- Close button --}}
        <button type="button" id="lb-close"
                class="absolute top-4 right-4 w-10 h-10 rounded-full
                       bg-white/10 hover:bg-white/20
                       flex items-center justify-center transition">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        {{-- Previous --}}
        <button type="button" id="lb-prev"
                class="absolute left-4 top-1/2 -translate-y-1/2
                       w-10 h-10 rounded-full bg-white/10 hover:bg-white/20
                       flex items-center justify-center transition">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>

        {{-- Next --}}
        <button type="button" id="lb-next"
                class="absolute right-4 top-1/2 -translate-y-1/2
                       w-10 h-10 rounded-full bg-white/10 hover:bg-white/20
                       flex items-center justify-center transition">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>

        {{-- Image --}}
        <div class="max-w-5xl max-h-[85vh] px-16 w-full flex items-center justify-center">
            <img id="lb-img" src="" alt=""
                 class="max-w-full max-h-[75vh] rounded-xl shadow-2xl object-contain">
        </div>

        {{-- Caption --}}
        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 text-center">
            <p id="lb-caption"
               class="text-white text-sm font-medium bg-black/40 rounded-lg px-4 py-2"></p>
            <p id="lb-meta"
               class="text-white/60 text-xs mt-1"></p>
        </div>

    </div>

    {{-- =============================================
         JAVASCRIPT
    ============================================= --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const csrfToken   = document.querySelector('meta[name="csrf-token"]')?.content;
            const uploadInput = document.getElementById('upload-input');
            const uploadArea  = document.getElementById('upload-area');
            const grid        = document.getElementById('media-grid');
            const selectAll   = document.getElementById('select-all');
            const countLabel  = document.getElementById('selected-count');
            const bulkBtn     = document.getElementById('bulk-delete-btn');
            const bulkForm    = document.getElementById('bulk-form');

            // ── LIGHTBOX ───────────────────────────────────────────────────────
            const lightbox  = document.getElementById('lightbox');
            const lbImg     = document.getElementById('lb-img');
            const lbCaption = document.getElementById('lb-caption');
            const lbMeta    = document.getElementById('lb-meta');
            const lbPrev    = document.getElementById('lb-prev');
            const lbNext    = document.getElementById('lb-next');
            const lbClose   = document.getElementById('lb-close');

            let lbItems   = []; // array of { url, name, size, dims }
            let lbCurrent = 0;

            function buildLightboxData() {
                lbItems = Array.from(document.querySelectorAll('[data-lb-url]')).map(el => ({
                    url:  el.dataset.lbUrl,
                    name: el.dataset.lbName,
                    size: el.dataset.lbSize,
                    dims: el.dataset.lbDims,
                }));
            }

            function openLightbox(index) {
                buildLightboxData();
                lbCurrent = index;
                showLightboxItem();
                lightbox.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeLightbox() {
                lightbox.classList.add('hidden');
                document.body.style.overflow = '';
            }

            function showLightboxItem() {
                const item = lbItems[lbCurrent];
                if (!item) return;
                lbImg.src        = item.url;
                lbImg.alt        = item.name;
                lbCaption.textContent = item.name;
                lbMeta.textContent    = [item.size, item.dims].filter(Boolean).join(' · ');
                lbPrev.style.visibility = lbCurrent === 0 ? 'hidden' : 'visible';
                lbNext.style.visibility = lbCurrent === lbItems.length - 1 ? 'hidden' : 'visible';
            }

            lbClose?.addEventListener('click', closeLightbox);
            lightbox?.addEventListener('click', e => { if (e.target === lightbox) closeLightbox(); });

            lbPrev?.addEventListener('click', () => {
                if (lbCurrent > 0) { lbCurrent--; showLightboxItem(); }
            });

            lbNext?.addEventListener('click', () => {
                if (lbCurrent < lbItems.length - 1) { lbCurrent++; showLightboxItem(); }
            });

            // Keyboard navigation
            document.addEventListener('keydown', e => {
                if (lightbox.classList.contains('hidden')) return;
                if (e.key === 'Escape')      closeLightbox();
                if (e.key === 'ArrowLeft')   { if (lbCurrent > 0) { lbCurrent--; showLightboxItem(); } }
                if (e.key === 'ArrowRight')  { if (lbCurrent < lbItems.length - 1) { lbCurrent++; showLightboxItem(); } }
            });

            // Bind click on all thumbnail images
            function bindThumbnailClicks() {
                document.querySelectorAll('[data-lb-index]').forEach(el => {
                    el.addEventListener('click', function () {
                        openLightbox(parseInt(this.dataset.lbIndex));
                    });
                });
            }

            bindThumbnailClicks();

            // ── UPLOAD ─────────────────────────────────────────────────────────
            uploadInput?.addEventListener('change', async function () {
                const files = Array.from(this.files);
                if (!files.length) return;

                uploadArea.classList.remove('hidden');

                for (const file of files) {
                    const row = createProgressRow(file.name);
                    uploadArea.appendChild(row);

                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('_token', csrfToken);

                    try {
                        const res  = await fetch('{{ route('admin.media.store') }}', {
                            method:  'POST',
                            headers: { 'Accept': 'application/json' },
                            body:    formData,
                        });
                        const data = await res.json();

                        if (data.success) {
                            setRowStatus(row, '✓ Uploaded', 'text-green-500 dark:text-green-400');
                            if (grid) prependGridItem(data.media);
                        } else {
                            setRowStatus(row, '✗ Failed', 'text-red-500 dark:text-red-400');
                        }
                    } catch {
                        setRowStatus(row, '✗ Error', 'text-red-500 dark:text-red-400');
                    }
                }

                uploadInput.value = '';
                // Rebuild lightbox data to include newly uploaded images
                buildLightboxData();
            });

            function createProgressRow(name) {
                const row   = document.createElement('div');
                row.className = 'flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400';
                row.innerHTML = `
                <svg class="w-4 h-4 shrink-0 animate-spin text-indigo-500"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="2" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                <span class="truncate">${escapeHtml(name)}</span>
                <span class="status ml-auto shrink-0 font-medium">Uploading...</span>
            `;
                return row;
            }

            function setRowStatus(row, text, classes) {
                const spinner = row.querySelector('svg');
                const status  = row.querySelector('.status');
                spinner.classList.add('hidden');
                status.textContent = text;
                status.className   = 'status ml-auto shrink-0 font-medium ' + classes;
            }

            function prependGridItem(media) {
                if (!grid) return;

                const index = document.querySelectorAll('[data-lb-index]').length;

                const wrapper = document.createElement('div');
                wrapper.className = 'group relative rounded-xl overflow-hidden cursor-pointer select-none';
                wrapper.innerHTML = `
                <div class="relative aspect-square overflow-hidden
                            bg-gray-100 dark:bg-gray-700
                            border border-gray-200 dark:border-gray-700
                            hover:border-indigo-400 dark:hover:border-indigo-600 transition-colors">
                    <input type="checkbox" name="ids[]" value="${media.id}"
                           class="media-checkbox absolute top-2 left-2 z-20
                                  w-4 h-4 rounded border-2 border-white dark:border-gray-300
                                  bg-white/80 dark:bg-gray-800/80
                                  text-indigo-600 focus:ring-indigo-400
                                  opacity-0 group-hover:opacity-100 transition-opacity"
                           onclick="event.stopPropagation()">
                    <img src="${media.url}"
                         alt="${escapeHtml(media.original_name)}"
                         data-lb-index="${index}"
                         data-lb-url="${media.url}"
                         data-lb-name="${escapeHtml(media.original_name)}"
                         data-lb-size="${media.formatted_size}"
                         data-lb-dims="${media.width && media.height ? media.width + '×' + media.height : ''}"
                         class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                         loading="lazy">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors"></div>
                </div>
                <div class="pt-1.5 pb-1 px-0.5">
                    <p class="text-xs font-medium text-gray-700 dark:text-gray-300 truncate">
                        ${escapeHtml(media.original_name)}
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">
                        ${media.formatted_size}${media.width ? ' · ' + media.width + '×' + media.height : ''}
                    </p>
                </div>
            `;

                grid.prepend(wrapper);
                rebindCheckboxes();
                bindThumbnailClicks();
            }

            // ── SELECT / BULK DELETE ────────────────────────────────────────────
            selectAll?.addEventListener('change', function () {
                document.querySelectorAll('.media-checkbox').forEach(cb => {
                    cb.checked = this.checked;
                });
                updateCount();
            });

            function rebindCheckboxes() {
                document.querySelectorAll('.media-checkbox').forEach(cb => {
                    cb.removeEventListener('change', updateCount);
                    cb.addEventListener('change', updateCount);
                });
            }

            function updateCount() {
                const checked = document.querySelectorAll('.media-checkbox:checked').length;
                countLabel.textContent = checked + ' selected';
                bulkBtn.classList.toggle('hidden', checked === 0);
                selectAll.checked = checked > 0 &&
                    checked === document.querySelectorAll('.media-checkbox').length;
            }

            bulkBtn?.addEventListener('click', function () {
                const count = document.querySelectorAll('.media-checkbox:checked').length;
                if (!count) return;
                if (confirm(`Permanently delete ${count} image${count > 1 ? 's' : ''}? This cannot be undone.`)) {
                    bulkForm.submit();
                }
            });

            rebindCheckboxes();

            function escapeHtml(str) {
                return String(str)
                    .replace(/&/g,'&amp;').replace(/</g,'&lt;')
                    .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
            }

        });
    </script>

</x-app-layout>
