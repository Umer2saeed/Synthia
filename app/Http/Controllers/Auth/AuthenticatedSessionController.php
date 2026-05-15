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
//    public function store(LoginRequest $request): RedirectResponse
//    {
//        $request->authenticate();
//        $request->session()->regenerate();
//
//        // Record last login timestamp
//        auth()->user()->update(['last_login_at' => now()]);
//
//        if (auth()->user()->hasRole('reader')) {
//            return redirect()
//                ->route('home')
//                ->with('success', 'Welcome back, ' . auth()->user()->name . '!');
//        }
//
//        return redirect()->intended(route('admin.dashboard', absolute: false));
//    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = auth()->user();

        if ($user->hasTwoFactorEnabled()) {
            $userId = $user->id;

            auth()->logout();

            $request->session()->put('2fa_user_id', $userId);

            return redirect()->route('two-factor.challenge');
        }

        /*
        |----------------------------------------------------------------------
        | NO 2FA — complete login normally.
        |
        | Everything below is your original code, unchanged.
        |----------------------------------------------------------------------
        */
        $request->session()->regenerate();

        // Record last login timestamp
        auth()->user()->update(['last_login_at' => now()]);

        if (auth()->user()->hasRole('reader')) {
            return redirect()
                ->route('home')
                ->with('success', 'Welcome back, ' . auth()->user()->name . '!');
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
