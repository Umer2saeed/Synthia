@extends('frontend.layouts.app')

@section('content')

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
                            <button id="bookmark-btn" data-post-id="{{ $post->id }}" data-bookmarked="{{ $isBookmarked ? 'true' : 'false' }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border transition-all duration-200
                       {{ $isBookmarked
                           ? 'bg-indigo-600 border-indigo-600 text-white hover:bg-indigo-700'
                           : 'bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:border-indigo-300 dark:hover:border-indigo-700 hover:text-indigo-600 dark:hover:text-indigo-400' }}">

                                {{-- Bookmark icon — filled when bookmarked, outline when not --}}
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
                            {{-- Guest — redirect to login when they try to bookmark --}}
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
                    <div class="bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-100 dark:border-indigo-900 rounded-2xl p-5">
                        <p class="text-xs font-bold text-indigo-500 uppercase tracking-widest mb-2">
                            ✦ Summary
                        </p>
                        <p class="text-sm text-indigo-800 dark:text-indigo-300 leading-relaxed">
                            {{ $post->ai_summary }}
                        </p>
                    </div>
                @endif

                {{-- Article Content --}}
                <div class="prose-content text-gray-700 dark:text-gray-300 text-base leading-relaxed">
                    {!! nl2br(e($post->content)) !!}
                </div>

                {{-- =============================================
     CLAP BUTTON SECTION
     =============================================

     HOW THIS UI WORKS:
     - The button shows the total claps on the post
     - When logged in: clicking sends an AJAX POST request
     - When logged out: clicking redirects to login
     - The hand emoji animates upward when clicked (CSS animation)
     - After 50 claps the button shows "Max" and becomes disabled
     - The progress bar fills as the user claps more
     ============================================= --}}
                <div class="flex items-center justify-center py-8 border-t border-b
            border-gray-100 dark:border-gray-800 my-6">

                    <div class="flex flex-col items-center gap-3">

                        {{-- Clap Button --}}

                        @auth
                            @if(!auth()->user()->hasVerifiedEmail())
                                {{-- Unverified — show read-only clap count with prompt --}}
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
                                {{--
                                | Logged-in user who does NOT own this post.
                                | They can clap — button is active.
                                --}}
                                <div class="flex flex-col items-center gap-2">

                                    {{-- The clap button itself --}}
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

                                        {{-- Hand clap emoji --}}
                                        <span id="clap-emoji"
                                              class="text-2xl transition-transform duration-150
                                     group-active:scale-125">
                            👏
                                        </span>

                                        {{-- User's personal clap count --}}
                                        <span id="user-clap-count" class="text-xs font-bold
                                     {{ $userClaps >= $maxClaps
                                         ? 'text-gray-400'
                                         : 'text-indigo-600 dark:text-indigo-400' }}">
                            {{ $userClaps > 0
                                ? ($userClaps >= $maxClaps ? 'Max' : '+' . $userClaps)
                                : '' }}
                                        </span>
                                    </button>

                                    {{-- Total clap count — shown below the button --}}
                                    <div class="text-center">
                                        <p id="total-clap-count"
                                           class="text-lg font-bold text-gray-800 dark:text-white">
                                            {{ number_format($totalClaps) }}
                                        </p>
                                        <p class="text-xs text-gray-400">
                                            {{ Str::plural('clap', $totalClaps) }}
                                        </p>
                                    </div>

                                    {{-- Progress bar showing user's clap usage --}}
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
                                {{--
                                | Post owner viewing their own post.
                                | Show total claps as read-only — cannot clap own post.
                                --}}
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
                            {{--
                            | Guest — not logged in.
                            | Show the total claps and a prompt to log in to clap.
                            --}}
                            <div class="flex flex-col items-center gap-2">
                                <a href="{{ route('login') }}"
                                   class="w-16 h-16 rounded-full border-2 border-indigo-200 dark:border-indigo-800 flex items-center justify-center text-2xl hover:bg-indigo-50 dark:hover:bg-indigo-950 hover:border-indigo-400 transition-all duration-200">
                                    👏
                                </a>
                                <p class="text-lg font-bold text-gray-800 dark:text-white">
                                    {{ number_format($totalClaps) }}
                                </p>
                                <p class="text-xs text-gray-400">
                                    <a href="{{ route('login') }}" class="text-indigo-500 hover:underline">Log in</a>
                                    to clap
                                </p>
                            </div>
                        @endauth

                    </div>
                </div>


                {{-- Tags --}}
                @if($post->tags->isNotEmpty())
                    <div class="flex flex-wrap items-center gap-2 pt-6 border-t border-gray-100 dark:border-gray-800">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide mr-1">Tags:</span>
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

                    {{-- Comment Form --}}
                    @auth
                        @if(!auth()->user()->hasVerifiedEmail())
                            {{--
                            | Unverified user — show verification prompt instead of form.
                            | Do not block them from reading the comments,
                            | just prevent posting until verified.
                            --}}
                            <div class="mb-8 px-5 py-4 bg-amber-50 dark:bg-amber-950
                            border border-amber-200 dark:border-amber-800
                            rounded-2xl">
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
                            {{-- Verified user — show normal comment form --}}
                            <form id="comment-form" class="mb-8" novalidate>
                                @csrf
                                <input type="hidden" name="post_id" value="{{ $post->id }}">

                                <div class="flex gap-3">
                                    <img src="{{ auth()->user()->avatar_url }}"
                                         alt="{{ auth()->user()->name }}"
                                         class="w-9 h-9 rounded-full object-cover border-2 border-indigo-100 dark:border-gray-700 shrink-0 mt-1">

                                    <div class="flex-1 space-y-3">
                                <textarea id="comment-content"
                                          name="content"
                                          rows="3"
                                          maxlength="1000"
                                          placeholder="Share your thoughts..."
                                          class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-3
                                         text-sm bg-white dark:bg-gray-900
                                         text-gray-700 dark:text-gray-300
                                         placeholder-gray-400 dark:placeholder-gray-600
                                         focus:outline-none focus:ring-2 focus:ring-indigo-300 dark:focus:ring-indigo-700
                                         resize-none transition-all"></textarea>

                                        <p id="comment-error" class="text-red-500 text-xs hidden"></p>

                                        <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-400">
                                            <span id="char-count">0</span>/1000
                                        </span>
                                            <button type="submit"
                                                    id="comment-submit-btn"
                                                    class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
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

            {{-- =============================================
                 SIDEBAR
                 ============================================= --}}
            <aside class="space-y-6">

                {{-- Author Card --}}
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-5 text-center">

                    <a href="{{ route('author.profile', $post->user->username ?? $post->user->id) }}">
                        <img src="{{ $post->user->avatar_url }}"
                             alt="{{ $post->user->name }}"
                             class="w-16 h-16 rounded-full object-cover border-4 border-indigo-100 dark:border-gray-700 mx-auto mb-3 hover:opacity-80 transition-opacity">
                    </a>

                    <a href="{{ route('author.profile', $post->user->username ?? $post->user->id) }}"
                       class="font-semibold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
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

                {{-- Categories & Tags sidebar --}}
                @include('frontend.partials._sidebar')

                {{-- Share --}}
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-5">
                    <h3 class="font-display font-bold text-gray-900 dark:text-white text-base mb-4">Share</h3>
                    <div class="flex gap-2">
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title) }}"
                           target="_blank"
                           class="flex-1 py-2 text-xs font-medium text-center rounded-xl bg-sky-50 dark:bg-sky-950 text-sky-600 dark:text-sky-400 hover:bg-sky-100 transition-colors">
                            Twitter/X
                        </a>
                        <button onclick="navigator.clipboard.writeText('{{ request()->url() }}'); this.textContent='Copied!';"
                                class="flex-1 py-2 text-xs font-medium text-center rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 transition-colors">
                            Copy Link
                        </button>
                    </div>
                </div>

            </aside>
        </div>

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

    {{-- AJAX Comment JS (same logic as before, adapted for frontend) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfToken  = document.querySelector('meta[name="csrf-token"]')?.content;
            const form       = document.getElementById('comment-form');
            const textarea   = document.getElementById('comment-content');
            const submitBtn  = document.getElementById('comment-submit-btn');
            const errorMsg   = document.getElementById('comment-error');
            const commentList = document.getElementById('comments-list');
            const countBadge = document.getElementById('comment-count');
            const charCount  = document.getElementById('char-count');

            if (textarea) {
                textarea.addEventListener('input', () => {
                    charCount.textContent = textarea.value.length;
                });
            }

            if (form) {
                form.addEventListener('submit', async function (e) {
                    e.preventDefault();
                    submitBtn.disabled     = true;
                    submitBtn.textContent  = 'Posting...';
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
                            textarea.value        = '';
                            charCount.textContent = '0';
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

            // Delete comment (own comments only on frontend)
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
                        el.style.opacity = '0';
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

    <script>
        /*
        |--------------------------------------------------------------------------
        | Clap Button JavaScript
        |--------------------------------------------------------------------------
        |
        | COMPLETE REQUEST FLOW:
        |
        | 1. User clicks the clap button
        | 2. We immediately animate the button (optimistic UI)
        |    — this makes the app feel instant even before server responds
        | 3. fetch() sends POST /posts/{id}/clap to the server
        |    with the CSRF token in the header
        | 4. Server runs ClapController@clap
        | 5. Server returns JSON:
        |    { success: true, total_claps: 42, user_claps: 5, maxed: false }
        | 6. We update the displayed numbers with the server's real values
        | 7. If maxed: we disable the button and show "Max"
        |
        | WHY OPTIMISTIC UI?
        | Without it, there is a small delay between click and number update.
        | With it, the number updates instantly and then confirms with server data.
        | This makes the experience feel responsive and alive.
        */
        document.addEventListener('DOMContentLoaded', function () {

            const clapBtn       = document.getElementById('clap-btn');
            const csrfToken     = document.querySelector('meta[name="csrf-token"]')?.content;

            // If no clap button on this page (e.g. post owner viewing), do nothing
            if (!clapBtn) return;

            /*
            |----------------------------------------------------------------------
            | Read data attributes from the button element
            |----------------------------------------------------------------------
            | We stored these values in the HTML using data-* attributes:
            |   data-post-id    → the post ID to send to the server
            |   data-user-claps → how many times this user has already clapped
            |   data-max-claps  → the maximum allowed (50)
            |
            | parseInt() converts the string attribute value to a real integer.
            */
            const postId   = clapBtn.dataset.postId;
            let userClaps  = parseInt(clapBtn.dataset.userClaps);
            const maxClaps = parseInt(clapBtn.dataset.maxClaps);

            /*
            |----------------------------------------------------------------------
            | DOM element references
            |----------------------------------------------------------------------
            | We grab all the elements we need to update once here,
            | rather than finding them inside the click handler every time.
            */
            const totalClapCount    = document.getElementById('total-clap-count');
            const userClapCount     = document.getElementById('user-clap-count');
            const clapProgress      = document.getElementById('clap-progress');
            const clapProgressText  = document.getElementById('clap-progress-text');
            const clapProgressWrap  = document.getElementById('clap-progress-wrapper');
            const clapEmoji         = document.getElementById('clap-emoji');

            /*
            |----------------------------------------------------------------------
            | Track request state
            |----------------------------------------------------------------------
            | isRequesting prevents multiple simultaneous AJAX requests.
            | If the user clicks very fast, we ignore clicks while a request
            | is already in flight — otherwise we could send 10 requests
            | before getting the first response back.
            */
            let isRequesting = false;

            /*
            |----------------------------------------------------------------------
            | Click handler
            |----------------------------------------------------------------------
            */
            clapBtn.addEventListener('click', async function () {

                // Ignore if already sending a request
                if (isRequesting) return;

                // Ignore if button is disabled (max reached)
                if (clapBtn.disabled) return;

                // Mark as requesting — blocks duplicate clicks
                isRequesting = true;

                /*
                |------------------------------------------------------------------
                | Optimistic UI update
                |------------------------------------------------------------------
                | Update the display IMMEDIATELY before the server responds.
                | This makes the UI feel instant.
                | If the server fails, we will revert the count.
                */
                userClaps++;
                const optimisticTotal = parseInt(totalClapCount.textContent.replace(/,/g, '')) + 1;

                // Animate the emoji
                clapEmoji.style.transform = 'scale(1.4) translateY(-4px)';
                setTimeout(() => { clapEmoji.style.transform = ''; }, 150);

                // Update user clap count display
                if (userClapCount) {
                    userClapCount.textContent = userClaps >= maxClaps ? 'Max' : '+' + userClaps;
                }

                // Update total count display optimistically
                totalClapCount.textContent = optimisticTotal.toLocaleString();

                // Show and update progress bar
                if (clapProgressWrap) clapProgressWrap.style.display = 'block';
                if (clapProgress) {
                    clapProgress.style.width = Math.min(100, (userClaps / maxClaps) * 100) + '%';
                }
                if (clapProgressText) {
                    clapProgressText.textContent = userClaps + '/' + maxClaps;
                }

                /*
                |------------------------------------------------------------------
                | Send the AJAX request to the server
                |------------------------------------------------------------------
                |
                | fetch() is the modern browser API for HTTP requests.
                |
                | method: 'POST'
                |   → We are changing data so we use POST, not GET.
                |
                | headers:
                |   X-CSRF-TOKEN → Laravel requires this on all POST/PUT/DELETE
                |                   requests to protect against CSRF attacks.
                |                   We read it from the meta tag in the layout.
                |
                |   X-Requested-With: XMLHttpRequest
                |   → Tells Laravel this is an AJAX request.
                |     This makes $request->ajax() return true in the controller.
                |
                |   Content-Type: application/json
                |   → We are not sending form data, just headers.
                |     The server knows this is a JSON request.
                */
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
                        /*
                        |--------------------------------------------------------------
                        | Confirm with server's real values
                        |--------------------------------------------------------------
                        | Replace our optimistic numbers with the actual server values.
                        | This corrects any discrepancy if multiple tabs were open.
                        */
                        totalClapCount.textContent = parseInt(data.total_claps).toLocaleString();

                        if (userClapCount) {
                            userClapCount.textContent = data.maxed ? 'Max' : '+' + data.user_claps;
                        }

                        if (clapProgress) {
                            clapProgress.style.width =
                                Math.min(100, (data.user_claps / maxClaps) * 100) + '%';
                        }

                        if (clapProgressText) {
                            clapProgressText.textContent = data.user_claps + '/' + maxClaps;
                        }

                        /*
                        |--------------------------------------------------------------
                        | Disable button if max reached
                        |--------------------------------------------------------------
                        | When the user has clapped 50 times, we:
                        |   1. Disable the button so it cannot be clicked
                        |   2. Change its visual style to show it is inactive
                        |   3. Show "Max" text instead of the count
                        */
                        if (data.maxed) {
                            clapBtn.disabled = true;
                            clapBtn.classList.remove(
                                'border-indigo-200', 'hover:border-indigo-400',
                                'hover:bg-indigo-50', 'cursor-pointer', 'active:scale-95'
                            );
                            clapBtn.classList.add(
                                'border-gray-200', 'cursor-not-allowed', 'opacity-60'
                            );

                            if (userClapCount) {
                                userClapCount.classList.remove('text-indigo-600');
                                userClapCount.classList.add('text-gray-400');
                            }
                        }

                    } else {
                        /*
                        |--------------------------------------------------------------
                        | Server returned an error — revert optimistic update
                        |--------------------------------------------------------------
                        */
                        userClaps--;
                        totalClapCount.textContent =
                            (parseInt(totalClapCount.textContent.replace(/,/g, '')) - 1).toLocaleString();

                        console.error('Clap failed:', data.message);
                    }

                } catch (error) {
                    /*
                    |------------------------------------------------------------------
                    | Network error — revert optimistic update
                    |------------------------------------------------------------------
                    */
                    userClaps--;
                    totalClapCount.textContent =
                        (parseInt(totalClapCount.textContent.replace(/,/g, '')) - 1).toLocaleString();

                    console.error('Network error:', error);

                } finally {
                    /*
                    |------------------------------------------------------------------
                    | Always unlock the button after request completes
                    |------------------------------------------------------------------
                    | Whether success or failure, we reset isRequesting to false
                    | so the next click can go through.
                    | 'finally' runs no matter what — success, error, or exception.
                    */
                    isRequesting = false;
                }
            });

        });
    </script>

    <script>
        /*
        |--------------------------------------------------------------------------
        | Bookmark Button JavaScript
        |--------------------------------------------------------------------------
        |
        | COMPLETE REQUEST FLOW:
        |
        | 1. User clicks the bookmark button
        | 2. We immediately update the button visually (optimistic UI)
        |    — saves appear instant without waiting for server
        | 3. fetch() sends POST /bookmarks with { post_id: X }
        | 4. Server runs BookmarkController@toggle
        | 5. If not bookmarked → creates bookmark record → returns { bookmarked: true }
        |    If already bookmarked → deletes bookmark record → returns { bookmarked: false }
        | 6. We confirm the button state with server's real response
        |
        | DIFFERENCE FROM CLAP:
        | Clap only goes one direction (increment up to max).
        | Bookmark toggles back and forth — add, remove, add, remove.
        | Each click reverses the previous state.
        */
        document.addEventListener('DOMContentLoaded', function () {

            const bookmarkBtn = document.getElementById('bookmark-btn');
            const csrfToken   = document.querySelector('meta[name="csrf-token"]')?.content;

            // If no bookmark button (guest view), do nothing
            if (!bookmarkBtn) return;

            /*
            |----------------------------------------------------------------------
            | Read initial state from data attributes
            |----------------------------------------------------------------------
            | data-post-id    → which post to bookmark
            | data-bookmarked → 'true' or 'false' string from Blade
            |
            | We convert 'true'/'false' string to real boolean with === 'true'
            */
            const postId          = bookmarkBtn.dataset.postId;
            let   isBookmarked    = bookmarkBtn.dataset.bookmarked === 'true';
            let   isRequesting    = false;

            /*
            |----------------------------------------------------------------------
            | DOM elements we will update
            |----------------------------------------------------------------------
            */
            const bookmarkLabel = document.getElementById('bookmark-label');
            const bookmarkIcon  = bookmarkBtn.querySelector('svg');

            /*
            |----------------------------------------------------------------------
            | updateButtonState() — helper function to update button appearance
            |----------------------------------------------------------------------
            | We extract the button styling into a reusable function because
            | we need to call it in two places:
            |   1. Optimistically on click (before server responds)
            |   2. After server responds to confirm the state
            |
            | @param saved (boolean) — true = bookmarked, false = not bookmarked
            */
            function updateButtonState(saved) {
                if (saved) {
                    /*
                    | BOOKMARKED STATE — filled, indigo background
                    | The bookmark icon becomes filled (fill="currentColor")
                    */
                    bookmarkBtn.classList.remove(
                        'bg-white', 'dark:bg-gray-900',
                        'border-gray-200', 'dark:border-gray-700',
                        'text-gray-600', 'dark:text-gray-400',
                        'hover:border-indigo-300', 'dark:hover:border-indigo-700',
                        'hover:text-indigo-600', 'dark:hover:text-indigo-400'
                    );
                    bookmarkBtn.classList.add(
                        'bg-indigo-600', 'border-indigo-600',
                        'text-white', 'hover:bg-indigo-700'
                    );
                    bookmarkIcon.setAttribute('fill', 'currentColor');
                    bookmarkLabel.textContent = 'Saved';

                } else {
                    /*
                    | NOT BOOKMARKED STATE — outline style
                    | The bookmark icon becomes an outline (fill="none")
                    */
                    bookmarkBtn.classList.remove(
                        'bg-indigo-600', 'border-indigo-600',
                        'text-white', 'hover:bg-indigo-700'
                    );
                    bookmarkBtn.classList.add(
                        'bg-white', 'dark:bg-gray-900',
                        'border-gray-200', 'dark:border-gray-700',
                        'text-gray-600', 'dark:text-gray-400',
                        'hover:border-indigo-300', 'dark:hover:border-indigo-700',
                        'hover:text-indigo-600', 'dark:hover:text-indigo-400'
                    );
                    bookmarkIcon.setAttribute('fill', 'none');
                    bookmarkLabel.textContent = 'Save';
                }
            }

            /*
            |----------------------------------------------------------------------
            | Click handler
            |----------------------------------------------------------------------
            */
            bookmarkBtn.addEventListener('click', async function () {

                // Prevent double clicks while request is in flight
                if (isRequesting) return;
                isRequesting = true;

                /*
                |------------------------------------------------------------------
                | Optimistic UI — toggle immediately
                |------------------------------------------------------------------
                | We flip the state locally BEFORE the server responds.
                | This makes the button feel instant.
                | If the server fails, we flip it back.
                */
                isBookmarked = !isBookmarked;
                updateButtonState(isBookmarked);

                try {
                    console.log('coming in');
                    /*
                    |------------------------------------------------------------------
                    | Send the AJAX request
                    |------------------------------------------------------------------
                    |
                    | Unlike the clap request which had no body,
                    | here we send a JSON body with the post_id.
                    |
                    | JSON.stringify() converts the JavaScript object to a JSON string:
                    |   { post_id: 5 }  →  '{"post_id":5}'
                    |
                    | The server reads this with: $request->validate(['post_id' => ...])
                    */
                    const response = await fetch('/bookmarks', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN':     csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type':     'application/json',
                        },
                        body: JSON.stringify({ post_id: postId }),
                    });

                    console.log(response);

                    const data = await response.json();

                    if (response.ok && data.success) {
                        /*
                        |--------------------------------------------------------------
                        | Confirm with server's real state
                        |--------------------------------------------------------------
                        | The server tells us definitively whether the post
                        | is now bookmarked or not. We update the button
                        | to match the server's truth.
                        |
                        | This handles edge cases like:
                        |   - Two browser tabs open — tab 2 may have different state
                        |   - Server error that caused a different outcome
                        */
                        isBookmarked = data.bookmarked;
                        updateButtonState(isBookmarked);

                    } else {
                        /*
                        |--------------------------------------------------------------
                        | Server returned error — revert optimistic update
                        |--------------------------------------------------------------
                        | Flip the state back to what it was before the click.
                        */
                        isBookmarked = !isBookmarked;
                        updateButtonState(isBookmarked);
                        console.error('Bookmark toggle failed:', data.message);
                    }

                } catch (error) {
                    /*
                    |------------------------------------------------------------------
                    | Network error — revert optimistic update
                    |------------------------------------------------------------------
                    */
                    isBookmarked = !isBookmarked;
                    updateButtonState(isBookmarked);
                    console.error('Network error:', error);

                } finally {
                    /*
                    |------------------------------------------------------------------
                    | Always unlock after request completes
                    |------------------------------------------------------------------
                    */
                    isRequesting = false;
                }
            });

        });
    </script>

@endsection
