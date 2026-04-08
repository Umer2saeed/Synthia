<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Clap extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Fillable Fields
    |--------------------------------------------------------------------------
    | user_id  → who clapped
    | post_id  → which post was clapped on
    | count    → how many times this user has clapped on this post
    |            starts at 1, increments up to MAX_CLAPS_PER_USER
    */
    protected $fillable = [
        'user_id',
        'post_id',
        'count',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    | count is stored as integer in DB — cast confirms this in PHP too.
    */
    protected $casts = [
        'count' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Max Claps Constant
    |--------------------------------------------------------------------------
    | Maximum number of times a single user can clap on a single post.
    | This matches Medium's behavior — you can clap up to 50 times.
    | Stored as a constant so it is defined once and used everywhere:
    |   - In the controller to enforce the limit
    |   - In the view to show the progress bar
    |   - In JavaScript to disable the button at the limit
    */
    const MAX_CLAPS_PER_USER = 50;

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * A clap belongs to a user — the person who clapped.
     * Foreign key: claps.user_id → users.id
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A clap belongs to a post — the post being clapped on.
     * Foreign key: claps.post_id → posts.id
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
