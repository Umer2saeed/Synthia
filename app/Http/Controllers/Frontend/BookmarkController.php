<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\Post;
use App\Traits\HasSeoMeta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    use HasSeoMeta;

    /*
    |--------------------------------------------------------------------------
    | toggle() — Add or remove a bookmark (AJAX)
    |--------------------------------------------------------------------------
    |
    | REQUEST FLOW:
    |   1. User clicks bookmark button on a post page
    |   2. JavaScript sends: POST /bookmarks
    |      Body: { post_id: 5 }
    |      Headers: X-CSRF-TOKEN, X-Requested-With: XMLHttpRequest
    |   3. Laravel routes to BookmarkController@toggle
    |   4. We check if this user already bookmarked this post
    |
    |   CASE A — Not bookmarked yet:
    |     Create a new bookmark record
    |     Return { bookmarked: true, message: 'Bookmarked!' }
    |     JavaScript turns button to filled/active state
    |
    |   CASE B — Already bookmarked:
    |     Delete the existing bookmark record
    |     Return { bookmarked: false, message: 'Removed' }
    |     JavaScript turns button to empty/inactive state
    |
    | WHY ONE ENDPOINT FOR BOTH ADD AND REMOVE?
    | The toggle pattern is cleaner than having /bookmarks/add
    | and /bookmarks/remove separately. The server decides what
    | to do based on whether a record exists. The client just
    | calls the same endpoint every time the button is clicked.
    */
    public function toggle(Request $request): JsonResponse
    {
        /*
        |----------------------------------------------------------------------
        | Step 1: Validate the incoming request
        |----------------------------------------------------------------------
        | The request body must contain a valid post_id.
        | 'exists:posts,id' checks the posts table to ensure
        | the post actually exists — prevents bookmarking ghost posts.
        */
        $validated = $request->validate([
            'post_id' => 'required|integer|exists:posts,id',
        ]);

        /*
        |----------------------------------------------------------------------
        | Step 2: Find the post and ensure it is published
        |----------------------------------------------------------------------
        | We only allow bookmarking published posts.
        | A reader should not be able to bookmark a draft post
        | even if they somehow know the post ID.
        */
        $post = Post::published()->findOrFail($validated['post_id']);

        /*
        |----------------------------------------------------------------------
        | Step 3: Check if bookmark already exists
        |----------------------------------------------------------------------
        | We look for an existing bookmark record matching
        | this specific user AND this specific post.
        |
        | first() returns the record if found, or null if not found.
        */
        $existingBookmark = Bookmark::where('user_id', auth()->id())
            ->where('post_id', $post->id)
            ->first();

        if ($existingBookmark) {
            /*
            |------------------------------------------------------------------
            | CASE A: Bookmark exists — REMOVE it
            |------------------------------------------------------------------
            | delete() removes this specific row from the bookmarks table.
            | SQL: DELETE FROM bookmarks WHERE id = {bookmark_id}
            */
            $existingBookmark->delete();

            return response()->json([
                'success'    => true,
                'bookmarked' => false, // tell JS button is now inactive
                'message'    => 'Bookmark removed.',
            ]);

        } else {
            /*
            |------------------------------------------------------------------
            | CASE B: No bookmark exists — ADD it
            |------------------------------------------------------------------
            | create() inserts a new row into the bookmarks table.
            | SQL: INSERT INTO bookmarks (user_id, post_id, created_at)
            |      VALUES ({user_id}, {post_id}, NOW())
            |
            | The unique constraint in the migration prevents duplicates
            | at the database level — a second safety net.
            */
            Bookmark::create([
                'user_id' => auth()->id(),
                'post_id' => $post->id,
            ]);

            return response()->json([
                'success'    => true,
                'bookmarked' => true, // tell JS button is now active
                'message'    => 'Post bookmarked!',
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | index() — Show the user's bookmarks page
    |--------------------------------------------------------------------------
    |
    | This is a regular page load — not AJAX.
    | The user navigates to /bookmarks and sees all their saved posts.
    |
    | QUERY FLOW:
    |   1. Get all bookmark records for the current user
    |   2. Each bookmark has a post_id — eager load the post
    |   3. Each post has a user and category — eager load those too
    |   4. Order by most recently bookmarked first
    |   5. Paginate so the page does not get too long
    |
    | WHY eager loading?
    | Without with('post.user', 'post.category'), Laravel would run
    | a separate query for each bookmark to get its post — N+1 problem.
    | With eager loading, it runs 3 queries total regardless of count.
    */
    public function index()
    {
        $bookmarks = auth()->user()
            ->bookmarks()
            ->with([
                'post',               // the bookmarked post
                'post.user',          // the post's author
                'post.category',      // the post's category
                'post.tags',          // the post's tags
            ])
            ->whereHas('post', function ($q) {
                /*
                | Only show bookmarks where the post is still published.
                | If an editor unpublishes a post after someone
                | bookmarked it, we hide it from their bookmarks
                | rather than showing a broken/private post.
                */
                $q->published();
            })
            ->latest() // most recently bookmarked first
            ->paginate(12);

        $seo = $this->buildSeo(
            title:       'My Bookmarks',
            description: 'Your saved articles on Synthia.',
            type:        'website',
        );

        return view('frontend.bookmarks', compact('bookmarks', 'seo'));
    }
}
