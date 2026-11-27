<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CommentFlag;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentFlagController extends Controller
{
    /*
    | AUTO-HIDE
    |   0 = a report never hides a comment (default). Moderators decide in /admin/moderation.
    |   3 = once 3 different people report a comment it disappears from the post
    |       and goes to the Pending queue until a moderator approves or removes it.
    */
    private const AUTO_HIDE_AT_FLAGS = 0;

    public function flag(Request $request, Comment $comment): JsonResponse|RedirectResponse
    {
        $request->validate(['reason' => ['nullable', 'string', 'max:200']]);

        $user = $request->user();

        if ($comment->user_id === $user->id) {
            return $this->respond($request, false, 'You cannot report your own comment.', 422);
        }

        if (! $comment->is_approved) {
            return $this->respond($request, false, 'This comment is not public yet.', 422);
        }

        $already = CommentFlag::where('comment_id', $comment->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($already) {
            return $this->respond($request, true, 'You have already reported this comment.');
        }

        try {
            // forceFill writes the columns even if CommentFlag::$fillable is incomplete.
            (new CommentFlag())->forceFill([
                'comment_id' => $comment->id,
                'user_id'    => $user->id,
                'reason'     => $request->input('reason'),
                'created_at' => now(),
            ])->save();
        } catch (QueryException $e) {
            // Two quick clicks hit the unique(comment_id, user_id) rule.
            return $this->respond($request, true, 'You have already reported this comment.');
        }

        if (self::AUTO_HIDE_AT_FLAGS > 0
            && CommentFlag::where('comment_id', $comment->id)->count() >= self::AUTO_HIDE_AT_FLAGS) {
            $comment->update(['is_approved' => false]);
        }

        return $this->respond($request, true, 'Comment reported. Thank you.');
    }

    /*
    | JSON for the AJAX Report button on the post page,
    | a normal redirect with a flash message for plain form posts.
    */
    private function respond(Request $request, bool $ok, string $message, int $status = 200): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => $ok, 'message' => $message], $ok ? 200 : $status);
        }

        return back()->with($ok ? 'success' : 'error', $message);
    }
}
