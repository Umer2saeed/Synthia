<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Series extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'cover_image',
        'is_complete',
    ];

    protected $casts = [
        'is_complete' => 'boolean',
    ];

    /*
    | Auto-generate slug from title on create.
    */
    protected static function booted(): void
    {
        static::creating(function (Series $series) {
            if (empty($series->slug)) {
                $series->slug = static::generateUniqueSlug($series->title);
            }
        });
    }

    public static function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $base  = Str::slug($title);
        $slug  = $base;
        $count = 2;

        while (
        static::where('slug', $slug)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists()
        ) {
            $slug = $base . '-' . $count++;
        }

        return $slug;
    }

    /*
    | Cover image full URL with fallback placeholder.
    */
    public function getCoverImageUrlAttribute(): string
    {
        if ($this->cover_image && Storage::disk('public')->exists($this->cover_image)) {
            return asset('storage/' . $this->cover_image);
        }

        return asset('images/og-default.jpg');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    | The pivot records — useful for ordering management.
    */
    public function seriesPosts(): HasMany
    {
        return $this->hasMany(SeriesPost::class)->orderBy('order');
    }

    /*
    | Published posts in this series in order.
    | This is what the series page and post nav use.
    */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'series_posts')
            ->withPivot('order')
            ->orderByPivot('order', 'asc');
    }

    /*
    | Only published posts — used on the public series page.
    */
    public function publishedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'series_posts')
            ->withPivot('order')
            ->where('status', 'published')
            ->orderByPivot('order', 'asc');
    }
}
