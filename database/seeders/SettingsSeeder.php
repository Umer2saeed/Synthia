<?php

namespace Database\Seeders;

use App\Services\SettingsService;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        app(SettingsService::class)->seedDefaults();
        $this->command->info('Default settings seeded.');
    }
}
