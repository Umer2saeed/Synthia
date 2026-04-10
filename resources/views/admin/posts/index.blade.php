<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Posts</h2>
                <p class="text-xs text-gray-400 mt-0.5">{{ now()->format('l, d F Y') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4">

        {{-- Flash Success Message --}}
        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Filters: Search + Status --}}
        <form method="GET" action="{{ route('admin.posts.index') }}"
              class="mb-5 flex flex-wrap gap-3 items-center">

            {{-- Search input --}}
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search by title..."
                   class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 w-64">

            {{-- Status dropdown filter --}}
            <select name="status"
                    class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">All Statuses</option>
                <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
            </select>

            <button type="submit"
                    class="px-4 py-2 bg-gray-700 text-white text-sm rounded-lg hover:bg-gray-800 transition">
                Filter
            </button>

            {{-- Clear filters link --}}
            @if(request('search') || request('status'))
                <a href="{{ route('admin.posts.index') }}"
                   class="text-sm text-red-500 hover:underline">Clear</a>
            @endif
        </form>

        <div class="flex justify-end mb-2">
            <div class="flex items-center gap-3">

                {{-- Trash link — shows count of trashed posts --}}
                @php
                    $trashedCount = auth()->user()->can('delete all posts')
                        ? \App\Models\Post::onlyTrashed()->count()
                        : \App\Models\Post::onlyTrashed()->where('user_id', auth()->id())->count();
                @endphp

                @if($trashedCount > 0)
                    <a href="{{ route('admin.posts.trash') }}"
                       class="flex items-center gap-1.5 px-3 py-2 text-sm text-red-600
                          bg-red-50 border border-red-200 rounded-lg
                          hover:bg-red-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Trash ({{ $trashedCount }})
                    </a>
                @else
                    <a href="{{ route('admin.posts.trash') }}"
                       class="flex items-center gap-1.5 px-3 py-2 text-sm text-gray-500
                          bg-gray-50 border border-gray-200 rounded-lg
                          hover:bg-gray-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Trash
                    </a>
                @endif

                @can('create posts')
                    <a href="{{ route('admin.posts.create') }}"
                       class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg
                          hover:bg-indigo-700 transition">
                        + New Post
                    </a>
                @endcan
            </div>
        </div>

        {{-- Posts Table --}}
        <div class="bg-white shadow rounded-xl overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-5 py-4">Cover</th>
                    <th class="px-5 py-4">Title</th>
                    <th class="px-5 py-4">Category</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4">Featured</th>
                    <th class="px-5 py-4">Author</th>
                    <th class="px-5 py-4">Created</th>
                    <th class="px-5 py-4 text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($posts as $post)
                    <tr class="hover:bg-gray-50 transition align-middle">

                        {{-- Cover Thumbnail --}}
                        <td class="px-5 py-3">
                            <img src="{{ $post->cover_image_url }}"
                                 alt="{{ $post->title }}"
                                 class="w-12 h-12 object-cover rounded-lg border border-gray-200">
                        </td>

                        {{-- Title + Slug --}}
                        <td class="px-5 py-3">
                            <div class="font-medium text-gray-800">{{ Str::limit($post->title, 45) }}</div>
                            <div class="text-xs text-gray-400 font-mono">{{ $post->slug }}</div>
                        </td>

                        {{-- Category --}}
                        <td class="px-5 py-3 text-gray-600">
                            {{ $post->category->name ?? '—' }}
                        </td>

                        {{-- Status Badge --}}
                        <td class="px-5 py-3">
                            @php
                                $badgeColor = match($post->status) {
                                    'published' => 'bg-green-100 text-green-700',
                                    'scheduled' => 'bg-yellow-100 text-yellow-700',
                                    default     => 'bg-gray-100 text-gray-500',
                                };
                            @endphp

                            <div>
        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeColor }}">
            {{ ucfirst($post->status) }}
        </span>

                                {{--
                                | Show publish date under the badge for scheduled posts.
                                | This tells the editor exactly when the post will go live
                                | without having to open the edit form.
                                --}}
                                @if($post->status === 'scheduled' && $post->published_at)
                                    <p class="text-xs text-yellow-600 mt-1"
                                       title="Will publish at {{ $post->published_at->format('d M Y H:i') }}">
                                        🕐 {{ $post->published_at->format('d M H:i') }}
                                    </p>
                                @endif
                            </div>
                        </td>

                        {{-- Featured --}}
                        <td class="px-5 py-3">
                            @if($post->is_featured)
                                <span class="text-yellow-500 text-lg" title="Featured">★</span>
                            @else
                                <span class="text-gray-300 text-lg">☆</span>
                            @endif
                        </td>

                        {{-- Author --}}
                        <td class="px-5 py-3 text-gray-600">
                            {{ $post->user->name ?? '—' }}
                        </td>

                        {{-- Date --}}
                        <td class="px-5 py-3 text-gray-400 text-xs">
                            {{ $post->created_at->format('d M Y') }}
                        </td>

                        {{-- Actions --}}
                        {{-- Actions --}}
                        <td class="px-5 py-3 text-right space-x-2 whitespace-nowrap">

                            {{-- View: anyone who can see the list can view the detail --}}
                            <a href="{{ route('admin.posts.show', $post) }}"
                               class="text-gray-500 hover:text-gray-800 text-xs font-medium">
                                View
                            </a>

                            {{--
                            | Edit: show if user can edit all posts (admin/editor)
                            | OR if user owns this post and can edit own posts (author)
                            --}}
                            @if(auth()->user()->can('edit all posts') ||
                               (auth()->user()->can('edit own posts') && $post->user_id === auth()->id()))
                                <a href="{{ route('admin.posts.edit', $post) }}"
                                   class="text-indigo-600 hover:underline text-xs font-medium">
                                    Edit
                                </a>
                            @endif

                            {{--
                            | Delete: show if user can delete all posts (admin/editor)
                            | OR if user owns this post and can delete own posts (author)
                            --}}
                            @if(auth()->user()->can('delete all posts') ||
                               (auth()->user()->can('delete own posts') && $post->user_id === auth()->id()))
                                <form action="{{ route('admin.posts.destroy', $post) }}"
                                      method="POST" class="inline"
                                      onsubmit="return confirm('Move this post to trash?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-500 hover:underline text-xs font-medium">
                                        Trash
                                    </button>
                                </form>
                            @endif

                        </td>


                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                            No posts found.
                            <a href="{{ route('admin.posts.create') }}" class="text-indigo-600 hover:underline">Create one</a>.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-5">
            {{ $posts->links() }}
        </div>

    </div>
</x-app-layout>
