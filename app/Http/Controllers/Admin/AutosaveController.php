<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostDraft;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AutosaveController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | save() — Save or update the autosave draft
    |--------------------------------------------------------------------------
    | Called by JavaScript every 60 seconds when content has changed.
    |
    | WHY updateOrCreate?
    | We want exactly ONE draft per user per post at all times.
    | updateOrCreate either creates a new row or updates the existing one.
    | This prevents stale drafts accumulating in the database.
    */
    public function save(Request $request): JsonResponse
    {
        $request->validate([
            'post_id' => ['nullable', 'integer', 'exists:posts,id'],
            'title'   => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
        ]);

        $draft = PostDraft::updateOrCreate(
        /*
        | Match on user + post combination.
        | For new posts: post_id is null (matched as null).
        */
            [
                'user_id' => auth()->id(),
                'post_id' => $request->input('post_id'),
            ],
            /*
            | Update these fields on every autosave.
            */
            [
                'title'    => $request->input('title'),
                'content'  => $request->input('content'),
                'saved_at' => now(),
            ]
        );

        return response()->json([
            'success'  => true,
            'draft_id' => $draft->id,
            'saved_at' => $draft->saved_at->toIso8601String(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | discard() — Delete an autosave draft
    |--------------------------------------------------------------------------
    | Called when:
    |   1. Author successfully saves/publishes the post
    |   2. Author clicks "Dismiss" on the restore banner
    */
    public function discard(Request $request): JsonResponse
    {
        $request->validate([
            'post_id' => ['nullable', 'integer'],
        ]);

        PostDraft::where('user_id', auth()->id())
            ->where('post_id', $request->input('post_id'))
            ->delete();

        return response()->json(['success' => true]);
    }
}
