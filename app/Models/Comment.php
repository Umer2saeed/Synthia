<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Fillable Fields
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'user_id',
        'post_id',
        'content',
        'is_approved',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    | is_approved is stored as 0/1 in DB — cast to true/false in PHP.
    */
    protected $casts = [
        'is_approved' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * A comment belongs to a user (the author of the comment).
     * Foreign key: comments.user_id → users.id
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A comment belongs to a post.
     * Foreign key: comments.post_id → posts.id
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Only return approved comments.
     * Usage: Comment::approved()->get()
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Only return pending (unapproved) comments.
     * Usage: Comment::pending()->get()
     */
    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }
}
