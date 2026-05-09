<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reading_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')
                ->constrained('reading_lists')
                ->cascadeOnDelete();
            $table->foreignId('post_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamp('added_at')->useCurrent();

            /*
            | One post per list — no duplicates.
            */
            $table->unique(['list_id', 'post_id'], 'reading_list_items_unique');
            $table->index('list_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_list_items');
    }
};
