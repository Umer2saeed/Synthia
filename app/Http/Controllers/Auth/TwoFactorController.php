<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class TwoFactorController extends Controller
{
    public function __construct(private TwoFactorService $twoFactor) {}

    /* ───────────── Setup (logged-in user) ───────────── */

    public function setup(Request $request)
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return redirect($this->profileUrl($user))
                ->with('info', 'Two-factor authentication is already enabled.');
        }

        // Reuse the stored secret so reloading the page never changes the QR code.
        $secret = $user->decryptedTwoFactorSecret() ?: $this->twoFactor->generateSecret($user);

        $qrSvg = $this->twoFactor->qrCodeSvg($this->twoFactor->qrCodeUrl($user, $secret));

        return view('auth.two-factor.setup', compact('secret', 'qrSvg'));
    }

    /* Throw the pending secret away and show a fresh QR code. */
    public function restart(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasTwoFactorEnabled()) {
            $this->twoFactor->generateSecret($user);
        }

        return redirect()->route('two-factor.setup')
            ->with('info', 'A new QR code was created. Delete the old entry in your authenticator app, then scan this one.');
    }

    public function confirm(Request $request): RedirectResponse
    {
        $request->merge(['code' => preg_replace('/\s+/', '', (string) $request->input('code'))]);
        $request->validate(['code' => ['required', 'digits:6']]);

        $user = $request->user();
        $key  = '2fa-setup:' . $user->id . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, config('twofactor.max_attempts', 5))) {
            return back()->withErrors([
                'code' => 'Too many attempts. Try again in ' . RateLimiter::availableIn($key) . ' seconds.',
            ]);
        }

        $plainCodes = $this->twoFactor->confirm($user, $request->code);

        if ($plainCodes === false) {
            RateLimiter::hit($key, 60);

            return back()->withErrors([
                'code' => 'That code did not match. Use the newest "' . config('app.name') . '" entry in your app, '
                    . 'and make sure your phone and PC clocks are set to automatic time.',
            ]);
        }

        RateLimiter::clear($key);

        $request->session()->put('2fa_recovery_codes', $plainCodes);

        return redirect()->route('two-factor.recovery-codes')
            ->with('success', 'Two-factor authentication is now enabled.');
    }

    public function recoveryCodes(Request $request)
    {
        $codes = $request->session()->pull('2fa_recovery_codes', []);

        if (empty($codes)) {
            return redirect($this->profileUrl($request->user()))
                ->with('info', 'Recovery codes are only shown once. If you lost them, disable and re-enable 2FA to get new ones.');
        }

        return view('auth.two-factor.recovery-codes', compact('codes'));
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);

        $this->twoFactor->disable($request->user());

        return back()->with('success', 'Two-factor authentication has been disabled.');
    }

    /* ───────────── Challenge (mid-login, not yet authenticated) ───────────── */

    public function challenge(Request $request)
    {
        if (! $this->pendingUserId($request)) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Your sign-in session expired. Please log in again.']);
        }

        return view('auth.two-factor.challenge');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:32']]);

        $userId = $this->pendingUserId($request);

        if (! $userId) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Your sign-in session expired. Please log in again.']);
        }

        $key = '2fa-challenge:' . $userId . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, config('twofactor.max_attempts', 5))) {
            return back()->withErrors([
                'code' => 'Too many attempts. Try again in ' . RateLimiter::availableIn($key) . ' seconds.',
            ]);
        }

        $user = User::find($userId);

        if (! $user || ! $user->hasTwoFactorEnabled()) {
            $this->clearPending($request);

            return redirect()->route('login');
        }

        $code = preg_replace('/\s+/', '', (string) $request->input('code'));

        // Six digits = authenticator code. Anything else is treated as a recovery code.
        $verified = preg_match('/^\d{6}$/', $code)
            ? $this->twoFactor->verify($user, $code)
            : $this->twoFactor->attemptRecoveryCode($user, $code);

        if (! $verified) {
            RateLimiter::hit($key, 60);

            return back()->withErrors([
                'code' => 'That code is not valid. Try the newest code from your authenticator app, or use a recovery code.',
            ]);
        }

        RateLimiter::clear($key);

        // Finish the login that AuthenticatedSessionController::store() paused.
        $remember = (bool) $request->session()->pull('2fa_remember', false);
        $this->clearPending($request);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        $user->update(['last_login_at' => now()]);

        if ($user->hasRole('reader')) {
            return redirect()->route('home')->with('success', 'Welcome back, ' . $user->name . '!');
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    /* ───────────── helpers ───────────── */

    private function pendingUserId(Request $request): ?int
    {
        $id      = $request->session()->get('2fa_user_id');
        $started = $request->session()->get('2fa_started_at');
        $ttl     = config('twofactor.challenge_ttl_minutes', 10) * 60;

        if (! $id || ! $started || (now()->timestamp - (int) $started) > $ttl) {
            $this->clearPending($request);

            return null;
        }

        return (int) $id;
    }

    private function clearPending(Request $request): void
    {
        $request->session()->forget(['2fa_user_id', '2fa_started_at', '2fa_remember']);
    }

    private function profileUrl(User $user): string
    {
        return $user->can('access admin panel')
            ? route('admin.profile.edit')
            : route('frontend.profile.edit');
    }
}
