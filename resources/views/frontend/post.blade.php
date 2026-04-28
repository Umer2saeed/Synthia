@extends('frontend.layouts.app')

@section('content')

    {{-- Reading progress bar — appears at top of browser window --}}
    <x-reading-progress />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">

            {{-- =============================================
                 MAIN ARTICLE
                 ============================================= --}}
            <div class="lg:col-span-2 space-y-8">

                {{-- Article Header --}}
                <header>
                    {{-- Category + Tags --}}
                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        @if($post->category)
                            <a href="{{ route('blog.category', $post->category->slug) }}"
                               class="text-xs font-semibold text-indigo-600 dark:text-indigo-400
                                      bg-indigo-50 dark:bg-indigo-950 px-3 py-1 rounded-full
                                      hover:bg-indigo-100 transition-colors">
                                {{ $post->category->name }}
                            </a>
                        @endif
                        @if($post->is_featured)
                            <span class="text-xs font-medium text-amber-600 dark:text-amber-400
                                         bg-amber-50 dark:bg-amber-950 px-3 py-1 rounded-full">
                                ★ Featured
                            </span>
                        @endif
                    </div>

                    {{-- Title --}}
                    <h1 class="font-display text-3xl sm:text-4xl font-bold
                               text-gray-900 dark:text-white leading-tight mb-5">
                        {{ $post->title }}
                    </h1>

                    {{-- Author + Meta + Bookmark Button --}}
                    <div class="flex items-center justify-between pb-6
                                border-b border-gray-100 dark:border-gray-800 flex-wrap gap-4">

                        {{-- Author info (left side) --}}
                        <div class="flex items-center gap-4">
                            <a href="{{ route('author.profile', $post->user->username ?? $post->user->id) }}">
                                <img src="{{ $post->user->avatar_url }}"
                                     alt="{{ $post->user->name }}"
                                     class="w-11 h-11 rounded-full object-cover border-2
                                            border-indigo-100 dark:border-gray-700
                                            hover:opacity-80 transition-opacity">
                            </a>
                            <div>
                                <a href="{{ route('author.profile', $post->user->username ?? $post->user->id) }}"
                                   class="text-sm font-semibold text-gray-900 dark:text-white
                                          hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    {{ $post->user->display_name }}
                                </a>
                                <div class="flex items-center gap-2 text-xs text-gray-400 mt-0.5">
                                    <span>{{ $post->published_at?->format('d M Y') ?? $post->created_at->format('d M Y') }}</span>
                                    <span>·</span>
                                    <span>{{ max(1, ceil(str_word_count(strip_tags($post->content)) / 200)) }} min read</span>
                                    <span>·</span>
                                    <span>{{ $comments->count() }} {{ Str::plural('comment', $comments->count()) }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Bookmark Button (right side) --}}
                        @auth
                            <button
                                id="bookmark-btn"
                                data-post-id="{{ $post->id }}"
                                data-bookmarked="{{ $isBookmarked ? 'true' : 'false' }}"
                                class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm
                                       font-medium border transition-all duration-200
                                       {{ $isBookmarked
                                           ? 'bg-indigo-600 border-indigo-600 text-white hover:bg-indigo-700'
                                           : 'bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:border-indigo-300 dark:hover:border-indigo-700 hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                                <svg class="w-4 h-4" fill="{{ $isBookmarked ? 'currentColor' : 'none' }}"
                                     stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                </svg>
                                <span id="bookmark-label">
                                    {{ $isBookmarked ? 'Saved' : 'Save' }}
                                </span>
                            </button>
                        @else
                            <a href="{{ route('login') }}"
                               class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm
                                      font-medium border border-gray-200 dark:border-gray-700
                                      text-gray-600 dark:text-gray-400
                                      hover:border-indigo-300 hover:text-indigo-600 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                </svg>
                                Save
                            </a>
                        @endauth

                    </div>
                </header>

                {{-- Cover Image --}}
                <div class="rounded-2xl overflow-hidden aspect-[16/9] bg-gray-100 dark:bg-gray-800">
                    <img src="{{ $post->cover_image_url }}"
                         alt="{{ $post->title }}"
                         class="w-full h-full object-cover">
                </div>

                {{-- AI Summary --}}
                @if($post->ai_summary)
                    <div class="bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-100
                                dark:border-indigo-900 rounded-2xl p-5">
                        <p class="text-xs font-bold text-indigo-500 uppercase tracking-widest mb-2">
                            ✦ Summary
                        </p>
                        <p class="text-sm text-indigo-800 dark:text-indigo-300 leading-relaxed">
                            {{ $post->ai_summary }}
                        </p>
                    </div>
                @endif

                {{-- =============================================
                     ARTICLE CONTENT
                     id="post-content" is required by:
                       1. Reading progress bar (tracks scroll within this div)
                       2. TOC (extracts h2/h3/h4 headings from this div)
                     scroll-mt-24 offsets heading anchors for sticky navbar
                ============================================= --}}
                <div id="post-content"
                     class="prose prose-lg prose-indigo dark:prose-invert max-w-none
                            prose-headings:font-bold prose-headings:scroll-mt-24
                            prose-headings:text-gray-900 dark:prose-headings:text-white
                            prose-p:text-gray-700 dark:prose-p:text-gray-300 prose-p:leading-relaxed
                            prose-a:text-indigo-600 dark:prose-a:text-indigo-400
                            prose-strong:text-gray-900 dark:prose-strong:text-white
                            prose-code:text-indigo-600 dark:prose-code:text-indigo-400
                            prose-pre:bg-gray-900 prose-pre:text-gray-100
                            prose-blockquote:border-indigo-500 prose-blockquote:text-gray-600
                            dark:prose-blockquote:text-gray-400">
                    {!! $post->content !!}
                </div>

                {{-- =============================================
                     CLAP BUTTON SECTION
                ============================================= --}}
                <div class="flex items-center justify-center py-8 border-t border-b
                            border-gray-100 dark:border-gray-800 my-6">
                    <div class="flex flex-col items-center gap-3">

                        @auth
                            @if(!auth()->user()->hasVerifiedEmail())
                                <div class="flex flex-col items-center gap-1">
                                    <span class="text-3xl opacity-50">👏</span>
                                    <p class="text-lg font-bold text-gray-800 dark:text-white">
                                        {{ number_format($totalClaps) }}
                                    </p>
                                    <p class="text-xs text-gray-400 text-center">
                                        Verify your email to clap
                                    </p>
                                </div>

                            @elseif($post->user_id !== auth()->id())
                                <div class="flex flex-col items-center gap-2">
                                    <button
                                        id="clap-btn"
                                        data-post-id="{{ $post->id }}"
                                        data-user-claps="{{ $userClaps }}"
                                        data-max-claps="{{ $maxClaps }}"
                                        {{ $userClaps >= $maxClaps ? 'disabled' : '' }}
                                        class="relative group flex flex-col items-center gap-1
                                               w-16 h-16 rounded-full border-2 transition-all duration-200
                                               focus:outline-none select-none
                                               {{ $userClaps >= $maxClaps
                                                   ? 'border-gray-200 dark:border-gray-700 cursor-not-allowed opacity-60'
                                                   : 'border-indigo-200 dark:border-indigo-800 hover:border-indigo-400 dark:hover:border-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950 cursor-pointer active:scale-95' }}">
                                        <span id="clap-emoji"
                                              class="text-2xl transition-transform duration-150
                                                     group-active:scale-125">
                                            👏
                                        </span>
                                        <span id="user-clap-count"
                                              class="text-xs font-bold
                                                     {{ $userClaps >= $maxClaps ? 'text-gray-400' : 'text-indigo-600 dark:text-indigo-400' }}">
                                            {{ $userClaps > 0 ? ($userClaps >= $maxClaps ? 'Max' : '+' . $userClaps) : '' }}
                                        </span>
                                    </button>

                                    <div class="text-center">
                                        <p id="total-clap-count"
                                           class="text-lg font-bold text-gray-800 dark:text-white">
                                            {{ number_format($totalClaps) }}
                                        </p>
                                        <p class="text-xs text-gray-400">
                                            {{ Str::plural('clap', $totalClaps) }}
                                        </p>
                                    </div>

                                    @if($userClaps > 0)
                                        <div class="w-16 mt-1">
                                            <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-1">
                                                <div id="clap-progress"
                                                     class="h-1 rounded-full bg-indigo-500 transition-all duration-300"
                                                     style="width: {{ min(100, ($userClaps / $maxClaps) * 100) }}%">
                                                </div>
                                            </div>
                                            <p class="text-xs text-gray-400 text-center mt-1">
                                                {{ $userClaps }}/{{ $maxClaps }}
                                            </p>
                                        </div>
                                    @else
                                        <div class="w-16 mt-1" id="clap-progress-wrapper" style="display:none">
                                            <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-1">
                                                <div id="clap-progress"
                                                     class="h-1 rounded-full bg-indigo-500 transition-all duration-300"
                                                     style="width: 0%">
                                                </div>
                                            </div>
                                            <p class="text-xs text-gray-400 text-center mt-1"
                                               id="clap-progress-text">
                                                0/{{ $maxClaps }}
                                            </p>
                                        </div>
                                    @endif
                                </div>

                            @else
                                <div class="flex flex-col items-center gap-1">
                                    <span class="text-3xl">👏</span>
                                    <p class="text-lg font-bold text-gray-800 dark:text-white">
                                        {{ number_format($totalClaps) }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ Str::plural('clap', $totalClaps) }} on your post
                                    </p>
                                </div>
                            @endif

                        @else
                            <div class="flex flex-col items-center gap-2">
                                <a href="{{ route('login') }}"
                                   class="w-16 h-16 rounded-full border-2 border-indigo-200
                                          dark:border-indigo-800 flex items-center justify-center
                                          text-2xl hover:bg-indigo-50 dark:hover:bg-indigo-950
                                          hover:border-indigo-400 transition-all duration-200">
                                    👏
                                </a>
                                <p class="text-lg font-bold text-gray-800 dark:text-white">
                                    {{ number_format($totalClaps) }}
                                </p>
                                <p class="text-xs text-gray-400">
                                    <a href="{{ route('login') }}"
                                       class="text-indigo-500 hover:underline">Log in</a>
                                    to clap
                                </p>
                            </div>
                        @endauth

                    </div>
                </div>

                {{-- Tags --}}
                @if($post->tags->isNotEmpty())
                    <div class="flex flex-wrap items-center gap-2 pt-6 border-t
                                border-gray-100 dark:border-gray-800">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide mr-1">
                            Tags:
                        </span>
                        @foreach($post->tags as $tag)
                            <a href="{{ route('blog.tag', $tag->slug) }}"
                               class="px-3 py-1 text-xs font-medium rounded-full
                                      bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400
                                      hover:bg-indigo-100 dark:hover:bg-indigo-950
                                      hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                # {{ $tag->name }}
                            </a>
                        @endforeach
                    </div>
                @endif

                {{-- =============================================
                     COMMENTS SECTION
                ============================================= --}}
                <section id="comments" class="pt-2">
                    <h2 class="font-display text-2xl font-bold text-gray-900 dark:text-white mb-6">
                        Comments
                        <span id="comment-count"
                              class="ml-2 text-base font-normal text-indigo-600 dark:text-indigo-400
                                     bg-indigo-50 dark:bg-indigo-950 px-2.5 py-0.5 rounded-full">
                            {{ $comments->count() }}
                        </span>
                    </h2>

                    @auth
                        @if(!auth()->user()->hasVerifiedEmail())
                            <div class="mb-8 px-5 py-4 bg-amber-50 dark:bg-amber-950
                                        border border-amber-200 dark:border-amber-800 rounded-2xl">
                                <p class="text-sm font-semibold text-amber-800 dark:text-amber-300 mb-1">
                                    ✉️ Verify your email to join the conversation
                                </p>
                                <p class="text-sm text-amber-700 dark:text-amber-400 mb-3">
                                    Please check your inbox and click the verification link we sent you.
                                </p>
                                <form method="POST" action="{{ route('verification.send') }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="text-xs font-semibold text-amber-700 dark:text-amber-300
                                                   underline hover:no-underline">
                                        Resend verification email
                                    </button>
                                </form>
                            </div>
                        @else
                            <form id="comment-form" class="mb-8" novalidate>
                                @csrf
                                <input type="hidden" name="post_id" value="{{ $post->id }}">
                                <div class="flex gap-3">
                                    <img src="{{ auth()->user()->avatar_url }}"
                                         alt="{{ auth()->user()->name }}"
                                         class="w-9 h-9 rounded-full object-cover border-2
                                                border-indigo-100 dark:border-gray-700 shrink-0 mt-1">
                                    <div class="flex-1 space-y-3">
                                        <textarea id="comment-content"
                                                  name="content"
                                                  rows="3"
                                                  maxlength="1000"
                                                  placeholder="Share your thoughts..."
                                                  class="w-full border border-gray-200 dark:border-gray-700
                                                         rounded-2xl px-4 py-3 text-sm
                                                         bg-white dark:bg-gray-900
                                                         text-gray-700 dark:text-gray-300
                                                         placeholder-gray-400 dark:placeholder-gray-600
                                                         focus:outline-none focus:ring-2 focus:ring-indigo-300
                                                         dark:focus:ring-indigo-700
                                                         resize-none transition-all"></textarea>
                                        <p id="comment-error" class="text-red-500 text-xs hidden"></p>
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs text-gray-400">
                                                <span id="char-count">0</span>/1000
                                            </span>
                                            <button type="submit"
                                                    id="comment-submit-btn"
                                                    class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700
                                                           text-white text-sm font-medium rounded-xl
                                                           transition-colors disabled:opacity-50
                                                           disabled:cursor-not-allowed">
                                                Post Comment
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        @endif
                    @else
                        <div class="mb-8 px-5 py-4 bg-gray-50 dark:bg-gray-900
                                    border border-gray-200 dark:border-gray-700 rounded-2xl">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <a href="{{ route('login') }}"
                                   class="text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">
                                    Log in
                                </a>
                                to join the conversation.
                            </p>
                        </div>
                    @endauth

                    {{-- Comments List --}}
                    <div id="comments-list" class="space-y-5">
                        @forelse($comments as $comment)
                            @include('frontend.partials._comment', compact('comment'))
                        @empty
                            <p id="no-comments-msg"
                               class="text-sm text-gray-400 text-center py-10">
                                No comments yet — be the first!
                            </p>
                        @endforelse
                    </div>
                </section>

            </div>
            {{-- End Main Article --}}

            {{-- =============================================
                 RIGHT SIDEBAR
                 sticky top-24 keeps it fixed while scrolling.
                 top-24 = 96px — adjust if your navbar is taller.
            ============================================= --}}
            <aside class="space-y-6">

                <div class="sticky top-24 space-y-6">

                    {{-- ==========================================
                         TABLE OF CONTENTS
                         hidden by default — JavaScript shows it
                         only when the post has 2+ headings.
                    ========================================== --}}
                    <div id="toc-wrapper"
                         class="hidden bg-white dark:bg-gray-900
                                border border-gray-100 dark:border-gray-800
                                rounded-2xl overflow-hidden">

                        {{-- TOC Header --}}
                        <div class="flex items-center justify-between px-4 py-3
                                    border-b border-gray-100 dark:border-gray-800">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none"
                                     stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="2" d="M4 6h16M4 10h16M4 14h10"/>
                                </svg>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                                    Contents
                                </h3>
                            </div>
                            <span data-toc-count
                                  class="text-xs text-gray-400 dark:text-gray-500">
                            </span>
                        </div>

                        {{-- TOC List — JavaScript fills this --}}
                        <div class="px-3 py-3 max-h-[50vh] overflow-y-auto">
                            <nav data-toc-list aria-label="Table of contents">
                            </nav>
                        </div>

                    </div>
                    {{-- End TOC --}}

                    {{-- ==========================================
                         AUTHOR CARD
                    ========================================== --}}
                    <div class="bg-white dark:bg-gray-900 rounded-2xl border
                                border-gray-100 dark:border-gray-800 p-5 text-center">

                        <a href="{{ route('author.profile', $post->user->username ?? $post->user->id) }}">
                            <img src="{{ $post->user->avatar_url }}"
                                 alt="{{ $post->user->name }}"
                                 class="w-16 h-16 rounded-full object-cover border-4
                                        border-indigo-100 dark:border-gray-700 mx-auto mb-3
                                        hover:opacity-80 transition-opacity">
                        </a>

                        <a href="{{ route('author.profile', $post->user->username ?? $post->user->id) }}"
                           class="font-semibold text-gray-900 dark:text-white
                                  hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                            {{ $post->user->display_name }}
                        </a>

                        @if($post->user->bio)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                                {{ $post->user->bio }}
                            </p>
                        @endif

                        <div class="mt-3 pt-3 border-t border-gray-50 dark:border-gray-800 space-y-1">
                            <span class="text-xs text-gray-400 block">
                                {{ $post->user->posts()->published()->count() }}
                                {{ Str::plural('article', $post->user->posts()->published()->count()) }} published
                            </span>
                            <a href="{{ route('author.profile', $post->user->username ?? $post->user->id) }}"
                               class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                                View all articles →
                            </a>
                        </div>
                    </div>

                    {{-- ==========================================
                         CATEGORIES & TAGS SIDEBAR
                    ========================================== --}}
                    @include('frontend.partials._sidebar')

                    {{-- ==========================================
                         SHARE
                    ========================================== --}}
                    <div class="bg-white dark:bg-gray-900 rounded-2xl border
                                border-gray-100 dark:border-gray-800 p-5">
                        <h3 class="font-display font-bold text-gray-900 dark:text-white text-base mb-4">
                            Share
                        </h3>
                        <div class="flex gap-2">
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title) }}"
                               target="_blank"
                               class="flex-1 py-2 text-xs font-medium text-center rounded-xl
                                      bg-sky-50 dark:bg-sky-950 text-sky-600 dark:text-sky-400
                                      hover:bg-sky-100 transition-colors">
                                Twitter/X
                            </a>
                            <button onclick="navigator.clipboard.writeText('{{ request()->url() }}'); this.textContent='Copied!';"
                                    class="flex-1 py-2 text-xs font-medium text-center rounded-xl
                                           bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400
                                           hover:bg-gray-200 transition-colors">
                                Copy Link
                            </button>
                        </div>
                    </div>

                </div>
                {{-- End sticky wrapper --}}

            </aside>
            {{-- End Sidebar --}}

        </div>
        {{-- End main grid --}}

        {{-- Related Posts --}}
        @if($relatedPosts->isNotEmpty())
            <div class="mt-16 pt-10 border-t border-gray-100 dark:border-gray-800">
                <h2 class="font-display text-2xl font-bold text-gray-900 dark:text-white mb-6">
                    Related Articles
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($relatedPosts as $post)
                        @include('frontend.partials._post-card', compact('post'))
                    @endforeach
                </div>
            </div>
        @endif

    </div>
    {{-- End max-w-7xl --}}

    {{-- =============================================
         MOBILE TOC TOGGLE
         Only visible below lg breakpoint.
         Floating button in bottom-right corner.
    ============================================= --}}
    <div class="lg:hidden">

        {{-- Floating button — hidden until user scrolls 300px --}}
        <button
            id="mobile-toc-toggle"
            type="button"
            class="fixed bottom-6 right-6 z-40 hidden
                   w-12 h-12 rounded-full shadow-lg
                   bg-indigo-600 hover:bg-indigo-700
                   text-white flex items-center justify-center
                   transition-all duration-200"
            aria-label="Toggle table of contents">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 6h16M4 10h16M4 14h10"/>
            </svg>
        </button>

        {{-- Mobile overlay panel --}}
        <div id="mobile-toc-overlay"
             class="hidden fixed inset-0 z-50 bg-black bg-opacity-50"
             role="dialog"
             aria-modal="true">

            <div class="absolute right-0 top-0 bottom-0 w-72 max-w-[85vw]
                        bg-white dark:bg-gray-900 shadow-xl flex flex-col">

                <div class="flex items-center justify-between px-4 py-4
                            border-b border-gray-100 dark:border-gray-800">
                    <h3 class="font-semibold text-gray-900 dark:text-white">
                        Contents
                    </h3>
                    <button id="mobile-toc-close"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto px-3 py-3">
                    <nav id="mobile-toc-list" aria-label="Table of contents mobile">
                    </nav>
                </div>

            </div>
        </div>

    </div>
    {{-- End Mobile TOC --}}

    {{-- =============================================
         JAVASCRIPT SECTION
    ============================================= --}}

    {{-- Comment Form JS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfToken   = document.querySelector('meta[name="csrf-token"]')?.content;
            const form        = document.getElementById('comment-form');
            const textarea    = document.getElementById('comment-content');
            const submitBtn   = document.getElementById('comment-submit-btn');
            const errorMsg    = document.getElementById('comment-error');
            const commentList = document.getElementById('comments-list');
            const countBadge  = document.getElementById('comment-count');
            const charCount   = document.getElementById('char-count');

            if (textarea) {
                textarea.addEventListener('input', () => {
                    charCount.textContent = textarea.value.length;
                });
            }

            if (form) {
                form.addEventListener('submit', async function (e) {
                    e.preventDefault();
                    submitBtn.disabled    = true;
                    submitBtn.textContent = 'Posting...';
                    errorMsg.classList.add('hidden');

                    try {
                        const response = await fetch('{{ route('comments.store') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN':     csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: new FormData(form),
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            document.getElementById('no-comments-msg')?.remove();
                            commentList.insertAdjacentHTML('afterbegin', data.html);
                            countBadge.textContent = data.count;
                            textarea.value         = '';
                            charCount.textContent  = '0';
                        } else if (response.status === 422) {
                            errorMsg.textContent = Object.values(data.errors)[0][0];
                            errorMsg.classList.remove('hidden');
                        } else {
                            errorMsg.textContent = data.message || 'Something went wrong.';
                            errorMsg.classList.remove('hidden');
                        }
                    } catch {
                        errorMsg.textContent = 'Network error. Please try again.';
                        errorMsg.classList.remove('hidden');
                    } finally {
                        submitBtn.disabled    = false;
                        submitBtn.textContent = 'Post Comment';
                    }
                });
            }

            if (commentList) {
                commentList.addEventListener('click', async function (e) {
                    if (!e.target.classList.contains('delete-comment-btn')) return;
                    if (!confirm('Delete this comment?')) return;

                    const id  = e.target.dataset.commentId;
                    const el  = document.querySelector(`.comment-item[data-comment-id="${id}"]`);

                    const response = await fetch(`/comments/${id}`, {
                        method:  'DELETE',
                        headers: {
                            'X-CSRF-TOKEN':     csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type':     'application/json',
                        },
                    });

                    const data = await response.json();
                    if (data.success) {
                        el.style.opacity    = '0';
                        el.style.transition = 'opacity 0.3s';
                        setTimeout(() => {
                            el.remove();
                            const current = parseInt(countBadge.textContent);
                            countBadge.textContent = Math.max(0, current - 1);
                        }, 300);
                    }
                });
            }
        });
    </script>

    {{-- Clap Button JS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const clapBtn   = document.getElementById('clap-btn');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            if (!clapBtn) return;

            const postId          = clapBtn.dataset.postId;
            let   userClaps       = parseInt(clapBtn.dataset.userClaps);
            const maxClaps        = parseInt(clapBtn.dataset.maxClaps);
            const totalClapCount  = document.getElementById('total-clap-count');
            const userClapCount   = document.getElementById('user-clap-count');
            const clapProgress    = document.getElementById('clap-progress');
            const clapProgressText = document.getElementById('clap-progress-text');
            const clapProgressWrap = document.getElementById('clap-progress-wrapper');
            const clapEmoji       = document.getElementById('clap-emoji');
            let   isRequesting    = false;

            clapBtn.addEventListener('click', async function () {
                if (isRequesting || clapBtn.disabled) return;
                isRequesting = true;

                userClaps++;
                const optimisticTotal = parseInt(totalClapCount.textContent.replace(/,/g, '')) + 1;

                clapEmoji.style.transform = 'scale(1.4) translateY(-4px)';
                setTimeout(() => { clapEmoji.style.transform = ''; }, 150);

                if (userClapCount) {
                    userClapCount.textContent = userClaps >= maxClaps ? 'Max' : '+' + userClaps;
                }

                totalClapCount.textContent = optimisticTotal.toLocaleString();

                if (clapProgressWrap) clapProgressWrap.style.display = 'block';
                if (clapProgress) {
                    clapProgress.style.width = Math.min(100, (userClaps / maxClaps) * 100) + '%';
                }
                if (clapProgressText) {
                    clapProgressText.textContent = userClaps + '/' + maxClaps;
                }

                try {
                    const response = await fetch(`/posts/${postId}/clap`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN':     csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type':     'application/json',
                        },
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        totalClapCount.textContent = parseInt(data.total_claps).toLocaleString();

                        if (userClapCount) {
                            userClapCount.textContent = data.maxed ? 'Max' : '+' + data.user_claps;
                        }
                        if (clapProgress) {
                            clapProgress.style.width = Math.min(100, (data.user_claps / maxClaps) * 100) + '%';
                        }
                        if (clapProgressText) {
                            clapProgressText.textContent = data.user_claps + '/' + maxClaps;
                        }

                        if (data.maxed) {
                            clapBtn.disabled = true;
                            clapBtn.classList.remove(
                                'border-indigo-200', 'hover:border-indigo-400',
                                'hover:bg-indigo-50', 'cursor-pointer', 'active:scale-95'
                            );
                            clapBtn.classList.add('border-gray-200', 'cursor-not-allowed', 'opacity-60');
                            if (userClapCount) {
                                userClapCount.classList.remove('text-indigo-600');
                                userClapCount.classList.add('text-gray-400');
                            }
                        }
                    } else {
                        userClaps--;
                        totalClapCount.textContent =
                            (parseInt(totalClapCount.textContent.replace(/,/g, '')) - 1).toLocaleString();
                    }
                } catch (error) {
                    userClaps--;
                    totalClapCount.textContent =
                        (parseInt(totalClapCount.textContent.replace(/,/g, '')) - 1).toLocaleString();
                    console.error('Network error:', error);
                } finally {
                    isRequesting = false;
                }
            });
        });
    </script>

    {{-- Bookmark Button JS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const bookmarkBtn = document.getElementById('bookmark-btn');
            const csrfToken   = document.querySelector('meta[name="csrf-token"]')?.content;

            if (!bookmarkBtn) return;

            const postId       = bookmarkBtn.dataset.postId;
            let   isBookmarked = bookmarkBtn.dataset.bookmarked === 'true';
            let   isRequesting = false;

            const bookmarkLabel = document.getElementById('bookmark-label');
            const bookmarkIcon  = bookmarkBtn.querySelector('svg');

            function updateButtonState(saved) {
                if (saved) {
                    bookmarkBtn.classList.remove(
                        'bg-white', 'dark:bg-gray-900', 'border-gray-200',
                        'dark:border-gray-700', 'text-gray-600', 'dark:text-gray-400',
                        'hover:border-indigo-300', 'dark:hover:border-indigo-700',
                        'hover:text-indigo-600', 'dark:hover:text-indigo-400'
                    );
                    bookmarkBtn.classList.add(
                        'bg-indigo-600', 'border-indigo-600', 'text-white', 'hover:bg-indigo-700'
                    );
                    bookmarkIcon.setAttribute('fill', 'currentColor');
                    bookmarkLabel.textContent = 'Saved';
                } else {
                    bookmarkBtn.classList.remove(
                        'bg-indigo-600', 'border-indigo-600', 'text-white', 'hover:bg-indigo-700'
                    );
                    bookmarkBtn.classList.add(
                        'bg-white', 'dark:bg-gray-900', 'border-gray-200',
                        'dark:border-gray-700', 'text-gray-600', 'dark:text-gray-400',
                        'hover:border-indigo-300', 'dark:hover:border-indigo-700',
                        'hover:text-indigo-600', 'dark:hover:text-indigo-400'
                    );
                    bookmarkIcon.setAttribute('fill', 'none');
                    bookmarkLabel.textContent = 'Save';
                }
            }

            bookmarkBtn.addEventListener('click', async function () {
                if (isRequesting) return;
                isRequesting = true;

                isBookmarked = !isBookmarked;
                updateButtonState(isBookmarked);

                try {
                    const response = await fetch('/bookmarks', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN':     csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type':     'application/json',
                        },
                        body: JSON.stringify({ post_id: postId }),
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        isBookmarked = data.bookmarked;
                        updateButtonState(isBookmarked);
                    } else {
                        isBookmarked = !isBookmarked;
                        updateButtonState(isBookmarked);
                    }
                } catch (error) {
                    isBookmarked = !isBookmarked;
                    updateButtonState(isBookmarked);
                    console.error('Network error:', error);
                } finally {
                    isRequesting = false;
                }
            });
        });
    </script>

    {{-- Mobile TOC JS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggle     = document.getElementById('mobile-toc-toggle');
            const overlay    = document.getElementById('mobile-toc-overlay');
            const closeBtn   = document.getElementById('mobile-toc-close');
            const mobileList = document.getElementById('mobile-toc-list');

            if (!toggle || !overlay) return;

            // Show floating button after scrolling 300px
            window.addEventListener('scroll', function () {
                const tocWrapper = document.getElementById('toc-wrapper');
                // Only show if TOC has content (post has headings)
                if (tocWrapper && !tocWrapper.classList.contains('hidden')) {
                    toggle.classList.toggle('hidden', window.scrollY <= 300);
                }
            }, { passive: true });

            function openMobileToc() {
                // Clone desktop TOC content into mobile panel
                const desktopList = document.querySelector('[data-toc-list]');
                if (desktopList && mobileList) {
                    mobileList.innerHTML = desktopList.innerHTML;
                }
                overlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeMobileToc() {
                overlay.classList.add('hidden');
                document.body.style.overflow = '';
            }

            toggle.addEventListener('click', openMobileToc);
            closeBtn?.addEventListener('click', closeMobileToc);

            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) closeMobileToc();
            });

            overlay.addEventListener('click', function (e) {
                if (e.target.closest('[data-toc-link]')) closeMobileToc();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeMobileToc();
            });
        });
    </script>

@endsection
