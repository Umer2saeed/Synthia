<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_revisions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('post_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('title', 500);

            // Stored as raw HTML — same format as posts.content
            $table->longText('content');

            $table->timestamp('created_at')->useCurrent();

            $table->index(['post_id', 'created_at'], 'revisions_post_time_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_revisions');
    }
};
