<?php

namespace App\Support;

class CacheKeys
{
    /*
    |--------------------------------------------------------------------------
    | Static cache key strings
    |--------------------------------------------------------------------------
    | Used as: Cache::remember(CacheKeys::SIDEBAR_CATEGORIES, ...)
    | Instead of: Cache::remember('sidebar_categories', ...)
    |
    | Why constants? If you type 'sidebar-categories' in one place
    | and 'sidebar_categories' in another, clearing one does not
    | clear the other. Constants prevent this entire class of bug.
    */
    const SIDEBAR_CATEGORIES  = 'sidebar_categories';
    const SIDEBAR_TAGS        = 'sidebar_tags';
    const HOME_FEATURED_POSTS = 'home_featured_posts';
    const HOME_LATEST_POSTS   = 'home_latest_posts';
    const DASHBOARD_STATS     = 'dashboard_stats';

    /*
    |--------------------------------------------------------------------------
    | Dynamic key generators
    |--------------------------------------------------------------------------
    | These are methods instead of constants because they include
    | variable data like a post ID or page number.
    | Usage: CacheKeys::post(5) → "post_5"
    */

    // Cache key for a single post fetched by its slug
    public static function postBySlug(string $slug): string
    {
        return "post_slug_{$slug}";
    }

    // Cache key for related posts of a specific post
    public static function postRelated(int $postId): string
    {
        return "post_{$postId}_related";
    }

    // Cache key for a paginated blog listing page
    public static function blogPage(int $page): string
    {
        return "blog_listing_page_{$page}";
    }
}
