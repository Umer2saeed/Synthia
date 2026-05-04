<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostDraft extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'post_id',
        'title',
        'content',
        'saved_at',
    ];

    protected $casts = [
        'saved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
