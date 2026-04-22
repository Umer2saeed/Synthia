{{--
| Reusable post card used on home, blog, category, and tag pages.
| Usage: @include('frontend.partials._post-card', ['post' => $post])
--}}
<article class="group bg-white dark:bg-gray-900 rounded-2xl border border-gray-100
                dark:border-gray-800 overflow-hidden hover:border-indigo-200
                dark:hover:border-indigo-800 hover:shadow-lg transition-all duration-300
                relative"> {{-- relative added for the bookmark badge --}}

    {{-- Bookmark indicator badge (top right corner) --}}
    @auth
{{--        @if($post->isBookmarkedBy(auth()->user()))--}}
        @if(($post->bookmarks_count ?? 0) > 0)
            <div class="absolute top-3 right-3 z-10">
                <span class="flex items-center justify-center w-7 h-7 rounded-full
                             bg-indigo-600 text-white shadow-md"
                      title="Saved to bookmarks">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                </span>
            </div>
        @endif
    @endauth

    {{-- Cover Image --}}
    <a href="{{ route('blog.post', $post->slug) }}" class="block overflow-hidden aspect-[16/9]">
        <img src="{{ $post->cover_image_url }}"
             alt="{{ $post->title }}"
             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
    </a>

    <div class="p-5">
        {{-- Category + Featured badge --}}
        <div class="flex items-center gap-2 mb-3">
            @if($post->category)
                <a href="{{ route('blog.category', $post->category->slug) }}"
                   class="text-xs font-semibold text-indigo-600 dark:text-indigo-400
                          bg-indigo-50 dark:bg-indigo-950 px-2.5 py-1 rounded-full
                          hover:bg-indigo-100 dark:hover:bg-indigo-900 transition-colors">
                    {{ $post->category->name }}
                </a>
            @endif

            @if($post->is_featured)
                <span class="text-xs font-medium text-amber-600 dark:text-amber-400
                             bg-amber-50 dark:bg-amber-950 px-2.5 py-1 rounded-full">
                    ★ Featured
                </span>
            @endif
        </div>

        {{-- Title --}}
        <h3 class="font-display font-bold text-gray-900 dark:text-white text-lg leading-snug mb-2
                   group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
            <a href="{{ route('blog.post', $post->slug) }}" class="line-clamp-2">
                {{ $post->title }}
            </a>
        </h3>

        {{-- AI Summary or content excerpt --}}
        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed line-clamp-3 mb-4">
            {{ $post->ai_summary ?? Str::limit(strip_tags($post->content), 120) }}
        </p>

        {{-- Footer: Author + Date + Read time + Claps --}}
        <div class="flex items-center justify-between pt-4
            border-t border-gray-50 dark:border-gray-800">

            {{-- Author --}}
            <div class="flex items-center gap-2">
                <img src="{{ $post->user->avatar_url }}"
                     alt="{{ $post->user->name }}"
                     class="w-7 h-7 rounded-full object-cover
                    border border-gray-100 dark:border-gray-700">
                <a href="{{ route('author.profile', $post->user->username ?? $post->user->id) }}"
                   class="text-xs font-medium text-gray-600 dark:text-gray-400
                  hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    {{ $post->user->display_name }}
                </a>
            </div>

            {{-- Meta: read time + date + claps --}}
            <div class="flex items-center gap-3 text-xs text-gray-400">
        <span>
            {{ max(1, ceil(str_word_count(strip_tags($post->content)) / 200)) }} min
        </span>
                <span>·</span>
                <span>
            {{ $post->published_at?->format('d M Y') ?? $post->created_at->format('d M Y') }}
        </span>

                {{-- Clap count --}}
                @php $postClaps = $post->totalClaps(); @endphp
                @if($postClaps > 0)
                    <span>·</span>
                    <span class="flex items-center gap-0.5">
{{--                👏 {{ number_format($postClaps) }}--}}
                👏 {{ number_format($post->claps_count ?? 0) }}
                👏 {{ number_format($post->claps_count ?? 0) }}
            </span>
                @endif
            </div>
        </div>

    </div>
</article>
