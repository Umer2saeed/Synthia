<?php

namespace App\Services;

use Illuminate\Support\Str;

class SanitizationService
{
    /*
    |--------------------------------------------------------------------------
    | cleanText() — Strip ALL HTML from plain text fields
    |--------------------------------------------------------------------------
    | Used for: names, bios, category names, tag names, search queries.
    | Never returns null — always returns a string (possibly empty).
    */
    public function cleanText(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        /*
        |----------------------------------------------------------------------
        | Step 1: Remove dangerous tags AND their content entirely.
        |----------------------------------------------------------------------
        | strip_tags() removes <script> but keeps "alert(1)" text inside.
        | We must remove script/style content BEFORE calling strip_tags().
        |
        | Regex explanation:
        |   <script      → opening tag start
        |   \b[^>]*>     → any attributes, then >
        |   .*?          → any content (lazy match)
        |   <\/script>   → closing tag
        |   /is          → i=case insensitive, s=dot matches newlines
        */
        $clean = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $value);
        $clean = preg_replace('/<style\b[^>]*>.*?<\/style>/is',   '', $clean);

        /*
        |----------------------------------------------------------------------
        | Step 2: Decode HTML entities before stripping tags.
        |----------------------------------------------------------------------
        | An attacker might encode < as &lt; to bypass strip_tags.
        | Decoding first catches encoded injection attempts.
        */
        $clean = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        /*
        |----------------------------------------------------------------------
        | Step 3: Remove all remaining HTML tags.
        |----------------------------------------------------------------------
        | Now strip_tags is safe — dangerous script content is already gone.
        */
        $clean = strip_tags($clean);

        /*
        |----------------------------------------------------------------------
        | Step 4: Normalize whitespace.
        |----------------------------------------------------------------------
        */
        $clean = Str::squish($clean);

        return $clean;
    }

    /*
    |--------------------------------------------------------------------------
    | cleanRichText() — Allow safe HTML for post content
    |--------------------------------------------------------------------------
    | Used for: post body content, post summary.
    | Falls back to strip_tags() if HTMLPurifier is unavailable.
    */
    public function cleanRichText(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return '';
        }

        /*
        | Try HTMLPurifier first — it is the correct tool for rich text.
        | If it throws any exception (misconfiguration, missing cache dir, etc.)
        | we fall back to a manual approach that is still safe.
        */
        try {
            if (function_exists('clean')) {
                $cleaned = clean($value, 'post_content');

                /*
                | HTMLPurifier sometimes returns empty string for valid content.
                | If the result is empty but input was not, fall back to strip_tags.
                | This prevents the "content cannot be empty" validation failure.
                */
                if (trim($cleaned) === '' && trim($value) !== '') {
                    return $this->fallbackRichTextClean($value);
                }

                return $cleaned;
            }
        } catch (\Throwable $e) {
            \Log::warning('SanitizationService: HTMLPurifier failed, using fallback', [
                'error' => $e->getMessage(),
            ]);
        }

        return $this->fallbackRichTextClean($value);
    }

    /*
    |--------------------------------------------------------------------------
    | fallbackRichTextClean() — Safe fallback when HTMLPurifier unavailable
    |--------------------------------------------------------------------------
    | Manually strips dangerous tags while keeping safe formatting tags.
    | Not as thorough as HTMLPurifier but safe for basic protection.
    */
    private function fallbackRichTextClean(string $value): string
    {
        /*
        | Step 1: Remove script tags and their content entirely.
        | The 's' flag makes . match newlines too (multiline scripts).
        */
        $clean = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $value);

        /*
        | Step 2: Remove iframe, object, embed tags entirely.
        */
        $clean = preg_replace('/<(iframe|object|embed|applet)\b[^>]*>.*?<\/\1>/is', '', $clean);

        /*
        | Step 3: Remove dangerous event attributes (onclick, onload, etc.)
        | These can execute JavaScript even without script tags.
        */
        $clean = preg_replace('/\bon\w+\s*=\s*["\'][^"\']*["\']/i', '', $clean);
        $clean = preg_replace('/\bon\w+\s*=\s*[^\s>]*/i', '', $clean);

        /*
        | Step 4: Remove javascript: and data: from href and src attributes.
        */
        $clean = preg_replace('/\b(href|src|action)\s*=\s*["\']?\s*(javascript|data|vbscript):[^"\'>\s]*/i', '', $clean);

        /*
        | Step 5: Strip all tags EXCEPT the safe ones using strip_tags
        | with an allowed tags list.
        */
        $allowedTags = '<p><br><strong><b><em><i><u><s><strike>'
            . '<h2><h3><h4><blockquote><pre><code>'
            . '<ul><ol><li><a><img><table><thead><tbody><tr><th><td>'
            . '<hr><span><div>';

        $clean = strip_tags($clean, $allowedTags);

        return $clean;
    }

    /*
    |--------------------------------------------------------------------------
    | cleanComment() — Minimal HTML for comments
    |--------------------------------------------------------------------------
    */
    public function cleanComment(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return '';
        }

        try {
            if (function_exists('clean')) {
                $cleaned = clean($value, 'comment');

                if (trim($cleaned) === '' && trim($value) !== '') {
                    return $this->fallbackCommentClean($value);
                }

                return $cleaned;
            }
        } catch (\Throwable $e) {
            \Log::warning('SanitizationService: comment purifier failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return $this->fallbackCommentClean($value);
    }

    private function fallbackCommentClean(string $value): string
    {
        $clean = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $value);
        $clean = preg_replace('/\bon\w+\s*=\s*["\'][^"\']*["\']/i', '', $clean);
        $clean = strip_tags($clean, '<b><strong><em><i><a><code>');

        return trim($clean);
    }

    /*
    |--------------------------------------------------------------------------
    | cleanSearch() — Sanitize search query
    |--------------------------------------------------------------------------
    */
    public function cleanSearch(?string $value, int $maxLength = 100): string
    {
        if ($value === null) {
            return '';
        }

        /*
        | Apply the same script removal as cleanText before stripping tags.
        | Search queries are reflected in page output so XSS is possible
        | if script content is not removed before tags are stripped.
        */
        $clean = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $value);
        $clean = preg_replace('/<style\b[^>]*>.*?<\/style>/is',   '', $clean);
        $clean = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $clean = strip_tags($clean);
        $clean = Str::squish($clean);

        return Str::limit($clean, $maxLength, '');
    }

    /*
    |--------------------------------------------------------------------------
    | cleanUsername() — Letters, numbers, hyphens, underscores only
    |--------------------------------------------------------------------------
    */
    public function cleanUsername(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $clean = strip_tags(trim($value));
        $clean = preg_replace('/[^a-zA-Z0-9_-]/', '', $clean);

        return strtolower($clean);
    }

    /*
    |--------------------------------------------------------------------------
    | cleanUrl() — Block javascript: and data: URIs
    |--------------------------------------------------------------------------
    */
    public function cleanUrl(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return '';
        }

        $clean = trim($value);

        if (preg_match('/^(javascript|data|vbscript):/i', $clean)) {
            return '';
        }

        if (filter_var($clean, FILTER_VALIDATE_URL)) {
            return $clean;
        }

        return '';
    }
}
