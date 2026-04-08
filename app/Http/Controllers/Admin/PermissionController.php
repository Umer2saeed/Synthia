<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | index() — List all permissions with role counts
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $this->authorizeManage();

        $permissions = Permission::withCount('roles')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.permissions.index', compact('permissions'));
    }

    /*
    |--------------------------------------------------------------------------
    | create() — Show create form
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $this->authorizeManage();

        return view('admin.permissions.create');
    }

    /*
    |--------------------------------------------------------------------------
    | store() — Save new permission
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $this->authorizeManage();

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:permissions,name',
        ]);

        Permission::create(['name' => $validated['name']]);

        return redirect()->route('admin.permissions.index')
            ->with('success', "Permission '{$validated['name']}' created.");
    }

    /*
    |--------------------------------------------------------------------------
    | show() — Not used, redirect to index
    |--------------------------------------------------------------------------
    */
    public function show(Permission $permission)
    {
        return redirect()->route('admin.permissions.index');
    }

    /*
    |--------------------------------------------------------------------------
    | edit() — Show edit form
    |--------------------------------------------------------------------------
    */
    public function edit(Permission $permission)
    {
        $this->authorizeManage();

        return view('admin.permissions.edit', compact('permission'));
    }

    /*
    |--------------------------------------------------------------------------
    | update() — Update permission name
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Permission $permission)
    {
        $this->authorizeManage();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100',
                Rule::unique('permissions', 'name')->ignore($permission->id)],
        ]);

        /*
        |----------------------------------------------------------------------
        | Guard: protect core system permissions from being renamed
        |----------------------------------------------------------------------
        | These permissions are hardcoded in middleware, seeders, and blade
        | files. Renaming them silently breaks the entire permission system.
        */
        $corePermissions = [
            'access admin panel',
            'manage users',
            'manage roles',
            'manage categories',
            'create posts',
            'edit own posts',
            'edit all posts',
            'delete own posts',
            'delete all posts',
            'publish posts',
            'view posts',
            'view categories',
            'view comments',
            'create comments',
            'delete comments',
        ];

        if (in_array($permission->name, $corePermissions) &&
            $validated['name'] !== $permission->name) {
            return redirect()->route('admin.permissions.edit', $permission)
                ->with('error', "'{$permission->name}' is a core system permission and cannot be renamed.");
        }

        $permission->update(['name' => $validated['name']]);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | destroy() — Delete a permission
    |--------------------------------------------------------------------------
    | Guard: core permissions cannot be deleted.
    | Deleting them would silently break role checks across the app.
    */
    public function destroy(Permission $permission)
    {
        $this->authorizeManage();

        $corePermissions = [
            'access admin panel',
            'manage users',
            'manage roles',
            'manage categories',
            'create posts',
            'edit own posts',
            'edit all posts',
            'delete own posts',
            'delete all posts',
            'publish posts',
            'view posts',
            'view categories',
            'view comments',
            'create comments',
            'delete comments',
        ];

        if (in_array($permission->name, $corePermissions)) {
            return redirect()->route('admin.permissions.index')
                ->with('error', "'{$permission->name}' is a core system permission and cannot be deleted.");
        }

        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | authorizeManage() — Reusable admin-only check
    |--------------------------------------------------------------------------
    */
    private function authorizeManage(): void
    {
        if (!auth()->user()->can('manage roles')) {
            abort(403, 'Only administrators can manage permissions.');
        }
    }
}
