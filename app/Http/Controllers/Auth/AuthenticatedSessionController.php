<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = $request->user();

        /*
        | 2FA: the password was correct, but the login is not finished yet.
        | Log the user back out, remember WHO is mid-login in the session
        | (for 10 minutes), and send them to the code page.
        */
        if ($user->hasTwoFactorEnabled()) {
            $remember = $request->boolean('remember');

            Auth::guard('web')->logout();
            $request->session()->regenerate();
            $request->session()->put('2fa_user_id', $user->id);
            $request->session()->put('2fa_remember', $remember);
            $request->session()->put('2fa_started_at', now()->timestamp);

            return redirect()->route('two-factor.challenge');
        }

        // No 2FA: normal login
        $request->session()->regenerate();

        $user->update(['last_login_at' => now()]);

        // Readers have no admin panel, so send them straight to the public site.
        if ($user->hasRole('reader')) {
            return redirect()
                ->route('home')
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        return redirect()->intended(route('admin.dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
