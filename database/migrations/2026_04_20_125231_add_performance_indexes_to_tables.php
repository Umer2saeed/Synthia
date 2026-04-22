<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |----------------------------------------------------------------------
        | posts table indexes
        |----------------------------------------------------------------------
        | These indexes make the most common queries instant.
        |
        | Without indexes: MySQL scans every row to find matches
        | With indexes:    MySQL jumps directly to matching rows
        */
        Schema::table('posts', function (Blueprint $table) {
            /*
            | status + published_at composite index
            | Used by: every published post query
            | Query: WHERE status='published' ORDER BY published_at DESC
            | Without this index: full table scan on every page load
            */
            if (!$this->indexExists('posts', 'posts_status_published_at_index')) {
                $table->index(['status', 'published_at'], 'posts_status_published_at_index');
            }

            /*
            | user_id index
            | Used by: author profile page, dashboard personal stats
            | Query: WHERE user_id = X
            */
            if (!$this->indexExists('posts', 'posts_user_id_index')) {
                $table->index('user_id', 'posts_user_id_index');
            }

            /*
            | category_id index
            | Used by: category page, related posts query
            | Query: WHERE category_id = X
            */
            if (!$this->indexExists('posts', 'posts_category_id_index')) {
                $table->index('category_id', 'posts_category_id_index');
            }

            /*
            | is_featured + status composite index
            | Used by: home page featured posts query
            | Query: WHERE is_featured = 1 AND status = 'published'
            */
            if (!$this->indexExists('posts', 'posts_featured_status_index')) {
                $table->index(['is_featured', 'status'], 'posts_featured_status_index');
            }
        });

        /*
        |----------------------------------------------------------------------
        | comments table indexes
        |----------------------------------------------------------------------
        */
        Schema::table('comments', function (Blueprint $table) {
            /*
            | post_id + is_approved composite index
            | Used by: approved comments on post page
            | Query: WHERE post_id = X AND is_approved = 1
            */
            if (!$this->indexExists('comments', 'comments_post_approved_index')) {
                $table->index(['post_id', 'is_approved'], 'comments_post_approved_index');
            }

            /*
            | user_id index
            | Used by: dashboard personal comment count
            | Query: WHERE user_id = X
            */
            if (!$this->indexExists('comments', 'comments_user_id_index')) {
                $table->index('user_id', 'comments_user_id_index');
            }
        });

        /*
        |----------------------------------------------------------------------
        | claps table indexes
        |----------------------------------------------------------------------
        */
        Schema::table('claps', function (Blueprint $table) {
            /*
            | post_id index
            | Used by: totalClaps() — SELECT SUM(count) WHERE post_id = X
            | Also used by withCount('claps')
            */
            if (!$this->indexExists('claps', 'claps_post_id_index')) {
                $table->index('post_id', 'claps_post_id_index');
            }

            /*
            | user_id + post_id composite index
            | Used by: userClaps() — WHERE user_id = X AND post_id = Y
            */
            if (!$this->indexExists('claps', 'claps_user_post_index')) {
                $table->index(['user_id', 'post_id'], 'claps_user_post_index');
            }
        });

        /*
        |----------------------------------------------------------------------
        | bookmarks table indexes
        |----------------------------------------------------------------------
        */
        Schema::table('bookmarks', function (Blueprint $table) {
            /*
            | user_id + post_id composite index
            | Used by: isBookmarkedBy() — WHERE user_id = X AND post_id = Y
            | Also used by withCount('bookmarks' filtered by user_id)
            */
            if (!$this->indexExists('bookmarks', 'bookmarks_user_post_index')) {
                $table->index(['user_id', 'post_id'], 'bookmarks_user_post_index');
            }
        });

        /*
        |----------------------------------------------------------------------
        | follows table indexes
        |----------------------------------------------------------------------
        */
        Schema::table('follows', function (Blueprint $table) {
            /*
            | follower_id index
            | Used by: following page — WHERE follower_id = X
            */
            if (!$this->indexExists('follows', 'follows_follower_id_index')) {
                $table->index('follower_id', 'follows_follower_id_index');
            }

            /*
            | following_id index
            | Used by: followers page — WHERE following_id = X
            | Also used by followers()->count()
            */
            if (!$this->indexExists('follows', 'follows_following_id_index')) {
                $table->index('following_id', 'follows_following_id_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_status_published_at_index');
            $table->dropIndex('posts_user_id_index');
            $table->dropIndex('posts_category_id_index');
            $table->dropIndex('posts_featured_status_index');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex('comments_post_approved_index');
            $table->dropIndex('comments_user_id_index');
        });

        Schema::table('claps', function (Blueprint $table) {
            $table->dropIndex('claps_post_id_index');
            $table->dropIndex('claps_user_post_index');
        });

        Schema::table('bookmarks', function (Blueprint $table) {
            $table->dropIndex('bookmarks_user_post_index');
        });

        Schema::table('follows', function (Blueprint $table) {
            $table->dropIndex('follows_follower_id_index');
            $table->dropIndex('follows_following_id_index');
        });
    }

    /*
    | Helper to check if an index already exists.
    | Prevents errors if the index was already created by an earlier migration.
    */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = \Illuminate\Support\Facades\DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        );

        return count($indexes) > 0;
    }
};
