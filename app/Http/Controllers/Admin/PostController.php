<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Jobs\OptimizePostCoverJob;
use App\Jobs\SendPostPublishedNotificationJob;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | index() — List posts
    |--------------------------------------------------------------------------
    | Admins and editors see ALL posts.
    | Authors only see THEIR OWN posts.
    | This is enforced here in the query, not just in the view.
    */
    public function index(Request $request)
    {
        $query = Post::with(['user', 'category', 'tags'])->withCount(['comments'])->latest();

        /*
        |----------------------------------------------------------------------
        | Scope to own posts if author
        |----------------------------------------------------------------------
        | We check 'edit all posts' — admins and editors have it, authors don't.
        | If the user cannot edit all posts, they can only see their own.
        */
        if (!auth()->user()->can('edit all posts')) {
            $query->where('user_id', auth()->id());
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $posts = $query->paginate(10)->withQueryString();

        return view('admin.posts.index', compact('posts'));
    }

    /*
    |--------------------------------------------------------------------------
    | create() — Show create form
    |--------------------------------------------------------------------------
    | Any user with 'create posts' can reach this.
    | Already protected by 'access admin panel' middleware on the route.
    */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $tags       = Tag::orderBy('name')->get();

        return view('admin.posts.create', compact('categories', 'tags'));
    }

    /*
    |--------------------------------------------------------------------------
    | store() — Save new post
    |--------------------------------------------------------------------------
    | KEY RULE: Authors cannot publish. If an author submits with
    | status = 'published' or 'scheduled', we override it to 'draft'.
    | Only users with 'publish posts' permission can set those statuses.
    */
    public function store(StorePostRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('posts', 'public');
        }

        if (empty($validated['slug'])) {
            $validated['slug'] = Post::generateUniqueSlug($validated['title']);
        }

        $validated['user_id']    = auth()->id();
        $validated['is_featured'] = $request->boolean('is_featured');

        $post = Post::create($validated);
        $post->tags()->sync($request->input('tags', []));

        /*
        | Dispatch optimization job only if a cover image was uploaded.
        | We pass both the post model and the original path explicitly.
        */
        if (!empty($validated['cover_image'])) {
            OptimizePostCoverJob::dispatch($post, $validated['cover_image']);
        }

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | show() — View single post detail
    |--------------------------------------------------------------------------
    */
    public function show(Post $post)
    {
        $post->load(['user', 'category', 'tags']);
        return view('admin.posts.show', compact('post'));
    }

    /*
    |--------------------------------------------------------------------------
    | edit() — Show edit form
    |--------------------------------------------------------------------------
    | Authors can only edit their own posts.
    | Admins and editors can edit any post.
    */
    public function edit(Post $post)
    {
        $this->authorizePostAccess($post, 'edit');

        $categories = Category::orderBy('name')->get();
        $tags       = Tag::orderBy('name')->get();
        $postTagIds = $post->tags->pluck('id')->toArray();

        return view('admin.posts.edit', compact('post', 'categories', 'tags', 'postTagIds'));
    }

    /*
    |--------------------------------------------------------------------------
    | update() — Save changes to existing post
    |--------------------------------------------------------------------------
    | Same ownership check as edit().
    | Same publish + featured enforcement as store().
    */
    public function update(UpdatePostRequest $request, Post $post)
    {
        /*
        | Capture the previous status BEFORE updating.
        | We need to know if status is CHANGING to published.
        | If it was already published, we do not send another email.
        |
        | Example:
        |   Before: status = 'draft'     → After: status = 'published' → SEND EMAIL
        |   Before: status = 'published' → After: status = 'published' → DO NOT SEND
        |   Before: status = 'scheduled' → After: status = 'published' → SEND EMAIL
        */
        $previousStatus = $post->status;

        $validated = $request->validated();

        if ($request->hasFile('cover_image')) {
            if ($post->cover_image) {
                Storage::disk('public')->delete($post->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('posts', 'public');
        } else {
            unset($validated['cover_image']);
        }

        if (empty($validated['slug'])) {
            $validated['slug'] = Post::generateUniqueSlug($validated['title'], $post->id);
        }

        $post->update($validated);
        $post->tags()->sync($request->input('tags', []));

        // Dispatch optimization for new cover image
        if ($request->hasFile('cover_image') && !empty($validated['cover_image'])) {
            OptimizePostCoverJob::dispatch($post->fresh(), $validated['cover_image']);
        }

       // Task 10: Dispatch post published notification
        $nowPublished    = $post->status === 'published';
        $wasNotPublished = $previousStatus !== 'published';
        $isNotOwnPost    = $post->user_id !== auth()->id();

        if ($nowPublished && $wasNotPublished && $isNotOwnPost) {
            SendPostPublishedNotificationJob::dispatch($post->fresh());
        }

        return redirect()->route('admin.posts.index')->with('success', 'Post updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | destroy() — Soft delete post
    |--------------------------------------------------------------------------
    | Authors can only delete their own posts.
    | Admins and editors can delete any post.
    */
    public function destroy(Post $post)
    {
        $this->authorizePostAccess($post, 'delete');

        $post->delete();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post moved to trash.');
    }

    /*
    |--------------------------------------------------------------------------
    | authorizePostAccess() — Reusable ownership + permission check
    |--------------------------------------------------------------------------
    | Called by edit(), update(), destroy() to avoid repeating the same
    | logic three times.
    |
    | $action is either 'edit' or 'delete'.
    |
    | Logic:
    |   - If user has 'edit all posts' or 'delete all posts' → allow (admin/editor)
    |   - If user owns the post → allow (author)
    |   - Otherwise → abort 403
    */
    private function authorizePostAccess(Post $post, string $action): void
    {
        $allPermission = $action === 'edit' ? 'edit all posts' : 'delete all posts';
        $ownPermission = $action === 'edit' ? 'edit own posts' : 'delete own posts';

        $canAll = auth()->user()->can($allPermission);
        $canOwn = auth()->user()->can($ownPermission) && $post->user_id === auth()->id();

        if (!$canAll && !$canOwn) {
            abort(403, 'You do not have permission to ' . $action . ' this post.');
        }
    }

    /*
|--------------------------------------------------------------------------
| trash() — List all soft-deleted posts
|--------------------------------------------------------------------------
| Post::onlyTrashed() returns ONLY records where deleted_at is not null.
| This is the opposite of the default which excludes soft deleted records.
|
| Only admin and editor can see all trashed posts.
| Authors can only see their own trashed posts.
*/
    public function trash(Request $request)
    {
        $query = Post::onlyTrashed()
            ->with(['user', 'category'])
            ->latest('deleted_at'); // most recently trashed first

        /*
        | Same ownership scoping as index() —
        | authors only see their own trashed posts.
        */
        if (!auth()->user()->can('delete all posts')) {
            $query->where('user_id', auth()->id());
        }

        // Search within trash
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $posts = $query->paginate(10)->withQueryString();

        return view('admin.posts.trash', compact('posts'));
    }

    /*
    |--------------------------------------------------------------------------
    | restore() — Restore a soft-deleted post
    |--------------------------------------------------------------------------
    | Post::onlyTrashed()->findOrFail() is needed because the default
    | Post::findOrFail() EXCLUDES soft deleted records — it would 404.
    |
    | After restore() the post goes back to its previous status (draft,
    | published, etc.) exactly as it was before deletion.
    */
    public function restore(Request $request, int $id)
    {
        /*
        | We use int $id instead of Post $id because Laravel's model binding
        | automatically excludes soft deleted records. We need to find the
        | trashed record manually using onlyTrashed().
        */
        $post = Post::onlyTrashed()->findOrFail($id);

        /*
        | Ownership check — same logic as edit() and destroy().
        | Authors can only restore their own posts.
        */
        $this->authorizePostAccess($post, 'delete');

        $post->restore();

        return redirect()->route('admin.posts.trash')
            ->with('success', "Post \"{$post->title}\" restored successfully.");
    }

    /*
    |--------------------------------------------------------------------------
    | forceDelete() — Permanently delete a post with no recovery
    |--------------------------------------------------------------------------
    | forceDelete() bypasses soft deletes and removes the record from the
    | database permanently. The cover image file is also deleted from storage
    | since there is no way to recover the post anyway.
    |
    | This is a destructive action — we add extra confirmation in the view.
    */
    public function forceDelete(int $id)
    {
        $post = Post::onlyTrashed()->findOrFail($id);

        $this->authorizePostAccess($post, 'delete');

        /*
        | Delete the cover image from storage before removing the DB record.
        | If we delete the record first and then fail on storage, we get an
        | orphaned file. Deleting the file first is safer.
        */
        if ($post->cover_image) {
            Storage::disk('public')->delete($post->cover_image);
        }

        /*
        | Detach all tag relationships from the pivot table.
        | forceDelete() does NOT automatically clean up pivot records
        | because the cascade only works on soft deletes, not force deletes
        | in all database configurations.
        */
        $post->tags()->detach();

        $post->forceDelete();

        return redirect()->route('admin.posts.trash')
            ->with('success', "Post permanently deleted.");
    }
}
