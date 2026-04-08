<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->string('cover_image')->nullable();
            $table->enum('status', ['draft', 'published', 'scheduled'])->default('draft');
            $table->boolean('is_featured')->default(false);
            // AI Fields
            $table->text('ai_summary')->nullable(); // For AI-generated snippets
            $table->json('ai_metadata')->nullable(); // To store AI prompts/tokens used
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
