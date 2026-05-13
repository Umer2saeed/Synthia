<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CommentFlag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentFlagController extends Controller
{
    public function flag(Request $request, Comment $comment): RedirectResponse
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:200'],
        ]);

        // Prevent flagging your own comment
        if ($comment->user_id === auth()->id()) {
            return back()->with('error', 'You cannot flag your own comment.');
        }

        // Prevent duplicate flags
        CommentFlag::firstOrCreate(
            ['comment_id' => $comment->id, 'user_id' => auth()->id()],
            ['reason' => $request->reason, 'created_at' => now()]
        );

        return back()->with('success', 'Comment reported. Thank you.');
    }
}
