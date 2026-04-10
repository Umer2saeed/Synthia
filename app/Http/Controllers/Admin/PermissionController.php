<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePermissionRequest;
use App\Http\Requests\Admin\UpdatePermissionRequest;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    public function index()
    {
        $this->authorizeManage();

        $permissions = Permission::withCount('roles')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.permissions.index', compact('permissions'));
    }

    public function create()
    {
        $this->authorizeManage();

        return view('admin.permissions.create');
    }

    public function store(StorePermissionRequest $request)
    {
        Permission::create($request->validated());

        return redirect()->route('admin.permissions.index')
            ->with('success', "Permission created.");
    }

    public function show(Permission $permission)
    {
        return redirect()->route('admin.permissions.index');
    }

    public function edit(Permission $permission)
    {
        $this->authorizeManage();

        return view('admin.permissions.edit', compact('permission'));
    }

    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        $corePermissions = [
            'access admin panel', 'manage users', 'manage roles',
            'manage categories', 'create posts', 'edit own posts',
            'edit all posts', 'delete own posts', 'delete all posts',
            'publish posts', 'view posts', 'view categories',
            'view comments', 'create comments', 'delete comments',
        ];

        $validated = $request->validated();

        if (in_array($permission->name, $corePermissions) &&
            $validated['name'] !== $permission->name) {
            return redirect()->route('admin.permissions.edit', $permission)
                ->with('error', "'{$permission->name}' is a core permission and cannot be renamed.");
        }

        $permission->update($validated);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission updated.');
    }

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
