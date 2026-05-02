<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            /*
            | views: unsigned integer starting at 0.
            | unsigned = no negative values (views cannot go below 0).
            | default 0 = all existing posts start at zero views.
            | We add an index because we will ORDER BY views in trending
            | and analytics queries — the index makes those fast.
            */
            $table->unsignedBigInteger('views')->default(0)->after('content');
            $table->index('views', 'posts_views_index');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_views_index');
            $table->dropColumn('views');
        });
    }
};
