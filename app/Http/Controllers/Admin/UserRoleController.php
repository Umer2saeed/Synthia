<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | edit() — Show role assignment form for a user
    |--------------------------------------------------------------------------
    */
    public function edit(User $user)
    {
        $this->authorizeManage();

        $roles     = Role::orderBy('name')->get();
        $userRoles = $user->roles->pluck('id')->toArray();

        return view('admin.users.roles', compact('user', 'roles', 'userRoles'));
    }

    /*
    |--------------------------------------------------------------------------
    | update() — Sync user roles
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, User $user)
    {
        $this->authorizeManage();

        $validated = $request->validate([
            'roles'   => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        /*
        |----------------------------------------------------------------------
        | Guard: cannot remove own admin role
        |----------------------------------------------------------------------
        */
        if ($user->id === auth()->id()) {
            $selectedRoleIds = $validated['roles'] ?? [];
            $adminRole       = Role::where('name', 'admin')->first();

            if ($adminRole && !in_array($adminRole->id, $selectedRoleIds)) {
                return redirect()->back()
                    ->with('error', 'You cannot remove your own admin role.');
            }
        }

        /*
        |----------------------------------------------------------------------
        | syncRoles() with model instances
        |----------------------------------------------------------------------
        | We pass IDs — Spatie resolves them to Permission models.
        | Using model instances avoids the "permission named 8" bug.
        */
        $roleModels = Role::whereIn('id', $validated['roles'] ?? [])->get();
        $user->syncRoles($roleModels);

        return redirect()->back()
            ->with('success', "Roles updated for {$user->name}.");
    }

    // ADD this private method
    private function authorizeManage(): void
    {
        if (!auth()->user()->can('manage roles')) {
            abort(403, 'Only administrators can assign roles.');
        }
    }
}
