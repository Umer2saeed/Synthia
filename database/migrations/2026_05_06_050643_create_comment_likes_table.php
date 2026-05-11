<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comment_likes', function (Blueprint $table) {
            $table->id();

            /*
            | user_id: who liked the comment
            | comment_id: which comment was liked
            |
            | Both cascade on delete:
            |   - User deleted → their likes deleted
            |   - Comment deleted → its likes deleted
            */
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('comment_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            | One like per user per comment — enforced at DB level.
            | If someone tries to insert a duplicate, MySQL throws
            | an error which we handle in the controller.
            */
            $table->unique(['user_id', 'comment_id'], 'comment_likes_user_comment_unique');

            /*
            | created_at only — likes are never updated.
            */
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_likes');
    }
};
