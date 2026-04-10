<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('posts')->latest()->paginate(10);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $this->authorizeManage();
        return view('admin.categories.create');
    }

    public function store(StoreCategoryRequest $request)
    {
        $validated = $request->validated();

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        Category::create($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function show(Category $category)
    {
        return redirect()->route('admin.categories.index');
    }

    public function edit(Category $category)
    {
        $this->authorizeManage();
        return view('admin.categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $validated = $request->validated();

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $this->authorizeManage();

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | authorizeManage() — Reusable permission check
    |--------------------------------------------------------------------------
    | Called by create, store, edit, update, destroy.
    | Aborts with 403 if user does not have 'manage categories'.
    | Admin and editor have this — author does not.
    */
    private function authorizeManage(): void
    {
        if (!auth()->user()->can('manage categories')) {
            abort(403, 'You do not have permission to manage categories.');
        }
    }
}
