<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comment_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reason', 200)->nullable();
            $table->timestamp('created_at')->useCurrent();

            // One flag per user per comment
            $table->unique(['comment_id', 'user_id'], 'comment_flags_unique');
            $table->index('comment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_flags');
    }
};
