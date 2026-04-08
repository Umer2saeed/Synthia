@extends('frontend.layouts.app')

@section('content')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12">

        {{-- Page Header --}}
        <div class="mb-10">
            <h1 class="font-display text-3xl sm:text-4xl font-bold
                   text-gray-900 dark:text-white mb-2">
                My Bookmarks
            </h1>
            <p class="text-gray-500 dark:text-gray-400">
                Articles you have saved to read later.
            </p>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="mb-6 px-4 py-3 bg-green-50 dark:bg-green-950 border
                    border-green-200 dark:border-green-800
                    text-green-700 dark:text-green-400 text-sm rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        @if($bookmarks->isEmpty())
            {{--
            | Empty state — user has no bookmarks yet.
            | We give them a clear call to action to start reading.
            --}}
            <div class="text-center py-20">
                <div class="w-16 h-16 rounded-full bg-indigo-50 dark:bg-indigo-950
                        flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-indigo-400" fill="none"
                         stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    No saved articles yet
                </h3>
                <p class="text-sm text-gray-400 mb-6 max-w-sm mx-auto">
                    When you find an article you want to read later, click the
                    <strong>Save</strong> button on the post to bookmark it.
                </p>
                <a href="{{ route('blog') }}"
                   class="inline-block px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700
                      text-white text-sm font-medium rounded-xl transition-colors">
                    Browse Articles
                </a>
            </div>

        @else
            {{-- Bookmark count --}}
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                {{ $bookmarks->total() }} saved {{ Str::plural('article', $bookmarks->total()) }}
            </p>

            {{-- Bookmarked Posts Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                @foreach($bookmarks as $bookmark)
                    {{--
                    | $bookmark is a Bookmark model with an eager-loaded $post.
                    | We pass $bookmark->post as $post to the post card partial
                    | so the partial works exactly the same as on the blog page.
                    --}}
                    @if($bookmark->post)
                        <div class="relative">
                            @include('frontend.partials._post-card', ['post' => $bookmark->post])

                            {{--
                            | Remove bookmark button overlay.
                            | Positioned over the card — clicking removes this
                            | post from the user's bookmarks via AJAX.
                            --}}
                            <div class="absolute top-3 left-3 z-10">
                                <button
                                    class="remove-bookmark-btn flex items-center gap-1.5
                                       px-2.5 py-1.5 text-xs font-medium
                                       bg-white dark:bg-gray-900 rounded-lg shadow-sm
                                       border border-gray-100 dark:border-gray-700
                                       text-gray-600 dark:text-gray-400
                                       hover:text-red-600 dark:hover:text-red-400
                                       hover:border-red-200 dark:hover:border-red-800
                                       transition-colors"
                                    data-post-id="{{ $bookmark->post->id }}"
                                    title="Remove bookmark">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                    </svg>
                                    Saved
                                </button>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Pagination --}}
            {{ $bookmarks->links() }}

        @endif

    </div>

    {{-- JavaScript for removing bookmarks from the bookmarks page --}}
    <script>
        /*
        |--------------------------------------------------------------------------
        | Remove Bookmark on Bookmarks Page
        |--------------------------------------------------------------------------
        |
        | On the bookmarks page specifically, clicking "Saved" should:
        |   1. Send the toggle request (which removes the bookmark)
        |   2. Animate the card out of the grid
        |   3. Update the bookmark count
        |   4. Show empty state if no bookmarks left
        |
        | This is different from the post page where we just toggle the button.
        | Here we actually REMOVE the card from the DOM because the post
        | no longer belongs in the user's bookmarks list.
        */
        document.addEventListener('DOMContentLoaded', function () {

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            /*
            | Event delegation — listen on the document for clicks on
            | any .remove-bookmark-btn button. This works even if buttons
            | are dynamically added later.
            */
            document.addEventListener('click', async function (e) {

                const btn = e.target.closest('.remove-bookmark-btn');
                if (!btn) return; // click was not on a remove button

                const postId    = btn.dataset.postId;
                const cardWrap  = btn.closest('.relative'); // the card wrapper div

                // Prevent double clicks
                if (btn.disabled) return;
                btn.disabled = true;
                btn.textContent = 'Removing...';

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

                    if (response.ok && data.success && !data.bookmarked) {
                        /*
                        |--------------------------------------------------------------
                        | Animate the card out and remove it from DOM
                        |--------------------------------------------------------------
                        | We use a CSS transition to fade and shrink the card
                        | before removing it. This feels much more polished
                        | than just instantly deleting the element.
                        */
                        cardWrap.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        cardWrap.style.opacity    = '0';
                        cardWrap.style.transform  = 'scale(0.95)';

                        setTimeout(() => {
                            cardWrap.remove();

                            /*
                            |----------------------------------------------------------
                            | Update the bookmark count display
                            |----------------------------------------------------------
                            | After removing a card, update the "X saved articles" text.
                            | We also check if the grid is now empty and show
                            | the empty state if so.
                            */
                            const remainingCards = document.querySelectorAll('.remove-bookmark-btn');
                            const countEl        = document.querySelector('p.text-sm.text-gray-500');

                            if (remainingCards.length === 0) {
                                // No bookmarks left — reload page to show empty state
                                // We reload because the empty state HTML is complex
                                window.location.reload();
                            } else if (countEl) {
                                // Update the count text
                                const count = remainingCards.length;
                                countEl.textContent = count + ' saved ' +
                                    (count === 1 ? 'article' : 'articles');
                            }

                        }, 300);

                    } else {
                        // Failed — re-enable button
                        btn.disabled = false;
                        btn.textContent = 'Saved';
                    }

                } catch (error) {
                    btn.disabled = false;
                    btn.textContent = 'Saved';
                    console.error('Remove bookmark error:', error);
                }
            });

        });
    </script>

@endsection
