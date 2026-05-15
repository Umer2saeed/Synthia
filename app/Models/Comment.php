<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\CommentFlag;

class Comment extends Model
{
    protected $fillable = [
        'user_id',
        'post_id',
        'content',
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_approved', false);
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

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class)->withTrashed();
    }

    /*
    | All likes on this comment.
    */
    public function likes(): HasMany
    {
        return $this->hasMany(CommentLike::class);
    }

    /*
    |--------------------------------------------------------------------------
    | isLikedBy() — Check if a specific user has liked this comment
    |--------------------------------------------------------------------------
    | WHY not just use $comment->likes->contains('user_id', $userId)?
    | That requires the likes collection to be loaded first.
    | This method works efficiently whether or not likes are eager loaded.
    */
    public function isLikedBy(int $userId): bool
    {
        /*
        | If likes are already eager loaded, use the collection.
        | This avoids an extra query when likes are loaded with withCount.
        */
        if ($this->relationLoaded('likes')) {
            return $this->likes->contains('user_id', $userId);
        }

        return $this->likes()->where('user_id', $userId)->exists();
    }



    public function flags(): HasMany
    {
        return $this->hasMany(CommentFlag::class);
    }

    public function isFlaggedByUser(int $userId): bool
    {
        return $this->flags()->where('user_id', $userId)->exists();
    }
}
