<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reaction extends Model
{
    /*
    | No updated_at column — reactions are created or deleted,
    | never updated. We only track created_at.
    */
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'post_id',
        'type',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /*
    | Available reaction types as a constant.
    | Used in validation and UI rendering.
    */
    const TYPES = ['like', 'insightful', 'love', 'funny'];

    /*
    | Emoji and label for each reaction type.
    | Used in the blade view to render buttons.
    */
    const DISPLAY = [
        'like'       => ['emoji' => '👍', 'label' => 'Like'],
        'insightful' => ['emoji' => '💡', 'label' => 'Insightful'],
        'love'       => ['emoji' => '❤️', 'label' => 'Love'],
        'funny'      => ['emoji' => '😄', 'label' => 'Funny'],
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
