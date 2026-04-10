<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePasswordRequest;
use App\Http\Requests\Admin\UpdateProfileRequest;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(UpdateProfileRequest $request)
    {
        $user      = auth()->user();
        $validated = $request->validated();

        if ($request->hasFile('avatar')) {
            $user->deleteAvatar();
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } else {
            unset($validated['avatar']);
        }

        $user->update($validated);

        return redirect()->route('admin.profile.edit')
            ->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        auth()->user()->update([
            'password' => $request->input('password'),
        ]);

        return redirect()->route('admin.profile.edit')
            ->with('success', 'Password changed successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function removeAvatar()
    {
        $user = auth()->user();
        $user->deleteAvatar();
        $user->update(['avatar' => null]);

        return back()->with('success', 'Avatar removed.');
    }

}
