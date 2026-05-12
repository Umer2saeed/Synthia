@extends('frontend.layouts.app')

@section('content')
    <x-slot name="title">My Analytics</x-slot>

    {{-- Load Chart.js from CDN --}}
    @push('head')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @endpush

    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 transition-colors duration-200">

        {{-- PAGE HEADER --}}
        <div class="border-b border-gray-200 dark:border-gray-800/60
                    bg-white dark:bg-gray-900/50">
            <div class="max-w-6xl mx-auto px-6 py-8">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center gap-5">
                        <div class="relative shrink-0">
                            <img src="{{ $author->avatar_url }}"
                                 alt="{{ $author->name }}"
                                 class="w-14 h-14 rounded-2xl object-cover
                                        ring-2 ring-indigo-500/30">
                        </div>
                        <div>
                            <p class="text-xs font-medium text-indigo-600 dark:text-indigo-400
                                       uppercase tracking-widest mb-1">
                                Author Analytics
                            </p>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $author->name }}
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-500 mt-0.5">
                                Last updated {{ now()->format('d M Y, H:i') }}
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('frontend.reader.dashboard') }}"
                       class="text-sm text-gray-500 dark:text-gray-400
                              hover:text-gray-700 dark:hover:text-gray-200 transition">
                        ← Dashboard
                    </a>
                </div>
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-6 py-8 space-y-8">

            {{-- STATS ROW --}}
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                @foreach([
                    ['label' => 'Total Views',     'value' => number_format($totalStats['total_views']),    'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z', 'light_bg' => 'bg-indigo-50 border-indigo-100', 'dark_bg' => 'dark:bg-indigo-500/10 dark:border-indigo-500/20', 'icon_bg' => 'bg-indigo-100 dark:bg-indigo-500/20', 'icon_text' => 'text-indigo-600 dark:text-indigo-400', 'val_text' => 'text-indigo-700 dark:text-indigo-300', 'lbl_text' => 'text-indigo-600/70 dark:text-indigo-400/70'],
                    ['label' => 'Total Claps',     'value' => number_format($totalStats['total_claps']),    'icon' => 'M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'light_bg' => 'bg-amber-50 border-amber-100', 'dark_bg' => 'dark:bg-amber-500/10 dark:border-amber-500/20', 'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20', 'icon_text' => 'text-amber-600 dark:text-amber-400', 'val_text' => 'text-amber-700 dark:text-amber-300', 'lbl_text' => 'text-amber-600/70 dark:text-amber-400/70'],
                    ['label' => 'Total Comments',  'value' => number_format($totalStats['total_comments']), 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'light_bg' => 'bg-emerald-50 border-emerald-100', 'dark_bg' => 'dark:bg-emerald-500/10 dark:border-emerald-500/20', 'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'icon_text' => 'text-emerald-600 dark:text-emerald-400', 'val_text' => 'text-emerald-700 dark:text-emerald-300', 'lbl_text' => 'text-emerald-600/70 dark:text-emerald-400/70'],
                    ['label' => 'Followers',       'value' => number_format($totalStats['total_followers']), 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'light_bg' => 'bg-purple-50 border-purple-100', 'dark_bg' => 'dark:bg-purple-500/10 dark:border-purple-500/20', 'icon_bg' => 'bg-purple-100 dark:bg-purple-500/20', 'icon_text' => 'text-purple-600 dark:text-purple-400', 'val_text' => 'text-purple-700 dark:text-purple-300', 'lbl_text' => 'text-purple-600/70 dark:text-purple-400/70'],
                    ['label' => 'Posts Published', 'value' => number_format($totalStats['total_posts']),    'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z', 'light_bg' => 'bg-rose-50 border-rose-100', 'dark_bg' => 'dark:bg-rose-500/10 dark:border-rose-500/20', 'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20', 'icon_text' => 'text-rose-600 dark:text-rose-400', 'val_text' => 'text-rose-700 dark:text-rose-300', 'lbl_text' => 'text-rose-600/70 dark:text-rose-400/70'],
                ] as $stat)
                    <div class="rounded-2xl border p-5
                                {{ $stat['light_bg'] }} {{ $stat['dark_bg'] }}
                                hover:scale-[1.02] transition-transform duration-200">
                        <div class="w-9 h-9 rounded-xl {{ $stat['icon_bg'] }}
                                    flex items-center justify-center mb-4">
                            <svg class="w-4 h-4 {{ $stat['icon_text'] }}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="1.75" d="{{ $stat['icon'] }}"/>
                            </svg>
                        </div>
                        <p class="text-2xl font-bold {{ $stat['val_text'] }} mb-1">
                            {{ $stat['value'] }}
                        </p>
                        <p class="text-xs font-semibold {{ $stat['lbl_text'] }}
                                   uppercase tracking-wider">
                            {{ $stat['label'] }}
                        </p>
                    </div>
                @endforeach
            </div>

            {{-- CHARTS ROW 1: Views + Claps --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Views Chart --}}
                <div class="bg-white dark:bg-gray-900
                            rounded-2xl border border-gray-200 dark:border-gray-800/80
                            p-6">
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-white mb-1">
                        Views Over Time
                    </h2>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mb-5">
                        Last 30 days
                    </p>
                    <div class="h-48">
                        <canvas id="viewsChart"></canvas>
                    </div>
                </div>

                {{-- Claps Chart --}}
                <div class="bg-white dark:bg-gray-900
                            rounded-2xl border border-gray-200 dark:border-gray-800/80
                            p-6">
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-white mb-1">
                        Claps Over Time
                    </h2>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mb-5">
                        Last 30 days
                    </p>
                    <div class="h-48">
                        <canvas id="clapsChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- CHARTS ROW 2: Comments + Followers --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Comments Chart --}}
                <div class="bg-white dark:bg-gray-900
                            rounded-2xl border border-gray-200 dark:border-gray-800/80
                            p-6">
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-white mb-1">
                        Comments Over Time
                    </h2>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mb-5">
                        Last 30 days
                    </p>
                    <div class="h-48">
                        <canvas id="commentsChart"></canvas>
                    </div>
                </div>

                {{-- Follower Growth Chart --}}
                <div class="bg-white dark:bg-gray-900
                            rounded-2xl border border-gray-200 dark:border-gray-800/80
                            p-6">
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-white mb-1">
                        Follower Growth
                    </h2>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mb-5">
                        Last 8 weeks
                    </p>
                    <div class="h-48">
                        <canvas id="followersChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- TOP POSTS + REFERRERS --}}
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

                {{-- Top by Views --}}
                @include('frontend.partials._top-posts-card', [
                    'title'     => 'Top by Views',
                    'subtitle'  => 'Most read posts',
                    'posts'     => $topByViews,
                    'unit'      => 'views',
                    'accent'    => 'text-indigo-600 dark:text-indigo-400',
                    'bar'       => 'bg-indigo-500',
                ])

                {{-- Top by Claps --}}
                @include('frontend.partials._top-posts-card', [
                    'title'     => 'Top by Claps',
                    'subtitle'  => 'Most appreciated posts',
                    'posts'     => $topByClaps,
                    'unit'      => 'claps',
                    'accent'    => 'text-amber-600 dark:text-amber-400',
                    'bar'       => 'bg-amber-500',
                ])

                {{-- Top by Comments --}}
                @include('frontend.partials._top-posts-card', [
                    'title'     => 'Top by Comments',
                    'subtitle'  => 'Most discussed posts',
                    'posts'     => $topByComments,
                    'unit'      => 'comments',
                    'accent'    => 'text-emerald-600 dark:text-emerald-400',
                    'bar'       => 'bg-emerald-500',
                ])

            </div>

            {{-- TRAFFIC SOURCES --}}
            @if(!empty($referrers))
                <div class="bg-white dark:bg-gray-900
                            rounded-2xl border border-gray-200 dark:border-gray-800/80
                            overflow-hidden">

                    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800/80">
                        <h2 class="text-sm font-semibold text-gray-800 dark:text-white">
                            Views by Category
                        </h2>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                            Which content categories drive the most views
                        </p>
                    </div>

                    @php $maxViews = collect($referrers)->max('views') ?: 1; @endphp

                    <div class="p-6 space-y-4">
                        @foreach($referrers as $source)
                            <div class="flex items-center gap-4">
                                <div class="w-32 shrink-0 text-xs font-medium
                                             text-gray-600 dark:text-gray-400 truncate">
                                    {{ $source['source'] }}
                                </div>
                                <div class="flex-1 bg-gray-100 dark:bg-gray-800
                                             rounded-full h-2 overflow-hidden">
                                    <div class="bg-indigo-500 h-full rounded-full transition-all"
                                         style="width: {{ round(($source['views'] / $maxViews) * 100) }}%">
                                    </div>
                                </div>
                                <div class="w-16 text-right text-xs font-semibold
                                             text-gray-700 dark:text-gray-300 shrink-0">
                                    {{ number_format($source['views']) }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
            @endif

        </div>
    </div>

    {{-- Chart.js initialisation --}}
    <script>
        (function () {

            /*
            | Read chart data passed from PHP as JSON.
            | All values are plain numbers — safe to embed directly.
            */
            const viewsData     = @json($viewsChart);
            const clapsData     = @json($clapsChart);
            const commentsData  = @json($commentsChart);
            const followersData = @json($followerChart);

            /*
            | Detect current theme to style charts correctly.
            | We read from the <html> class — same as the rest of Synthia.
            */
            const isDark = document.documentElement.classList.contains('dark');

            const gridColor  = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
            const labelColor = isDark ? '#9ca3af' : '#6b7280';

            /*
            | Shared Chart.js defaults for all line charts.
            */
            function lineChartConfig(labels, data, color, fillColor) {
                return {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            data,
                            borderColor:     color,
                            backgroundColor: fillColor,
                            borderWidth:     2,
                            pointRadius:     0,
                            pointHoverRadius:4,
                            fill:            true,
                            tension:         0.4,
                        }],
                    },
                    options: {
                        responsive:          true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                mode:      'index',
                                intersect: false,
                            },
                        },
                        scales: {
                            x: {
                                grid:  { color: gridColor },
                                ticks: {
                                    color:    labelColor,
                                    font:     { size: 10 },
                                    maxTicksLimit: 8,
                                },
                            },
                            y: {
                                beginAtZero: true,
                                grid:  { color: gridColor },
                                ticks: {
                                    color:    labelColor,
                                    font:     { size: 10 },
                                    precision: 0,
                                },
                            },
                        },
                    },
                };
            }

            // Views — indigo
            new Chart(
                document.getElementById('viewsChart'),
                lineChartConfig(
                    viewsData.labels,
                    viewsData.data,
                    '#6366f1',
                    'rgba(99,102,241,0.12)'
                )
            );

            // Claps — amber
            new Chart(
                document.getElementById('clapsChart'),
                lineChartConfig(
                    clapsData.labels,
                    clapsData.data,
                    '#f59e0b',
                    'rgba(245,158,11,0.12)'
                )
            );

            // Comments — emerald
            new Chart(
                document.getElementById('commentsChart'),
                lineChartConfig(
                    commentsData.labels,
                    commentsData.data,
                    '#10b981',
                    'rgba(16,185,129,0.12)'
                )
            );

            // Followers — bar chart (weekly, not daily)
            new Chart(document.getElementById('followersChart'), {
                type: 'bar',
                data: {
                    labels: followersData.labels,
                    datasets: [{
                        data:            followersData.data,
                        backgroundColor: isDark ? 'rgba(139,92,246,0.7)' : 'rgba(109,40,217,0.7)',
                        borderRadius:    6,
                        borderSkipped:   false,
                    }],
                },
                options: {
                    responsive:          true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                    },
                    scales: {
                        x: {
                            grid:  { display: false },
                            ticks: {
                                color:  labelColor,
                                font:   { size: 9 },
                                maxRotation: 30,
                            },
                        },
                        y: {
                            beginAtZero: true,
                            grid:  { color: gridColor },
                            ticks: {
                                color:     labelColor,
                                font:      { size: 10 },
                                precision: 0,
                            },
                        },
                    },
                },
            });

        })();
    </script>

@endsection
