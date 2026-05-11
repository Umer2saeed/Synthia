<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Badge extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'criteria_type',
        'criteria_value',
        'color',
    ];

    public function userBadges(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    /*
    | All users who have earned this badge.
    */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot('earned_at', 'awarded_by')
            ->withTimestamps();
    }
}
