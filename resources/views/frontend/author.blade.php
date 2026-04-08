@extends('frontend.layouts.app')

@section('content')

    {{-- =============================================
         AUTHOR HERO SECTION
         ============================================= --}}
    <section class="bg-gradient-to-br from-gray-50 via-white to-indigo-50
                dark:from-gray-950 dark:via-gray-900 dark:to-indigo-950
                border-b border-gray-100 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">

                {{-- Avatar --}}
                <div class="shrink-0">
                    <img src="{{ $author->avatar_url }}"
                         alt="{{ $author->name }}"
                         class="w-24 h-24 sm:w-32 sm:h-32 rounded-full object-cover
                            border-4 border-white dark:border-gray-800
                            shadow-lg">
                </div>

                {{-- Author Info --}}
                <div class="flex-1 text-center sm:text-left">

                    {{-- Name + Role Badge --}}
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-1">
                        <h1 class="font-display text-2xl sm:text-3xl font-bold
                               text-gray-900 dark:text-white">
                            {{ $author->name }}
                        </h1>

                        @php
                            $role      = $author->roles->first();
                            $roleColor = match($role?->name) {
                                'admin'  => 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-400',
                                'editor' => 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-400',
                                'author' => 'bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-400',
                                default  => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                            };
                        @endphp
                        @if($role)
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium {{ $roleColor }}">
                            {{ ucfirst($role->name) }}
                        </span>
                        @endif
                    </div>

                    {{-- Username --}}
                    @if($author->username)
                        <p class="text-sm text-gray-400 font-mono mb-3">
                            @ {{ $author->username }}
                        </p>
                    @endif

                    {{-- Bio --}}
                    @if($author->bio)
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed max-w-xl mb-4">
                            {{ $author->bio }}
                        </p>
                    @endif

                    {{-- Stats Row --}}
                    <div class="flex flex-wrap justify-center sm:justify-start gap-6 mb-4">
                        <div class="text-center sm:text-left">
                            <p class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ $author->posts_count }}
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ Str::plural('Article', $author->posts_count) }}
                            </p>
                        </div>
                        <div class="w-px bg-gray-200 dark:bg-gray-700 hidden sm:block"></div>

                        {{-- Total claps received across all posts --}}
                        @php
                            $totalAuthorClaps = $author->posts()
                                                       ->published()
                                                       ->withSum('claps', 'count')
                                                       ->get()
                                                       ->sum('claps_sum_count');
                        @endphp
                        <div class="text-center sm:text-left">
                            <p class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ number_format($totalAuthorClaps) }}
                            </p>
                            <p class="text-xs text-gray-400">Total Claps</p>
                        </div>
                        <div class="w-px bg-gray-200 dark:bg-gray-700 hidden sm:block"></div>


                        <div class="text-center sm:text-left">
                            {{--
                            | id="followers-count-stat" — updated by JS when follow is toggled
                            | This is the stat in the hero row (different from the one below the button)
                            --}}
                            <p class="text-xl font-bold text-gray-900 dark:text-white"
                               id="followers-count-stat">
                                {{ $author->followers_count }}
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ Str::plural('Follower', $author->followers_count) }}
                            </p>
                        </div>
                        <div class="w-px bg-gray-200 dark:bg-gray-700 hidden sm:block"></div>
                        <div class="text-center sm:text-left">
                            <p class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ $author->following_count }}
                            </p>
                            <p class="text-xs text-gray-400">Following</p>
                        </div>
                        <div class="w-px bg-gray-200 dark:bg-gray-700 hidden sm:block"></div>
                        <div class="text-center sm:text-left">
                            <p class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ $author->created_at->format('M Y') }}
                            </p>
                            <p class="text-xs text-gray-400">Member Since</p>
                        </div>
                    </div>

                    {{-- Follow Button --}}
                    @auth
                        @if(auth()->id() !== $author->id)
                            {{--
                            | Logged-in user viewing someone else's profile.
                            | Show the follow/unfollow button.
                            |
                            | data-author-id    → sent to the server in the AJAX request
                            | data-following    → initial state ('true' or 'false')
                            | data-author-name  → used in the button label
                            --}}
                            <button
                                id="follow-btn"
                                data-author-id="{{ $author->id }}"
                                data-following="{{ $isFollowing ? 'true' : 'false' }}"
                                data-author-name="{{ $author->display_name }}"
                                class="px-5 py-2 text-sm font-medium rounded-xl transition-all duration-200
                   {{ $isFollowing
                       ? 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-red-50 dark:hover:bg-red-950 hover:text-red-600 dark:hover:text-red-400 hover:border-red-200 dark:hover:border-red-800'
                       : 'bg-indigo-600 hover:bg-indigo-700 text-white border border-indigo-600' }}">
                                {{ $isFollowing ? 'Following' : 'Follow' }}
                            </button>

                        @else
                            {{-- Viewing own profile --}}
                            <a href="{{ route('frontend.profile.edit') }}"
                               class="px-5 py-2 text-sm font-medium rounded-xl
                  bg-gray-100 dark:bg-gray-800
                  text-gray-700 dark:text-gray-300
                  border border-gray-200 dark:border-gray-700
                  hover:bg-gray-200 dark:hover:bg-gray-700
                  transition-colors">
                                Edit Profile
                            </a>
                        @endif
                    @else
                        {{-- Guest sees a Follow button that redirects to login --}}
                        <a href="{{ route('login') }}"
                           class="px-5 py-2 text-sm font-medium rounded-xl
              bg-indigo-600 hover:bg-indigo-700
              text-white border border-indigo-600
              transition-colors">
                            Follow
                        </a>
                    @endauth

                    {{-- Followers count display — updated by JavaScript after toggle --}}
                    <p class="text-sm text-gray-400 mt-2">
                        <span id="followers-count">{{ $author->followers_count }}</span>
                        {{ Str::plural('follower', $author->followers_count) }}
                    </p>

                </div>
            </div>
        </div>
    </section>

    {{-- =============================================
         POSTS + SIDEBAR
         ============================================= --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">

            {{-- Posts Grid --}}
            <div class="lg:col-span-2">

                {{-- Section Header --}}
                <div class="flex items-center justify-between mb-6">
                    <h2 class="font-display text-xl font-bold text-gray-900 dark:text-white">
                        Articles by {{ $author->display_name }}
                    </h2>
                    <span class="text-sm text-gray-400">
                    {{ $posts->total() }} {{ Str::plural('article', $posts->total()) }}
                </span>
                </div>

                {{-- Posts --}}
                @if($posts->isEmpty())
                    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100
                            dark:border-gray-800 p-12 text-center">
                        <p class="text-4xl mb-4">✍️</p>
                        <p class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">
                            No articles yet
                        </p>
                        <p class="text-sm text-gray-400">
                            {{ $author->display_name }} hasn't published any articles yet.
                            Check back later!
                        </p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                        @foreach($posts as $post)
                            @include('frontend.partials._post-card', compact('post'))
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    {{ $posts->links() }}
                @endif

            </div>

            {{-- Sidebar --}}
            <aside class="space-y-6">

                {{-- Author Meta Card --}}
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100
                        dark:border-gray-800 p-5">
                    <h3 class="font-display font-bold text-gray-900 dark:text-white
                           text-base mb-4">
                        About the Author
                    </h3>

                    <div class="space-y-3">
                        {{-- Member since --}}
                        <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Member since
                        </span>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                            {{ $author->created_at->format('d M Y') }}
                        </span>
                        </div>

                        {{-- Total articles --}}
                        <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Articles published
                        </span>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                            {{ $author->posts_count }}
                        </span>
                        </div>

                        {{-- Followers --}}
                        <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Followers
                        </span>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                            {{ $author->followers_count }}
                        </span>
                        </div>
                    </div>
                </div>

                {{-- Categories + Tags sidebar --}}
                @include('frontend.partials._sidebar')

            </aside>

        </div>
    </div>

    <script>
        /*
        |--------------------------------------------------------------------------
        | Follow Button JavaScript
        |--------------------------------------------------------------------------
        |
        | COMPLETE FLOW:
        |
        | 1. User clicks Follow button
        | 2. We immediately update button visually (optimistic UI)
        |    Follow → Following (if following)
        |    Following → Follow (if unfollowing)
        | 3. fetch() POST /authors/{id}/follow
        | 4. FollowController@toggle runs
        |    → isFollowing? YES → unfollow → { following: false, followers_count: X }
        |    → isFollowing? NO  → follow   → { following: true,  followers_count: X }
        | 5. We update button and follower counts with server's real values
        |
        | HOVER BEHAVIOR:
        | When hovering over "Following" button, we show "Unfollow" text
        | with a red tint. This is the standard pattern (like Twitter/GitHub).
        | It tells the user what will happen if they click.
        */
        document.addEventListener('DOMContentLoaded', function () {

            const followBtn      = document.getElementById('follow-btn');
            const csrfToken      = document.querySelector('meta[name="csrf-token"]')?.content;

            // No follow button means the user is viewing their own profile
            if (!followBtn) return;

            /*
            |----------------------------------------------------------------------
            | Read initial state from data attributes
            |----------------------------------------------------------------------
            */
            const authorId      = followBtn.dataset.authorId;
            const authorName    = followBtn.dataset.authorName;
            let   isFollowing   = followBtn.dataset.following === 'true';
            let   isRequesting  = false;

            /*
            |----------------------------------------------------------------------
            | DOM elements to update
            |----------------------------------------------------------------------
            | There are two follower count displays:
            |   1. followersCount    → small text below the button
            |   2. followersCountStat → the big number in the stats row
            | We update both simultaneously.
            */
            const followersCount     = document.getElementById('followers-count');
            const followersCountStat = document.getElementById('followers-count-stat');

            /*
            |----------------------------------------------------------------------
            | updateButtonState() — update button appearance
            |----------------------------------------------------------------------
            | @param following (boolean)
            |   true  = currently following → show "Following" (green/gray)
            |   false = not following       → show "Follow" (indigo)
            */
            function updateButtonState(following) {
                if (following) {
                    /*
                    | FOLLOWING STATE
                    | Gray background, "Following" text
                    | On hover → red tint hinting at unfollow action
                    */
                    followBtn.textContent = 'Following';
                    followBtn.className = `px-5 py-2 text-sm font-medium rounded-xl
                transition-all duration-200
                bg-gray-100 dark:bg-gray-800
                text-gray-700 dark:text-gray-300
                border border-gray-200 dark:border-gray-700
                hover:bg-red-50 dark:hover:bg-red-950
                hover:text-red-600 dark:hover:text-red-400
                hover:border-red-200 dark:hover:border-red-800`;

                } else {
                    /*
                    | NOT FOLLOWING STATE
                    | Indigo background, "Follow" text
                    */
                    followBtn.textContent = 'Follow';
                    followBtn.className = `px-5 py-2 text-sm font-medium rounded-xl
                transition-all duration-200
                bg-indigo-600 hover:bg-indigo-700
                text-white border border-indigo-600`;
                }
            }

            /*
            |----------------------------------------------------------------------
            | updateFollowerCounts() — update both count displays
            |----------------------------------------------------------------------
            | @param count (integer) — the new follower count from server
            */
            function updateFollowerCounts(count) {
                const formatted = count.toLocaleString();
                if (followersCount)     followersCount.textContent     = formatted;
                if (followersCountStat) followersCountStat.textContent = formatted;
            }

            /*
            |----------------------------------------------------------------------
            | Hover effect — show "Unfollow" text on hover when following
            |----------------------------------------------------------------------
            | This is a UX pattern that hints at what clicking will do.
            | It only applies when the user IS following.
            */
            followBtn.addEventListener('mouseenter', function () {
                if (isFollowing) {
                    this.textContent = 'Unfollow';
                }
            });

            followBtn.addEventListener('mouseleave', function () {
                if (isFollowing) {
                    this.textContent = 'Following';
                }
            });

            /*
            |----------------------------------------------------------------------
            | Click handler — main follow/unfollow logic
            |----------------------------------------------------------------------
            */
            followBtn.addEventListener('click', async function () {

                // Prevent duplicate requests
                if (isRequesting) return;
                isRequesting = true;

                /*
                |------------------------------------------------------------------
                | Optimistic UI — toggle immediately
                |------------------------------------------------------------------
                | Flip the state and update the button before server responds.
                | Also update the follower count optimistically.
                */
                isFollowing = !isFollowing;
                updateButtonState(isFollowing);

                // Optimistically update count
                const currentCount = parseInt(
                    followersCount?.textContent.replace(/,/g, '') || '0'
                );
                const optimisticCount = isFollowing
                    ? currentCount + 1  // just followed → count goes up
                    : currentCount - 1; // just unfollowed → count goes down
                updateFollowerCounts(Math.max(0, optimisticCount));

                try {
                    /*
                    |------------------------------------------------------------------
                    | Send AJAX request
                    |------------------------------------------------------------------
                    | URL: /authors/{authorId}/follow
                    | Method: POST
                    |
                    | We send NO body — the author ID is in the URL.
                    | The controller uses route model binding to find the User.
                    |
                    | WHY no body?
                    | The URL already contains all the information needed.
                    | POST /authors/7/follow means "toggle follow on user 7".
                    | No additional data is needed in the request body.
                    */
                    const response = await fetch(`/authors/${authorId}/follow`, {
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
                        | Replace our optimistic values with the server's truth.
                        | The server counts directly from the database so it is
                        | always accurate — no race conditions.
                        */
                        isFollowing = data.following;
                        updateButtonState(data.following);
                        updateFollowerCounts(data.followers_count);

                    } else {
                        /*
                        |--------------------------------------------------------------
                        | Server error — revert optimistic update
                        |--------------------------------------------------------------
                        */
                        isFollowing = !isFollowing;
                        updateButtonState(isFollowing);
                        updateFollowerCounts(currentCount);
                        console.error('Follow toggle failed:', data.message);
                    }

                } catch (error) {
                    /*
                    |------------------------------------------------------------------
                    | Network error — revert
                    |------------------------------------------------------------------
                    */
                    isFollowing = !isFollowing;
                    updateButtonState(isFollowing);
                    updateFollowerCounts(currentCount);
                    console.error('Network error:', error);

                } finally {
                    isRequesting = false;
                }
            });

        });
    </script>

@endsection
