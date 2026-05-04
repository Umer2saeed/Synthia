<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            /*
            | user_id: who performed the action.
            | Nullable because system actions (scheduled commands)
            | have no user — they are performed automatically.
            */
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            | action: a short machine-readable identifier.
            | Examples: 'post.created', 'user.deleted', 'comment.approved'
            | We use dot notation for easy filtering and grouping.
            */
            $table->string('action', 100);

            /*
            | model_type: the fully-qualified class name of the affected model.
            | Examples: 'App\Models\Post', 'App\Models\User'
            | Nullable for actions that don't affect a specific model.
            */
            $table->string('model_type', 150)->nullable();

            /*
            | model_id: the ID of the affected model record.
            | Combined with model_type this gives us a polymorphic reference.
            */
            $table->unsignedBigInteger('model_id')->nullable();

            /*
            | description: human-readable explanation of what happened.
            | Examples: 'Published post "My Article"'
            |           'Deleted user john@example.com'
            |           'Changed role from author to editor'
            */
            $table->text('description');

            /*
            | ip: the IP address of the request that triggered the action.
            | Useful for security auditing.
            | Nullable for system/scheduled actions.
            */
            $table->string('ip', 45)->nullable();

            /*
            | We only store created_at — logs are never updated, only created.
            */
            $table->timestamp('created_at')->useCurrent();

            /*
            | Indexes for the most common filter queries in the admin panel.
            */
            $table->index('action');
            $table->index('user_id');
            $table->index('created_at');
            $table->index(['model_type', 'model_id'], 'activity_logs_model_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
