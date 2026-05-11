<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->string('slug', 100)->unique();
            $table->text('description');

            /*
            | icon: emoji or SVG path string for display.
            | We use emojis for simplicity — no external icon dependency.
            */
            $table->string('icon', 20)->default('🏅');

            /*
            | criteria_type: what action triggers this badge.
            | criteria_value: the threshold number.
            | Example: type=posts_published, value=10 → "Published 10 posts"
            |
            | null criteria = manually awarded only (no auto-award).
            */
            $table->string('criteria_type', 80)->nullable();
            $table->unsignedInteger('criteria_value')->nullable();

            /*
            | color: Tailwind color class for the badge pill.
            */
            $table->string('color', 80)->default('bg-gray-100 text-gray-700');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
