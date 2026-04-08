<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tag extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Fillable Fields
    |--------------------------------------------------------------------------
    | Only 'name' and 'slug' exist on this table. Simple and lean.
    */
    protected $fillable = ['name', 'slug'];

    /*
    |--------------------------------------------------------------------------
    | Auto Slug Generation via Model Boot
    |--------------------------------------------------------------------------
    | Same pattern used in Post and Category. If slug is not provided,
    | we generate it from the name before saving.
    */
    protected static function booted(): void
    {
        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = static::generateUniqueSlug($tag->name);
            }
        });

        static::updating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = static::generateUniqueSlug($tag->name, $tag->id);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Unique Slug Generator
    |--------------------------------------------------------------------------
    | Appends -1, -2, etc. if slug already exists in the tags table.
    | $ignoreId is used on update to exclude the current record from the check.
    */
    protected static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug     = Str::slug($name);
        $original = $slug;
        $count    = 1;

        while (
        static::where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $original . '-' . $count++;
        }

        return $slug;
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    | A tag belongs to many posts through the post_tag pivot table.
    | This is a many-to-many relationship:
    |   - One tag can be attached to many posts
    |   - One post can have many tags
    |
    | Laravel automatically looks for the pivot table 'post_tag'
    | (alphabetical order of the two model names).
    */
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_tag');
    }
}
