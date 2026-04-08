<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | index() — Admin panel: list all comments with filters
    |--------------------------------------------------------------------------
    | Only admin and editor reach this — enforced by route middleware
    | 'permission:delete comments'. This is a double safety check.
    */
    public function index(Request $request)
    {
        $this->authorizeManage();

        $query = Comment::with(['user', 'post'])->latest();

        // Filter by approval status: ?status=pending or ?status=approved
        if ($request->filled('status')) {
            $query->where(
                'is_approved',
                $request->status === 'approved' ? true : false
            );
        }

        // Search by comment content
        if ($request->filled('search')) {
            $query->where('content', 'like', '%' . $request->search . '%');
        }

        $comments = $query->paginate(20)->withQueryString();

        return view('admin.comments.index', compact('comments'));
    }

    /*
    |--------------------------------------------------------------------------
    | destroy() — Delete a comment
    |--------------------------------------------------------------------------
    | Admin panel delete — only admin and editor reach this route.
    | They can delete ANY comment regardless of ownership.
    */
    public function destroy(Request $request, Comment $comment): JsonResponse
    {
        $this->authorizeManage();

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | approve() — Toggle comment approval status
    |--------------------------------------------------------------------------
    | Flips is_approved true ↔ false.
    | Only admin and editor can approve/unapprove.
    */
    public function approve(Comment $comment): JsonResponse
    {
        $this->authorizeManage();

        $comment->update([
            'is_approved' => !$comment->is_approved,
        ]);

        return response()->json([
            'success'     => true,
            'is_approved' => $comment->is_approved,
            'message'     => $comment->is_approved
                ? 'Comment approved.'
                : 'Comment unapproved.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | authorizeManage() — Reusable permission check
    |--------------------------------------------------------------------------
    | Double safety net on top of route middleware.
    | Aborts 403 if user does not have 'delete comments'.
    */
    private function authorizeManage(): void
    {
        if (!auth()->user()->can('delete comments')) {
            abort(403, 'You do not have permission to manage comments.');
        }
    }
}
