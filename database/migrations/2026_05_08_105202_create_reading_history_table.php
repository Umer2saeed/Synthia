<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reading_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();

            /*
            | read_at: when the user last visited this post.
            | We UPDATE this on repeat visits rather than inserting new rows.
            | One row per user per post — always the most recent visit time.
            */
            $table->timestamp('read_at');

            $table->unique(['user_id', 'post_id'], 'reading_history_user_post_unique');
            $table->index(['user_id', 'read_at'], 'reading_history_user_time_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_history');
    }
};
