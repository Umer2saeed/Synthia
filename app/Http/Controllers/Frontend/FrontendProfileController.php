<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePasswordRequest;
use App\Http\Requests\Frontend\UpdateFrontendProfileRequest;
use App\Traits\HasSeoMeta;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class FrontendProfileController extends Controller
{
    use HasSeoMeta;

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

    public function update(UpdateFrontendProfileRequest $request)
    {
        $user = auth()->user();
        $validated = $request->validated();

        if ($request->hasFile('avatar')) {
            $user->deleteAvatar();
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } else {
            unset($validated['avatar']);
        }

        $user->update($validated);

        return redirect()->route('frontend.profile.show')
            ->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        auth()->user()->update([
            'password' => $request->input('password'),
        ]);

        return redirect()->route('frontend.profile.edit')
            ->with('success', 'Password changed successfully.');
    }

    public function removeAvatar()
    {
        $user = auth()->user();

        $user->deleteAvatar();
        $user->update(['avatar' => null]);

        return back()->with('success', 'Avatar removed. Your initials will be shown instead.');
    }
}
