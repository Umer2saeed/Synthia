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
                        {{ $comment->user->display_name }}
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

            <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                {{ $comment->content }}
            </p>
        </div>

        <div class="flex items-center gap-3 mt-1 px-1">
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
        </div>
    </div>
</div>
