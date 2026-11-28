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
    | Create a new secret and store it (unconfirmed) on the user.
    |
    | forceFill()->save() instead of update(): update() only writes columns listed in
    | User::$fillable, and the two_factor_* columns are (correctly) not fillable.
    | That is exactly why the secret was never saved before.
    */
    public function generateSecret(User $user): string
    {
        $secret = $this->google2fa->generateSecretKey(32);

        $user->forceFill([
            'two_factor_secret'         => encrypt($secret),
            'two_factor_confirmed_at'   => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        return $secret;
    }

    public function qrCodeUrl(User $user, string $secret): string
    {
        return $this->google2fa->getQRCodeUrl(config('app.name'), $user->email, $secret);
    }

    /* Base64 SVG, used as <img src="data:image/svg+xml;base64,..."> */
    public function qrCodeSvg(string $url): string
    {
        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );

        return base64_encode((new \BaconQrCode\Writer($renderer))->writeString($url));
    }

    public function verify(User $user, string $code): bool
    {
        $secret = $user->decryptedTwoFactorSecret();

        if (! $secret) {
            return false;
        }

        return (bool) $this->google2fa->verifyKey($secret, $code, config('twofactor.window', 1));
    }

    /* Confirm setup with the first code. Returns the plain recovery codes (shown once) or false. */
    public function confirm(User $user, string $code): array|false
    {
        if (! $this->verify($user, $code)) {
            return false;
        }

        $plainCodes  = $this->generateRecoveryCodes();
        $hashedCodes = array_map(fn ($c) => Hash::make($c), $plainCodes);

        $user->forceFill([
            'two_factor_confirmed_at'   => now(),
            'two_factor_recovery_codes' => encrypt(json_encode($hashedCodes)),
        ])->save();

        return $plainCodes;
    }

    public function disable(User $user): void
    {
        $user->forceFill([
            'two_factor_secret'         => null,
            'two_factor_confirmed_at'   => null,
            'two_factor_recovery_codes' => null,
        ])->save();
    }

    /* 10 codes shaped XXXX-XXXX-XXXX */
    private function generateRecoveryCodes(int $count = 10): array
    {
        return Collection::times($count, fn () => strtoupper(
            Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4)
        ))->toArray();
    }

    /*
    | Try a recovery code. A valid code is consumed, so it works once only.
    | Input is normalised: "abcd efgh ijkl", "ABCD-EFGH-IJKL" and "abcdefghijkl" all work.
    */
    public function attemptRecoveryCode(User $user, string $code): bool
    {
        $hashes = $user->getRecoveryCodes();

        if (empty($hashes)) {
            return false;
        }

        $code = $this->normaliseRecoveryCode($code);

        foreach ($hashes as $index => $hash) {
            if (Hash::check($code, $hash)) {
                unset($hashes[$index]);

                $user->forceFill([
                    'two_factor_recovery_codes' => encrypt(json_encode(array_values($hashes))),
                ])->save();

                return true;
            }
        }

        return false;
    }

    private function normaliseRecoveryCode(string $code): string
    {
        $clean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code));

        return strlen($clean) === 12 ? implode('-', str_split($clean, 4)) : strtoupper(trim($code));
    }
}
