<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTagRequest;
use App\Http\Requests\Admin\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::withCount('posts')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.tags.index', compact('tags'));
    }

    public function create()
    {
        $this->authorizeManage();
        return view('admin.tags.create');
    }

    public function store(StoreTagRequest $request)
    {
        Tag::create($request->validated());

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag created successfully.');
    }

    public function show(Tag $tag)
    {
        return redirect()->route('admin.tags.index');
    }

    public function edit(Tag $tag)
    {
        $this->authorizeManage();
        return view('admin.tags.edit', compact('tag'));
    }

    public function update(UpdateTagRequest $request, Tag $tag)
    {
        $tag->update($request->validated());

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag updated successfully.');
    }

    public function destroy(Tag $tag)
    {
        $this->authorizeManage();

        $tag->delete();

        return redirect()->route('admin.tags.index')->with('success', 'Tag deleted successfully.');
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
