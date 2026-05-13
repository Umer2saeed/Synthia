<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body    { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f9fafb; color: #111827; margin: 0; padding: 0; }
        .wrap   { max-width: 580px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: #4f46e5; padding: 32px 40px; }
        .header h1 { color: #fff; margin: 0; font-size: 20px; font-weight: 700; }
        .header p  { color: #c7d2fe; margin: 4px 0 0; font-size: 13px; }
        .body   { padding: 32px 40px; }
        .stats  { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 28px; }
        .stat   { background: #f3f4f6; border-radius: 10px; padding: 16px; }
        .stat .num  { font-size: 28px; font-weight: 800; color: #4f46e5; }
        .stat .lbl  { font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 2px; }
        .section { margin-bottom: 24px; }
        .section h2 { font-size: 13px; font-weight: 700; color: #374151; margin: 0 0 8px; text-transform: uppercase; letter-spacing: 0.05em; }
        .post-card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 14px 16px; }
        .post-card .title { font-weight: 600; color: #111827; font-size: 14px; margin: 0 0 4px; }
        .post-card .meta  { font-size: 12px; color: #9ca3af; }
        .badge  { display: inline-block; padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 600; }
        .badge.warn { background: #fef3c7; color: #92400e; }
        .footer { background: #f9fafb; padding: 20px 40px; border-top: 1px solid #f3f4f6; }
        .footer p { font-size: 12px; color: #9ca3af; margin: 0; }
        a { color: #4f46e5; }
    </style>
</head>
<body>
<div class="wrap">

    <div class="header">
        <h1>{{ config('app.name') }} — Weekly Report</h1>
        <p>{{ now()->subWeek()->format('d M') }} – {{ now()->format('d M Y') }}</p>
    </div>

    <div class="body">

        <div class="stats">
            <div class="stat">
                <div class="num">{{ $stats['new_posts'] }}</div>
                <div class="lbl">New Posts</div>
            </div>
            <div class="stat">
                <div class="num">{{ $stats['new_users'] }}</div>
                <div class="lbl">New Users</div>
            </div>
            <div class="stat">
                <div class="num">{{ number_format($stats['total_views']) }}</div>
                <div class="lbl">Total Views</div>
            </div>
            <div class="stat">
                <div class="num">{{ $stats['pending_comments'] }}</div>
                <div class="lbl">
                    Pending Comments
                    @if($stats['pending_comments'] > 0)
                        <span class="badge warn">Needs attention</span>
                    @endif
                </div>
            </div>
        </div>

        @if($stats['top_post'])
            <div class="section">
                <h2>🏆 Top Post This Week</h2>
                <div class="post-card">
                    <p class="title">{{ $stats['top_post']['title'] }}</p>
                    <p class="meta">
                        {{ number_format($stats['top_post']['views']) }} views ·
                        by {{ $stats['top_post']['author'] }} ·
                        {{ $stats['top_post']['published_at'] }}
                    </p>
                    <a href="{{ $stats['top_post']['url'] }}"
                       style="font-size:12px; margin-top: 6px; display:inline-block;">
                        Read post →
                    </a>
                </div>
            </div>
        @endif

        <div class="section">
            <h2>Quick Links</h2>
            <p style="font-size:13px; color:#6b7280; line-height:1.8;">
                <a href="{{ route('admin.dashboard') }}">Admin Dashboard</a> ·
                <a href="{{ route('admin.posts.index') }}">Manage Posts</a> ·
                <a href="{{ route('admin.comments.index') }}">Manage Comments</a> ·
                <a href="{{ route('admin.users.index') }}">Manage Users</a>
            </p>
        </div>

    </div>

    <div class="footer">
        <p>
            This is an automated report from {{ config('app.name') }}.
            Sent every Monday at 9AM.
        </p>
    </div>

</div>
</body>
</html>
