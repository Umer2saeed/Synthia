<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Request;

class ActivityLog extends Model
{
    /*
    | No updated_at — activity logs are immutable once created.
    */
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'ip',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Action constants — prevents typos across the codebase
    |--------------------------------------------------------------------------
    */
    const ACTION_POST_CREATED   = 'post.created';
    const ACTION_POST_UPDATED   = 'post.updated';
    const ACTION_POST_PUBLISHED = 'post.published';
    const ACTION_POST_DELETED   = 'post.deleted';
    const ACTION_POST_RESTORED  = 'post.restored';

    const ACTION_USER_CREATED = 'user.created';
    const ACTION_USER_UPDATED = 'user.updated';
    const ACTION_USER_DELETED = 'user.deleted';

    const ACTION_ROLE_CHANGED       = 'role.changed';
    const ACTION_PERMISSION_CHANGED = 'permission.changed';

    const ACTION_COMMENT_APPROVED = 'comment.approved';
    const ACTION_COMMENT_DELETED  = 'comment.deleted';

    const ACTION_CATEGORY_CREATED = 'category.created';
    const ACTION_CATEGORY_UPDATED = 'category.updated';
    const ACTION_CATEGORY_DELETED = 'category.deleted';

    const ACTION_TAG_CREATED = 'tag.created';
    const ACTION_TAG_UPDATED = 'tag.updated';
    const ACTION_TAG_DELETED = 'tag.deleted';

    /*
    |--------------------------------------------------------------------------
    | record() — Static helper to create a log entry
    |--------------------------------------------------------------------------
    | Usage:
    |   ActivityLog::record(
    |       action:      ActivityLog::ACTION_POST_PUBLISHED,
    |       description: 'Published post "My Article Title"',
    |       model:       $post,
    |   );
    |
    | We use a static method so calling code stays clean —
    | no need to instantiate the model manually.
    */
    public static function record(
        string $action,
        string $description,
        ?Model $model = null,
    ): self {
        return static::create([
            'user_id'    => auth()->id(),
            'action'     => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id'   => $model?->id,
            'description'=> $description,
            'ip'         => Request::ip(),
            'created_at' => now(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | actionLabel() — Human-readable action label for display
    |--------------------------------------------------------------------------
    */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'post.created'        => 'Post Created',
            'post.updated'        => 'Post Updated',
            'post.published'      => 'Post Published',
            'post.deleted'        => 'Post Deleted',
            'post.restored'       => 'Post Restored',
            'user.created'        => 'User Registered',
            'user.updated'        => 'User Updated',
            'user.deleted'        => 'User Deleted',
            'role.changed'        => 'Role Changed',
            'permission.changed'  => 'Permission Changed',
            'comment.approved'    => 'Comment Approved',
            'comment.deleted'     => 'Comment Deleted',
            'category.created'    => 'Category Created',
            'category.updated'    => 'Category Updated',
            'category.deleted'    => 'Category Deleted',
            'tag.created'         => 'Tag Created',
            'tag.updated'         => 'Tag Updated',
            'tag.deleted'         => 'Tag Deleted',
            default               => ucwords(str_replace('.', ' ', $this->action)),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | actionColor() — Badge color for each action type
    |--------------------------------------------------------------------------
    */
    public function getActionColorAttribute(): string
    {
        return match(true) {
            str_contains($this->action, '.created')   => 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400',
            str_contains($this->action, '.published')  => 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-400',
            str_contains($this->action, '.updated')   => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-400',
            str_contains($this->action, '.deleted')   => 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-400',
            str_contains($this->action, '.restored')  => 'bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-400',
            str_contains($this->action, 'role.')      => 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-400',
            str_contains($this->action, 'comment.')   => 'bg-orange-100 dark:bg-orange-900 text-orange-700 dark:text-orange-400',
            default                                    => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400',
        };
    }
}
