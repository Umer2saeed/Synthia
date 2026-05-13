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
        /*
        | Use the same scope names your Comment model already defines.
        | Check app/Models/Comment.php for scopeApproved() and scopePending().
        | If your comment status column is named differently, adjust here.
        */
        $pendingComments = Comment::where('is_approved', 'pending')
            ->with(['user', 'post:id,title,slug'])
            ->withCount('flags')
            ->orderByDesc('created_at')
            ->paginate(20, ['*'], 'pending_page');

        $flaggedComments = Comment::where('is_approved', 'approved')
            ->whereHas('flags')
            ->with(['user', 'post:id,title,slug', 'flags.user'])
            ->withCount('flags')
            ->orderByDesc('flags_count')
            ->paginate(20, ['*'], 'flagged_page');

        $pendingCount = Comment::where('is_approved', 'pending')->count();
        $flaggedCount = Comment::where('is_approved', 'approved')
            ->whereHas('flags')
            ->count();

        return view('admin.moderation.index', compact(
            'pendingComments',
            'flaggedComments',
            'pendingCount',
            'flaggedCount'
        ));
    }

    public function approve(Comment $comment): RedirectResponse
    {
        $comment->update(['is_approved' => 'approved']);

        return back()->with('success', 'Comment approved.');
    }

    public function reject(Comment $comment): RedirectResponse
    {
        $comment->update(['is_approved' => 'rejected']);

        return back()->with('success', 'Comment rejected.');
    }

    public function dismiss(Comment $comment): RedirectResponse
    {
        // Dismiss all flags — comment stays approved
        $comment->flags()->delete();

        return back()->with('success', 'Flags dismissed.');
    }

    /*
    | Bulk approve or reject multiple comments at once.
    */
    public function bulk(Request $request): RedirectResponse
    {
        $request->validate([
            'action'  => ['required', 'in:approve,reject'],
            'ids'     => ['required', 'array', 'min:1'],
            'ids.*'   => ['integer', 'exists:comments,id'],
        ]);

        $newStatus = $request->action === 'approve' ? 'approved' : 'rejected';

        $count = Comment::whereIn('id', $request->ids)->update(['status' => $newStatus]);

        return back()->with('success', "{$count} " . \Str::plural('comment', $count) . " {$newStatus}.");
    }
}
