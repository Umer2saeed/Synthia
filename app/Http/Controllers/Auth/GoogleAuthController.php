<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;

class GoogleAuthController extends Controller
{
    /*
    | Redirect the user to Google's OAuth consent screen.
    | Socialite handles building the redirect URL with the correct scopes.
    */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /*
    | Google redirects back here after the user consents.
    | We find or create the user and log them in.
    */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            /*
            | User denied access or something went wrong on Google's end.
            | Redirect back to login with a clear message.
            */
            return redirect()->route('login')
                ->with('error', 'Google sign-in was cancelled or failed. Please try again.');
        }

        /*
        | Find existing user by google_id first (returning Google users).
        | Fall back to email match (user registered manually, now using Google).
        | Create a new account if neither exists.
        */
        $user = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            /*
            | Existing user — update their Google ID and avatar if not set.
            | Never overwrite a manually set avatar with a Google one.
            */
            $updates = ['google_id' => $googleUser->getId()];

            if (!$user->avatar && $googleUser->getAvatar()) {
                $updates['avatar'] = $googleUser->getAvatar();
            }

            $user->update($updates);

        } else {
            /*
            | New user — create an account from the Google profile.
            | Mark email as verified immediately (Google already verified it).
            | Assign the default 'reader' role.
            */
            $user = User::create([
                'name'              => $googleUser->getName(),
                'email'             => $googleUser->getEmail(),
                'google_id'         => $googleUser->getId(),
                'avatar'            => $googleUser->getAvatar(),
                'password'          => bcrypt(Str::random(32)), // random — user can set via forgot password
                'email_verified_at' => now(),
            ]);

            /*
            | Assign reader role by default.
            | If the 'reader' role doesn't exist yet, skip gracefully.
            */
            $readerRole = Role::where('name', 'reader')->first();
            if ($readerRole) {
                $user->assignRole($readerRole);
            }
        }

        /*
        | Log the user in and regenerate the session for security.
        | Remember = true so they stay logged in across browser restarts.
        */
        Auth::login($user, true);
        request()->session()->regenerate();

        // Record last login
        $user->update(['last_login_at' => now()]);

        /*
        | Role-based redirect — same logic as AuthenticatedSessionController.
        | Readers go to the frontend; everyone else goes to the admin dashboard.
        */
        if ($user->hasRole('reader')) {
            return redirect()->route('home')
                ->with('success', 'Welcome to Synthia, ' . $user->name . '!');
        }

        return redirect()->intended(route('admin.dashboard'));
    }
}
