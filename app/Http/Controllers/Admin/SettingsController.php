<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function __construct(
        private SettingsService $settings
    ) {}

    public function index()
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $settings = $this->settings->all();

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $validated = $request->validate([
            'site_name'             => ['required', 'string', 'max:100'],
            'site_tagline'          => ['nullable', 'string', 'max:200'],
            'default_post_status'   => ['required', 'in:draft,published'],
            'comments_open'         => ['boolean'],
            'comments_auto_approve' => ['boolean'],
            'weekly_report_enabled' => ['boolean'],
            'weekly_report_email'   => ['nullable', 'email', 'max:150'],
            'maintenance_mode'      => ['boolean'],
            'maintenance_message'   => ['nullable', 'string', 'max:500'],
            'logo'                  => ['nullable', 'image', 'max:1024', 'mimes:jpg,jpeg,png,webp,svg'],
            'favicon'               => ['nullable', 'image', 'max:256', 'mimes:png,ico'],
        ], [
            'site_name.required' => 'Site name is required.',
            'weekly_report_email.email' => 'Please enter a valid email address for the weekly report.',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $old = $this->settings->get('logo_path');
            if ($old) Storage::disk('public')->delete($old);

            $path = $request->file('logo')->store('settings', 'public');
            $this->settings->set('logo_path', $path);
        }

        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            $old = $this->settings->get('favicon_path');
            if ($old) Storage::disk('public')->delete($old);

            $path = $request->file('favicon')->store('settings', 'public');
            $this->settings->set('favicon_path', $path);
        }

        $this->settings->setMany([
            'site_name'             => $validated['site_name'],
            'site_tagline'          => $validated['site_tagline'] ?? '',
            'default_post_status'   => $validated['default_post_status'],
            'comments_open'         => $request->boolean('comments_open') ? '1' : '0',
            'comments_auto_approve' => $request->boolean('comments_auto_approve') ? '1' : '0',
            'weekly_report_enabled' => $request->boolean('weekly_report_enabled') ? '1' : '0',
            'weekly_report_email'   => $validated['weekly_report_email'] ?? '',
            'maintenance_mode'      => $request->boolean('maintenance_mode') ? '1' : '0',
            'maintenance_message'   => $validated['maintenance_message'] ?? '',
        ]);

        return back()->with('success', 'Settings saved successfully.');
    }

    /*
    | Remove logo or favicon.
    */
    public function removeImage(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $field = $request->input('field'); // 'logo_path' or 'favicon_path'

        if (!in_array($field, ['logo_path', 'favicon_path'])) {
            abort(422);
        }

        $path = $this->settings->get($field);
        if ($path) {
            Storage::disk('public')->delete($path);
        }

        $this->settings->set($field, null);

        return back()->with('success', 'Image removed.');
    }
}
