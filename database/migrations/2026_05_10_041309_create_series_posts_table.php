<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('series_posts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('series_id')
                ->constrained('series')
                ->cascadeOnDelete();

            $table->foreignId('post_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            | order: the position of this post within the series.
            | 1 = first, 2 = second, etc.
            | Authors set this in the post editor sidebar.
            */
            $table->unsignedSmallInteger('order')->default(1);

            /*
            | One post can only appear once per series.
            | A post CAN belong to multiple series (no restriction).
            */
            $table->unique(['series_id', 'post_id'], 'series_posts_unique');
            $table->index(['series_id', 'order'], 'series_posts_order_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('series_posts');
    }
};
