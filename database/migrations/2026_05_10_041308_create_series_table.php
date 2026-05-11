<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('series', function (Blueprint $table) {
            $table->id();

            /*
            | user_id: the author who owns this series.
            | Only the owner and admins/editors can manage it.
            */
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title', 200);
            $table->string('slug', 220)->unique();
            $table->text('description')->nullable();

            /*
            | cover_image: stored path in storage/app/public/series/
            | Displayed on the series listing page.
            */
            $table->string('cover_image')->nullable();

            /*
            | is_complete: signals to readers whether the series is
            | finished or still being written.
            */
            $table->boolean('is_complete')->default(false);

            $table->timestamps();

            $table->index('user_id');
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('series');
    }
};
