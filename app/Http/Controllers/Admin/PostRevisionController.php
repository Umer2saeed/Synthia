<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostRevision;
use App\Services\RevisionService;
use Illuminate\Http\RedirectResponse;

class PostRevisionController extends Controller
{
    public function __construct(
        private RevisionService $revisionService
    ) {}

    /*
    | List all revisions for a post.
    */
    public function index(Post $post)
    {
        $this->authorizeAccess($post);

        $revisions = $post->revisions()->with('user')->get();

        return view('admin.posts.revisions.index', compact('post', 'revisions'));
    }

    /*
    | Show a single revision with diff against the current post content.
    */
    public function show(Post $post, PostRevision $revision)
    {
        $this->authorizeAccess($post);

        abort_if($revision->post_id !== $post->id, 404);

        $titleDiff   = $this->revisionService->diff($post->title,   $revision->title);
        $contentDiff = $this->revisionService->diff($post->content, $revision->content);

        return view('admin.posts.revisions.show', compact(
            'post', 'revision', 'titleDiff', 'contentDiff'
        ));
    }

    /*
    | Restore a revision: snapshot current state first, then apply revision.
    */
    public function restore(Post $post, PostRevision $revision): RedirectResponse
    {
        $this->authorizeAccess($post);

        abort_if($revision->post_id !== $post->id, 404);

        // Snapshot current content before overwriting — safety net
        $this->revisionService->snapshot($post);

        $post->update([
            'title'   => $revision->title,
            'content' => $revision->content,
        ]);

        return redirect()
            ->route('admin.posts.edit', $post)
            ->with('success', 'Revision restored successfully. The previous version was saved as a revision.');
    }

    private function authorizeAccess(Post $post): void
    {
        $canEdit = auth()->user()->can('edit all posts') ||
            (auth()->user()->can('edit own posts') && $post->user_id === auth()->id());

        abort_unless($canEdit, 403);
    }
}
