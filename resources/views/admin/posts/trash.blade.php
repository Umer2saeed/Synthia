<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Trash</h2>
                <p class="text-xs text-gray-400 mt-0.5">
                    Soft-deleted posts. Restore or permanently delete them.
                </p>
            </div>
            <a href="{{ route('admin.posts.index') }}"
               class="flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700
                      text-sm rounded-lg hover:bg-gray-200 transition">
                ← Back to Posts
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-5 px-4 py-3 bg-green-50 border border-green-200
                        text-green-800 text-sm rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 px-4 py-3 bg-red-50 border border-red-200
                        text-red-700 text-sm rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        {{-- Info Banner --}}
        <div class="mb-5 px-4 py-3 bg-amber-50 border border-amber-200
                    text-amber-700 text-sm rounded-lg flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Posts in trash can be restored at any time. Permanently deleted posts
            cannot be recovered.
        </div>

        {{-- Search within trash --}}
        <form method="GET" action="{{ route('admin.posts.trash') }}"
              class="mb-5 flex gap-3 items-center">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search trashed posts..."
                   class="border border-gray-300 rounded-lg px-4 py-2 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-400 w-72">
            <button type="submit"
                    class="px-4 py-2 bg-gray-700 text-white text-sm rounded-lg
                           hover:bg-gray-800 transition">
                Search
            </button>
            @if(request('search'))
                <a href="{{ route('admin.posts.trash') }}"
                   class="text-sm text-red-500 hover:underline">Clear</a>
            @endif
        </form>

        {{-- Trashed Posts Table --}}
        <div class="bg-white shadow rounded-xl overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-5 py-4">Post</th>
                    <th class="px-5 py-4">Category</th>
                    <th class="px-5 py-4">Author</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4">Deleted</th>
                    <th class="px-5 py-4 text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($posts as $post)
                    <tr class="hover:bg-gray-50 transition align-middle opacity-75
                                   hover:opacity-100">

                        {{-- Post Title + Cover --}}
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <img src="{{ $post->cover_image_url }}"
                                     alt="{{ $post->title }}"
                                     class="w-10 h-10 rounded-lg object-cover
                                                border border-gray-200 shrink-0 grayscale">
                                <div>
                                    <p class="font-medium text-gray-600 line-through text-xs">
                                        {{ Str::limit($post->title, 45) }}
                                    </p>
                                    <p class="text-xs text-gray-400 font-mono">
                                        {{ $post->slug }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        {{-- Category --}}
                        <td class="px-5 py-3 text-gray-500 text-xs">
                            {{ $post->category->name ?? '—' }}
                        </td>

                        {{-- Author --}}
                        <td class="px-5 py-3 text-gray-500 text-xs">
                            {{ $post->user->name ?? '—' }}
                        </td>

                        {{-- Status badge --}}
                        <td class="px-5 py-3">
                            @php
                                $statusColor = match($post->status) {
                                    'published' => 'bg-green-100 text-green-700',
                                    'scheduled' => 'bg-yellow-100 text-yellow-700',
                                    default     => 'bg-gray-100 text-gray-500',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                    {{ ucfirst($post->status) }}
                                </span>
                        </td>

                        {{-- Deleted at --}}
                        <td class="px-5 py-3 text-gray-400 text-xs">
                                <span title="{{ $post->deleted_at->format('d M Y H:i') }}">
                                    {{ $post->deleted_at->diffForHumans() }}
                                </span>
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-3 text-right space-x-2 whitespace-nowrap">

                            {{-- Restore --}}
                            <form action="{{ route('admin.posts.restore', $post->id) }}"
                                  method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="px-3 py-1.5 text-xs font-medium text-green-700
                                                   bg-green-50 border border-green-200 rounded-lg
                                                   hover:bg-green-100 transition-colors">
                                    ↩ Restore
                                </button>
                            </form>

                            {{-- Force Delete --}}
                            <form action="{{ route('admin.posts.force-delete', $post->id) }}"
                                  method="POST" class="inline"
                                  onsubmit="return confirm('PERMANENTLY delete \'{{ addslashes($post->title) }}\'?\n\nThis action CANNOT be undone. The post and its cover image will be deleted forever.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="px-3 py-1.5 text-xs font-medium text-red-700
                                                   bg-red-50 border border-red-200 rounded-lg
                                                   hover:bg-red-100 transition-colors">
                                    🗑 Delete Forever
                                </button>
                            </form>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-gray-200" fill="none"
                                     stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="1.5"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <p class="text-gray-400 text-sm font-medium">
                                    Trash is empty
                                </p>
                                <p class="text-gray-300 text-xs">
                                    Deleted posts will appear here.
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $posts->links() }}
        </div>

    </div>
</x-app-layout>
