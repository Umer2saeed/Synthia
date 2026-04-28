<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /*
    | FULLTEXT INDEX is MySQL-only.
    | SQLite (used in testing with :memory:) does not support it.
    | We guard every statement with a driver check.
    */

    public function up(): void
    {
        DB::statement(
            'ALTER TABLE posts ADD FULLTEXT INDEX posts_fulltext_index (title, content)'
        );
    }

    public function down(): void
    {
        DB::statement(
            'ALTER TABLE posts DROP INDEX posts_fulltext_index'
        );
    }
};
