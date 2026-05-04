<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reactions', function (Blueprint $table) {
            $table->id();

            /*
            | user_id: who reacted
            | post_id: which post they reacted to
            | type:    which reaction (like, insightful, love, funny)
            |
            | The unique constraint on [user_id, post_id] ensures
            | one reaction per user per post at the database level.
            | If someone tries to insert a duplicate, MySQL returns
            | an error — our controller handles this gracefully.
            */
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();

            $table->enum('type', ['like', 'insightful', 'love', 'funny']);

            $table->timestamp('created_at')->useCurrent();

            /*
            | Unique constraint: one row per user per post.
            | The type can change (UPDATE) but there can only be
            | one reaction per user per post at any time.
            */
            $table->unique(['user_id', 'post_id'], 'reactions_user_post_unique');

            /*
            | Index on post_id for fast count queries per post.
            */
            $table->index(['post_id', 'type'], 'reactions_post_type_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};
