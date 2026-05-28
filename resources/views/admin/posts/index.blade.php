<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Posts</h2>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                    {{ now()->format('l, d F Y') }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4">

        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-100 dark:bg-green-900
                        border border-green-300 dark:border-green-700
                        text-green-800 dark:text-green-300 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.posts.index') }}"
              class="mb-5 flex flex-wrap gap-3 items-center">

            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search by title..."
                   class="border border-gray-300 dark:border-gray-600
                          bg-white dark:bg-gray-800
                          text-gray-800 dark:text-gray-200
                          placeholder-gray-400 dark:placeholder-gray-500
                          rounded-lg px-4 py-2 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-400 w-64">

            <select name="status"
                    class="border border-gray-300 dark:border-gray-600
                           bg-white dark:bg-gray-800
                           text-gray-800 dark:text-gray-200
                           rounded-lg px-4 py-2 text-sm
                           focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">All Statuses</option>
                <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
            </select>

            <button type="submit"
                    class="px-4 py-2 bg-gray-700 dark:bg-gray-600
                           hover:bg-gray-800 dark:hover:bg-gray-500
                           text-white text-sm rounded-lg transition">
                Filter
            </button>

            @if(request('search') || request('status'))
                <a href="{{ route('admin.posts.index') }}"
                   class="text-sm text-red-500 dark:text-red-400 hover:underline">Clear</a>
            @endif
        </form>

        {{-- Top bar: Trash link + Bulk actions + New Post --}}
        <div class="flex items-center justify-between mb-3 flex-wrap gap-3">

            {{-- Bulk action form — wraps the entire table --}}
            <form id="bulk-form"
                  action="{{ route('admin.posts.bulk') }}"
                  method="POST"
                  class="flex items-center gap-3">
                @csrf

                <select name="action"
                        id="bulk-action-select"
                        class="border border-gray-300 dark:border-gray-600
                               bg-white dark:bg-gray-800
                               text-gray-800 dark:text-gray-200
                               rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="">Bulk Actions</option>
                    @can('publish posts')
                        <option value="publish">Publish Selected</option>
                        <option value="draft">Set to Draft</option>
                    @endcan
                    @can('delete own posts')
                        <option value="trash">Move to Trash</option>
                    @endcan
                    @can('delete all posts')
                        <option value="delete">Delete Permanently</option>
                    @endcan
                </select>

                <button type="button"
                        id="bulk-apply-btn"
                        class="px-4 py-2 bg-gray-700 dark:bg-gray-600
                               hover:bg-gray-800 dark:hover:bg-gray-500
                               text-white text-sm rounded-lg transition">
                    Apply
                </button>

                <span id="selected-count"
                      class="text-xs text-gray-400 dark:text-gray-500">
                    0 selected
                </span>
            </form>

            <div class="flex items-center gap-3">

                @php
                    $trashedCount = auth()->user()->can('delete all posts')
                        ? \App\Models\Post::onlyTrashed()->count()
                        : \App\Models\Post::onlyTrashed()->where('user_id', auth()->id())->count();
                @endphp

                @if($trashedCount > 0)
                    <a href="{{ route('admin.posts.trash') }}"
                       class="flex items-center gap-1.5 px-3 py-2 text-sm
                              text-red-600 dark:text-red-400
                              bg-red-50 dark:bg-red-950
                              border border-red-200 dark:border-red-800
                              rounded-lg hover:bg-red-100 dark:hover:bg-red-900
                              transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Trash ({{ $trashedCount }})
                    </a>
                @else
                    <a href="{{ route('admin.posts.trash') }}"
                       class="flex items-center gap-1.5 px-3 py-2 text-sm
                              text-gray-500 dark:text-gray-400
                              bg-gray-50 dark:bg-gray-800
                              border border-gray-200 dark:border-gray-700
                              rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700
                              transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Trash
                    </a>
                @endif

                @can('create posts')
                    <a href="{{ route('admin.posts.create') }}"
                       class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700
                              text-white text-sm rounded-lg transition">
                        + New Post
                    </a>
                @endcan
            </div>
        </div>

        {{-- Posts Table --}}
        <div class="bg-white dark:bg-gray-800
                    shadow rounded-xl overflow-hidden
                    border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-900
                              text-gray-500 dark:text-gray-400
                              uppercase text-xs tracking-wider">
                <tr>
                    {{-- Select All checkbox --}}
                    <th class="px-4 py-4 w-10">
                        <input type="checkbox"
                               id="select-all"
                               class="rounded border-gray-300 dark:border-gray-600
                                          text-indigo-600 focus:ring-indigo-400">
                    </th>
                    <th class="px-3 py-2">Cover</th>
                    <th class="px-3 py-2">Title</th>
                    <th class="px-3 py-2">Category</th>
                    <th class="px-3 py-2">Status</th>
                    <th class="px-3 py-2">Featured</th>
                    <th class="px-3 py-2">Views</th>
                    <th class="px-3 py-2">Author</th>
                    <th class="px-3 py-2">Created</th>
                    <th class="px-3 py-2 text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($posts as $post)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition align-middle">

                        {{-- Row checkbox --}}
                        <td class="px-3 py-2">
                            <input type="checkbox"
                                   name="post_ids[]"
                                   value="{{ $post->id }}"
                                   form="bulk-form"
                                   class="post-checkbox rounded border-gray-300 dark:border-gray-600
                                              text-indigo-600 focus:ring-indigo-400">
                        </td>

                        <td class="px-3 py-2">
                            <img src="{{ $post->cover_image_url }}"
                                 alt="{{ $post->title }}"
                                 data-single-lightbox="{{ $post->cover_image_url }}"
                                 data-single-lightbox-name="{{ $post->title }}"
                                 class="w-12 h-12 object-cover rounded-lg
                                    border border-gray-200 dark:border-gray-700
                                    cursor-pointer hover:opacity-80 transition-opacity">
                        </td>

                        <td class="px-3 py-2">
                            <div class="font-medium text-gray-800 dark:text-gray-200">
                                {{ Str::limit($post->title, 45) }}
                            </div>
                            <div class="text-xs text-gray-400 dark:text-gray-500 font-mono">
                                {{ $post->slug }}
                            </div>
                        </td>

                        <td class="px-3 py-2 text-gray-600 dark:text-gray-400">
                            {{ $post->category->name ?? '—' }}
                        </td>

                        <td class="px-3 py-2">
                            @php
                                $badgeColor = match($post->status) {
                                    'published' => 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400',
                                    'scheduled' => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-400',
                                    default     => 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400',
                                };
                            @endphp
                            <div>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeColor }}">
                                        {{ ucfirst($post->status) }}
                                    </span>
                                @if($post->status === 'scheduled' && $post->published_at)
                                    <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                                        🕐 {{ $post->published_at->format('d M H:i') }}
                                    </p>
                                @endif
                            </div>
                        </td>

                        <td class="px-3 py-2">
                            @if($post->is_featured)
                                <span class="text-yellow-500 text-lg">★</span>
                            @else
                                <span class="text-gray-300 dark:text-gray-600 text-lg">☆</span>
                            @endif
                        </td>

                        <td class="px-3 py-2 text-gray-600 dark:text-gray-400 text-xs">
                            {{ $post->formatted_views }}
                        </td>

                        <td class="px-3 py-2 text-gray-600 dark:text-gray-400">
                            {{ $post->user->name ?? '—' }}
                        </td>

                        <td class="px-3 py-2 text-gray-400 dark:text-gray-500 text-xs">
                            {{ $post->created_at->format('d M Y') }}
                        </td>

                        <td class="px-3 py-2 text-right space-x-1 whitespace-nowrap">
                            <a href="{{ route('admin.posts.show', $post) }}"
                               class="text-gray-500 dark:text-gray-400
                                          hover:text-gray-800 dark:hover:text-white
                                          text-xs font-medium">
                                View
                            </a>

                            @if(auth()->user()->can('edit all posts') ||
                               (auth()->user()->can('edit own posts') && $post->user_id === auth()->id()))
                                <a href="{{ route('admin.posts.edit', $post) }}"
                                   class="text-indigo-600 dark:text-indigo-400
                                              hover:underline text-xs font-medium">
                                    Edit
                                </a>
                            @endif

                            @if(auth()->user()->can('delete all posts') ||
                               (auth()->user()->can('delete own posts') && $post->user_id === auth()->id()))
                                <form action="{{ route('admin.posts.destroy', $post) }}"
                                      method="POST" class="inline"
                                      onsubmit="return confirm('Move this post to trash?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-500 dark:text-red-400
                                                       hover:underline text-xs font-medium">
                                        Trash
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10"
                            class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
                            No posts found.
                            <a href="{{ route('admin.posts.create') }}"
                               class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                Create one
                            </a>.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $posts->links() }}
        </div>

    </div>

    {{-- Single-image lightbox (no navigation, used for post cover thumbnails) --}}
    <div id="single-lightbox"
         class="hidden fixed inset-0 z-50 flex items-center justify-center
            bg-black/85 backdrop-blur-sm cursor-pointer"
         onclick="document.getElementById('single-lightbox').classList.add('hidden');
              document.body.style.overflow='';">

        <button type="button"
                class="absolute top-4 right-4 w-10 h-10 rounded-full
                   bg-white/10 hover:bg-white/20
                   flex items-center justify-center transition">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <img id="single-lb-img" src="" alt=""
             class="max-w-[80vw] max-h-[80vh] rounded-2xl shadow-2xl object-contain"
             onclick="event.stopPropagation()">

        <p id="single-lb-caption"
           class="absolute bottom-6 left-1/2 -translate-x-1/2
              text-white text-sm bg-black/40 rounded-lg px-4 py-2 text-center max-w-md">
        </p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const lb      = document.getElementById('single-lightbox');
            const lbImg   = document.getElementById('single-lb-img');
            const lbCap   = document.getElementById('single-lb-caption');

            document.querySelectorAll('[data-single-lightbox]').forEach(function (img) {
                img.addEventListener('click', function () {
                    lbImg.src            = this.dataset.singleLightbox;
                    lbImg.alt            = this.dataset.singleLightboxName;
                    lbCap.textContent    = this.dataset.singleLightboxName;
                    lb.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                });
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !lb.classList.contains('hidden')) {
                    lb.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            });
        });
    </script>

    <script>
        /*
        |--------------------------------------------------------------------------
        | Bulk Post Actions JavaScript
        |--------------------------------------------------------------------------
        */
        document.addEventListener('DOMContentLoaded', function () {

            const selectAll     = document.getElementById('select-all');
            const checkboxes    = document.querySelectorAll('.post-checkbox');
            const selectedCount = document.getElementById('selected-count');
            const applyBtn      = document.getElementById('bulk-apply-btn');
            const actionSelect  = document.getElementById('bulk-action-select');
            const bulkForm      = document.getElementById('bulk-form');

            /*
            | Update the "X selected" counter when any checkbox changes
            */
            function updateCount() {
                const checked = document.querySelectorAll('.post-checkbox:checked').length;
                selectedCount.textContent = checked + ' selected';
            }

            /*
            | Select All checkbox — checks/unchecks all row checkboxes
            */
            selectAll.addEventListener('change', function () {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateCount();
            });

            /*
            | Individual checkbox — update count and sync select-all state
            */
            checkboxes.forEach(function (cb) {
                cb.addEventListener('change', function () {
                    updateCount();

                    /*
                    | If all are checked, check the select-all box.
                    | If any is unchecked, uncheck the select-all box.
                    */
                    const allChecked = document.querySelectorAll('.post-checkbox:checked').length === checkboxes.length;
                    selectAll.checked = allChecked;
                });
            });

            /*
            | Apply button — validate then submit with confirmation for destructive actions
            */
            applyBtn.addEventListener('click', function () {
                const action  = actionSelect.value;
                const checked = document.querySelectorAll('.post-checkbox:checked').length;

                if (!action) {
                    alert('Please select an action from the dropdown.');
                    return;
                }

                if (checked === 0) {
                    alert('Please select at least one post.');
                    return;
                }

                /*
                | Require confirmation for destructive actions.
                */
                if (action === 'trash' || action === 'delete') {
                    const word    = action === 'delete' ? 'permanently delete' : 'move to trash';
                    const plural  = checked === 1 ? 'post' : 'posts';
                    const confirm = window.confirm(
                        `Are you sure you want to ${word} ${checked} ${plural}?\n\n` +
                        (action === 'delete' ? 'This action CANNOT be undone.' : 'They can be restored from trash.')
                    );
                    if (!confirm) return;
                }

                bulkForm.submit();
            });

        });
    </script>


</x-app-layout>
