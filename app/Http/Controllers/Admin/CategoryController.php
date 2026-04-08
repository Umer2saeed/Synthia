<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | index() — List all categories
    |--------------------------------------------------------------------------
    | All three roles (admin, editor, author) can view the list.
    | Already protected by 'access admin panel' middleware on the route.
    */
    public function index()
    {
        $categories = Category::withCount('posts')->latest()->paginate(10);
        return view('admin.categories.index', compact('categories'));
    }

    /*
    |--------------------------------------------------------------------------
    | create() — Show create form
    |--------------------------------------------------------------------------
    | Only admin and editor have 'manage categories' permission.
    | Authors hitting this URL directly get a 403.
    */
    public function create()
    {
        $this->authorizeManage();
        return view('admin.categories.create');
    }

    /*
    |--------------------------------------------------------------------------
    | store() — Save new category
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $this->authorizeManage();

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | show() — Not used in admin panel
    |--------------------------------------------------------------------------
    */
    public function show(Category $category)
    {
        return redirect()->route('admin.categories.index');
    }

    /*
    |--------------------------------------------------------------------------
    | edit() — Show edit form
    |--------------------------------------------------------------------------
    */
    public function edit(Category $category)
    {
        $this->authorizeManage();
        return view('admin.categories.edit', compact('category'));
    }

    /*
    |--------------------------------------------------------------------------
    | update() — Save changes
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Category $category)
    {
        $this->authorizeManage();

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => ['nullable', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($category->id)],
            'description' => 'nullable|string',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | destroy() — Delete category
    |--------------------------------------------------------------------------
    */
    public function destroy(Category $category)
    {
        $this->authorizeManage();

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
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
