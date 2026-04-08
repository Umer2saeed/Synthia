@extends('frontend.layouts.app')

@section('content')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12">

        {{-- Page Header --}}
        <div class="mb-10">
            <h1 class="font-display text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-2">
                The Blog
            </h1>
            <p class="text-gray-500 dark:text-gray-400">
                All articles, tutorials, and stories in one place.
            </p>
        </div>

        {{-- Search + Filter bar --}}
        <form method="GET" action="{{ route('blog') }}"
              class="flex flex-wrap gap-3 items-center mb-10">

            <div class="relative flex-1 min-w-48">
                <input type="text" name="search"
                       value="{{ request('search') }}"
                       placeholder="Search articles..."
                       class="w-full pl-10 pr-4 py-2.5 text-sm bg-white dark:bg-gray-900
                          border border-gray-200 dark:border-gray-700 rounded-xl
                          text-gray-700 dark:text-gray-300 placeholder-gray-400
                          focus:outline-none focus:ring-2 focus:ring-indigo-300 dark:focus:ring-indigo-700
                          transition">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <select name="category"
                    class="px-4 py-2.5 text-sm bg-white dark:bg-gray-900
                       border border-gray-200 dark:border-gray-700 rounded-xl
                       text-gray-700 dark:text-gray-300
                       focus:outline-none focus:ring-2 focus:ring-indigo-300 dark:focus:ring-indigo-700">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->slug }}"
                        {{ request('category') === $cat->slug ? 'selected' : '' }}>
                        {{ $cat->name }} ({{ $cat->posts_count }})
                    </option>
                @endforeach
            </select>

            <button type="submit"
                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                Search
            </button>

            @if(request('search') || request('category'))
                <a href="{{ route('blog') }}"
                   class="px-4 py-2.5 text-sm text-red-500 hover:text-red-700 font-medium transition-colors">
                    Clear ✕
                </a>
            @endif
        </form>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">

            {{-- Posts --}}
            <div class="lg:col-span-2">

                {{-- Active search indicator + result count --}}
                @if($searchQuery)
                    <div class="mb-6 flex items-center gap-2">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
            <span class="font-semibold text-gray-900 dark:text-white">
                {{ $posts->total() }}
            </span>
                            {{ Str::plural('result', $posts->total()) }} for
                            "<span class="text-indigo-600 dark:text-indigo-400 font-medium">
                {{ $searchQuery }}
            </span>"
                        </p>
                        <a href="{{ route('blog') }}"
                           class="text-xs text-red-500 hover:underline ml-2">
                            Clear ✕
                        </a>
                    </div>
                @endif

                @if($posts->isEmpty())
                    <div class="text-center py-20">
                        <p class="text-4xl mb-4">🔍</p>
                        <p class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">
                            No articles found
                        </p>
                        <p class="text-sm text-gray-400 mb-4">
                            No results for "<strong>{{ $searchQuery }}</strong>".
                            Try different keywords or browse all articles.
                        </p>
                        <a href="{{ route('blog') }}"
                           class="inline-block px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700
                  text-white text-sm font-medium rounded-xl transition-colors">
                            Browse All Articles
                        </a>
                    </div>
                @else
                    {{-- When searching: show search result cards with highlighted excerpts --}}
                    @if($searchQuery)
                        <div class="space-y-4 mb-8">
                            @foreach($posts as $post)
                                @php
                                    $searchService      = app(\App\Services\PostSearchService::class);
                                    $excerpt            = $searchService->getExcerpt(
                                                              strip_tags($post->content),
                                                              $searchQuery,
                                                              150
                                                          );
                                    $highlightedTitle   = $searchService->highlightTerms(e($post->title), $searchQuery);
                                    $highlightedExcerpt = $searchService->highlightTerms(e($excerpt), $searchQuery);
                                @endphp

                                {{-- Search result row card --}}
                                <div class="bg-white dark:bg-gray-900 rounded-2xl border
                            border-gray-100 dark:border-gray-800 p-5
                            hover:border-indigo-200 dark:hover:border-indigo-800
                            transition-colors">
                                    <div class="flex gap-4">

                                        {{-- Thumbnail --}}
                                        <a href="{{ route('blog.post', $post->slug) }}"
                                           class="shrink-0">
                                            <img src="{{ $post->cover_image_url }}"
                                                 alt="{{ $post->title }}"
                                                 class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl
                                        object-cover border border-gray-100
                                        dark:border-gray-700">
                                        </a>

                                        <div class="flex-1 min-w-0">
                                            {{-- Category --}}
                                            @if($post->category)
                                                <a href="{{ route('blog.category', $post->category->slug) }}"
                                                   class="text-xs font-semibold text-indigo-600
                                          dark:text-indigo-400 hover:underline">
                                                    {{ $post->category->name }}
                                                </a>
                                            @endif

                                            {{-- Title with highlighted search terms --}}
                                            <h3 class="font-display font-bold text-gray-900
                                       dark:text-white text-base leading-snug
                                       mt-1 mb-1">
                                                <a href="{{ route('blog.post', $post->slug) }}"
                                                   class="hover:text-indigo-600 dark:hover:text-indigo-400
                                          transition-colors">
                                                    {!! $highlightedTitle !!}
                                                </a>
                                            </h3>

                                            {{-- Excerpt with highlighted search terms --}}
                                            <p class="text-sm text-gray-500 dark:text-gray-400
                                      leading-relaxed line-clamp-2">
                                                {!! $highlightedExcerpt !!}
                                            </p>

                                            {{-- Meta --}}
                                            <div class="flex items-center gap-3 mt-2 text-xs text-gray-400">
                                                <span>{{ $post->user->display_name }}</span>
                                                <span>·</span>
                                                <span>
                                    {{ $post->published_at?->format('d M Y')
                                        ?? $post->created_at->format('d M Y') }}
                                </span>
                                                <span>·</span>
                                                <span>
                                    {{ max(1, ceil(str_word_count(strip_tags($post->content)) / 200)) }}
                                    min read
                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    @else
                        {{-- No search — show normal post card grid --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                            @foreach($posts as $post)
                                @include('frontend.partials._post-card', compact('post'))
                            @endforeach
                        </div>
                    @endif

                    {{-- Pagination --}}
                    {{ $posts->links() }}
                @endif

            </div>

            {{-- Sidebar --}}
            @include('frontend.partials._sidebar')

        </div>
    </div>

@endsection
