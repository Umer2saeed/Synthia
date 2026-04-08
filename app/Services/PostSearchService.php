<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostSearchService
{
    /*
    |--------------------------------------------------------------------------
    | MINIMUM_WORD_LENGTH
    |--------------------------------------------------------------------------
    | MySQL's full text search ignores words shorter than this length.
    | The default MySQL setting is 4 characters (ft_min_word_len = 4).
    |
    | This means searching "PHP" (3 chars) would return no results
    | from full text search. For short words we fall back to LIKE.
    |
    | We set this to 3 to match common technical terms like "API", "PHP".
    | Note: this requires MySQL ft_min_word_len to be set to 3 as well.
    | If you cannot change MySQL config, keep this at 4.
    */
    const MINIMUM_WORD_LENGTH = 3;

    /*
    |--------------------------------------------------------------------------
    | search() — Main search method
    |--------------------------------------------------------------------------
    |
    | Decides which search strategy to use based on the query:
    |   1. Empty query       → return latest posts (no search)
    |   2. Short query       → LIKE search (fallback for 1-2 char queries)
    |   3. Normal query      → Full Text search with relevance ranking
    |
    | @param string $query     The search term from the user
    | @param int    $perPage   Results per page (default 12)
    | @param array  $filters   Additional filters (category slug, etc.)
    |
    | @return LengthAwarePaginator
    */
    public function search(
        ?string $query   = null,    // ← Accept null with ? prefix
        int     $perPage = 12,
        array   $filters = []
    ): LengthAwarePaginator {

        /*
        |----------------------------------------------------------------------
        | Normalize the query
        |----------------------------------------------------------------------
        | Convert null to empty string so all downstream code
        | works with a guaranteed string type.
        | ?? '' means: if $query is null, use '' instead.
        */
        $cleanQuery = trim(strip_tags($query ?? ''));

        // Empty query — return latest posts
        if (empty($cleanQuery)) {
            return $this->getLatestPosts($perPage, $filters);
        }

        // Very short query — fall back to LIKE
        if (mb_strlen($cleanQuery) < self::MINIMUM_WORD_LENGTH) {
            return $this->likeSearch($cleanQuery, $perPage, $filters);
        }

        // Normal query — full text search
        return $this->fullTextSearch($cleanQuery, $perPage, $filters);
    }

    /*
    |--------------------------------------------------------------------------
    | fullTextSearch() — The main full text search implementation
    |--------------------------------------------------------------------------
    |
    | SQL GENERATED:
    |   SELECT posts.*,
    |     MATCH(title, content) AGAINST('laravel tips' IN NATURAL LANGUAGE MODE)
    |     AS relevance_score
    |   FROM posts
    |   WHERE posts.deleted_at IS NULL
    |   AND posts.status = 'published'
    |   AND MATCH(title, content) AGAINST('laravel tips' IN NATURAL LANGUAGE MODE)
    |   ORDER BY relevance_score DESC
    |
    | WHY selectRaw for relevance_score?
    | We add the MATCH...AGAINST expression as a SELECT column so we can
    | ORDER BY it without running the MATCH calculation twice.
    | If we put MATCH...AGAINST only in WHERE and ORDER BY separately,
    | MySQL runs the full text search twice — double the work.
    | Adding it as a named column (relevance_score) means MySQL
    | calculates it once and reuses it for ordering.
    |
    | WHY IN NATURAL LANGUAGE MODE?
    | Natural Language Mode understands that "laravel tips" means
    | find posts about laravel AND tips, not the exact phrase.
    | It also assigns relevance scores automatically.
    */
    private function fullTextSearch(
        string $query,
        int    $perPage,
        array  $filters
    ): LengthAwarePaginator {

        $baseQuery = Post::with(['user', 'category', 'tags'])
            ->published()
            /*
            | selectRaw adds a computed column to the SELECT statement.
            | 'posts.*' means include all regular post columns.
            | The MATCH...AGAINST expression calculates the relevance score.
            |
            | We use ? as a parameter placeholder for the search query.
            | This is a prepared statement — protects against SQL injection.
            | The second argument [$query] provides the value for ?.
            */
            ->selectRaw(
                'posts.*, MATCH(title, content) AGAINST(? IN NATURAL LANGUAGE MODE) AS relevance_score',
                [$query]
            )
            /*
            | The WHERE clause filters to only matching posts.
            | Posts where neither title nor content contain the search
            | words get a score of 0 and are excluded by this condition.
            |
            | WHY > 0 instead of just having MATCH in WHERE?
            | MySQL only returns rows where MATCH score > 0 by default
            | in Natural Language Mode. The > 0 makes this explicit
            | and readable.
            */
            ->whereRaw(
                'MATCH(title, content) AGAINST(? IN NATURAL LANGUAGE MODE)',
                [$query]
            )
            /*
            | Order by relevance score descending.
            | Most relevant posts appear first.
            | Posts mentioning the search term many times rank higher
            | than posts mentioning it once.
            */
            ->orderByDesc('relevance_score');

        // Apply additional filters (category, etc.)
        $baseQuery = $this->applyFilters($baseQuery, $filters);

        return $baseQuery->paginate($perPage)->withQueryString();
    }

    /*
    |--------------------------------------------------------------------------
    | likeSearch() — Fallback for very short queries
    |--------------------------------------------------------------------------
    | Used when query is less than MINIMUM_WORD_LENGTH characters.
    | Full text search does not index very short words so we
    | fall back to LIKE for these cases.
    |
    | This is the same search you had before — kept as a fallback only.
    */
    private function likeSearch(
        string $query,
        int    $perPage,
        array  $filters
    ): LengthAwarePaginator {

        $baseQuery = Post::with(['user', 'category', 'tags'])
            ->published()
            ->where(function ($q) use ($query) {
                $q->where('title',   'like', '%' . $query . '%')
                    ->orWhere('content', 'like', '%' . $query . '%');
            })
            ->latest('published_at');

        $baseQuery = $this->applyFilters($baseQuery, $filters);

        return $baseQuery->paginate($perPage)->withQueryString();
    }

    /*
    |--------------------------------------------------------------------------
    | getLatestPosts() — Return latest posts when no query given
    |--------------------------------------------------------------------------
    */
    private function getLatestPosts(
        int   $perPage,
        array $filters
    ): LengthAwarePaginator {

        $baseQuery = Post::with(['user', 'category', 'tags'])
            ->published()
            ->latest('published_at');

        $baseQuery = $this->applyFilters($baseQuery, $filters);

        return $baseQuery->paginate($perPage)->withQueryString();
    }

    /*
    |--------------------------------------------------------------------------
    | applyFilters() — Apply additional query filters
    |--------------------------------------------------------------------------
    | Accepts the query builder and an array of filter options.
    | Currently supports:
    |   'category' → filter by category slug
    |
    | We keep this separate so it can be reused by all three search methods.
    */
    private function applyFilters($query, array $filters)
    {
        // Filter by category slug if provided
        if (!empty($filters['category'])) {
            $query->whereHas('category', function ($q) use ($filters) {
                $q->where('slug', $filters['category']);
            });
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | highlightTerms() — Wrap search terms in <mark> tags for display
    |--------------------------------------------------------------------------
    | This is a helper for the view — it takes a text excerpt and
    | wraps any occurrence of the search terms in <mark> tags.
    |
    | Example:
    |   Input:  "Learn laravel deployment today"
    |   Query:  "laravel deployment"
    |   Output: "Learn <mark>laravel</mark> <mark>deployment</mark> today"
    |
    | The view can then style <mark> to highlight matched words.
    |
    | @param string $text  The text to search within
    | @param string $query The search query to highlight
    | @return string       HTML with highlighted terms
    */
    public function highlightTerms(string $text, string $query): string
    {
        if (empty($query)) {
            return $text;
        }

        /*
        | Split the query into individual words.
        | preg_split splits on one or more whitespace characters.
        | array_filter removes any empty strings.
        */
        $words = array_filter(preg_split('/\s+/', trim($query)));

        foreach ($words as $word) {
            /*
            | preg_replace with:
            |   /word/i  → case-insensitive match
            |   <mark>   → standard HTML highlight element
            |
            | preg_quote() escapes any special regex characters
            | in the word so user input like "c++" is safe.
            */
            $text = preg_replace(
                '/' . preg_quote($word, '/') . '/i',
                '<mark class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 px-0.5 rounded">$0</mark>',
                $text
            );
        }

        return $text;
    }

    /*
    |--------------------------------------------------------------------------
    | getExcerpt() — Extract a relevant excerpt from post content
    |--------------------------------------------------------------------------
    | Instead of showing the first 120 characters of every post,
    | this method finds WHERE in the content the search term appears
    | and extracts text around that location.
    |
    | Example with query "deployment":
    |   BAD:  "Laravel is a PHP framework for building..."
    |         (search term "deployment" not visible in excerpt)
    |
    |   GOOD: "...this guide covers laravel deployment on Digital..."
    |         (search term visible in context)
    |
    | @param string $content The full post content
    | @param string $query   The search query
    | @param int    $length  Length of excerpt (default 150 chars)
    */
    public function getExcerpt(string $content, string $query, int $length = 150): string
    {
        // Strip HTML tags from content
        $plainText = strip_tags($content);

        if (empty($query)) {
            return Str::limit($plainText, $length);
        }

        // Find the position of the first search word in the content
        $firstWord = explode(' ', trim($query))[0];
        $position  = stripos($plainText, $firstWord);

        if ($position === false) {
            // Word not found — return the beginning of the content
            return Str::limit($plainText, $length);
        }

        /*
        | Calculate start position — go back half the excerpt length
        | so the search term appears in the middle of the excerpt,
        | not at the very beginning.
        */
        $start = max(0, $position - ($length / 2));

        // Extract the excerpt
        $excerpt = substr($plainText, $start, $length);

        // Add ellipsis if we did not start from the beginning
        if ($start > 0) {
            $excerpt = '...' . $excerpt;
        }

        // Add ellipsis if there is more content after
        if (($start + $length) < strlen($plainText)) {
            $excerpt .= '...';
        }

        return $excerpt;
    }
}
