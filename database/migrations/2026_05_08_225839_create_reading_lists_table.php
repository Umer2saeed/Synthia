<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reading_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);

            /*
            | slug: URL-friendly version of the name.
            | Used in the public share URL.
            | Not unique globally — unique per user only.
            */
            $table->string('slug', 120);
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'slug'], 'reading_lists_user_slug_unique');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_lists');
    }
};
