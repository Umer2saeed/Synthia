<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Typography\FontFactory;

class OgImageService
{
    /*
    | Standard Open Graph image dimensions used by all major platforms.
    | Twitter, LinkedIn, Facebook all expect 1200×630.
    */
    const WIDTH  = 1200;
    const HEIGHT = 630;

    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /*
    | getUrl() — Return the OG image URL for a post.
    | Generates the image if it does not exist yet.
    */
    public function getUrl(Post $post): string
    {
        $path = $this->storagePath($post);

        if (!Storage::disk('public')->exists($path)) {
            $this->generate($post);
        }

        return asset('storage/' . $path);
    }

    /*
    | generate() — Build and save the OG image for a post.
    | Called by getUrl() lazily, or by the observer on post update.
    */
    public function generate(Post $post): void
    {
        $canvas = $this->manager->create(self::WIDTH, self::HEIGHT);

        // Fill with a dark indigo gradient background using rectangle layers
        $canvas->fill('#1e1b4b');

        // Top-left accent block
        $canvas->drawRectangle(0, 0, function ($rect) {
            $rect->size(400, 8);
            $rect->background('#6366f1');
        });

        // Bottom-right accent block
        $canvas->drawRectangle(800, self::HEIGHT - 8, function ($rect) {
            $rect->size(400, 8);
            $rect->background('#818cf8');
        });

        // Subtle right-side vertical bar
        $canvas->drawRectangle(self::WIDTH - 8, 0, function ($rect) {
            $rect->size(8, self::HEIGHT);
            $rect->background('#4f46e5');
        });

        /*
        | Write post title — word-wrapped to fit the canvas width.
        | We use the built-in GD font (size 5) as the fallback
        | because custom fonts require a TTF file path.
        | We generate the title text as wrapped lines manually.
        */
        $title        = $this->cleanText($post->title);
        $titleLines   = $this->wrapText($title, 42);
        $titleY       = 180;
        $lineHeight   = 72;

        foreach ($titleLines as $index => $line) {
            if ($index >= 4) break; // max 4 lines

            $canvas->text($line, 80, $titleY + ($index * $lineHeight), function (FontFactory $font) {
                $font->color('#ffffff');
                $font->size(52);
                $font->align('left');
                $font->valign('top');
            });
        }

        // Author name
        $authorName = 'by ' . $this->cleanText($post->user->name ?? 'Synthia Author');
        $authorY    = $titleY + (min(count($titleLines), 4) * $lineHeight) + 30;

        $canvas->text($authorName, 80, $authorY, function (FontFactory $font) {
            $font->color('#a5b4fc');
            $font->size(28);
            $font->align('left');
            $font->valign('top');
        });

        // Synthia branding — bottom left
        $canvas->text('Synthia', 80, self::HEIGHT - 60, function (FontFactory $font) {
            $font->color('#6366f1');
            $font->size(32);
            $font->align('left');
            $font->valign('top');
        });

        // Category label — top area
        if ($post->category) {
            $canvas->text(
                strtoupper($post->category->name),
                80, 120,
                function (FontFactory $font) {
                    $font->color('#818cf8');
                    $font->size(22);
                    $font->align('left');
                    $font->valign('top');
                }
            );
        }

        // Ensure the directory exists
        Storage::disk('public')->makeDirectory('og-images');

        // Save to storage
        $fullPath = Storage::disk('public')->path($this->storagePath($post));
        $canvas->toPng()->save($fullPath);
    }

    /*
    | clear() — Delete the cached OG image for a post.
    | Called by PostObserver when post is updated.
    */
    public function clear(Post $post): void
    {
        $path = $this->storagePath($post);

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function storagePath(Post $post): string
    {
        return 'og-images/' . $post->id . '.png';
    }

    /*
    | Word-wrap text to a maximum character width per line.
    */
    private function wrapText(string $text, int $maxChars): array
    {
        $words   = explode(' ', $text);
        $lines   = [];
        $current = '';

        foreach ($words as $word) {
            $test = $current === '' ? $word : $current . ' ' . $word;

            if (mb_strlen($test) <= $maxChars) {
                $current = $test;
            } else {
                if ($current !== '') {
                    $lines[] = $current;
                }
                $current = $word;
            }
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines;
    }

    /*
    | Strip HTML tags and normalize whitespace.
    */
    private function cleanText(string $text): string
    {
        return trim(preg_replace('/\s+/', ' ', strip_tags($text)));
    }
}
