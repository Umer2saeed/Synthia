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
        $request->session()->regenerate();

        // Record last login timestamp
        auth()->user()->update(['last_login_at' => now()]);

        /*
   |----------------------------------------------------------------------
   | Role-based redirect after login
   |----------------------------------------------------------------------
   | Readers have no admin panel access, so we send them straight to the
   | public frontend instead of letting the middleware catch and redirect
   | them from /dashboard — cleaner UX, one less redirect.
   |
   | All other roles go to the dashboard as normal.
   */
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
