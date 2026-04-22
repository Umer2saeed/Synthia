<!DOCTYPE html>
<html lang="en" x-data="themeManager()" :class="{ 'dark': isDark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{--
    | SEO Meta Component
    | Each page passes its own $seo array from the controller.
    | If no $seo is passed, sensible defaults are used automatically.
    --}}
    <x-seo-meta
        :title="$seo['title']           ?? config('app.name')"
        :description="$seo['description'] ?? 'Explore stories, insights, and tutorials on Synthia.'"
        :image="$seo['image']           ?? asset('images/og-default.jpg')"
        :url="$seo['url']               ?? request()->url()"
        :type="$seo['type']             ?? 'website'"
        :author="$seo['author']         ?? null"
        :published-at="$seo['publishedAt'] ?? null"
    />

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    {{-- Tailwind via Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Prevent flash of wrong theme --}}
    <script>
        (function () {
            const theme = localStorage.getItem('synthia-theme');
            if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <style>
        .font-display { font-family: 'Playfair Display', serif; }
        .font-body    { font-family: 'Inter', sans-serif; }
        *, *::before, *::after {
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.2s ease;
        }
        .prose-content p  { margin-bottom: 1.25rem; line-height: 1.8; }
        .prose-content h2 { font-size: 1.5rem; font-weight: 700; margin: 2rem 0 1rem; font-family: 'Playfair Display', serif; }
        .prose-content h3 { font-size: 1.25rem; font-weight: 600; margin: 1.5rem 0 0.75rem; }
        .prose-content ul { list-style: disc; padding-left: 1.5rem; margin-bottom: 1rem; }
        .prose-content ol { list-style: decimal; padding-left: 1.5rem; margin-bottom: 1rem; }
        .prose-content code { background: #f1f5f9; padding: 0.15rem 0.4rem; border-radius: 4px; font-size: 0.875rem; }
        .dark .prose-content code { background: #1e293b; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
</head>

<body class="font-body bg-white dark:bg-gray-950 text-gray-800 dark:text-gray-200 antialiased">

{{-- HEADER --}}
@include('frontend.partials._header')

{{-- MAIN CONTENT --}}
<main class="min-h-screen">
{{--    {{ $slot }}--}}

    {{-- Flash error from admin panel redirect --}}
    @if(session('error'))
        <div x-data="{ show: true }"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="max-w-7xl mx-auto px-4 sm:px-6 mt-4">
            <div class="flex items-center justify-between gap-4 px-4 py-3
                    bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800
                    text-red-700 dark:text-red-400 text-sm rounded-xl">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('error') }}
                </div>
                <button @click="show = false" class="text-red-400 hover:text-red-600 transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @yield('content')
</main>

{{-- FOOTER --}}
@include('frontend.partials._footer')

{{-- Alpine.js dark mode manager --}}
<script>
    function themeManager() {
        return {
            /*
            | isDark drives the :class binding on <html>.
            | We read from localStorage on init so the preference
            | persists across page reloads.
            */
            isDark: localStorage.getItem('synthia-theme') === 'dark' ||
                (!localStorage.getItem('synthia-theme') &&
                    window.matchMedia('(prefers-color-scheme: dark)').matches),

            toggle() {
                this.isDark = !this.isDark;
                // Persist the choice in localStorage
                localStorage.setItem('synthia-theme', this.isDark ? 'dark' : 'light');
            },
        };
    }
</script>

</body>
</html>
