<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('admin.profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
//        $request->user()->fill($request->validated());
//
//        if ($request->user()->isDirty('email')) {
//            $request->user()->email_verified_at = null;
//        }
//
//        $request->user()->save();
//
//        return Redirect::route('profile.edit')->with('status', 'profile-updated');

        $user = auth()->user();

        dd($user);

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => [
                'nullable', 'string', 'max:50', 'alpha_dash',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'bio'    => 'nullable|string|max:300',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $user->deleteAvatar();
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if (!$request->hasFile('avatar')) {
            unset($validated['avatar']);
        }

        $user->update($validated);

        return redirect()->route('admin.profile.edit')->with('success', 'Profile updated successfully.');
    }


    /*
    |--------------------------------------------------------------------------
    | updatePassword() — Change own password
    |--------------------------------------------------------------------------
    */
    public function updatePassword(Request $request)
    {
        $request->validate([
            /*
            | 'current_password' rule → Laravel built-in that checks the
            | provided value against the authenticated user's actual password.
            | No need to manually Hash::check() — Laravel does it for you.
            */
            'current_password' => ['required', 'current_password'],

            /*
            | Password::defaults() enforces your app's password rules:
            | min 8 chars by default. You can chain ->mixedCase()->numbers()
            | etc. to add more rules.
            */
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        auth()->user()->update([
            'password' => $request->input('password'), // auto-hashed via cast
        ]);

        return redirect()->route('admin.profile.edit')
            ->with('success', 'Password changed successfully.');
    }



    /**
     * Delete the user's account.
     */
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


    /*
   |--------------------------------------------------------------------------
   | removeAvatar() — Delete the user's avatar and reset to default
   |--------------------------------------------------------------------------
   */
    public function removeAvatar()
    {
        $user = auth()->user();
        $user->deleteAvatar();
        $user->update(['avatar' => null]);

        return back()->with('success', 'Avatar removed.');
    }

}
