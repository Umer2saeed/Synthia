<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ darkMode: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }"
      x-bind:class="darkMode ? 'dark' : ''"
      x-init="$watch('darkMode', v => localStorage.setItem('theme', v ? 'dark' : 'light'))">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Synthia') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /*
        | Auth layout — split screen.
        | Left panel: feature showcase (hidden on mobile).
        | Right panel: form.
        | Uses CSS custom properties for light/dark theming.
        */

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #f8f7ff;
            --surface:   #ffffff;
            --primary:   #6366f1;
            --primary-h: #4f46e5;
            --accent:    #7c3aed;
            --text:      #1e1b4b;
            --text-muted:#6b7280;
            --border:    #e0e0f0;
            --input-bg:  #f5f4ff;
            --error:     #ef4444;
            --shadow:    0 4px 24px rgba(99,102,241,0.10);
        }

        .dark {
            --bg:        #0f0e1a;
            --surface:   #1a1828;
            --primary:   #6366f1;
            --primary-h: #818cf8;
            --accent:    #a78bfa;
            --text:      #f1f0ff;
            --text-muted:#9ca3af;
            --border:    #2d2b3d;
            --input-bg:  #13121f;
            --error:     #f87171;
            --shadow:    0 4px 32px rgba(0,0,0,0.40);
        }

        html, body {
            height: 100%;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            transition: background 0.3s, color 0.3s;
        }

        /* ── LAYOUT ──────────────────────────────────────────────── */
        .auth-shell {
            display: flex;
            min-height: 100vh;
        }

        /* Left panel — hidden below md */
        .auth-left {
            display: none;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4c1d95 100%);
        }

        @media (min-width: 768px) {
            .auth-left  { display: flex; flex: 1; flex-direction: column; justify-content: center; padding: 56px 48px; }
            .auth-right { flex: 1; }
        }

        @media (min-width: 1280px) {
            .auth-left  { padding: 72px 64px; }
        }

        /* Ambient orb — decorative background glow */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.35;
            pointer-events: none;
        }
        .orb-1 { width: 420px; height: 420px; background: #6366f1; top: -120px; right: -80px; }
        .orb-2 { width: 300px; height: 300px; background: #7c3aed; bottom: -60px; left: -60px; }
        .orb-3 { width: 200px; height: 200px; background: #a78bfa; top: 45%; left: 30%; opacity: 0.2; }

        /* Brand mark on left panel */
        .left-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 56px;
            position: relative;
            z-index: 2;
        }

        .left-brand-icon {
            width: 36px; height: 36px;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }

        .left-brand-name {
            font-size: 20px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.02em;
        }

        /* Feature cards — rotate automatically */
        .feature-stage {
            position: relative;
            z-index: 2;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .feature-headline {
            font-size: clamp(28px, 3.5vw, 42px);
            font-weight: 800;
            color: #ffffff;
            line-height: 1.15;
            letter-spacing: -0.03em;
            margin-bottom: 48px;
        }

        .feature-headline span {
            background: linear-gradient(90deg, #a78bfa, #c4b5fd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .feature-cards {
            position: relative;
            min-height: 160px;
        }

        .feature-card {
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0.07);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 20px;
            padding: 28px;
            opacity: 0;
            transform: translateY(16px);
            transition: opacity 0.6s ease, transform 0.6s ease;
            pointer-events: none;
        }

        .feature-card.active {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .feature-card-icon {
            width: 44px; height: 44px;
            background: rgba(255,255,255,0.12);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 16px;
            font-size: 20px;
        }

        .feature-card-title {
            font-size: 17px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 8px;
        }

        .feature-card-desc {
            font-size: 14px;
            color: rgba(255,255,255,0.65);
            line-height: 1.6;
        }

        /* Dots indicator */
        .feature-dots {
            display: flex;
            gap: 6px;
            margin-top: 200px;
            position: relative;
            z-index: 2;
        }

        .feature-dot {
            width: 6px; height: 6px;
            border-radius: 99px;
            background: rgba(255,255,255,0.3);
            transition: width 0.3s, background 0.3s;
            cursor: pointer;
        }

        .feature-dot.active {
            width: 22px;
            background: #a78bfa;
        }

        /* ── RIGHT PANEL ─────────────────────────────────────────── */
        .auth-right {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
            background: var(--bg);
            min-height: 100vh;
        }

        .auth-box {
            width: 100%;
            max-width: 420px;
        }

        /* Logo on the form side (mobile too) */
        .form-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 36px;
        }

        .form-brand-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, #6366f1, #7c3aed);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 16px rgba(99,102,241,0.35);
        }

        .form-brand-name {
            font-size: 22px;
            font-weight: 800;
            color: var(--text);
            letter-spacing: -0.02em;
        }

        .form-title {
            font-size: 26px;
            font-weight: 800;
            color: var(--text);
            letter-spacing: -0.02em;
            margin-bottom: 6px;
        }

        .form-subtitle {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 32px;
            line-height: 1.5;
        }

        .form-subtitle a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }

        .form-subtitle a:hover { text-decoration: underline; }

        /* Google button */
        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 11px 16px;
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text);
            cursor: pointer;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            text-decoration: none;
            margin-bottom: 24px;
            box-shadow: var(--shadow);
        }

        .btn-google:hover {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
        }

        /* Divider */
        .auth-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .auth-divider-line {
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .auth-divider-text {
            font-size: 12px;
            font-weight: 500;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        /* Form fields */
        .field {
            margin-bottom: 18px;
        }

        .field label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 6px;
        }

        .field-wrap {
            position: relative;
        }

        .field input {
            width: 100%;
            padding: 11px 14px;
            background: var(--input-bg);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            font-size: 14px;
            color: var(--text);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            font-family: inherit;
        }

        .field input::placeholder { color: var(--text-muted); }

        .field input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
        }

        .field input.has-eye { padding-right: 44px; }

        /* Eye toggle */
        .eye-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-muted);
            padding: 4px;
            display: flex;
            align-items: center;
            transition: color 0.2s;
        }

        .eye-btn:hover { color: var(--primary); }

        /* Error text */
        .field-error {
            font-size: 12px;
            color: var(--error);
            margin-top: 5px;
        }

        /* Forgot password row */
        .field-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 6px;
        }

        .field-row label {
            margin-bottom: 0;
        }

        .link-subtle {
            font-size: 12px;
            color: var(--primary);
            font-weight: 500;
            text-decoration: none;
        }

        .link-subtle:hover { text-decoration: underline; }

        /* Remember me */
        .checkbox-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
        }

        .checkbox-row input[type="checkbox"] {
            width: 16px; height: 16px;
            accent-color: var(--primary);
            cursor: pointer;
            border-radius: 4px;
        }

        .checkbox-row label {
            font-size: 13px;
            color: var(--text-muted);
            cursor: pointer;
        }

        /* Submit button */
        .btn-primary {
            display: block;
            width: 100%;
            padding: 13px 16px;
            background: linear-gradient(135deg, #6366f1, #7c3aed);
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            color: #ffffff;
            cursor: pointer;
            letter-spacing: -0.01em;
            transition: opacity 0.2s, box-shadow 0.2s, transform 0.15s;
            box-shadow: 0 4px 20px rgba(99,102,241,0.4);
            font-family: inherit;
        }

        .btn-primary:hover {
            opacity: 0.93;
            box-shadow: 0 6px 28px rgba(99,102,241,0.5);
            transform: translateY(-1px);
        }

        .btn-primary:active { transform: translateY(0); }

        /* Session / status alert */
        .alert-status {
            padding: 10px 14px;
            background: rgba(99,238,146,0.12);
            border: 1px solid rgba(99,238,146,0.3);
            border-radius: 10px;
            font-size: 13px;
            color: #34d399;
            margin-bottom: 20px;
        }

        .alert-error-list {
            padding: 10px 14px;
            background: rgba(239,68,68,0.08);
            border: 1px solid rgba(239,68,68,0.25);
            border-radius: 10px;
            font-size: 13px;
            color: var(--error);
            margin-bottom: 20px;
        }

        /* Dark mode toggle (top right) */
        .theme-toggle {
            position: fixed;
            top: 16px;
            right: 16px;
            z-index: 50;
            width: 36px; height: 36px;
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            color: var(--text-muted);
            transition: border-color 0.2s;
            box-shadow: var(--shadow);
        }

        .theme-toggle:hover { border-color: var(--primary); color: var(--primary); }

        @media (prefers-reduced-motion: reduce) {
            .feature-card { transition: none; }
            .btn-primary  { transition: none; }
        }
    </style>
</head>
<body>

{{-- Dark mode toggle --}}
<button class="theme-toggle" @click="darkMode = !darkMode" title="Toggle dark mode">
    <svg x-show="!darkMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
    </svg>
    <svg x-show="darkMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
    </svg>
</button>

<div class="auth-shell">

    {{-- ── LEFT PANEL ───────────────────────────────────────────── --}}
    <div class="auth-left">
        {{-- Ambient orbs --}}
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>

        {{-- Brand --}}
        <div class="left-brand">
            <div class="left-brand-icon">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
            </div>
            <span class="left-brand-name">Synthia</span>
        </div>

        {{-- Feature stage --}}
        <div class="feature-stage">
            <h2 class="feature-headline">
                Your platform for<br>
                <span>ideas that matter.</span>
            </h2>

            <div class="feature-cards" id="featureCards">

                <div class="feature-card active">
                    <div class="feature-card-icon">✍️</div>
                    <div class="feature-card-title">Write without limits</div>
                    <div class="feature-card-desc">
                        A distraction-free editor with rich formatting, code blocks,
                        and media — built for writers who mean business.
                    </div>
                </div>

                <div class="feature-card">
                    <div class="feature-card-icon">📊</div>
                    <div class="feature-card-title">Know your audience</div>
                    <div class="feature-card-desc">
                        Real-time analytics on views, claps, and followers.
                        Understand what resonates and write more of it.
                    </div>
                </div>

                <div class="feature-card">
                    <div class="feature-card-icon">🔒</div>
                    <div class="feature-card-title">Security built in</div>
                    <div class="feature-card-desc">
                        Two-factor authentication, role-based access, and
                        content moderation — your platform stays clean and safe.
                    </div>
                </div>

                <div class="feature-card">
                    <div class="feature-card-icon">🚀</div>
                    <div class="feature-card-title">Ship content fast</div>
                    <div class="feature-card-desc">
                        Draft autosave, scheduled publishing, revision history —
                        never lose a word, never miss a deadline.
                    </div>
                </div>

            </div>

            <div class="feature-dots" id="featureDots">
                <div class="feature-dot active" data-index="0"></div>
                <div class="feature-dot" data-index="1"></div>
                <div class="feature-dot" data-index="2"></div>
                <div class="feature-dot" data-index="3"></div>
            </div>
        </div>
    </div>

    {{-- ── RIGHT PANEL ──────────────────────────────────────────── --}}
    <div class="auth-right">
        <div class="auth-box">

            {{-- Logo (visible on mobile too) --}}
            <div class="form-brand">
                <div class="form-brand-icon">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                </div>
                <span class="form-brand-name">Synthia</span>
            </div>

            {{-- Page content injected here --}}
            {{ $slot }}

        </div>
    </div>

</div>

<script>
    // ── FEATURE CARD ROTATOR ──────────────────────────────────────
    (function () {
        const cards = document.querySelectorAll('.feature-card');
        const dots  = document.querySelectorAll('.feature-dot');
        let current = 0;
        let timer;

        function goTo(index) {
            cards[current].classList.remove('active');
            dots[current].classList.remove('active');
            current = index;
            cards[current].classList.add('active');
            dots[current].classList.add('active');
        }

        function next() {
            goTo((current + 1) % cards.length);
        }

        function startTimer() {
            clearInterval(timer);
            timer = setInterval(next, 4000);
        }

        dots.forEach(dot => {
            dot.addEventListener('click', function () {
                goTo(parseInt(this.dataset.index));
                startTimer(); // reset timer on manual click
            });
        });

        startTimer();
    })();

    // ── PASSWORD EYE TOGGLE ───────────────────────────────────────
    document.querySelectorAll('.eye-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = this.previousElementSibling;
            const isText = input.type === 'text';
            input.type = isText ? 'password' : 'text';

            // swap icon
            this.querySelector('.eye-open').classList.toggle('hidden', !isText);
            this.querySelector('.eye-closed').classList.toggle('hidden', isText);
        });
    });
</script>

<x-toast :validation="false" />
</body>
</html>
