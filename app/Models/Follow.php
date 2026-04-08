<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Follow extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Fillable Fields
    |--------------------------------------------------------------------------
    | follower_id  → the user who is doing the following (I follow you)
    | following_id → the user who is being followed (you are followed)
    |
    | Both are foreign keys pointing to the users table.
    | This is a self-referential relationship — users table references itself.
    */
    protected $fillable = [
        'follower_id',
        'following_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * The user who is doing the following.
     * "I am the follower."
     *
     * Example: $follow->follower → returns the User model of the person
     *          who clicked Follow.
     */
    public function follower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    /**
     * The user who is being followed.
     * "I am being followed."
     *
     * Example: $follow->following → returns the User model of the author
     *          that was followed.
     *
     * Why 'following_id' as second argument?
     * Laravel's default assumption is that the foreign key is named
     * after the relationship method (following_id) — but since this
     * relationship is on the same users table, we must be explicit
     * to avoid confusion with the other foreign key.
     */
    public function following(): BelongsTo
    {
        return $this->belongsTo(User::class, 'following_id');
    }
}
