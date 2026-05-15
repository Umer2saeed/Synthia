<div class="comment-item flex gap-3 group"
     data-comment-id="{{ $comment->id }}">

    <img src="{{ $comment->user->avatar_url }}"
         alt="{{ $comment->user->name }}"
         class="w-8 h-8 rounded-full object-cover
                border border-gray-200 dark:border-gray-700
                shrink-0 mt-0.5">

    <div class="flex-1">
        <div class="bg-gray-50 dark:bg-gray-700 rounded-xl px-4 py-3">

            <div class="flex items-center justify-between mb-1 flex-wrap gap-1">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                        {{ $comment->user->display_name ?? $comment->user->name }}
                    </span>
                    @if(!$comment->is_approved)
                        <span class="px-2 py-0.5
                                     bg-yellow-100 dark:bg-yellow-900
                                     text-yellow-700 dark:text-yellow-400
                                     text-xs rounded-full">
                            Pending
                        </span>
                    @endif
                </div>
                <span class="text-xs text-gray-400 dark:text-gray-500">
                    {{ $comment->created_at->diffForHumans() }}
                </span>
            </div>

            <p class="text-sm text-gray-700 dark:text-gray-300
                      leading-relaxed whitespace-pre-line">
                {{ $comment->content }}
            </p>
        </div>

        <div class="flex items-center justify-between mt-1.5 px-1">

            {{-- Left side: actions --}}
            <div class="flex items-center gap-3">

                {{--
                | DELETE BUTTON
                | Uses class "delete-comment-btn" which is bound by JavaScript.
                | IMPORTANT: The Report button must NOT have this class.
                --}}
                @if(auth()->check() && (
                    $comment->user_id === auth()->id() ||
                    auth()->user()->can('delete comments')
                ))
                    <button
                        type="button"
                        class="delete-comment-btn text-xs
                   text-red-400 dark:text-red-500
                   hover:text-red-600 dark:hover:text-red-400
                   opacity-0 group-hover:opacity-100 transition"
                        data-comment-id="{{ $comment->id }}">
                        Delete
                    </button>
                @endif

                {{--
                | REPORT BUTTON
                | Completely separate from Delete.
                | Does NOT use "delete-comment-btn" class.
                | Uses a standard form POST — no JavaScript dependency.
                | event.stopPropagation() prevents the click from bubbling to
                | any parent element that might have its own click handler.
                --}}
                @auth
                    @if(auth()->id() !== $comment->user_id)
                        <form
                            action="{{ route('comments.flag', ['comment' => $comment->id]) }}"
                            method="POST"
                            class="inline"
                            onsubmit="event.stopPropagation();">
                            @csrf
                            <button
                                type="submit"
                                title="Report this comment"
                                class="report-comment-btn text-xs
                           text-gray-400 dark:text-gray-600
                           hover:text-amber-500 dark:hover:text-amber-400
                           opacity-0 group-hover:opacity-100 transition"
                                onclick="event.stopPropagation(); return confirm('Report this comment as inappropriate?');">
                                🚩 Report
                            </button>
                        </form>
                    @endif
                @endauth

            </div>


            {{-- Right side: Like button --}}
            <div class="flex items-center">

                @php
                    /*
                    | Determine if current user has liked this comment.
                    |
                    | $likedCommentIds is passed from BlogController@show.
                    | In the AJAX context (after posting a new comment),
                    | this variable may not exist — we default to false.
                    |
                    | $comment->likes_count is added by withCount('likes')
                    | in the controller. For newly posted comments returned
                    | via AJAX, likes_count starts at 0.
                    */
                    $isLiked    = isset($likedCommentIds)
                                  && in_array($comment->id, $likedCommentIds);
                    $likeCount  = $comment->likes_count ?? 0;
                    $isOwn      = auth()->check() && auth()->id() === $comment->user_id;
                @endphp

                @auth
                    @if(!$isOwn)
                        {{--
                        | Like button — shown to authenticated users who
                        | did not write this comment.
                        |
                        | data-comment-id: used by JS to identify which comment
                        | data-liked:      current like state (true/false string)
                        |                  JS reads this to know the current state
                        --}}
                        <button
                            type="button"
                            class="like-btn flex items-center gap-1.5
                           text-xs font-medium transition-all duration-200
                           {{ $isLiked
                               ? 'text-red-500 dark:text-red-400'
                               : 'text-gray-400 dark:text-gray-500 hover:text-red-400 dark:hover:text-red-400' }}"
                            data-comment-id="{{ $comment->id }}"
                            data-liked="{{ $isLiked ? 'true' : 'false' }}">

                            {{-- Heart icon — filled when liked, outline when not --}}
                            @if($isLiked)
                                {{-- Filled heart --}}
                                <svg class="like-icon w-3.5 h-3.5 transition-transform duration-200"
                                     viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 21.593c-5.63-5.539-11-10.297-11-14.402
                                     C1 3.518 2.995 2 5 2c1.557 0 3.064.749 4 2
                                     .937-1.25 2.443-2 4-2 2.006 0 4 1.518 4 5.191
                                     0 4.104-5.369 8.862-11 14.402z"/>
                                </svg>
                            @else
                                {{-- Outline heart --}}
                                <svg class="like-icon w-3.5 h-3.5 transition-transform duration-200"
                                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06
                                     a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78
                                     1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                </svg>
                            @endif

                            {{-- Like count — hidden when 0 --}}
                            <span class="like-count">
                        {{ $likeCount > 0 ? $likeCount : '' }}
                    </span>
                        </button>

                    @else
                        {{--
                        | Comment owner — show a read-only like count.
                        | No button — you cannot like your own comment.
                        | We still show the count so they can see engagement.
                        --}}
                        @if($likeCount > 0)
                            <span class="flex items-center gap-1.5 text-xs
                                 text-gray-400 dark:text-gray-500">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24"
                             fill="currentColor" class="text-red-400">
                            <path d="M12 21.593c-5.63-5.539-11-10.297-11-14.402
                                     C1 3.518 2.995 2 5 2c1.557 0 3.064.749 4 2
                                     .937-1.25 2.443-2 4-2 2.006 0 4 1.518 4 5.191
                                     0 4.104-5.369 8.862-11 14.402z"/>
                        </svg>
                        {{ $likeCount }}
                    </span>
                        @endif

                    @endif

                @else
                    {{--
                    | Guest — show like count if any, no interactive button.
                    | Clicking does nothing — they need to log in.
                    --}}
                    @if($likeCount > 0)
                        <span class="flex items-center gap-1.5 text-xs
                             text-gray-400 dark:text-gray-500">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor"
                         stroke-width="2">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06
                                 a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78
                                 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                    {{ $likeCount }}
                </span>
                    @endif
                @endauth

            </div>
        </div>
    </div>
</div>
