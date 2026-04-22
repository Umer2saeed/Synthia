<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Jobs\OptimizeAvatarJob;
use App\Jobs\SendAccountStatusChangedJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | index() — List all users with filters
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $this->authorizeManage();

        $query = User::with('roles')
            ->withCount('posts', 'comments')
            ->latest();

        // Filter by role: ?role=editor
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Filter by status: ?status=inactive
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by name, email, or username: ?search=umer
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name',     'like', '%' . $request->search . '%')
                    ->orWhere('email',    'like', '%' . $request->search . '%')
                    ->orWhere('username', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->paginate(15)->withQueryString();
        $roles  = Role::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /*
    |--------------------------------------------------------------------------
    | show() — View a single user profile
    |--------------------------------------------------------------------------
    */
    public function show(User $user)
    {
        $this->authorizeManage();

        $user->load('roles');
        $user->loadCount(['posts', 'comments', 'followers', 'following']);

        return view('admin.users.show', compact('user'));
    }

    /*
    |--------------------------------------------------------------------------
    | edit() — Show edit form for a user
    |--------------------------------------------------------------------------
    */
    public function edit(User $user)
    {
        $this->authorizeManage();

        $roles = Role::orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /*
    |--------------------------------------------------------------------------
    | update() — Validate and update user
    |--------------------------------------------------------------------------
    */
    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorizeManage();

        $validated = $request->validated();

        if ($request->hasFile('avatar')) {
            $user->deleteAvatar();
            $validated['avatar'] = $request->file('avatar')
                ->store('avatars', 'public');
        } else {
            unset($validated['avatar']);
        }

        $user->update($validated);

        // Dispatch avatar optimization
        if ($request->hasFile('avatar') && !empty($validated['avatar'])) {
            OptimizeAvatarJob::dispatch($user->fresh(), $validated['avatar']);
        }

        if ($request->has('roles')) {
            if ($user->id === auth()->id() && !in_array('admin', $request->input('roles', []))) {
                return redirect()->route('admin.users.edit', $user)
                    ->with('error', 'You cannot remove your own admin role.');
            }
            $user->syncRoles($request->input('roles', []));
        }

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$user->name}' updated successfully.");
    }

    /*
    |--------------------------------------------------------------------------
    | destroy() — Permanently delete a user
    |--------------------------------------------------------------------------
    | Two guards:
    |   1. Cannot delete yourself
    |   2. Cannot delete another admin (protect against accidents)
    */
    public function destroy(User $user)
    {
        $this->authorizeManage();

        // Guard: cannot delete yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        // Guard: cannot delete another admin
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete another administrator.');
        }

        // Delete avatar file from storage before removing the record
        $user->deleteAvatar();

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | toggleStatus() — Flip active ↔ inactive
    |--------------------------------------------------------------------------
    | Guards:
    |   1. Cannot toggle your own status
    |   2. Cannot deactivate another admin
    */
    public function toggleStatus(User $user)
    {
        $this->authorizeManage();

        // Guard: cannot change your own status
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own status.');
        }

        // Guard: cannot toggle another admin's status
        if ($user->hasRole('admin')) {
            return back()->with('error', 'You cannot change an administrator\'s status.');
        }

        $newStatus = $user->status === 'active' ? 'inactive' : 'active';

        $user->update(['status' => $newStatus]);

        SendAccountStatusChangedJob::dispatch($user->fresh(), $newStatus);

        return back()->with('success', "User status updated to {$newStatus}.");
    }

    /*
|--------------------------------------------------------------------------
| authorizeManage() — Reusable admin-only check
|--------------------------------------------------------------------------
| Called at the top of every method as a double safety net
| on top of the route middleware.
*/
    private function authorizeManage(): void
    {
        if (!auth()->user()->can('manage users')) {
            abort(403, 'Only administrators can manage users.');
        }
    }
}
