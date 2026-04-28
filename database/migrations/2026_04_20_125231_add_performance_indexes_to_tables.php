<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    | These indexes use MySQL-specific syntax for checking existence.
    | SQLite does not support SHOW INDEX or the same index types.
    | We skip this migration entirely on SQLite.
    */

    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (!$this->indexExists('posts', 'posts_status_published_at_index')) {
                $table->index(['status', 'published_at'], 'posts_status_published_at_index');
            }
            if (!$this->indexExists('posts', 'posts_user_id_index')) {
                $table->index('user_id', 'posts_user_id_index');
            }
            if (!$this->indexExists('posts', 'posts_category_id_index')) {
                $table->index('category_id', 'posts_category_id_index');
            }
            if (!$this->indexExists('posts', 'posts_featured_status_index')) {
                $table->index(['is_featured', 'status'], 'posts_featured_status_index');
            }
        });

        Schema::table('comments', function (Blueprint $table) {
            if (!$this->indexExists('comments', 'comments_post_approved_index')) {
                $table->index(['post_id', 'is_approved'], 'comments_post_approved_index');
            }
            if (!$this->indexExists('comments', 'comments_user_id_index')) {
                $table->index('user_id', 'comments_user_id_index');
            }
        });

        Schema::table('claps', function (Blueprint $table) {
            if (!$this->indexExists('claps', 'claps_post_id_index')) {
                $table->index('post_id', 'claps_post_id_index');
            }
            if (!$this->indexExists('claps', 'claps_user_post_index')) {
                $table->index(['user_id', 'post_id'], 'claps_user_post_index');
            }
        });

        Schema::table('bookmarks', function (Blueprint $table) {
            if (!$this->indexExists('bookmarks', 'bookmarks_user_post_index')) {
                $table->index(['user_id', 'post_id'], 'bookmarks_user_post_index');
            }
        });

        Schema::table('follows', function (Blueprint $table) {
            if (!$this->indexExists('follows', 'follows_follower_id_index')) {
                $table->index('follower_id', 'follows_follower_id_index');
            }
            if (!$this->indexExists('follows', 'follows_following_id_index')) {
                $table->index('following_id', 'follows_following_id_index');
            }
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

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

    private function indexExists(string $table, string $indexName): bool
    {
        /*
        | This method uses SHOW INDEX which is MySQL-only.
        | It is only called when driver is already confirmed as mysql above,
        | so this is safe.
        */
        $indexes = DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        );

        return count($indexes) > 0;
    }
};
