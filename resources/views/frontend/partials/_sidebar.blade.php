<aside class="space-y-6">

    {{-- Categories --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-5">
        <h3 class="font-display font-bold text-gray-900 dark:text-white text-base mb-4">Categories</h3>
        <ul class="space-y-1">
            @forelse($categories as $category)
                <li>
                    <a href="{{ route('blog.category', $category->slug) }}"
                       class="flex items-center justify-between px-3 py-2 rounded-lg text-sm
                              text-gray-600 dark:text-gray-400
                              hover:bg-indigo-50 dark:hover:bg-gray-800
                              hover:text-indigo-600 dark:hover:text-indigo-400
                              transition-colors group">
                        <span class="font-medium">{{ $category->name }}</span>
                        <span class="text-xs text-gray-400 group-hover:text-indigo-400 bg-gray-100
                                     dark:bg-gray-800 group-hover:bg-indigo-100
                                     dark:group-hover:bg-gray-700 px-2 py-0.5 rounded-full transition-colors">
                            {{ $category->posts_count }}
                        </span>
                    </a>
                </li>
            @empty
                <li class="text-sm text-gray-400 px-3">No categories yet.</li>
            @endforelse
        </ul>
    </div>

    {{-- Popular Tags --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-5">
        <h3 class="font-display font-bold text-gray-900 dark:text-white text-base mb-4">Popular Tags</h3>
        @if($popularTags->isEmpty())
            <p class="text-sm text-gray-400">No tags yet.</p>
        @else
            <div class="flex flex-wrap gap-2">
                @foreach($popularTags as $tag)
                    <a href="{{ route('blog.tag', $tag->slug) }}"
                       class="px-3 py-1 text-xs font-medium rounded-full
                              bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400
                              hover:bg-indigo-100 dark:hover:bg-indigo-950
                              hover:text-indigo-600 dark:hover:text-indigo-400
                              transition-colors">
                        # {{ $tag->name }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>

</aside>
