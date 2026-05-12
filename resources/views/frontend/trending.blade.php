@extends('frontend.layouts.app')

@section('content')
    <x-slot name="title">Trending</x-slot>

    <div class="max-w-4xl mx-auto px-4 py-10">

        {{-- Header --}}
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <span class="text-3xl">🔥</span>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Trending
                </h1>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm">
                Most popular posts in the last 7 days — ranked by views, claps, and comments.
            </p>
        </div>

        @if($posts->isEmpty())
            <div class="text-center py-20
                        bg-white dark:bg-gray-800
                        rounded-2xl border border-gray-100 dark:border-gray-700">
                <span class="text-5xl block mb-4">📊</span>
                <p class="text-gray-400 dark:text-gray-500 text-sm">
                    No trending data yet.
                    Run <code class="text-indigo-500 dark:text-indigo-400">php artisan trending:recalculate</code>
                    to populate trending posts.
                </p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($posts as $index => $post)
                    <article class="group flex items-center gap-5 p-5
                                    bg-white dark:bg-gray-800
                                    rounded-2xl border border-gray-100 dark:border-gray-700
                                    hover:shadow-md hover:border-indigo-100 dark:hover:border-indigo-900
                                    transition-all duration-200">

                        {{-- Rank number --}}
                        <div class="shrink-0 w-10 text-center">
                            @if($index === 0)
                                <span class="text-2xl">🥇</span>
                            @elseif($index === 1)
                                <span class="text-2xl">🥈</span>
                            @elseif($index === 2)
                                <span class="text-2xl">🥉</span>
                            @else
                                <span class="text-xl font-bold text-gray-300 dark:text-gray-600">
                                    {{ $index + 1 }}
                                </span>
                            @endif
                        </div>

                        {{-- Cover image --}}
                        <a href="{{ route('blog.post', $post->slug) }}" class="shrink-0">
                            <img src="{{ $post->cover_image_url }}"
                                 alt="{{ $post->title }}"
                                 class="w-20 h-20 object-cover rounded-xl
                                        border border-gray-100 dark:border-gray-700
                                        group-hover:ring-2 group-hover:ring-indigo-300
                                        dark:group-hover:ring-indigo-700 transition-all">
                        </a>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                @if($post->category)
                                    <span class="text-xs px-2 py-0.5 rounded-full
                                                 bg-indigo-50 dark:bg-indigo-950
                                                 text-indigo-600 dark:text-indigo-400
                                                 font-medium">
                                        {{ $post->category->name }}
                                    </span>
                                @endif
                                {{-- Trending fire badge --}}
                                <span class="text-xs px-2 py-0.5 rounded-full
                                             bg-red-50 dark:bg-red-950
                                             text-red-500 dark:text-red-400
                                             font-medium">
                                    🔥 Trending
                                </span>
                            </div>

                            <h2 class="font-semibold text-gray-900 dark:text-white
                                       text-sm leading-snug mb-2 line-clamp-2">
                                <a href="{{ route('blog.post', $post->slug) }}"
                                   class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                                    {{ $post->title }}
                                </a>
                            </h2>

                            <div class="flex items-center gap-3 text-xs
                                        text-gray-400 dark:text-gray-500 flex-wrap">

                                <div class="flex items-center gap-1.5">
                                    <img src="{{ $post->user->avatar_url }}"
                                         alt="{{ $post->user->name }}"
                                         class="w-4 h-4 rounded-full object-cover">
                                    <span>{{ $post->user->name }}</span>
                                </div>

                                <span class="text-gray-200 dark:text-gray-700">·</span>

                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none"
                                         stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0
                                                 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542
                                                 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    {{ number_format($post->views) }}
                                </span>

                                <span class="flex items-center gap-1">
                                    👏 {{ number_format($post->claps_count) }}
                                </span>

                                <span class="flex items-center gap-1">
                                    💬 {{ $post->comments_count }}
                                </span>

                            </div>
                        </div>

                    </article>
                @endforeach
            </div>
        @endif

    </div>
@endsection
