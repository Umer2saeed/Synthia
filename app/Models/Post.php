<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\Clap;
use App\Models\Bookmark;

class Post extends Model
{
    /*
    |--------------------------------------------------------------------------
    | SoftDeletes Trait
    |--------------------------------------------------------------------------
    | This trait adds soft delete support. Instead of permanently removing a
    | record from the DB, it sets the `deleted_at` timestamp. You can restore
    | it later. Requires `$table->softDeletes()` in migration — which you have.
    */
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Fillable Fields
    |--------------------------------------------------------------------------
    | These are the fields allowed for mass assignment (e.g., Post::create([...]))
    | Any field NOT in this list will be silently ignored during mass assignment.
    */
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'content',
        'cover_image',
        'status',
        'is_featured',
        'ai_summary',
        'ai_metadata',
        'published_at',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    | Automatically cast DB column types to PHP types when reading the model.
    | 'ai_metadata' is stored as JSON in the DB but will be decoded to an array.
    | 'published_at' is a timestamp — cast to Carbon instance for easy formatting.
    | 'is_featured' cast to boolean so we get true/false instead of 0/1.
    */
    protected $casts = [
        'ai_metadata'  => 'array',
        'published_at' => 'datetime',
        'is_featured'  => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Model Boot — Auto Slug Generation
    |--------------------------------------------------------------------------
    | The `booted()` method runs lifecycle hooks. Here we auto-generate a
    | unique slug from the title before creating or updating if none is given.
    */
    protected static function booted(): void
    {
        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = static::generateUniqueSlug($post->title);
            }
        });

        static::updating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = static::generateUniqueSlug($post->title, $post->id);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Unique Slug Generator
    |--------------------------------------------------------------------------
    | Generates a slug from the title, then checks DB for duplicates.
    | If "my-post" exists, it tries "my-post-1", "my-post-2", etc.
    | We exclude the current post ID on update to avoid false conflicts.
    */
    protected static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $count = 1;

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
    */

    /**
     * A post belongs to a single user (the author).
     * Foreign key: posts.user_id → users.id
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A post belongs to one category.
     * Foreign key: posts.category_id → categories.id
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Tags Relationship
    |--------------------------------------------------------------------------
    | A post can have many tags, and a tag can belong to many posts.
    | This is managed through the post_tag pivot table.
    |
    | withTimestamps() is NOT called here because your post_tag migration
    | does not include created_at/updated_at columns.
    |
    | Usage:
    |   $post->tags                          → collection of tags
    |   $post->tags->pluck('name')           → ['laravel', 'php']
    |   $post->tags()->sync([1, 3, 5])       → replace all tags
    |   $post->tags()->attach($tagId)        → add a tag
    |   $post->tags()->detach($tagId)        → remove a tag
    */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tag');
    }


    /**
     * A post has many comments.
     * Foreign key: comments.post_id
     *
     * Usage:
     *   $post->comments                    → all comments (approved + pending)
     *   $post->comments()->approved()->get() → only approved comments
     *   $post->comments()->count()          → total comment count
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }


    /*
    |--------------------------------------------------------------------------
    | Scopes (Query Helpers)
    |--------------------------------------------------------------------------
    | Local scopes let you chain reusable query conditions cleanly.
    | Usage: Post::published()->get() or Post::featured()->latest()->get()
    */

    /** Only return published posts */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /** Only return featured posts */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /** Only return draft posts */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors (Computed Attributes)
    |--------------------------------------------------------------------------
    | Accessors let you add virtual read-only attributes to a model.
    | Access like: $post->cover_image_url
    */

    /**
     * Returns full public URL for the cover image.
     * Falls back to a placeholder if no image is stored.
     */
    public function getCoverImageUrlAttribute(): string
    {
        return $this->cover_image
            ? asset('storage/' . $this->cover_image)
            : asset('images/placeholder.jpg');
    }

    /**
     * Returns a human-readable label for the status badge.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'published' => 'Published',
            'scheduled'  => 'Scheduled',
            default      => 'Draft',
        };
    }

    /*
|--------------------------------------------------------------------------
| Claps Relationship
|--------------------------------------------------------------------------
| A post has many clap records — one per user who has clapped.
| The total clap count is the SUM of all count values, not the
| number of rows. This is because each user can clap multiple times
| and their total is stored in a single row.
|
| Example:
|   User A clapped 5 times → one row with count=5
|   User B clapped 3 times → one row with count=3
|   Total claps on post = 5+3 = 8 (not 2 rows)
*/
    public function claps()
    {
        return $this->hasMany(Clap::class);
    }

    /*
    |--------------------------------------------------------------------------
    | totalClaps() — Sum of all clap counts on this post
    |--------------------------------------------------------------------------
    | We use sum('count') not count() because each row stores how many
    | times that user clapped — not just whether they clapped.
    |
    | Usage: $post->totalClaps()
    */
    public function totalClaps(): int
    {
        return $this->claps()->sum('count');
    }

    /*
    |--------------------------------------------------------------------------
    | userClaps() — How many times the current user has clapped
    |--------------------------------------------------------------------------
    | Returns 0 if:
    |   - No user is logged in
    |   - The logged-in user has never clapped on this post
    |
    | Usage: $post->userClaps()
    */
    public function userClaps(): int
    {
        if (!auth()->check()) {
            return 0;
        }

        $clap = $this->claps()
            ->where('user_id', auth()->id())
            ->first();

        return $clap ? $clap->count : 0;
    }

    /*
|--------------------------------------------------------------------------
| Bookmarks Relationship
|--------------------------------------------------------------------------
| A post can be bookmarked by many users.
| Each bookmark is one row in the bookmarks table.
|
| Usage:
|   $post->bookmarks          → collection of all bookmark records
|   $post->bookmarks()->count() → how many users bookmarked this post
*/
    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    /*
    |--------------------------------------------------------------------------
    | isBookmarkedBy() — Check if a specific user bookmarked this post
    |--------------------------------------------------------------------------
    | Returns true or false.
    |
    | Usage in controllers:
    |   $post->isBookmarkedBy(auth()->user())
    |
    | Usage in views:
    |   @if($post->isBookmarkedBy(auth()->user()))
    |
    | WHY a method instead of a property?
    | Because we need to pass the user — a property cannot take arguments.
    */
    public function isBookmarkedBy(?User $user): bool
    {
        if (!$user) {
            return false; // guests can never have bookmarks
        }

        /*
        | exists() is more efficient than count() > 0 or first() !== null
        | because it stops as soon as it finds one matching row.
        | The SQL it runs:
        | SELECT EXISTS(SELECT 1 FROM bookmarks WHERE post_id=X AND user_id=Y)
        */
        return $this->bookmarks()
            ->where('user_id', $user->id)
            ->exists();
    }
}
