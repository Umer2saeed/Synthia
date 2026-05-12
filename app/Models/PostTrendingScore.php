<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostTrendingScore extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'score',
        'views_snapshot',
        'claps_snapshot',
        'comments_snapshot',
        'calculated_at',
    ];

    protected $casts = [
        'calculated_at' => 'datetime',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
