<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | up() — Add the full text index
    |--------------------------------------------------------------------------
    |
    | WHY a full text index on both title AND content?
    | We want to search across both columns simultaneously.
    | A single MATCH() call can search multiple columns at once
    | but only if they share a single combined full text index.
    |
    | We CANNOT do:
    |   MATCH(title) AGAINST('laravel') OR MATCH(content) AGAINST('laravel')
    | MySQL requires both columns to be in the same FULLTEXT index
    | for a combined MATCH() call.
    |
    | WHY NOT use Schema::table() with ->fullText()?
    | Laravel's Blueprint::fullText() method was added in Laravel 9
    | but has inconsistencies with combined multi-column indexes
    | on some MySQL versions. Using raw DB::statement() is safer
    | and gives us direct control over the index definition.
    */
    public function up(): void
    {
        DB::statement(
            'ALTER TABLE posts ADD FULLTEXT INDEX posts_fulltext_index (title, content)'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | down() — Remove the full text index
    |--------------------------------------------------------------------------
    | If we ever roll back this migration, we drop the index.
    | This leaves the posts table exactly as it was before.
    */
    public function down(): void
    {
        DB::statement(
            'ALTER TABLE posts DROP INDEX posts_fulltext_index'
        );
    }
};
