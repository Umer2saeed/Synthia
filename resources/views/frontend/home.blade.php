<x-slot name="title">Home</x-slot>

{{-- Use frontend layout --}}
@extends('frontend.layouts.app')

@section('content')

    {{-- =============================================
         HERO SECTION
         ============================================= --}}
    <section class="bg-gradient-to-br from-indigo-50 via-white to-purple-50
                dark:from-gray-950 dark:via-gray-900 dark:to-indigo-950
                border-b border-gray-100 dark:border-gray-800">

        {{-- Welcome flash message --}}
        @if(session('success'))
            <div x-data="{ show: true }"
                 x-show="show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-init="setTimeout(() => show = false, 5000)"
                 class="max-w-7xl mx-auto px-4 sm:px-6 pt-2">
                <div class="flex items-center justify-between gap-4 px-4 py-3
                    bg-green-50 dark:bg-green-950
                    border border-green-200 dark:border-green-800
                    text-green-700 dark:text-green-400
                    text-sm rounded-xl">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                    <button @click="show = false"
                            class="text-green-400 hover:text-green-600 transition-colors shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-20">
            <div class="text-center max-w-3xl mx-auto mb-14">
            <span class="inline-block text-xs font-semibold text-indigo-600 dark:text-indigo-400
                         bg-indigo-50 dark:bg-indigo-950 px-3 py-1 rounded-full mb-4 tracking-wide uppercase">
                Welcome to Synthia
            </span>
                <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white leading-tight mb-5">
                    Ideas Worth
                    <span class="text-indigo-600 dark:text-indigo-400">Reading</span>
                </h1>
                <p class="text-lg text-gray-500 dark:text-gray-400 leading-relaxed">
                    Explore stories, insights, and tutorials from writers who care about quality.
                </p>
            </div>

            {{-- Featured Posts Grid --}}
            @if($featuredPosts->isNotEmpty())
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- Large featured card (first post) --}}
                    @if($featuredPosts->count() >= 1)
                        @php $featured = $featuredPosts->first(); @endphp
                        <div class="lg:col-span-2">
                            <article class="group relative h-full min-h-72 rounded-2xl overflow-hidden bg-gray-900">
                                <img src="{{ $featured->cover_image_url }}"
                                     alt="{{ $featured->title }}"
                                     class="absolute inset-0 w-full h-full object-cover opacity-60
                                        group-hover:opacity-50 group-hover:scale-105 transition-all duration-500">

                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>

                                <div class="relative p-6 h-full flex flex-col justify-end">
                                    @if($featured->category)
                                        <a href="{{ route('blog.category', $featured->category->slug) }}"
                                           class="inline-block text-xs font-semibold text-indigo-300
                                              bg-indigo-900/60 px-2.5 py-1 rounded-full mb-3 w-fit
                                              hover:bg-indigo-800/60 transition-colors">
                                            {{ $featured->category->name }}
                                        </a>
                                    @endif

                                    <h2 class="font-display text-2xl font-bold text-white leading-snug mb-2">
                                        <a href="{{ route('blog.post', $featured->slug) }}"
                                           class="hover:text-indigo-300 transition-colors">
                                            {{ $featured->title }}
                                        </a>
                                    </h2>

                                    <p class="text-sm text-gray-300 line-clamp-2 mb-4">
                                        {{ $featured->ai_summary ?? Str::limit(strip_tags($featured->content), 100) }}
                                    </p>

                                    <div class="flex items-center gap-3">
                                        <img src="{{ $featured->user->avatar_url }}"
                                             alt="{{ $featured->user->name }}"
                                             class="w-7 h-7 rounded-full border-2 border-white/30 object-cover">
                                        <span class="text-xs text-gray-300">{{ $featured->user->display_name }}</span>
                                        <span class="text-gray-600">·</span>
                                        <span class="text-xs text-gray-400">
                                        {{ $featured->published_at?->format('d M Y') ?? $featured->created_at->format('d M Y') }}
                                    </span>
                                    </div>
                                </div>
                            </article>
                        </div>
                    @endif

                    {{-- Two smaller featured cards --}}
                    <div class="flex flex-col gap-6">
                        @foreach($featuredPosts->skip(1)->take(2) as $post)
                            <article class="group relative rounded-2xl overflow-hidden bg-gray-900 flex-1 min-h-40">
                                <img src="{{ $post->cover_image_url }}"
                                     alt="{{ $post->title }}"
                                     class="absolute inset-0 w-full h-full object-cover opacity-60
                                        group-hover:opacity-50 group-hover:scale-105 transition-all duration-500">

                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent"></div>

                                <div class="relative p-5 h-full flex flex-col justify-end">
                                    @if($post->category)
                                        <span class="text-xs text-indigo-300 font-medium mb-1">
                                        {{ $post->category->name }}
                                    </span>
                                    @endif
                                    <h3 class="font-display text-base font-bold text-white leading-snug">
                                        <a href="{{ route('blog.post', $post->slug) }}"
                                           class="hover:text-indigo-300 transition-colors line-clamp-2">
                                            {{ $post->title }}
                                        </a>
                                    </h3>
                                </div>
                            </article>
                        @endforeach
                    </div>

                </div>
            @endif
        </div>
    </section>

    {{-- =============================================
         LATEST POSTS + SIDEBAR
         ============================================= --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 py-16">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">

            {{-- Posts Grid --}}
            <div class="lg:col-span-2">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="font-display text-2xl font-bold text-gray-900 dark:text-white">
                        Latest Posts
                    </h2>
                    <a href="{{ route('blog') }}"
                       class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                        View all →
                    </a>
                </div>

                @if($latestPosts->isEmpty())
                    <div class="text-center py-16 text-gray-400">
                        <p class="text-lg mb-2">No posts yet.</p>
                        <p class="text-sm">Check back soon!</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        @foreach($latestPosts as $post)
                            @include('frontend.partials._post-card', compact('post'))
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            @include('frontend.partials._sidebar')

        </div>
    </section>

@endsection
