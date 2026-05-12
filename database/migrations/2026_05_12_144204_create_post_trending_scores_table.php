<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_trending_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('score')->default(0);
            $table->unsignedInteger('views_snapshot')->default(0);
            $table->unsignedInteger('claps_snapshot')->default(0);
            $table->unsignedInteger('comments_snapshot')->default(0);
            $table->timestamp('calculated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_trending_scores');
    }
};
