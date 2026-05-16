<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /*
    | Generate a new secret key and store it (unconfirmed) on the user.
    | Returns the plain-text secret for QR code generation.
    */
    public function generateSecret(User $user): string
    {
        $secret = $this->google2fa->generateSecretKey();

        $user->update([
            'two_factor_secret'       => encrypt($secret),
            'two_factor_confirmed_at' => null, // reset confirmation
        ]);

        return $secret;
    }

    /*
    | Build the QR code URL for the authenticator app.
    */
    public function qrCodeUrl(User $user, string $secret): string
    {
        return $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );
    }

    /*
    | Generate a base64 PNG QR code image for embedding in HTML.
    */
    public function qrCodeSvg(string $url): string
    {
        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );

        $writer = new \BaconQrCode\Writer($renderer);
        return base64_encode($writer->writeString($url));
    }

    /*
    | Verify a TOTP code against the user's secret.
    | Returns true if the code is valid within the allowed window.
    */
    public function verify(User $user, string $code): bool
    {
        $secret = $user->decryptedTwoFactorSecret();
        if (!$secret) return false;
        return $this->google2fa->verifyKey($secret, $code, 4);
    }

//    public function verify(User $user, string $code): bool
//    {
//
////        dd('coming here');
//        $secret = $user->decryptedTwoFactorSecret();
//
//        if (!$secret) {
//            \Log::error('2FA verify: secret is null for user ' . $user->id);
//            return false;
//        }
//
//        /*
//        | Log the server timestamp so you can compare with your phone.
//        | If the times are far apart, clock skew is the cause.
//        */
//        \Log::info('2FA verify attempt', [
//            'user_id'    => $user->id,
//            'code'       => $code,
//            'server_time'=> now()->toDateTimeString(),
//            'timestamp'  => now()->timestamp,
//        ]);
//
//        $result = $this->google2fa->verifyKey($secret, $code, 4);
//
//        \Log::info('2FA verify result: ' . ($result ? 'PASS' : 'FAIL'));
//
//        return $result;
//    }

    /*
    | Confirm 2FA setup after the user verifies their first code.
    | Also generates and stores recovery codes.
    | Returns the plain-text recovery codes (shown once to the user).
    */
    public function confirm(User $user, string $code): array|false
    {
        if (!$this->verify($user, $code)) {
            return false;
        }

        $plainCodes   = $this->generateRecoveryCodes();
        $hashedCodes  = array_map(fn($c) => Hash::make($c), $plainCodes);

        $user->update([
            'two_factor_confirmed_at'   => now(),
            'two_factor_recovery_codes' => encrypt(json_encode($hashedCodes)),
        ]);

        return $plainCodes;
    }

    /*
    | Disable 2FA completely — wipe secret and codes.
    */
    public function disable(User $user): void
    {
        $user->update([
            'two_factor_secret'         => null,
            'two_factor_confirmed_at'   => null,
            'two_factor_recovery_codes' => null,
        ]);
    }

    /*
    | Generate 10 random recovery codes in the format XXXX-XXXX-XXXX.
    */
    private function generateRecoveryCodes(int $count = 10): array
    {
        return Collection::times($count, function () {
            return strtoupper(
                Str::random(4) . '-' .
                Str::random(4) . '-' .
                Str::random(4)
            );
        })->toArray();
    }

    /*
    | Try a recovery code — checks bcrypt against stored hashes.
    | Invalidates (removes) the used code if valid.
    | Returns true if a valid code was found and consumed.
    */
    public function attemptRecoveryCode(User $user, string $code): bool
    {
        $hashes = $user->getRecoveryCodes();
        if (empty($hashes)) return false;

        foreach ($hashes as $index => $hash) {
            if (Hash::check($code, $hash)) {
                // Remove the used code so it cannot be reused
                unset($hashes[$index]);
                $user->update([
                    'two_factor_recovery_codes' => encrypt(json_encode(array_values($hashes))),
                ]);
                return true;
            }
        }

        return false;
    }
}
