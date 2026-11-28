<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class TwoFactorReset extends Command
{
    protected $signature = '2fa:reset {email : Email of the user} {--force : Skip the confirmation}';
    protected $description = 'Remove 2FA from a user (recovery for lockouts)';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('No user with that email.');
            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm("Remove two-factor authentication from {$user->email}?")) {
            return self::SUCCESS;
        }

        $user->forceFill([
            'two_factor_secret'         => null,
            'two_factor_confirmed_at'   => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        $this->info("2FA removed for {$user->email}. They can sign in with a password and set it up again.");

        return self::SUCCESS;
    }
}
