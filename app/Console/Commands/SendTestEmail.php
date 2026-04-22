<?php

namespace App\Console\Commands;

use App\Mail\TestMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestEmail extends Command
{
    /*
    |--------------------------------------------------------------------------
    | Command signature with an argument
    |--------------------------------------------------------------------------
    | {email} is a required argument.
    | Usage: php artisan mail:test your@email.com
    |
    | {--name=} is an optional option with a default.
    | Usage: php artisan mail:test your@email.com --name="Umer"
    */
    protected $signature   = 'mail:test {email} {--name=Umer}';
    protected $description = 'Send a test email to verify Synthia email infrastructure';

    public function handle(): int
    {
        $email = $this->argument('email');
        $name  = $this->option('name');

        $this->info("Sending test email to: {$email}");

        try {
            /*
            |----------------------------------------------------------------------
            | Send the test email SYNCHRONOUSLY (not queued)
            |----------------------------------------------------------------------
            | We use send() not queue() here because this is a test command.
            | We want immediate feedback — success or error — right in the terminal.
            | All REAL emails in Tasks 5-14 will use queue() instead.
            */
            Mail::to($email)->send(new TestMail($name));

            $this->info('✓ Test email sent successfully!');
            $this->newLine();
            $this->line('Check your Mailtrap inbox at: https://mailtrap.io/inboxes');
            $this->newLine();
            $this->table(
                ['Setting', 'Value'],
                [
                    ['MAIL_MAILER', config('mail.default')],
                    ['MAIL_HOST',   config('mail.mailers.smtp.host')],
                    ['MAIL_PORT',   config('mail.mailers.smtp.port')],
                    ['From Address', config('mail.from.address')],
                    ['From Name',    config('mail.from.name')],
                ]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('✗ Failed to send email: ' . $e->getMessage());
            $this->newLine();
            $this->warn('Check your .env MAIL_* settings and try again.');

            return Command::FAILURE;
        }
    }
}
