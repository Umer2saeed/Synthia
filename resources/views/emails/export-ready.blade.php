<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body    { font-family: -apple-system, sans-serif; background: #f9fafb; color: #111; margin: 0; padding: 40px 20px; }
        .wrap   { max-width: 480px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 36px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        h2      { color: #4f46e5; margin: 0 0 8px; font-size: 18px; }
        p       { color: #6b7280; font-size: 14px; line-height: 1.6; }
        .btn    { display: inline-block; margin-top: 20px; padding: 12px 24px; background: #4f46e5; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px; }
        .note   { font-size: 12px; color: #9ca3af; margin-top: 16px; }
    </style>
</head>
<body>
<div class="wrap">
    <h2>Your Export Is Ready</h2>
    <p>Hi {{ $adminName }}, your posts export has finished processing and is ready to download.</p>
    <a href="{{ $downloadUrl }}" class="btn">Download CSV</a>
    <p class="note">This link will expire in 24 hours. After that, run a new export.</p>
</div>
</body>
</html>
