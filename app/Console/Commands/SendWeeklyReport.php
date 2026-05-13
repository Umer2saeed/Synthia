<?php

namespace App\Console\Commands;

use App\Mail\WeeklyReportMail;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SendWeeklyReport extends Command
{
    protected $signature   = 'report:weekly {--force : Send even if disabled in settings}';
    protected $description = 'Send the weekly platform report to admin';

    public function handle(): int
    {
        /*
        | Check if weekly reports are enabled.
        | We store the toggle in cache for now (D6 settings will replace this).
        | Default is enabled.
        */
        $enabled = Cache::get('setting.weekly_report_enabled', true);

        if (!$enabled && !$this->option('force')) {
            $this->info('Weekly report is disabled. Use --force to send anyway.');
            return Command::SUCCESS;
        }

        $stats = $this->buildStats();

        $adminEmail = $this->resolveAdminEmail();

        if (!$adminEmail) {
            $this->error('No admin email found. Cannot send report.');
            return Command::FAILURE;
        }

        Mail::to($adminEmail)->send(new WeeklyReportMail($stats));

        $this->info("Weekly report sent to {$adminEmail}.");

        return Command::SUCCESS;
    }

    private function buildStats(): array
    {
        $since = now()->subWeek();

        $newPosts = Post::published()
            ->where('published_at', '>=', $since)
            ->count();

        $newUsers = User::where('created_at', '>=', $since)->count();

        $totalViews = Post::published()->sum('views');

        $pendingComments = Comment::pending()->count();

        $topPost = Post::published()
            ->where('published_at', '>=', $since)
            ->orderByDesc('views')
            ->with('user')
            ->first();

        return [
            'new_posts'        => $newPosts,
            'new_users'        => $newUsers,
            'total_views'      => $totalViews,
            'pending_comments' => $pendingComments,
            'top_post'         => $topPost ? [
                'title'        => $topPost->title,
                'views'        => $topPost->views,
                'author'       => $topPost->user->name ?? '—',
                'published_at' => $topPost->published_at->format('d M Y'),
                'url'          => route('blog.post', $topPost->slug),
            ] : null,
        ];
    }

    private function resolveAdminEmail(): ?string
    {
        /*
        | Priority:
        | 1. MAIL_ADMIN_ADDRESS env variable (explicit config)
        | 2. First admin role user in the database
        */
        $envEmail = config('mail.admin_address');
        if ($envEmail) return $envEmail;

        $admin = User::role('admin')->orderBy('id')->first();
        return $admin?->email;
    }
}
