<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ExportPostsCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private int   $adminUserId,
        private array $filters
    ) {}

    public function handle(): void
    {
        $filename = 'exports/posts-' . now()->format('Y-m-d-His') . '-' . $this->adminUserId . '.csv';
        $fullPath = Storage::disk('local')->path($filename);

        Storage::disk('local')->makeDirectory('exports');

        $handle = fopen($fullPath, 'w');

        fputcsv($handle, [
            'ID', 'Title', 'Slug', 'Status', 'Author', 'Category',
            'Tags', 'Views', 'Claps', 'Comments', 'Published At', 'Created At', 'URL',
        ]);

        $query = Post::query();

        if (!empty($this->filters['status']))      $query->where('status', $this->filters['status']);
        if (!empty($this->filters['author_id']))   $query->where('user_id', $this->filters['author_id']);
        if (!empty($this->filters['category_id'])) $query->where('category_id', $this->filters['category_id']);
        if (!empty($this->filters['date_from']))   $query->whereDate('created_at', '>=', $this->filters['date_from']);
        if (!empty($this->filters['date_to']))     $query->whereDate('created_at', '<=', $this->filters['date_to']);

        $query->with(['user', 'category', 'tags'])
            ->withCount('comments')
            ->orderByDesc('created_at')
            ->chunk(200, function ($posts) use ($handle) {
                foreach ($posts as $post) {
                    $claps = \App\Models\Clap::where('post_id', $post->id)->sum('count');

                    fputcsv($handle, [
                        $post->id,
                        $post->title,
                        $post->slug,
                        $post->status,
                        $post->user->name ?? '—',
                        $post->category->name ?? '—',
                        $post->tags->pluck('name')->implode(', '),
                        $post->views ?? 0,
                        $claps,
                        $post->comments_count,
                        /*
                        | Format: "14 May 2026" — short, human readable,
                        | no ambiguity, fits in a narrow column.
                        | Excel does not interpret it as a numeric date so
                        | it displays as plain text — never shows ######.
                        */
                        $post->published_at
                            ? $post->published_at->format('d M Y')
                            : 'Not published',
                        $post->created_at->format('d M Y'),
                        route('blog.post', $post->slug),
                    ]);
                }
            });

        fclose($handle);

        // Email the admin a download link
        $admin    = User::find($this->adminUserId);
        $download = route('admin.export.download', ['file' => basename($filename)]);

        Mail::to($admin->email)->send(
            new \App\Mail\ExportReadyMail($admin->name, $download)
        );
    }
}
