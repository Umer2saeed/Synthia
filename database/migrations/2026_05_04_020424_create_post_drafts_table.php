<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_drafts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            /*
            | post_id is nullable:
            |   null   → autosave for a NEW post (not yet saved to posts table)
            |   integer → autosave for an EXISTING post (editing a post)
            |
            | This lets us track unsaved new post drafts separately from
            | edits to existing posts.
            */
            $table->foreignId('post_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title')->nullable();
            $table->longText('content')->nullable();

            /*
            | saved_at: when this autosave was last written.
            | We update this on every autosave tick.
            | The UI shows "Autosaved X ago" using this timestamp.
            */
            $table->timestamp('saved_at');

            /*
            | One autosave per user per post (or per user for new posts).
            | We upsert on this unique key — always one draft, updated in place.
            */
            $table->unique(['user_id', 'post_id'], 'post_drafts_user_post_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_drafts');
    }
};
