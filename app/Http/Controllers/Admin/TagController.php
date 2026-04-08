<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TagController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | index() — List all tags with post counts
    |--------------------------------------------------------------------------
    | All three roles (admin, editor, author) can view the list.
    | Authors need to see tags so they can understand what exists
    | and select them when writing posts.
    */
    public function index()
    {
        $tags = Tag::withCount('posts')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.tags.index', compact('tags'));
    }

    /*
    |--------------------------------------------------------------------------
    | create() — Show create form
    |--------------------------------------------------------------------------
    | Only admin and editor have 'manage categories' permission.
    | Tags are grouped under the same permission as categories
    | because they are both content taxonomy features.
    */
    public function create()
    {
        $this->authorizeManage();
        return view('admin.tags.create');
    }

    /*
    |--------------------------------------------------------------------------
    | store() — Save new tag
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $this->authorizeManage();

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:tags,name',
            'slug' => 'nullable|string|max:100|unique:tags,slug',
        ]);

        Tag::create($validated);

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | show() — Not used in admin panel
    |--------------------------------------------------------------------------
    */
    public function show(Tag $tag)
    {
        return redirect()->route('admin.tags.index');
    }

    /*
    |--------------------------------------------------------------------------
    | edit() — Show edit form
    |--------------------------------------------------------------------------
    */
    public function edit(Tag $tag)
    {
        $this->authorizeManage();
        return view('admin.tags.edit', compact('tag'));
    }

    /*
    |--------------------------------------------------------------------------
    | update() — Save changes
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Tag $tag)
    {
        $this->authorizeManage();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('tags', 'name')->ignore($tag->id)],
            'slug' => ['nullable', 'string', 'max:100', Rule::unique('tags', 'slug')->ignore($tag->id)],
        ]);

        $tag->update($validated);

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | destroy() — Delete tag
    |--------------------------------------------------------------------------
    | Deleting a tag automatically removes its records from the post_tag
    | pivot table because of onDelete('cascade') in your migration.
    */
    public function destroy(Tag $tag)
    {
        $this->authorizeManage();

        $tag->delete();

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | authorizeManage() — Reusable permission check
    |--------------------------------------------------------------------------
    | Tags use the same 'manage categories' permission as categories.
    | This keeps the permission system simple — both are taxonomy features
    | and should be managed by the same roles.
    */
    private function authorizeManage(): void
    {
        if (!auth()->user()->can('manage categories')) {
            abort(403, 'You do not have permission to manage tags.');
        }
    }
}
