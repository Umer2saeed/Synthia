<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ReadingList;
use App\Models\ReadingListItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReadingListController extends Controller
{
    /*
    | Index — show all lists for the authenticated user.
    */
    public function index(): \Illuminate\View\View
    {
        $lists = ReadingList::where('user_id', auth()->id())
            ->withCount('items')
            ->orderByDesc('created_at')
            ->get();

        return view('frontend.reading-lists.index', compact('lists'));
    }

    /*
    | Show — public list view. Anyone can view public lists.
    | Private lists are only visible to their owner.
    */
    public function show(ReadingList $readingList, string $slug): \Illuminate\View\View
    {
        /*
        | If list is private, only the owner can see it.
        */
        if (!$readingList->is_public && $readingList->user_id !== auth()->id()) {
            abort(403, 'This list is private.');
        }

        $items = $readingList->items()
            ->with(['post' => fn($q) => $q->with(['user', 'category'])])
            ->orderByDesc('added_at')
            ->get()
            ->filter(fn($item) => $item->post !== null);

        return view('frontend.reading-lists.show', compact('readingList', 'items'));
    }

    /*
    | Store — create a new reading list.
    */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'is_public' => ['boolean'],
        ]);

        /*
        | Limit: max 20 lists per user to prevent abuse.
        */
        $count = ReadingList::where('user_id', auth()->id())->count();
        if ($count >= 20) {
            return back()->with('error', 'You can create a maximum of 20 reading lists.');
        }

        ReadingList::create([
            'user_id'   => auth()->id(),
            'name'      => $request->name,
            'is_public' => $request->boolean('is_public', false),
        ]);

        return back()->with('success', 'Reading list created successfully.');
    }

    /*
    | Update — rename a list or toggle its public/private status.
    */
    public function update(Request $request, ReadingList $readingList): RedirectResponse
    {
        abort_unless($readingList->user_id === auth()->id(), 403);

        $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'is_public' => ['boolean'],
        ]);

        $readingList->update([
            'name'      => $request->name,
            'is_public' => $request->boolean('is_public', false),
        ]);

        return back()->with('success', 'Reading list updated.');
    }

    /*
    | Destroy — delete a list and all its items.
    */
    public function destroy(ReadingList $readingList): RedirectResponse
    {
        abort_unless($readingList->user_id === auth()->id(), 403);

        $readingList->delete();

        return redirect()
            ->route('reading-lists.index')
            ->with('success', 'Reading list deleted.');
    }

    /*
    | toggleItem — Add or remove a post from a reading list via AJAX.
    */
    public function toggleItem(Request $request, ReadingList $readingList): JsonResponse
    {
        abort_unless($readingList->user_id === auth()->id(), 403);

        $request->validate([
            'post_id' => ['required', 'integer', 'exists:posts,id'],
        ]);

        $postId      = $request->integer('post_id');
        $existingItem = ReadingListItem::where('list_id', $readingList->id)
            ->where('post_id', $postId)
            ->first();

        if ($existingItem) {
            $existingItem->delete();
            $inList = false;
        } else {
            ReadingListItem::create([
                'list_id'  => $readingList->id,
                'post_id'  => $postId,
                'added_at' => now(),
            ]);
            $inList = true;
        }

        return response()->json([
            'success' => true,
            'in_list' => $inList,
            'count'   => $readingList->items()->count(),
        ]);
    }

    /*
    | getUserLists — Return the current user's lists for the post page dropdown.
    | Also returns which lists already contain the given post.
    */
    public function getUserLists(Request $request): JsonResponse
    {
        $postId = $request->integer('post_id');

        $lists = ReadingList::where('user_id', auth()->id())
            ->withCount('items')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($list) => [
                'id'       => $list->id,
                'name'     => $list->name,
                'count'    => $list->items_count,
                'in_list'  => $list->hasPost($postId),
            ]);

        return response()->json(['lists' => $lists]);
    }
}
