<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BulkPostController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | apply() — Apply a bulk action to selected posts
    |--------------------------------------------------------------------------
    |
    | SECURITY:
    | We do NOT trust the client to tell us which posts the user can edit.
    | For each post we re-check the permission server-side.
    | An author cannot bulk-publish posts by hacking the form.
    |
    | FLOW:
    |   1. Validate: action is valid, at least one post selected
    |   2. Fetch only posts the current user can actually see
    |   3. For each post: check permission, apply action
    |   4. Redirect with count of successfully affected posts
    */
    public function apply(Request $request): RedirectResponse
    {
        $request->validate([
            'action'   => ['required', 'string', 'in:publish,draft,trash,delete'],
            'post_ids' => ['required', 'array', 'min:1'],
            'post_ids.*' => ['integer', 'exists:posts,id'],
        ]);

        $action  = $request->input('action');
        $postIds = $request->input('post_ids');
        $user    = auth()->user();
        $count   = 0;

        /*
        | Fetch selected posts with soft-deleted included (for trash action)
        */
        $posts = Post::whereIn('id', $postIds)->get();

        foreach ($posts as $post) {

            /*
            | Permission check per post.
            | Mirrors the logic in the posts index blade view.
            */
            $canEdit = $user->can('edit all posts') ||
                ($user->can('edit own posts') && $post->user_id === $user->id);

            $canDelete = $user->can('delete all posts') ||
                ($user->can('delete own posts') && $post->user_id === $user->id);

            $canPublish = $user->can('publish posts');

            switch ($action) {

                case 'publish':
                    if (!$canPublish) continue 2; // skip this post
                    $post->update([
                        'status'       => 'published',
                        'published_at' => $post->published_at ?? now(),
                    ]);
                    ActivityLog::record(
                        action:      ActivityLog::ACTION_POST_PUBLISHED,
                        description: 'Bulk published post "' . $post->title . '"',
                        model:       $post,
                    );
                    $count++;
                    break;

                case 'draft':
                    if (!$canEdit) continue 2;
                    $post->update(['status' => 'draft']);
                    ActivityLog::record(
                        action:      ActivityLog::ACTION_POST_UPDATED,
                        description: 'Bulk set post to draft: "' . $post->title . '"',
                        model:       $post,
                    );
                    $count++;
                    break;

                case 'trash':
                    if (!$canDelete) continue 2;
                    ActivityLog::record(
                        action:      ActivityLog::ACTION_POST_DELETED,
                        description: 'Bulk trashed post "' . $post->title . '"',
                        model:       $post,
                    );
                    $post->delete(); // soft delete
                    $count++;
                    break;

                case 'delete':
                    /*
                    | Permanent delete — admin only.
                    */
                    if (!$user->can('delete all posts')) continue 2;
                    ActivityLog::record(
                        action:      ActivityLog::ACTION_POST_DELETED,
                        description: 'Bulk permanently deleted post "' . $post->title . '"',
                        model:       $post,
                    );
                    $post->forceDelete();
                    $count++;
                    break;
            }
        }

        $actionLabel = match($action) {
            'publish' => 'published',
            'draft'   => 'set to draft',
            'trash'   => 'moved to trash',
            'delete'  => 'permanently deleted',
        };

        return redirect()
            ->route('admin.posts.index')
            ->with('success', "{$count} " . Str::plural('post', $count) . " {$actionLabel} successfully.");
    }
}
