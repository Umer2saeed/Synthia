<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance — {{ app(\App\Services\SettingsService::class)->get('site_name', config('app.name')) }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0f0e17;
            color: #fffffe;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        .card {
            max-width: 480px;
            width: 100%;
            text-align: center;
        }
        .icon {
            font-size: 56px;
            margin-bottom: 24px;
            display: block;
        }
        h1 {
            font-size: 28px;
            font-weight: 800;
            color: #fffffe;
            margin-bottom: 12px;
        }
        p {
            font-size: 16px;
            color: #a7a9be;
            line-height: 1.7;
            margin-bottom: 32px;
        }
        .badge {
            display: inline-block;
            padding: 6px 16px;
            background: #6246ea;
            border-radius: 99px;
            font-size: 12px;
            font-weight: 700;
            color: #fffffe;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
<div class="card">
    <span class="icon">🔧</span>
    <h1>Under Maintenance</h1>
    <p>{{ $message }}</p>
    <span class="badge">Back Soon</span>
</div>
</body>
</html>
