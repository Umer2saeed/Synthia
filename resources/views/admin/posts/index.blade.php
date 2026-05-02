<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Posts</h2>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ now()->format('l, d F Y') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4">

        {{-- Flash Success Message --}}
        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-100 dark:bg-green-900 border border-green-300 dark:border-green-700
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
                <option value="">All</option>
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

        <div class="flex justify-end mb-2">
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
                              rounded-lg hover:bg-red-100 dark:hover:bg-red-900 transition-colors">
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
                              rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
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
                    <th class="px-5 py-4">Cover</th>
                    <th class="px-5 py-4">Title</th>
                    <th class="px-5 py-4">Category</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4">Featured</th>
                    <th class="px-5 py-4">Author</th>
                    <th class="px-5 py-4">Views</th>
                    <th class="px-5 py-4">Created</th>
                    <th class="px-5 py-4 text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-400 dark:divide-gray-700">
                @forelse($posts as $post)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition align-middle">

                        <td class="px-5 py-3">
                            <img src="{{ $post->cover_image_url }}"
                                 alt="{{ $post->title }}"
                                 class="w-12 h-12 object-cover rounded-lg
                                            border border-gray-200 dark:border-gray-700">
                        </td>

                        <td class="px-5 py-3">
                            <div class="font-medium text-gray-800 dark:text-gray-200">
                                {{ Str::limit($post->title, 45) }}
                            </div>
                            <div class="text-xs text-gray-400 dark:text-gray-500 font-mono">
                                {{ $post->slug }}
                            </div>
                        </td>

                        <td class="px-5 py-3 text-gray-600 dark:text-gray-400">
                            {{ $post->category->name ?? '—' }}
                        </td>

                        <td class="px-5 py-3">
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

                        <td class="px-5 py-3">
                            @if($post->is_featured)
                                <span class="text-yellow-500 text-lg" title="Featured">★</span>
                            @else
                                <span class="text-gray-300 dark:text-gray-600 text-lg">☆</span>
                            @endif
                        </td>

                        <td class="px-5 py-3 text-gray-600 dark:text-gray-400">
                            {{ $post->user->name ?? '—' }}
                        </td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-400 text-xs">
                            {{ $post->formatted_views }}
                        </td>
                        <td class="px-5 py-3 text-gray-400 dark:text-gray-500 text-xs">
                            {{ $post->created_at->format('d M Y') }}
                        </td>

                        <td class="px-5 py-3 text-right space-x-2 whitespace-nowrap">

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
                        <td colspan="8" class="px-6 py-12 text-center
                                                   text-gray-400 dark:text-gray-500">
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
</x-app-layout>
