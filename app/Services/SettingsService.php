<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    const CACHE_KEY = 'app_settings';
    const CACHE_TTL = 60 * 60; // 1 hour

    /*
    | get() — Read one setting value, with cache.
    */
    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->all();
        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    /*
    | set() — Write one setting and clear the cache.
    */
    public function set(string $key, mixed $value): void
    {
        Setting::set($key, $value);
        Cache::forget(self::CACHE_KEY);
    }

    /*
    | setMany() — Write multiple settings at once.
    */
    public function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }
        Cache::forget(self::CACHE_KEY);
    }

    /*
    | all() — Return all settings as a flat key-value array, cached.
    */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Setting::all()->pluck('value', 'key')->toArray();
        });
    }

    /*
    | bool() — Read a setting as a boolean.
    | Stored as '1'/'0' in the database.
    */
    public function bool(string $key, bool $default = false): bool
    {
        $value = $this->get($key);
        if ($value === null) return $default;
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /*
    | Seed default values — called from the seeder or first-run setup.
    */
    public function seedDefaults(): void
    {
        $defaults = [
            'site_name'              => config('app.name'),
            'site_tagline'           => 'A modern blog platform.',
            'default_post_status'    => 'draft',
            'comments_open'          => '1',
            'comments_auto_approve'  => '0',
            'weekly_report_enabled'  => '1',
            'weekly_report_email'    => config('mail.admin_address', ''),
            'maintenance_mode'       => '0',
            'maintenance_message'    => 'We are performing scheduled maintenance. Back soon!',
        ];

        foreach ($defaults as $key => $value) {
            // Only seed if key does not already exist
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }
}
