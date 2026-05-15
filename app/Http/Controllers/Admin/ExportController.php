<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ExportPostsCsvJob;
use App\Models\Category;
use App\Models\Clap;
use App\Models\Post;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExportController extends Controller
{
    const LARGE_EXPORT_THRESHOLD = 500;

    public function index()
    {
        $authors    = User::has('posts')->orderBy('name')->get(['id', 'name']);
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('admin.export.index', compact('authors', 'categories'));
    }

    /*
    | Export posts as CSV — streamed directly or queued for large sets.
    */
    public function exportCsv(Request $request)
    {
        $request->validate([
            'status'      => ['nullable', 'in:published,draft,scheduled'],
            'author_id'   => ['nullable', 'integer', 'exists:users,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'date_from'   => ['nullable', 'date'],
            'date_to'     => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $query = $this->buildQuery($request);
        $count = $query->count();

        /*
        | Large exports are dispatched to a queue job.
        | The job builds the CSV and emails the admin a download link.
        */
        if ($count > self::LARGE_EXPORT_THRESHOLD) {
            ExportPostsCsvJob::dispatch(
                auth()->id(),
                $request->only(['status', 'author_id', 'category_id', 'date_from', 'date_to'])
            );

            return back()->with(
                'success',
                "Export contains {$count} posts. It will be emailed to " .
                auth()->user()->email . " when ready."
            );
        }

        return $this->streamCsv($query, $request);
    }

    /*
    | Export a single post as PDF.
    */
    public function exportPdf(Post $post)
    {
        $post->load(['user', 'category', 'tags']);

        $pdf = Pdf::loadView('admin.export.post-pdf', compact('post'))
            ->setPaper('a4', 'portrait');

        $filename = Str::slug($post->title) . '-' . $post->id . '.pdf';

        return $pdf->download($filename);
    }

    /*
    | Stream CSV response — avoids loading all rows into memory at once.
    */

    private function streamCsv($query, Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'posts-export-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($query) {

            $handle = fopen('php://output', 'w');

            /*
            | BOM (Byte Order Mark) for UTF-8.
            | Without this, Excel on Windows treats the file as ANSI
            | and shows garbled characters or refuses to open it.
            | Excel reads the BOM and correctly identifies the encoding.
            */
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'ID',
                'Title',
                'Slug',
                'Status',
                'Author',
                'Category',
                'Tags',
                'Views',
                'Claps',
                'Comments',
                'Published At',
                'Created At',
                'URL',
            ]);

            $query->with(['user', 'category', 'tags'])
                ->withCount('comments')
                ->chunk(200, function ($posts) use ($handle) {
                    foreach ($posts as $post) {
                        $claps = Clap::where('post_id', $post->id)->sum('count');

                        fputcsv($handle, [
                            $post->id,
                            $post->title,
                            $post->slug,
                            $post->status,
                            $post->user->name ?? '—',
                            $post->category->name ?? '—',
                            $post->tags->pluck('name')->implode(', '),
                            $post->views ?? 0,
                            (int) $claps,
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

        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }
    private function buildQuery(Request $request)
    {
        $query = Post::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('author_id')) {
            $query->where('user_id', $request->author_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query->orderByDesc('created_at');
    }

    /*
| Download a previously exported file (from queued job).
| File is stored in storage/local/exports/ — not publicly accessible.
*/
    public function download(Request $request)
    {
        $file = basename($request->query('file')); // sanitize — basename strips path traversal
        $path = 'exports/' . $file;

        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'Export file not found or has expired.');
        }

        return Storage::disk('local')->download($path, $file, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
