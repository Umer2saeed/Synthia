@extends('frontend.layouts.app')

@section('content')
    <x-slot name="title">Your Feed</x-slot>

    <div class="max-w-3xl mx-auto px-4 py-10">

        {{-- Page header --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Your Feed
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Posts from authors you follow
            </p>
        </div>

        @if($isEmpty || $posts->isEmpty())

            {{-- Empty state --}}
            <div class="flex flex-col items-center justify-center
                        py-20 text-center">

                <div class="w-16 h-16 rounded-full
                            bg-gray-100 dark:bg-gray-800
                            flex items-center justify-center mb-5">
                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-500"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="1.5"
                              d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1
                                 m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9
                                 M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                </div>

                @if($isEmpty)
                    <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Follow some authors to see their posts here
                    </h2>
                    <p class="text-sm text-gray-400 dark:text-gray-500 max-w-sm">
                        When you follow an author, their new posts will appear
                        in this feed.
                    </p>
                    <a href="{{ route('blog') }}"
                       class="mt-6 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700
                              text-white text-sm font-medium rounded-xl transition">
                        Explore the Blog
                    </a>
                @else
                    <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        No posts yet from authors you follow
                    </h2>
                    <p class="text-sm text-gray-400 dark:text-gray-500 max-w-sm">
                        The authors you follow have not published anything yet.
                        Check back later.
                    </p>
                @endif

            </div>

        @else

            {{-- Post list --}}
            <div class="space-y-6">
                @foreach($posts as $post)

                    <article class="bg-white dark:bg-gray-800
                                    rounded-2xl shadow-sm
                                    border border-gray-100 dark:border-gray-700
                                    overflow-hidden
                                    hover:shadow-md transition-shadow duration-200">

                        <div class="flex gap-4 p-5">

                            {{-- Cover image --}}
                            @if($post->cover_image)
                                <a href="{{ route('blog.post', $post->slug) }}"
                                   class="shrink-0">
                                    <img src="{{ $post->cover_image_url }}"
                                         alt="{{ $post->title }}"
                                         class="w-24 h-24 object-cover rounded-xl
                                                border border-gray-100 dark:border-gray-700">
                                </a>
                            @endif

                            <div class="flex-1 min-w-0">

                                {{-- Author info --}}
                                <div class="flex items-center gap-2 mb-2">
                                    <img src="{{ $post->user->avatar_url }}"
                                         alt="{{ $post->user->name }}"
                                         class="w-6 h-6 rounded-full object-cover
                                                border border-gray-200 dark:border-gray-700">
                                    <a href="{{ route('author.profile', $post->user->username ?? $post->user->id) }}"
                                       class="text-xs font-medium text-gray-600 dark:text-gray-400
                                              hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                                        {{ $post->user->name }}
                                    </a>
                                    <span class="text-gray-300 dark:text-gray-600 text-xs">·</span>
                                    <span class="text-xs text-gray-400 dark:text-gray-500">
                                        {{ $post->published_at->diffForHumans() }}
                                    </span>
                                </div>

                                {{-- Title --}}
                                <h2 class="font-semibold text-gray-900 dark:text-white
                                           text-sm leading-snug mb-1.5
                                           line-clamp-2">
                                    <a href="{{ route('blog.post', $post->slug) }}"
                                       class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                                        {{ $post->title }}
                                    </a>
                                </h2>

                                {{-- Category + reading time --}}
                                <div class="flex items-center gap-2 flex-wrap">

                                    @if($post->category)
                                        <span class="px-2 py-0.5
                                                     bg-indigo-50 dark:bg-indigo-950
                                                     text-indigo-600 dark:text-indigo-400
                                                     text-xs rounded-full font-medium">
                                            {{ $post->category->name }}
                                        </span>
                                    @endif

                                    <span class="text-xs text-gray-400 dark:text-gray-500">
                                        {{ max(1, ceil(str_word_count(strip_tags($post->content)) / 200)) }}
                                        min read
                                    </span>

                                    @if($post->claps_count > 0)
                                        <span class="flex items-center gap-1
                                                     text-xs text-gray-400 dark:text-gray-500">
                                            <span>👏</span>
                                            {{ $post->claps_count }}
                                        </span>
                                    @endif

                                    @if($post->comments_count > 0)
                                        <span class="flex items-center gap-1
                                                     text-xs text-gray-400 dark:text-gray-500">
                                            <svg class="w-3 h-3" fill="none"
                                                 stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M8 12h.01M12 12h.01M16 12h.01M21 12c0
                                                         4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949
                                                         L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12
                                                         c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                            </svg>
                                            {{ $post->comments_count }}
                                        </span>
                                    @endif

                                </div>

                            </div>
                        </div>

                    </article>

                @endforeach
            </div>

            {{-- Pagination --}}
            @if($posts->hasPages())
                <div class="mt-8">
                    {{ $posts->links() }}
                </div>
            @endif

        @endif

    </div>


@endsection
