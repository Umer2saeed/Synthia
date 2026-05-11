<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ReadingList extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /*
    | Auto-generate slug from name before creating.
    */
    protected static function booted(): void
    {
        static::creating(function (ReadingList $list) {
            $list->slug = $list->generateUniqueSlug($list->name, $list->user_id);
        });

        static::updating(function (ReadingList $list) {
            if ($list->isDirty('name')) {
                $list->slug = $list->generateUniqueSlug($list->name, $list->user_id, $list->id);
            }
        });
    }

    /*
    | Generate a slug unique per user.
    | If "laravel-tutorials" exists, tries "laravel-tutorials-2", etc.
    */
    public function generateUniqueSlug(string $name, int $userId, ?int $excludeId = null): string
    {
        $base  = Str::slug($name);
        $slug  = $base;
        $count = 2;

        while (
        static::where('user_id', $userId)
            ->where('slug', $slug)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists()
        ) {
            $slug = $base . '-' . $count++;
        }

        return $slug;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReadingListItem::class, 'list_id');
    }

    /*
    | Check if a specific post is in this list.
    */
    public function hasPost(int $postId): bool
    {
        return $this->items()->where('post_id', $postId)->exists();
    }

    /*
    | Public share URL.
    */
    public function getShareUrlAttribute(): string
    {
        return route('reading-lists.show', [$this->id, $this->slug]);
    }
}
