<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Follow;

class User extends Authenticatable
{
    /*
    |--------------------------------------------------------------------------
    | Traits
    |--------------------------------------------------------------------------
    | Notifiable  → allows sending notifications (email, SMS, etc.)
    | HasRoles    → Spatie trait for role/permission support
    */
    use Notifiable, HasRoles;

    /*
    |--------------------------------------------------------------------------
    | Fillable Fields
    |--------------------------------------------------------------------------
    | Includes both original fields and the new profile fields we added
    | via the second migration (avatar, username, bio, status, last_login_at).
    */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'avatar',
        'bio',
        'status',
        'last_login_at',
    ];

    /*
    |--------------------------------------------------------------------------
    | Hidden Fields
    |--------------------------------------------------------------------------
    | These fields are excluded when the model is serialized to JSON/array.
    | Never expose password or remember_token to the frontend.
    */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    | Automatically converts DB values to proper PHP types.
    | 'password' => 'hashed' means Laravel auto-hashes on assignment —
    | so you NEVER need Hash::make() manually when using $user->password = '...'.
    */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'password'          => 'hashed',
    ];

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    | Virtual attributes computed from existing data.
    | Access like a normal property: $user->avatar_url
    */

    /**
     * Returns the full public URL of the user's avatar.
     * Falls back to a UI-generated avatar using their initials via
     * the free UI Avatars service — no broken image links ever.
     */
    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=6366f1&color=fff&size=128';
    }

    /**
     * Returns a human-readable status label with a color hint.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active'   => 'Active',
            'inactive' => 'Inactive',
            default    => 'Unknown',
        };
    }

    /**
     * Returns display name — username if set, otherwise full name.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->username ?? $this->name;
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * A user has written many posts.
     * Foreign key: posts.user_id
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * A user has written many comments.
     * Foreign key: comments.user_id
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * A user has many claps (reactions on posts).
     * Foreign key: claps.user_id
     */
    public function claps(): HasMany
    {
        return $this->hasMany(Clap::class);
    }

    /**
     * A user has many bookmarks.
     * Foreign key: bookmarks.user_id
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * Users this user is following.
     * follower_id = this user, following_id = people they follow.
     *
     * Usage: $user->following → collection of users they follow
     */
    public function following(): HasMany
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    /**
     * Users who follow this user.
     * following_id = this user, follower_id = their followers.
     *
     * Usage: $user->followers → collection of users who follow them
     */
    public function followers(): HasMany
    {
        return $this->hasMany(Follow::class, 'following_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if this user is following another user.
     * Usage: $currentUser->isFollowing($otherUser)
     */
    public function isFollowing(User $user): bool
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    /**
     * Delete the old avatar file from storage.
     * Called before uploading a new one in the controller.
     */

    public function follow(User $user): void
    {
        /*
        | We use firstOrCreate instead of create to prevent duplicates
        | at the application level. The database unique constraint is
        | the final safety net.
        |
        | firstOrCreate checks: does a row with these values exist?
        |   Yes → return existing row (do nothing)
        |   No  → create a new row
        */
        $this->following()->firstOrCreate([
            'following_id' => $user->id,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | unfollow() — Unfollow a user
    |--------------------------------------------------------------------------
    | Deletes the follow record.
    | Safe to call even if not following — delete() on empty result does nothing.
    |
    | Usage: auth()->user()->unfollow($author)
    */
    public function unfollow(User $user): void
    {
        $this->following()
            ->where('following_id', $user->id)
            ->delete();
    }

    public function deleteAvatar(): void
    {
        if ($this->avatar) {
            Storage::disk('public')->delete($this->avatar);
        }
    }

}
