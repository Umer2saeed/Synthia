<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorDiagnose extends Command
{
    protected $signature = '2fa:diagnose {email : Email of the user} {--code= : The 6-digit code your phone shows right now}';
    protected $description = 'Find out why 2FA codes are rejected (storage, encryption, clock drift)';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('No user with that email.');
            return self::FAILURE;
        }

        $this->info('— Database —');
        foreach (['two_factor_secret', 'two_factor_confirmed_at', 'two_factor_recovery_codes'] as $col) {
            $this->line(sprintf('  column %-28s %s', $col, Schema::hasColumn('users', $col) ? 'exists' : 'MISSING (run the migration)'));
        }

        $this->line('  secret stored in database      ' . ($user->getRawOriginal('two_factor_secret') ? 'yes' : 'NO'));
        $secret = $user->decryptedTwoFactorSecret();
        $this->line('  secret decrypts                ' . ($secret ? 'yes (' . strlen($secret) . ' chars)' : 'NO (empty, or APP_KEY changed since it was saved)'));
        $this->line('  setup confirmed at             ' . ($user->two_factor_confirmed_at ?? 'not confirmed'));
        $this->line('  recovery codes left            ' . count($user->getRecoveryCodes()));
        $this->line('  enforce for admins             ' . (config('twofactor.enforce_admin') ? 'ON' : 'off'));
        $this->line('  accepted window                ±' . (config('twofactor.window') * 30) . ' seconds');

        $this->newLine();
        $this->info('— Clock —');
        $this->line('  server time (' . config('app.timezone') . ')       ' . now()->toDateTimeString());

        try {
            $date = Http::timeout(5)->head('https://www.google.com')->header('Date');
            if ($date) {
                $skew = now()->timestamp - Carbon::parse($date)->timestamp;
                $this->line("  difference from internet time  {$skew} seconds");
                if (abs($skew) > 20) {
                    $this->warn('  Your PC clock is off. Windows: Settings > Time & language > Date & time > "Sync now".');
                }
            }
        } catch (\Throwable $e) {
            $this->line('  (could not reach the internet to compare time)');
        }

        if (! $secret) {
            $this->newLine();
            $this->error('No usable secret. Open /two-factor/setup to create one. If 2FA is half-set-up, run: php artisan 2fa:reset ' . $user->email);
            return self::FAILURE;
        }

        $g = new Google2FA();

        $this->newLine();
        $this->info('— Codes —');
        $this->line('  code the SERVER expects now    ' . $g->getCurrentOtp($secret));
        $this->line('  (your authenticator app should show the same number)');

        if ($phoneCode = $this->option('code')) {
            $match = null;
            for ($w = 0; $w <= 40; $w++) {
                if ($g->verifyKey($secret, $phoneCode, $w)) {
                    $match = $w;
                    break;
                }
            }

            $this->newLine();
            if ($match === null) {
                $this->error('  That code does not match this secret at ANY time offset (within 20 minutes).');
                $this->line('  Cause: the authenticator entry belongs to a different (older) secret.');
                $this->line('  Fix: php artisan 2fa:reset ' . $user->email . ', delete the old entry on your phone, scan the new QR.');
            } elseif ($match === 0) {
                $this->info('  Match: the code is valid right now. The clocks agree.');
            } else {
                $this->warn('  Match, but only within ±' . ($match * 30) . ' seconds: the phone and PC clocks differ.');
                $this->line('  Fix the clocks (set both to automatic time) or raise TWO_FACTOR_WINDOW in .env.');
            }
        } else {
            $this->line('  Tip: run again with --code=123456 (the number on your phone) to measure clock drift.');
        }

        return self::SUCCESS;
    }
}
