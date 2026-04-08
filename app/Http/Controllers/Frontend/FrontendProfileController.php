<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Traits\HasSeoMeta;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class FrontendProfileController extends Controller
{
    use HasSeoMeta;

    /*
    |--------------------------------------------------------------------------
    | show() — View own public profile
    |--------------------------------------------------------------------------
    | Shows the logged-in user their own profile as others see it.
    | Also shows their recent comments and post activity if they are an author.
    */
    public function show()
    {
        $user = auth()->user();

        /*
        | Load relationships needed on the profile page.
        | We eager load to avoid N+1 queries in the view.
        */
        $user->load('roles');
        $user->loadCount(['posts', 'comments']);

        /*
        | Recent posts — only shown if user is an author/editor/admin
        | and has published posts.
        */
        $recentPosts = $user->posts()
            ->published()
            ->with('category')
            ->latest('published_at')
            ->limit(5)
            ->get();

        /*
        | Recent comments this user has left across the site.
        */
        $recentComments = $user->comments()
            ->approved()
            ->with('post')
            ->latest()
            ->limit(5)
            ->get();

        $seo = $this->buildSeo(
            title:       'My Profile — ' . $user->display_name,
            description: $user->bio ?? 'Member profile on Synthia.',
            type:        'website',
        );

        return view('frontend.profile.show', compact(
            'user', 'recentPosts', 'recentComments', 'seo'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | edit() — Show edit form
    |--------------------------------------------------------------------------
    | Any logged-in user can edit their own profile here —
    | including readers who cannot access the admin panel profile page.
    */
    public function edit()
    {
        $user = auth()->user();

        $seo = $this->buildSeo(
            title:       'Edit Profile',
            description: 'Update your Synthia profile.',
            type:        'website',
        );

        return view('frontend.profile.edit', compact('user', 'seo'));
    }

    /*
    |--------------------------------------------------------------------------
    | update() — Save profile changes
    |--------------------------------------------------------------------------
    */
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => [
                'nullable',
                'string',
                'max:50',
                'alpha_dash', // letters, numbers, hyphens, underscores only
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'bio'    => 'nullable|string|max:300',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        /*
        |----------------------------------------------------------------------
        | Handle avatar upload
        |----------------------------------------------------------------------
        | Delete the old avatar from storage first to avoid orphaned files
        | building up in storage/app/public/avatars/.
        */
        if ($request->hasFile('avatar')) {
            $user->deleteAvatar();
            $validated['avatar'] = $request->file('avatar')
                ->store('avatars', 'public');
        } else {
            /*
            | If no new file was uploaded, remove 'avatar' from the
            | validated array so we do not overwrite existing with null.
            */
            unset($validated['avatar']);
        }

        $user->update($validated);

        return redirect()->route('frontend.profile.show')
            ->with('success', 'Profile updated successfully.');
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
            | 'current_password' is a Laravel built-in validation rule.
            | It checks the value against the authenticated user's actual
            | hashed password — no manual Hash::check() needed.
            */
            'current_password' => ['required', 'current_password'],

            /*
            | Password::defaults() enforces minimum 8 characters.
            | 'confirmed' means the field password_confirmation must
            | exist and match the password field.
            */
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        /*
        | The 'hashed' cast on the User model means we do NOT need
        | Hash::make() here — Laravel hashes it automatically on save.
        */
        auth()->user()->update([
            'password' => $request->input('password'),
        ]);

        return redirect()->route('frontend.profile.edit')
            ->with('success', 'Password changed successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | removeAvatar() — Delete avatar and reset to default
    |--------------------------------------------------------------------------
    */
    public function removeAvatar()
    {
        $user = auth()->user();

        $user->deleteAvatar();
        $user->update(['avatar' => null]);

        return back()->with('success', 'Avatar removed. Your initials will be shown instead.');
    }
}
