<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bookmark extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Fillable Fields
    |--------------------------------------------------------------------------
    | Only two fields needed:
    |   user_id → who saved the bookmark
    |   post_id → which post was bookmarked
    |
    | No 'count' field like claps — bookmarks are binary (on or off).
    | The unique constraint in the migration prevents duplicates.
    */
    protected $fillable = [
        'user_id',
        'post_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * A bookmark belongs to a user — the person who saved it.
     * Foreign key: bookmarks.user_id → users.id
     *
     * When we delete a user, all their bookmarks are deleted too
     * because the migration has onDelete('cascade').
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A bookmark belongs to a post — the post that was saved.
     * Foreign key: bookmarks.post_id → posts.id
     *
     * When we delete a post, all bookmarks pointing to it
     * are deleted too because the migration has onDelete('cascade').
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
