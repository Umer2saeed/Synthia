@extends('frontend.layouts.app')

@section('content')

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-12">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="font-display text-3xl font-bold text-gray-900 dark:text-white">
                    Following
                </h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">
                    Authors you are following.
                </p>
            </div>
            <a href="{{ route('followers.index') }}"
               class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                My Followers →
            </a>
        </div>

        @if($follows->isEmpty())
            <div class="text-center py-20">
                <div class="w-16 h-16 rounded-full bg-indigo-50 dark:bg-indigo-950
                        flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-indigo-400" fill="none"
                         stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Not following anyone yet
                </h3>
                <p class="text-sm text-gray-400 mb-6 max-w-sm mx-auto">
                    Visit an author's profile and click Follow to see their
                    articles in your feed.
                </p>
                <a href="{{ route('blog') }}"
                   class="inline-block px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700
                      text-white text-sm font-medium rounded-xl transition-colors">
                    Discover Authors
                </a>
            </div>

        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
                @foreach($follows as $follow)
                    @if($follow->following)
                        @php $author = $follow->following; @endphp

                        <div class="bg-white dark:bg-gray-900 rounded-2xl border
                                border-gray-100 dark:border-gray-800 p-5
                                hover:border-indigo-200 dark:hover:border-indigo-800
                                transition-colors">

                            {{-- Author Avatar + Name --}}
                            <div class="flex items-center gap-3 mb-4">
                                <a href="{{ route('author.profile', $author->username ?? $author->id) }}">
                                    <img src="{{ $author->avatar_url }}"
                                         alt="{{ $author->name }}"
                                         class="w-12 h-12 rounded-full object-cover
                                            border-2 border-indigo-100 dark:border-gray-700
                                            hover:opacity-80 transition-opacity">
                                </a>
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('author.profile', $author->username ?? $author->id) }}"
                                       class="font-semibold text-gray-900 dark:text-white
                                          hover:text-indigo-600 dark:hover:text-indigo-400
                                          transition-colors block truncate">
                                        {{ $author->display_name }}
                                    </a>
                                    @if($author->username)
                                        <p class="text-xs text-gray-400 font-mono">
                                            @{{ $author->username }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            {{-- Bio --}}
                            @if($author->bio)
                                <p class="text-sm text-gray-500 dark:text-gray-400
                                      leading-relaxed line-clamp-2 mb-4">
                                    {{ $author->bio }}
                                </p>
                            @endif

                            {{-- Stats --}}
                            <div class="flex items-center justify-between
                                    pt-4 border-t border-gray-50 dark:border-gray-800">
                            <span class="text-xs text-gray-400">
                                {{ $author->posts()->published()->count() }}
                                {{ Str::plural('article', $author->posts()->published()->count()) }}
                            </span>
                                <span class="text-xs text-gray-400">
                                {{ $author->followers()->count() }}
                                    {{ Str::plural('follower', $author->followers()->count()) }}
                            </span>
                            </div>

                            {{-- Unfollow Button --}}
                            <button
                                class="unfollow-btn mt-3 w-full px-4 py-2 text-xs font-medium
                                   rounded-xl border border-gray-200 dark:border-gray-700
                                   text-gray-600 dark:text-gray-400
                                   hover:bg-red-50 dark:hover:bg-red-950
                                   hover:text-red-600 dark:hover:text-red-400
                                   hover:border-red-200 dark:hover:border-red-800
                                   transition-colors"
                                data-author-id="{{ $author->id }}">
                                Unfollow
                            </button>

                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Pagination --}}
            {{ $follows->links() }}
        @endif

    </div>

    {{-- Unfollow from following page --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            document.addEventListener('click', async function (e) {

                const btn = e.target.closest('.unfollow-btn');
                if (!btn) return;

                if (btn.disabled) return;
                btn.disabled     = true;
                btn.textContent  = 'Unfollowing...';

                const authorId = btn.dataset.authorId;
                const card     = btn.closest('div.bg-white, div.dark\\:bg-gray-900');

                try {
                    const response = await fetch(`/authors/${authorId}/follow`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN':     csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type':     'application/json',
                        },
                    });

                    const data = await response.json();

                    if (response.ok && data.success && !data.following) {
                        /*
                        | Author unfollowed — animate card out and remove from DOM.
                        | Same pattern as bookmarks page.
                        */
                        const cardWrapper = btn.closest('.bg-white');
                        if (cardWrapper) {
                            cardWrapper.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                            cardWrapper.style.opacity    = '0';
                            cardWrapper.style.transform  = 'scale(0.95)';
                            setTimeout(() => {
                                cardWrapper.remove();
                                // Reload if grid is now empty
                                const remaining = document.querySelectorAll('.unfollow-btn');
                                if (remaining.length === 0) window.location.reload();
                            }, 300);
                        }
                    } else {
                        btn.disabled    = false;
                        btn.textContent = 'Unfollow';
                    }

                } catch (error) {
                    btn.disabled    = false;
                    btn.textContent = 'Unfollow';
                    console.error('Unfollow error:', error);
                }
            });

        });
    </script>

@endsection
