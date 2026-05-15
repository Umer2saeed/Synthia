<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    public function __construct(
        private TwoFactorService $twoFactor
    ) {}

    /*
    | Show the 2FA setup page with QR code.
    */
    public function setup(Request $request)
    {
        $user   = auth()->user();
        $secret = $user->decryptedTwoFactorSecret();

        // Generate a new secret if none exists or setup was never confirmed
        if (!$secret) {
            $secret = $this->twoFactor->generateSecret($user);
        }

        $qrUrl = $this->twoFactor->qrCodeUrl($user, $secret);
        $qrSvg = $this->twoFactor->qrCodeSvg($qrUrl);

        return view('auth.two-factor.setup', compact('secret', 'qrSvg'));
    }

    /*
    | Confirm 2FA setup with the first TOTP code.
    */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ]);

        $plainCodes = $this->twoFactor->confirm(
            auth()->user(),
            $request->code
        );

        if ($plainCodes === false) {
            return back()->withErrors([
                'code' => 'The code is incorrect. Please check your authenticator app and try again.',
            ]);
        }

        // Store recovery codes in session to show once on the next page
        session(['2fa_recovery_codes' => $plainCodes]);

        return redirect()->route('two-factor.recovery-codes')
            ->with('success', 'Two-factor authentication has been enabled!');
    }

    /*
    | Show recovery codes (shown once immediately after setup).
    */
    public function recoveryCodes(Request $request)
    {
        $codes = session()->pull('2fa_recovery_codes', []);

        // If codes are not in session, show codes from DB (hashed — not readable)
        // In this case we just redirect to profile
        if (empty($codes)) {
            return redirect()->route('admin.profile.edit')
                ->with('info', 'Recovery codes were already shown. If you lost them, disable and re-enable 2FA to generate new ones.');
        }

        return view('auth.two-factor.recovery-codes', compact('codes'));
    }

    /*
    | Disable 2FA.
    */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $this->twoFactor->disable(auth()->user());

        return back()->with('success', 'Two-factor authentication has been disabled.');
    }

    /*
    | Show the 2FA challenge page (entered after successful password login).
    */
    public function challenge()
    {
        // Must have a pending 2FA challenge in session
        if (!session('2fa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor.challenge');
    }

    /*
    | Verify the TOTP code on the challenge page.
    */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $userId = session('2fa_user_id');

        if (!$userId) {
            return redirect()->route('login');
        }

        $user = \App\Models\User::findOrFail($userId);
        $code = trim($request->code);

        $verified = false;

        if (ctype_digit($code) && strlen($code) === 6) {
            $verified = $this->twoFactor->verify($user, $code);
        }

        if (!$verified) {
            $verified = $this->twoFactor->attemptRecoveryCode($user, $code);
        }

        if (!$verified) {
            return back()->withErrors([
                'code' => 'The code is invalid. Try another code from your authenticator app or use a recovery code.',
            ])->withInput();
        }

        /*
        | 2FA passed — complete the login that was interrupted in store().
        | loginUsingId() establishes the authenticated session.
        | session()->regenerate() prevents session fixation attacks.
        | forget('2fa_user_id') cleans up the temporary session key.
        */
        auth()->loginUsingId($userId);
        session()->forget('2fa_user_id');
        $request->session()->regenerate();

        /*
        | Record last login — was skipped during the interrupted login.
        */
        $user->update(['last_login_at' => now()]);

        /*
        | Apply the same role-based redirect logic from store().
        | Readers go to the frontend — all others go to the admin dashboard.
        */
        if ($user->hasRole('reader')) {
            return redirect()
                ->route('home')
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        return redirect()->intended(route('admin.dashboard'));
    }
}
