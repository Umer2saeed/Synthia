<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\ActivityLog;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    public function edit(User $user)
    {
        $this->authorizeManage();

        $roles     = Role::orderBy('name')->get();
        $userRoles = $user->roles->pluck('id')->toArray();

        return view('admin.users.roles', compact('user', 'roles', 'userRoles'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorizeManage();

        $validated = $request->validated();

        if ($request->hasFile('avatar')) {
            $user->deleteAvatar();
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } else {
            unset($validated['avatar']);
        }

        $user->update($validated);

        if ($request->has('roles')) {
            if ($user->id === auth()->id() && !in_array('admin', $request->input('roles', []))) {
                return redirect()->route('admin.users.edit', $user)
                    ->with('error', 'You cannot remove your own admin role.');
            }
            $user->syncRoles($request->input('roles', []));
        }

        /*
    | Log the role change after roles are saved.
    */
        $roleNames = collect($request->roles ?? [])->implode(', ');

        ActivityLog::record(
            action:      ActivityLog::ACTION_ROLE_CHANGED,
            description: 'Changed roles for ' . $user->email . ' to: ' . ($roleNames ?: 'none'),
            model:       $user,
        );

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$user->name}' updated successfully.");
    }

    // ADD this private method
    private function authorizeManage(): void
    {
        if (!auth()->user()->can('manage roles')) {
            abort(403, 'Only administrators can assign roles.');
        }
    }
}
