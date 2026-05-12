@extends('frontend.layouts.app')

@section('content')

    <x-slot name="title">My Dashboard</x-slot>

    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 transition-colors duration-200">

        {{-- PAGE HEADER --}}
        <div class="border-b border-gray-200 dark:border-gray-800/60
                    bg-white dark:bg-gray-900/50 backdrop-blur-sm">
            <div class="max-w-6xl mx-auto px-6 py-8">
                <div class="flex items-center gap-5">

                    <div class="relative shrink-0">
                        <img src="{{ $user->avatar_url }}"
                             alt="{{ $user->name }}"
                             class="w-16 h-16 rounded-2xl object-cover
                                    ring-2 ring-indigo-500/40">
                        <span class="absolute -bottom-1 -right-1 w-4 h-4
                                     bg-emerald-500 rounded-full
                                     border-2 border-white dark:border-gray-900">
                        </span>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-indigo-600 dark:text-indigo-400
                                   uppercase tracking-widest mb-1">
                            Reader Dashboard
                        </p>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $user->name }}
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-500 mt-0.5">
                            Member since {{ $user->created_at->format('F Y') }}
                        </p>
                    </div>

                </div>
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-6 py-8 space-y-8">

            {{-- STATS ROW --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

                @foreach([
                    [
                        'label'     => 'Posts Read',
                        'value'     => $stats['posts_read'],
                        'icon'      => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                        'light_bg'  => 'bg-indigo-50 border-indigo-100',
                        'dark_bg'   => 'dark:bg-indigo-500/10 dark:border-indigo-500/20',
                        'icon_bg'   => 'bg-indigo-100 dark:bg-indigo-500/20',
                        'icon_text' => 'text-indigo-600 dark:text-indigo-400',
                        'val_text'  => 'text-indigo-700 dark:text-indigo-300',
                        'lbl_text'  => 'text-indigo-600/70 dark:text-indigo-400/70',
                    ],
                    [
                        'label'     => 'Following',
                        'value'     => $stats['authors_followed'],
                        'icon'      => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                        'light_bg'  => 'bg-purple-50 border-purple-100',
                        'dark_bg'   => 'dark:bg-purple-500/10 dark:border-purple-500/20',
                        'icon_bg'   => 'bg-purple-100 dark:bg-purple-500/20',
                        'icon_text' => 'text-purple-600 dark:text-purple-400',
                        'val_text'  => 'text-purple-700 dark:text-purple-300',
                        'lbl_text'  => 'text-purple-600/70 dark:text-purple-400/70',
                    ],
                    [
                        'label'     => 'Comments',
                        'value'     => $stats['comments_made'],
                        'icon'      => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
                        'light_bg'  => 'bg-emerald-50 border-emerald-100',
                        'dark_bg'   => 'dark:bg-emerald-500/10 dark:border-emerald-500/20',
                        'icon_bg'   => 'bg-emerald-100 dark:bg-emerald-500/20',
                        'icon_text' => 'text-emerald-600 dark:text-emerald-400',
                        'val_text'  => 'text-emerald-700 dark:text-emerald-300',
                        'lbl_text'  => 'text-emerald-600/70 dark:text-emerald-400/70',
                    ],
                    [
                        'label'     => 'Claps Given',
                        'value'     => $stats['claps_given'],
                        'icon'      => 'M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                        'light_bg'  => 'bg-amber-50 border-amber-100',
                        'dark_bg'   => 'dark:bg-amber-500/10 dark:border-amber-500/20',
                        'icon_bg'   => 'bg-amber-100 dark:bg-amber-500/20',
                        'icon_text' => 'text-amber-600 dark:text-amber-400',
                        'val_text'  => 'text-amber-700 dark:text-amber-300',
                        'lbl_text'  => 'text-amber-600/70 dark:text-amber-400/70',
                    ],
                ] as $stat)

                    <div class="rounded-2xl border p-5
                                {{ $stat['light_bg'] }} {{ $stat['dark_bg'] }}
                                hover:scale-[1.02] transition-transform duration-200">

                        <div class="w-9 h-9 rounded-xl {{ $stat['icon_bg'] }}
                                    flex items-center justify-center mb-4">
                            <svg class="w-4.5 h-4.5 {{ $stat['icon_text'] }}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="1.75" d="{{ $stat['icon'] }}"/>
                            </svg>
                        </div>

                        <p class="text-3xl font-bold {{ $stat['val_text'] }} mb-1">
                            {{ number_format($stat['value']) }}
                        </p>
                        <p class="text-xs font-semibold {{ $stat['lbl_text'] }}
                                   uppercase tracking-wider">
                            {{ $stat['label'] }}
                        </p>

                    </div>

                @endforeach

            </div>

            {{-- MAIN CONTENT GRID --}}
            <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">

                {{-- Left column — Reading History + Bookmarks --}}
                <div class="xl:col-span-3 space-y-6">

                    {{-- READING HISTORY --}}
                    <div class="rounded-2xl bg-white dark:bg-gray-900
                                border border-gray-200 dark:border-gray-800/80
                                overflow-hidden">

                        <div class="flex items-center justify-between px-6 py-5
                                    border-b border-gray-100 dark:border-gray-800/80">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg
                                            bg-indigo-100 dark:bg-indigo-500/10
                                            flex items-center justify-center">
                                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-sm font-semibold
                                               text-gray-800 dark:text-white">
                                        Reading History
                                    </h2>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">
                                        Last 30 days
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('feed.activity') }}"
                               class="text-xs font-medium
                                      text-indigo-600 dark:text-indigo-400
                                      hover:text-indigo-800 dark:hover:text-indigo-300
                                      transition-colors flex items-center gap-1">
                                View Feed
                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>

                        @if($readingHistory->isEmpty())
                            <div class="flex flex-col items-center justify-center py-14 px-6">
                                <div class="w-12 h-12 rounded-2xl
                                            bg-gray-100 dark:bg-gray-800
                                            flex items-center justify-center mb-4">
                                    <svg class="w-6 h-6 text-gray-400 dark:text-gray-600"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="1.5"
                                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                    No reading history yet
                                </p>
                                <a href="{{ route('blog') }}"
                                   class="text-xs text-indigo-600 dark:text-indigo-400
                                          hover:underline transition-colors">
                                    Start reading →
                                </a>
                            </div>
                        @else
                            <div class="divide-y divide-gray-100 dark:divide-gray-800/60">
                                @foreach($readingHistory as $history)
                                    <a href="{{ route('blog.post', $history->post->slug) }}"
                                       class="flex items-center gap-4 px-6 py-4
                                              hover:bg-gray-50 dark:hover:bg-gray-800/40
                                              transition-colors group">

                                        <div class="shrink-0">
                                            <img src="{{ $history->post->cover_image_url }}"
                                                 alt="{{ $history->post->title }}"
                                                 class="w-14 h-14 rounded-xl object-cover
                                                        ring-1 ring-black/5 dark:ring-white/5
                                                        group-hover:ring-indigo-400/40
                                                        transition-all">
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold
                                                       text-gray-700 dark:text-gray-200
                                                       group-hover:text-gray-900 dark:group-hover:text-white
                                                       transition-colors truncate">
                                                {{ $history->post->title }}
                                            </p>
                                            <div class="flex items-center gap-2 mt-1.5">
                                                @if($history->post->category)
                                                    <span class="text-xs px-2 py-0.5
                                                                 bg-indigo-50 dark:bg-indigo-500/10
                                                                 text-indigo-600 dark:text-indigo-400
                                                                 rounded-md font-medium">
                                                        {{ $history->post->category->name }}
                                                    </span>
                                                @endif
                                                <span class="text-xs text-gray-400 dark:text-gray-600">
                                                    {{ $history->read_at->diffForHumans() }}
                                                </span>
                                            </div>
                                        </div>

                                        <svg class="w-4 h-4
                                                    text-gray-300 dark:text-gray-700
                                                    group-hover:text-indigo-500 dark:group-hover:text-indigo-400
                                                    transition-colors shrink-0"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>

                                    </a>
                                @endforeach
                            </div>
                        @endif

                    </div>

                    {{-- BOOKMARKS --}}
                    <div class="rounded-2xl bg-white dark:bg-gray-900
                                border border-gray-200 dark:border-gray-800/80
                                overflow-hidden">

                        <div class="flex items-center justify-between px-6 py-5
                                    border-b border-gray-100 dark:border-gray-800/80">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg
                                            bg-amber-100 dark:bg-amber-500/10
                                            flex items-center justify-center">
                                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-sm font-semibold
                                               text-gray-800 dark:text-white">
                                        Bookmarks
                                    </h2>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">
                                        {{ $bookmarks->count() }}
                                        {{ Str::plural('saved post', $bookmarks->count()) }}
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('bookmarks.index') }}"
                               class="text-xs font-medium
                                      text-amber-600 dark:text-amber-400
                                      hover:text-amber-800 dark:hover:text-amber-300
                                      transition-colors flex items-center gap-1">
                                View All
                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>

                        @if($bookmarks->isEmpty())
                            <div class="flex flex-col items-center justify-center py-14 px-6">
                                <div class="w-12 h-12 rounded-2xl
                                            bg-gray-100 dark:bg-gray-800
                                            flex items-center justify-center mb-4">
                                    <svg class="w-6 h-6 text-gray-400 dark:text-gray-600"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="1.5"
                                              d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                    No bookmarks yet
                                </p>
                                <a href="{{ route('blog') }}"
                                   class="text-xs text-amber-600 dark:text-amber-400
                                          hover:underline transition-colors">
                                    Find posts to save →
                                </a>
                            </div>
                        @else
                            <div class="divide-y divide-gray-100 dark:divide-gray-800/60">
                                @foreach($bookmarks as $bookmark)
                                    <a href="{{ route('blog.post', $bookmark->post->slug) }}"
                                       class="flex items-center gap-4 px-6 py-4
                                              hover:bg-gray-50 dark:hover:bg-gray-800/40
                                              transition-colors group">

                                        <div class="shrink-0">
                                            <img src="{{ $bookmark->post->cover_image_url }}"
                                                 alt="{{ $bookmark->post->title }}"
                                                 class="w-14 h-14 rounded-xl object-cover
                                                        ring-1 ring-black/5 dark:ring-white/5
                                                        group-hover:ring-amber-400/40
                                                        transition-all">
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold
                                                       text-gray-700 dark:text-gray-200
                                                       group-hover:text-gray-900 dark:group-hover:text-white
                                                       transition-colors truncate">
                                                {{ $bookmark->post->title }}
                                            </p>
                                            <div class="flex items-center gap-2 mt-1.5">
                                                <img src="{{ $bookmark->post->user->avatar_url }}"
                                                     alt="{{ $bookmark->post->user->name }}"
                                                     class="w-4 h-4 rounded-full object-cover shrink-0">
                                                <span class="text-xs text-gray-500 dark:text-gray-500 truncate">
                                                    {{ $bookmark->post->user->name ?? '—' }}
                                                </span>
                                                <span class="text-gray-300 dark:text-gray-700 text-xs shrink-0">·</span>
                                                <span class="text-xs text-gray-400 dark:text-gray-600 shrink-0">
                                                    {{ $bookmark->created_at->diffForHumans() }}
                                                </span>
                                            </div>
                                        </div>

                                        <svg class="w-4 h-4
                                                    text-gray-300 dark:text-gray-700
                                                    group-hover:text-amber-500 dark:group-hover:text-amber-400
                                                    transition-colors shrink-0"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>

                                    </a>
                                @endforeach
                            </div>
                        @endif

                    </div>

                </div>

                {{-- Right column — Following + Quick Links --}}
                <div class="xl:col-span-2 space-y-6">

                    {{-- FOLLOWING --}}
                    <div class="rounded-2xl bg-white dark:bg-gray-900
                                border border-gray-200 dark:border-gray-800/80
                                overflow-hidden">

                        <div class="flex items-center justify-between px-6 py-5
                                    border-b border-gray-100 dark:border-gray-800/80">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg
                                            bg-purple-100 dark:bg-purple-500/10
                                            flex items-center justify-center">
                                    <svg class="w-4 h-4 text-purple-600 dark:text-purple-400"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10
                                                 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3
                                                 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0
                                                 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-sm font-semibold
                                               text-gray-800 dark:text-white">
                                        Following
                                    </h2>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">
                                        {{ $stats['authors_followed'] }}
                                        {{ Str::plural('author', $stats['authors_followed']) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        @if($following->isEmpty())
                            <div class="flex flex-col items-center justify-center py-12 px-6">
                                <p class="text-sm text-gray-500 dark:text-gray-500 text-center">
                                    Not following anyone yet.
                                </p>
                                <a href="{{ route('blog') }}"
                                   class="mt-3 text-xs
                                          text-purple-600 dark:text-purple-400
                                          hover:underline transition-colors">
                                    Find authors →
                                </a>
                            </div>
                        @else
                            <div class="p-4 space-y-1">
                                @foreach($following as $author)
                                    <a href="{{ route('author.profile', $author->username ?? $author->id) }}"
                                       class="flex items-center gap-3 p-3
                                              rounded-xl
                                              hover:bg-gray-50 dark:hover:bg-gray-800/60
                                              transition-colors group">

                                        <img src="{{ $author->avatar_url }}"
                                             alt="{{ $author->name }}"
                                             class="w-10 h-10 rounded-xl object-cover shrink-0
                                                    ring-1 ring-black/5 dark:ring-white/5
                                                    group-hover:ring-purple-400/40
                                                    transition-all">

                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold
                                                       text-gray-700 dark:text-gray-200
                                                       group-hover:text-gray-900 dark:group-hover:text-white
                                                       transition-colors truncate">
                                                {{ $author->name }}
                                            </p>
                                            <p class="text-xs text-gray-400 dark:text-gray-600 mt-0.5">
                                                {{ $author->posts_count }}
                                                {{ Str::plural('post', $author->posts_count) }}
                                            </p>
                                        </div>

                                        <svg class="w-4 h-4 shrink-0
                                                    text-gray-300 dark:text-gray-700
                                                    group-hover:text-purple-500 dark:group-hover:text-purple-400
                                                    transition-colors"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>

                                    </a>
                                @endforeach
                            </div>
                        @endif

                    </div>

                    {{-- BADGES --}}
                    @if($user->badges->isNotEmpty())
                        <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800/80 overflow-hidden">

                            <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-100 dark:border-gray-800/80">
                                <div class="w-8 h-8 rounded-lg bg-yellow-100 dark:bg-yellow-500/10 flex items-center justify-center">
                                    <span class="text-sm">🏅</span>
                                </div>
                                <div>
                                    <h2 class="text-sm font-semibold text-gray-800 dark:text-white">
                                        Badges
                                    </h2>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">
                                        {{ $user->badges->count() }}
                                        {{ Str::plural('achievement', $user->badges->count()) }}
                                    </p>
                                </div>
                            </div>

                            <div class="p-5 flex flex-wrap gap-2">
                                @foreach($user->badges as $badge)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5
                                         {{ $badge->color }}
                                         rounded-full text-xs font-medium border border-current/10"
                                                      title="{{ $badge->description }}">
                                        {{ $badge->icon }} {{ $badge->name }}
                                    </span>
                                @endforeach
                            </div>

                        </div>
                    @endif

                    {{-- QUICK LINKS --}}
                    <div class="rounded-2xl bg-white dark:bg-gray-900
                                border border-gray-200 dark:border-gray-800/80
                                p-5">

                        <h2 class="text-sm font-semibold
                                   text-gray-800 dark:text-white
                                   mb-4 px-1">
                            Quick Links
                        </h2>

                        <nav class="space-y-1">
                            @foreach([
                                [
                                    'href'      => route('feed.activity'),
                                    'label'     => 'Activity Feed',
                                    'icon'      => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z',
                                    'icon_bg'   => 'bg-indigo-100 dark:bg-indigo-500/10',
                                    'icon_text' => 'text-indigo-600 dark:text-indigo-400',
                                ],
                                [
                                    'href'      => route('bookmarks.index'),
                                    'label'     => 'All Bookmarks',
                                    'icon'      => 'M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z',
                                    'icon_bg'   => 'bg-amber-100 dark:bg-amber-500/10',
                                    'icon_text' => 'text-amber-600 dark:text-amber-400',
                                ],
                                [
                                    'href'      => route('following.index'),
                                    'label'     => 'Following',
                                    'icon'      => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                                    'icon_bg'   => 'bg-purple-100 dark:bg-purple-500/10',
                                    'icon_text' => 'text-purple-600 dark:text-purple-400',
                                ],
                                [
                                    'href'      => route('blog'),
                                    'label'     => 'Explore Blog',
                                    'icon'      => 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9',
                                    'icon_bg'   => 'bg-emerald-100 dark:bg-emerald-500/10',
                                    'icon_text' => 'text-emerald-600 dark:text-emerald-400',
                                ],
                                [
                                    'href'      => route('frontend.author.analytics'),
                                    'label'     => 'My Analytics',
                                    'icon'      => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                                    'icon_bg'   => 'bg-indigo-100 dark:bg-indigo-500/10',
                                    'icon_text' => 'text-indigo-600 dark:text-indigo-400',
                                ],
                                [
                                    'href'      => route('frontend.profile.edit'),
                                    'label'     => 'Edit Profile',
                                    'icon'      => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                                    'icon_bg'   => 'bg-gray-100 dark:bg-gray-700',
                                    'icon_text' => 'text-gray-600 dark:text-gray-400',
                                ],
                            ] as $link)

                                <a href="{{ $link['href'] }}"
                                   class="flex items-center gap-3 px-3 py-2.5
                                          rounded-xl
                                          hover:bg-gray-50 dark:hover:bg-gray-800/60
                                          transition-colors group">

                                    <div class="w-8 h-8 rounded-lg {{ $link['icon_bg'] }}
                                                flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4 {{ $link['icon_text'] }}"
                                             fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="1.75" d="{{ $link['icon'] }}"/>
                                        </svg>
                                    </div>

                                    <span class="text-sm font-medium
                                                 text-gray-600 dark:text-gray-400
                                                 group-hover:text-gray-900 dark:group-hover:text-white
                                                 transition-colors">
                                        {{ $link['label'] }}
                                    </span>

                                    <svg class="w-3.5 h-3.5 ml-auto shrink-0
                                                text-gray-300 dark:text-gray-700
                                                group-hover:text-gray-500 dark:group-hover:text-gray-400
                                                transition-colors"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>

                                </a>

                            @endforeach
                        </nav>

                    </div>

                </div>

            </div>

        </div>
    </div>

@endsection
