<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Settings</h2>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
            Platform-wide configuration
        </p>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4">

        @if(session('success'))
            <div class="mb-6 px-4 py-3 bg-green-100 dark:bg-green-900
                        border border-green-300 dark:border-green-700
                        text-green-800 dark:text-green-300 rounded-xl text-sm">
                ✓ {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 px-4 py-3 bg-red-50 dark:bg-red-950
                        border border-red-200 dark:border-red-800
                        text-red-700 dark:text-red-400 rounded-xl text-sm">
                <p class="font-medium mb-1">Please fix the following errors:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.settings.update') }}"
              method="POST"
              enctype="multipart/form-data"
              class="space-y-6">
            @csrf

            {{-- =============================================
                 SECTION: GENERAL
            ============================================= --}}
            <div class="bg-white dark:bg-gray-800
                        rounded-2xl border border-gray-200 dark:border-gray-700
                        overflow-hidden">

                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700
                            bg-gray-50 dark:bg-gray-900">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200
                                flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-900
                                     flex items-center justify-center text-xs">🌐</span>
                        General
                    </h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                        Basic site identity settings
                    </p>
                </div>

                <div class="p-6 space-y-5">

                    <div>
                        <label class="block text-sm font-medium
                                       text-gray-700 dark:text-gray-300 mb-1.5">
                            Site Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="site_name"
                               value="{{ old('site_name', $settings['site_name'] ?? config('app.name')) }}"
                               class="w-full border border-gray-300 dark:border-gray-600
                                      bg-white dark:bg-gray-900
                                      text-gray-800 dark:text-gray-200
                                      rounded-xl px-4 py-2.5 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-indigo-400
                                      @error('site_name') border-red-400 @enderror">
                        @error('site_name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium
                                       text-gray-700 dark:text-gray-300 mb-1.5">
                            Tagline
                        </label>
                        <input type="text"
                               name="site_tagline"
                               value="{{ old('site_tagline', $settings['site_tagline'] ?? '') }}"
                               placeholder="A short description of your site"
                               class="w-full border border-gray-300 dark:border-gray-600
                                      bg-white dark:bg-gray-900
                                      text-gray-800 dark:text-gray-200
                                      rounded-xl px-4 py-2.5 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>

                    {{-- Logo --}}
                    <div>
                        <label class="block text-sm font-medium
                                       text-gray-700 dark:text-gray-300 mb-1.5">
                            Logo
                        </label>
                        @if(!empty($settings['logo_path']))
                            <div class="flex items-center gap-4 mb-3">
                                <img src="{{ asset('storage/' . $settings['logo_path']) }}"
                                     alt="Current logo"
                                     class="h-12 object-contain rounded-lg
                                            border border-gray-200 dark:border-gray-700 bg-white p-1">
                                <form action="{{ route('admin.settings.remove-image') }}"
                                      method="POST"
                                      onsubmit="return confirm('Remove logo?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="field" value="logo_path">
                                    <button type="submit"
                                            class="text-xs text-red-500 dark:text-red-400 hover:underline">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        @endif
                        <input type="file"
                               name="logo"
                               accept="image/jpg,image/jpeg,image/png,image/webp,image/svg+xml"
                               class="w-full text-sm text-gray-500 dark:text-gray-400
                                      file:mr-3 file:py-2 file:px-3 file:rounded-xl
                                      file:border-0 file:text-sm file:font-medium
                                      file:bg-indigo-50 dark:file:bg-indigo-950
                                      file:text-indigo-700 dark:file:text-indigo-400
                                      hover:file:bg-indigo-100 cursor-pointer">
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                            PNG, SVG, JPG — max 1MB. Recommended: 200×50px
                        </p>
                    </div>

                    {{-- Favicon --}}
                    <div>
                        <label class="block text-sm font-medium
                                       text-gray-700 dark:text-gray-300 mb-1.5">
                            Favicon
                        </label>
                        @if(!empty($settings['favicon_path']))
                            <div class="flex items-center gap-4 mb-3">
                                <img src="{{ asset('storage/' . $settings['favicon_path']) }}"
                                     alt="Current favicon"
                                     class="w-8 h-8 object-contain rounded
                                            border border-gray-200 dark:border-gray-700 bg-white p-0.5">
                                <form action="{{ route('admin.settings.remove-image') }}"
                                      method="POST"
                                      onsubmit="return confirm('Remove favicon?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="field" value="favicon_path">
                                    <button type="submit"
                                            class="text-xs text-red-500 dark:text-red-400 hover:underline">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        @endif
                        <input type="file"
                               name="favicon"
                               accept="image/png"
                               class="w-full text-sm text-gray-500 dark:text-gray-400
                                      file:mr-3 file:py-2 file:px-3 file:rounded-xl
                                      file:border-0 file:text-sm file:font-medium
                                      file:bg-indigo-50 dark:file:bg-indigo-950
                                      file:text-indigo-700 dark:file:text-indigo-400
                                      hover:file:bg-indigo-100 cursor-pointer">
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                            PNG only — max 256KB. Recommended: 32×32px or 64×64px
                        </p>
                    </div>

                </div>
            </div>

            {{-- =============================================
                 SECTION: CONTENT
            ============================================= --}}
            <div class="bg-white dark:bg-gray-800
                        rounded-2xl border border-gray-200 dark:border-gray-700
                        overflow-hidden">

                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700
                            bg-gray-50 dark:bg-gray-900">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200
                                flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-900
                                     flex items-center justify-center text-xs">📝</span>
                        Content
                    </h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                        Post creation defaults
                    </p>
                </div>

                <div class="p-6">
                    <div>
                        <label class="block text-sm font-medium
                                       text-gray-700 dark:text-gray-300 mb-1.5">
                            Default Post Status
                        </label>
                        <select name="default_post_status"
                                class="w-full border border-gray-300 dark:border-gray-600
                                       bg-white dark:bg-gray-900
                                       text-gray-800 dark:text-gray-200
                                       rounded-xl px-4 py-2.5 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="draft"
                                {{ ($settings['default_post_status'] ?? 'draft') === 'draft' ? 'selected' : '' }}>
                                Draft — posts are saved as drafts until manually published
                            </option>
                            <option value="published"
                                {{ ($settings['default_post_status'] ?? 'draft') === 'published' ? 'selected' : '' }}>
                                Published — posts go live immediately on save
                            </option>
                        </select>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                            This is the pre-selected status in the post editor. Authors can still change it.
                        </p>
                    </div>
                </div>
            </div>

            {{-- =============================================
                 SECTION: COMMENTS
            ============================================= --}}
            <div class="bg-white dark:bg-gray-800
                        rounded-2xl border border-gray-200 dark:border-gray-700
                        overflow-hidden">

                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700
                            bg-gray-50 dark:bg-gray-900">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200
                                flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-900
                                     flex items-center justify-center text-xs">💬</span>
                        Comments
                    </h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                        Global comment behaviour
                    </p>
                </div>

                <div class="p-6 space-y-4">

                    @foreach([
                        [
                            'name'    => 'comments_open',
                            'label'   => 'Comments Open',
                            'desc'    => 'Allow readers to post comments. Disabling this hides the comment form on all posts globally.',
                            'key'     => 'comments_open',
                        ],
                        [
                            'name'    => 'comments_auto_approve',
                            'label'   => 'Auto-Approve Comments',
                            'desc'    => 'New comments from all users are approved instantly without moderation. Turn off to send all comments to the moderation queue.',
                            'key'     => 'comments_auto_approve',
                        ],
                    ] as $toggle)
                        <div class="flex items-start justify-between gap-4 py-3
                                    border-b border-gray-100 dark:border-gray-700 last:border-0">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ $toggle['label'] }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 leading-relaxed">
                                    {{ $toggle['desc'] }}
                                </p>
                            </div>
                            <div class="shrink-0 pt-0.5">
                                @php $isOn = ($settings[$toggle['key']] ?? '0') === '1'; @endphp
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden"
                                           name="{{ $toggle['name'] }}"
                                           value="0">
                                    <input type="checkbox"
                                           name="{{ $toggle['name'] }}"
                                           value="1"
                                           {{ $isOn ? 'checked' : '' }}
                                           class="sr-only peer">
                                    <div class="w-10 h-5 rounded-full
                                                bg-gray-200 dark:bg-gray-700
                                                peer-checked:bg-indigo-600
                                                peer-focus:ring-2 peer-focus:ring-indigo-400
                                                transition-colors relative
                                                after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                                after:w-4 after:h-4 after:bg-white after:rounded-full
                                                after:transition-all
                                                peer-checked:after:translate-x-5">
                                    </div>
                                </label>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>

            {{-- =============================================
                 SECTION: EMAIL / REPORTS
            ============================================= --}}
            <div class="bg-white dark:bg-gray-800
                        rounded-2xl border border-gray-200 dark:border-gray-700
                        overflow-hidden">

                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700
                            bg-gray-50 dark:bg-gray-900">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200
                                flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-900
                                     flex items-center justify-center text-xs">📧</span>
                        Email & Reports
                    </h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                        Weekly report email configuration
                    </p>
                </div>

                <div class="p-6 space-y-4">

                    <div class="flex items-start justify-between gap-4 py-3
                                border-b border-gray-100 dark:border-gray-700">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Weekly Report Email
                            </p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                Send a weekly summary every Monday at 9AM.
                            </p>
                        </div>
                        <div class="shrink-0 pt-0.5">
                            @php $reportOn = ($settings['weekly_report_enabled'] ?? '1') === '1'; @endphp
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="weekly_report_enabled" value="0">
                                <input type="checkbox"
                                       name="weekly_report_enabled"
                                       value="1"
                                       {{ $reportOn ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-10 h-5 rounded-full
                                            bg-gray-200 dark:bg-gray-700
                                            peer-checked:bg-indigo-600
                                            peer-focus:ring-2 peer-focus:ring-indigo-400
                                            transition-colors relative
                                            after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                            after:w-4 after:h-4 after:bg-white after:rounded-full
                                            after:transition-all
                                            peer-checked:after:translate-x-5">
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium
                                       text-gray-700 dark:text-gray-300 mb-1.5">
                            Report Recipient Email
                        </label>
                        <input type="email"
                               name="weekly_report_email"
                               value="{{ old('weekly_report_email', $settings['weekly_report_email'] ?? '') }}"
                               placeholder="admin@example.com"
                               class="w-full border border-gray-300 dark:border-gray-600
                                      bg-white dark:bg-gray-900
                                      text-gray-800 dark:text-gray-200
                                      rounded-xl px-4 py-2.5 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-indigo-400
                                      @error('weekly_report_email') border-red-400 @enderror">
                        @error('weekly_report_email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                            If blank, falls back to the first admin user's email.
                        </p>
                    </div>

                </div>
            </div>

            {{-- =============================================
                 SECTION: MAINTENANCE
            ============================================= --}}
            <div class="bg-white dark:bg-gray-800
                        rounded-2xl border border-gray-200 dark:border-gray-700
                        overflow-hidden
                        {{ ($settings['maintenance_mode'] ?? '0') === '1'
                            ? 'ring-2 ring-red-400 dark:ring-red-600'
                            : '' }}">

                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700
                            bg-gray-50 dark:bg-gray-900">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200
                                flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-red-100 dark:bg-red-900
                                     flex items-center justify-center text-xs">🔧</span>
                        Maintenance Mode
                        @if(($settings['maintenance_mode'] ?? '0') === '1')
                            <span class="ml-1 px-2 py-0.5 text-xs font-bold rounded-full
                                         bg-red-100 dark:bg-red-900
                                         text-red-700 dark:text-red-400 animate-pulse">
                                ACTIVE
                            </span>
                        @endif
                    </h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                        Show a maintenance page to all non-admin visitors
                    </p>
                </div>

                <div class="p-6 space-y-4">

                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Enable Maintenance Mode
                            </p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                Admins can still browse the site normally. All other visitors see the maintenance message.
                            </p>
                        </div>
                        <div class="shrink-0 pt-0.5">
                            @php $maintenanceOn = ($settings['maintenance_mode'] ?? '0') === '1'; @endphp
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="maintenance_mode" value="0">
                                <input type="checkbox"
                                       name="maintenance_mode"
                                       value="1"
                                       {{ $maintenanceOn ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-10 h-5 rounded-full
                                            bg-gray-200 dark:bg-gray-700
                                            peer-checked:bg-red-500
                                            peer-focus:ring-2 peer-focus:ring-red-400
                                            transition-colors relative
                                            after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                            after:w-4 after:h-4 after:bg-white after:rounded-full
                                            after:transition-all
                                            peer-checked:after:translate-x-5">
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium
                                       text-gray-700 dark:text-gray-300 mb-1.5">
                            Maintenance Message
                        </label>
                        <textarea name="maintenance_message"
                                  rows="3"
                                  class="w-full border border-gray-300 dark:border-gray-600
                                         bg-white dark:bg-gray-900
                                         text-gray-800 dark:text-gray-200
                                         rounded-xl px-4 py-2.5 text-sm
                                         focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('maintenance_message', $settings['maintenance_message'] ?? '') }}</textarea>
                    </div>

                </div>
            </div>

            {{-- Save button --}}
            <div class="flex items-center justify-between">
                <p class="text-xs text-gray-400 dark:text-gray-500">
                    Settings are applied immediately after saving.
                </p>
                <button type="submit"
                        class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700
                               text-white font-semibold text-sm rounded-xl
                               transition shadow-sm shadow-indigo-200 dark:shadow-none">
                    Save Settings
                </button>
            </div>

        </form>
    </div>
</x-app-layout>
