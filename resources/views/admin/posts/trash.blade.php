<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Trash</h2>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ now()->format('l, d F Y') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-5 px-4 py-3 bg-green-50 dark:bg-green-900
                        border border-green-200 dark:border-green-700
                        text-green-800 dark:text-green-300 text-sm rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 px-4 py-3 bg-red-50 dark:bg-red-950
                        border border-red-200 dark:border-red-800
                        text-red-700 dark:text-red-400 text-sm rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        {{-- Info Banner --}}
        <div class="mb-5 px-4 py-3 bg-amber-50 dark:bg-amber-950
                    border border-amber-200 dark:border-amber-800
                    text-amber-700 dark:text-amber-400 text-sm rounded-lg flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Posts in trash can be restored at any time. Permanently deleted posts cannot be recovered.
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('admin.posts.trash') }}"
              class="mb-5 flex gap-3 items-center">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search trashed posts..."
                   class="border border-gray-300 dark:border-gray-600
                          bg-white dark:bg-gray-800
                          text-gray-800 dark:text-gray-200
                          placeholder-gray-400 dark:placeholder-gray-500
                          rounded-lg px-4 py-2 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-400 w-72">
            <button type="submit"
                    class="px-4 py-2 bg-gray-700 dark:bg-gray-600
                           hover:bg-gray-800 dark:hover:bg-gray-500
                           text-white text-sm rounded-lg transition">
                Search
            </button>
            @if(request('search'))
                <a href="{{ route('admin.posts.trash') }}"
                   class="text-sm text-red-500 dark:text-red-400 hover:underline">Clear</a>
            @endif
        </form>

        <div class="flex justify-end mb-2">
            <a href="{{ route('admin.posts.index') }}"
               class="flex items-center gap-2 px-4 py-2
                      bg-gray-100 dark:bg-gray-800
                      text-gray-700 dark:text-gray-300
                      border border-gray-200 dark:border-gray-700
                      text-sm rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                ← Back to Posts
            </a>
        </div>

        {{-- Trashed Posts Table --}}
        <div class="bg-white dark:bg-gray-800
                    shadow rounded-xl overflow-hidden
                    border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-900
                              text-gray-500 dark:text-gray-400
                              uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-5 py-4">Post</th>
                    <th class="px-5 py-4">Category</th>
                    <th class="px-5 py-4">Author</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4">Deleted</th>
                    <th class="px-5 py-4 text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($posts as $post)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700
                                   transition align-middle opacity-75 hover:opacity-100">

                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <img src="{{ $post->cover_image_url }}"
                                     alt="{{ $post->title }}"
                                     class="w-10 h-10 rounded-lg object-cover
                                                border border-gray-200 dark:border-gray-700
                                                shrink-0 grayscale">
                                <div>
                                    <p class="font-medium text-gray-600 dark:text-gray-400
                                                   line-through text-xs">
                                        {{ Str::limit($post->title, 45) }}
                                    </p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 font-mono">
                                        {{ $post->slug }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        <td class="px-5 py-3 text-gray-500 dark:text-gray-400 text-xs">
                            {{ $post->category->name ?? '—' }}
                        </td>

                        <td class="px-5 py-3 text-gray-500 dark:text-gray-400 text-xs">
                            {{ $post->user->name ?? '—' }}
                        </td>

                        <td class="px-5 py-3">
                            @php
                                $statusColor = match($post->status) {
                                    'published' => 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400',
                                    'scheduled' => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-400',
                                    default     => 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                    {{ ucfirst($post->status) }}
                                </span>
                        </td>

                        <td class="px-5 py-3 text-gray-400 dark:text-gray-500 text-xs">
                                <span title="{{ $post->deleted_at->format('d M Y H:i') }}">
                                    {{ $post->deleted_at->diffForHumans() }}
                                </span>
                        </td>

                        <td class="px-5 py-3 text-right space-x-2 whitespace-nowrap">

                            {{-- Restore --}}
                            <form action="{{ route('admin.posts.restore', $post->id) }}"
                                  method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="px-3 py-1.5 text-xs font-medium
                                                   text-green-700 dark:text-green-400
                                                   bg-green-50 dark:bg-green-950
                                                   border border-green-200 dark:border-green-800
                                                   rounded-lg hover:bg-green-100 dark:hover:bg-green-900
                                                   transition-colors">
                                    ↩ Restore
                                </button>
                            </form>

                            {{-- Force Delete --}}
                            <form action="{{ route('admin.posts.force-delete', $post->id) }}"
                                  method="POST" class="inline"
                                  onsubmit="return confirm('PERMANENTLY delete \'{{ addslashes($post->title) }}\'?\n\nThis action CANNOT be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="px-3 py-1.5 text-xs font-medium
                                                   text-red-700 dark:text-red-400
                                                   bg-red-50 dark:bg-red-950
                                                   border border-red-200 dark:border-red-800
                                                   rounded-lg hover:bg-red-100 dark:hover:bg-red-900
                                                   transition-colors">
                                    🗑 Delete Forever
                                </button>
                            </form>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-gray-200 dark:text-gray-700"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="1.5"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <p class="text-gray-400 dark:text-gray-500 text-sm font-medium">
                                    Trash is empty
                                </p>
                                <p class="text-gray-300 dark:text-gray-600 text-xs">
                                    Deleted posts will appear here.
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $posts->links() }}
        </div>

    </div>
</x-app-layout>
