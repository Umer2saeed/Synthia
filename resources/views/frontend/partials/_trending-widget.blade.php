@if($trendingPosts->isNotEmpty())
    <div class="bg-white dark:bg-gray-800
                rounded-2xl border border-gray-100 dark:border-gray-700
                overflow-hidden">

        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white
                        flex items-center gap-2">
                🔥 Trending
            </h3>
        </div>

        <div class="divide-y divide-gray-50 dark:divide-gray-700">
            @foreach($trendingPosts->take(5) as $index => $post)
                <a href="{{ route('blog.post', $post->slug) }}"
                   class="flex items-center gap-3 px-5 py-3.5
                          hover:bg-gray-50 dark:hover:bg-gray-700
                          transition-colors group">

                    <span class="text-lg font-bold
                                 text-gray-200 dark:text-gray-700
                                 w-5 shrink-0 text-center">
                        {{ $index + 1 }}
                    </span>

                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold
                                   text-gray-700 dark:text-gray-300
                                   group-hover:text-indigo-600 dark:group-hover:text-indigo-400
                                   transition line-clamp-2">
                            {{ $post->title }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                            {{ number_format($post->views) }} views
                        </p>
                    </div>

                </a>
            @endforeach
        </div>

    </div>
@endif
