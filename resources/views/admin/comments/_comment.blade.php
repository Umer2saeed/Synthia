{{--
|--------------------------------------------------------------------------
| Single Comment Partial
|--------------------------------------------------------------------------
| Used in two places:
|   1. posts/show.blade.php — looped to render existing comments on load
|   2. CommentController@store — rendered to HTML string, returned as JSON
|
| The `data-comment-id` attribute is used by the delete JS handler.
--}}
<div class="comment-item flex gap-3 group"
     data-comment-id="{{ $comment->id }}">

    {{-- User Avatar --}}
    <img src="{{ $comment->user->avatar_url }}"
         alt="{{ $comment->user->name }}"
         class="w-8 h-8 rounded-full object-cover border border-gray-200 shrink-0 mt-0.5">

    {{-- Comment Body --}}
    <div class="flex-1">
        <div class="bg-gray-50 rounded-xl px-4 py-3">

            {{-- Author + Timestamp --}}
            <div class="flex items-center justify-between mb-1 flex-wrap gap-1">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-gray-800">
                        {{ $comment->user->display_name }}
                    </span>

                    {{-- Pending badge — shown to admins/editors only --}}
                    @if(!$comment->is_approved)
                        <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 text-xs rounded-full">
                            Pending
                        </span>
                    @endif
                </div>

                <span class="text-xs text-gray-400">
                    {{ $comment->created_at->diffForHumans() }}
                </span>
            </div>

            {{-- Comment Content --}}
            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">
                {{ $comment->content }}
            </p>
        </div>

        {{-- Action Links: Delete (own comment or admin/editor) --}}
        <div class="flex items-center gap-3 mt-1 px-1">
            @if(auth()->check() && (
                $comment->user_id === auth()->id() ||
                auth()->user()->can('delete comments')
            ))
                <button
                    type="button"
                    class="delete-comment-btn text-xs text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition"
                    data-comment-id="{{ $comment->id }}">
                    Delete
                </button>
            @endif
        </div>
    </div>
</div>
