<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | index() — List all roles with permission counts
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $this->authorizeManage();

        $roles = Role::withCount('permissions')
            ->orderBy('name')
            ->get();

        return view('admin.roles.index', compact('roles'));
    }

    /*
    |--------------------------------------------------------------------------
    | create() — Show form to create a new role
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $this->authorizeManage();

        $permissions = Permission::orderBy('name')->get();

        return view('admin.roles.create', compact('permissions'));
    }

    /*
    |--------------------------------------------------------------------------
    | store() — Save new role and sync its permissions
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $this->authorizeManage();

        $validated = $request->validate([
            'name'          => 'required|string|max:100|unique:roles,name',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create(['name' => $validated['name']]);

        if (!empty($validated['permissions'])) {
            /*
            | Resolve IDs to Permission model instances.
            | Spatie requires models or names — not raw IDs.
            | This prevents the "no permission named 8" error.
            */
            $permissionModels = Permission::whereIn('id', $validated['permissions'])->get();
            $role->syncPermissions($permissionModels);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$role->name}' created successfully.");
    }

    /*
    |--------------------------------------------------------------------------
    | show() — Not used, redirect to index
    |--------------------------------------------------------------------------
    */
    public function show(Role $role)
    {
        return redirect()->route('admin.roles.index');
    }

    /*
    |--------------------------------------------------------------------------
    | edit() — Show edit form pre-filled with current permissions
    |--------------------------------------------------------------------------
    */
    public function edit(Role $role)
    {
        $this->authorizeManage();

        $permissions     = Permission::orderBy('name')->get();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /*
    |--------------------------------------------------------------------------
    | update() — Rename role and re-sync permissions
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Role $role)
    {
        $this->authorizeManage();

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:100',
                Rule::unique('roles', 'name')->ignore($role->id)],
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        /*
        |----------------------------------------------------------------------
        | Guard: cannot rename the admin role
        |----------------------------------------------------------------------
        | The admin role is the foundation of the entire permission system.
        | Renaming it would break all role checks silently.
        */
        if ($role->name === 'admin' && $validated['name'] !== 'admin') {
            return redirect()->route('admin.roles.edit', $role)
                ->with('error', 'The admin role name cannot be changed.');
        }

        $role->update(['name' => $validated['name']]);

        $permissionModels = Permission::whereIn('id', $validated['permissions'] ?? [])->get();
        $role->syncPermissions($permissionModels);

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$role->name}' updated successfully.");
    }

    /*
    |--------------------------------------------------------------------------
    | destroy() — Delete a role
    |--------------------------------------------------------------------------
    | Guards:
    |   1. Cannot delete the admin role — would lock everyone out
    |   2. Cannot delete a role that has users assigned to it
    */
    public function destroy(Role $role)
    {
        $this->authorizeManage();

        // Guard: admin role is protected
        if ($role->name === 'admin') {
            return redirect()->route('admin.roles.index')
                ->with('error', 'The admin role cannot be deleted.');
        }

        /*
        |----------------------------------------------------------------------
        | Guard: role has active users
        |----------------------------------------------------------------------
        | Deleting a role with users assigned silently removes their access.
        | We block this and ask the admin to reassign users first.
        */
        $userCount = $role->users()->count();

        if ($userCount > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', "Cannot delete '{$role->name}' — {$userCount} " .
                    Str::plural('user', $userCount) . " still assigned to it. " .
                    "Reassign them first.");
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$role->name}' deleted successfully.");
    }

    /*
    |--------------------------------------------------------------------------
    | authorizeManage() — Reusable admin-only check
    |--------------------------------------------------------------------------
    | Double safety net on top of the route middleware.
    | Consistent with CategoryController, TagController, CommentController.
    */
    private function authorizeManage(): void
    {
        if (!auth()->user()->can('manage roles')) {
            abort(403, 'Only administrators can manage roles.');
        }
    }
}
