<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $this->authorizeManage();

        $roles = Role::withCount('permissions')
            ->orderBy('name')
            ->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $this->authorizeManage();

        $permissions = Permission::orderBy('name')->get();

        return view('admin.roles.create', compact('permissions'));
    }

    public function store(StoreRoleRequest $request)
    {
        $validated = $request->validated();
        $role      = Role::create(['name' => $validated['name']]);

        if (!empty($validated['permissions'])) {
            $permissionModels = Permission::whereIn('id', $validated['permissions'])->get();
            $role->syncPermissions($permissionModels);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$role->name}' created successfully.");
    }

    public function show(Role $role)
    {
        return redirect()->route('admin.roles.index');
    }

    public function edit(Role $role)
    {
        $this->authorizeManage();

        $permissions     = Permission::orderBy('name')->get();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $validated = $request->validated();

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
