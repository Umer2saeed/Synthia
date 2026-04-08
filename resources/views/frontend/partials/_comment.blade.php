<div class="comment-item flex gap-3 group" data-comment-id="{{ $comment->id }}">
    <img src="{{ $comment->user->avatar_url }}"
         alt="{{ $comment->user->name }}"
         class="w-9 h-9 rounded-full object-cover border-2 border-gray-100 dark:border-gray-700 shrink-0 mt-0.5">

    <div class="flex-1">
        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl px-4 py-3">
            <div class="flex items-center justify-between mb-1 flex-wrap gap-1">
                <span class="text-sm font-semibold text-gray-900 dark:text-white">
                    {{ $comment->user->display_name }}
                </span>
                <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
            </div>
            <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                {{ $comment->content }}
            </p>
        </div>

        @auth
            @if($comment->user_id === auth()->id() || auth()->user()->can('delete comments'))
                <div class="mt-1 px-2">
                    <button type="button"
                            class="delete-comment-btn text-xs text-red-400 hover:text-red-600
                                   opacity-0 group-hover:opacity-100 transition-opacity"
                            data-comment-id="{{ $comment->id }}">
                        Delete
                    </button>
                </div>
            @endif
        @endauth
    </div>
</div>
