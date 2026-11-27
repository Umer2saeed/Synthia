<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CommentFlag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    public function index()
    {
        // Pending = waiting for a moderator (is_approved = false)
        $pendingComments = Comment::where('is_approved', false)
            ->with(['user', 'post:id,title,slug'])
            ->withCount('flags')
            ->orderByDesc('created_at')
            ->paginate(20, ['*'], 'pending_page');

        // Flagged = public comments that readers have reported
        $flaggedComments = Comment::where('is_approved', true)
            ->whereHas('flags')
            ->with(['user', 'post:id,title,slug', 'flags.user'])
            ->withCount('flags')
            ->orderByDesc('flags_count')
            ->paginate(20, ['*'], 'flagged_page');

        $pendingCount = Comment::where('is_approved', false)->count();
        $flaggedCount = Comment::where('is_approved', true)->whereHas('flags')->count();

        return view('admin.moderation.index', compact(
            'pendingComments',
            'flaggedComments',
            'pendingCount',
            'flaggedCount'
        ));
    }

    public function approve(Comment $comment): RedirectResponse
    {
        $comment->update(['is_approved' => true]);

        // The moderator has reviewed it, so old reports no longer apply.
        $comment->flags()->delete();

        return back()->with('success', 'Comment approved.');
    }

    // A true/false column has no "rejected" state, so rejecting removes the comment.
    public function reject(Comment $comment): RedirectResponse
    {
        $comment->delete();

        return back()->with('success', 'Comment removed.');
    }

    public function dismiss(Comment $comment): RedirectResponse
    {
        $comment->flags()->delete();

        return back()->with('success', 'Flags dismissed. The comment stays published.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => ['required', 'in:approve,reject'],
            'ids'    => ['required', 'array', 'min:1'],
            'ids.*'  => ['integer', 'exists:comments,id'],
        ]);

        if ($request->action === 'approve') {
            CommentFlag::whereIn('comment_id', $request->ids)->delete();

            $count = Comment::whereIn('id', $request->ids)->update(['is_approved' => true]);

            return back()->with('success', "{$count} comment(s) approved.");
        }

        $count = Comment::whereIn('id', $request->ids)->delete();

        return back()->with('success', "{$count} comment(s) removed.");
    }
}
