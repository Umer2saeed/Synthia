<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\Clap;
use App\Models\Post;
use App\Models\User;
use App\Models\UserBadge;

class BadgeService
{
    /*
    | checkAndAward() — Run all auto-award checks for a user.
    | Called from Observers after relevant actions.
    | Each check is isolated so one failure does not block others.
    */
    public function checkAndAward(User $user): void
    {
        try {
            $this->checkPostsBadges($user);
            $this->checkClapsBadges($user);
        } catch (\Exception $e) {
            \Log::error('BadgeService::checkAndAward failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /*
    | Check badges triggered by post publication count.
    */
    private function checkPostsBadges(User $user): void
    {
        $publishedCount = Post::published()
            ->where('user_id', $user->id)
            ->count();

        $this->awardMatchingBadges($user, 'posts_published', $publishedCount);
    }

    /*
    | Check badges triggered by total claps received.
    */
    private function checkClapsBadges(User $user): void
    {
        $clapsReceived = Clap::whereHas('post', fn($q) => $q->where('user_id', $user->id))
            ->sum('count');

        $this->awardMatchingBadges($user, 'claps_received', (int) $clapsReceived);
    }

    /*
    | Find all badges for a criteria type where the user meets the threshold
    | and does not already have the badge — then award them.
    */
    private function awardMatchingBadges(User $user, string $criteriaType, int $currentValue): void
    {
        $eligibleBadges = Badge::where('criteria_type', $criteriaType)
            ->where('criteria_value', '<=', $currentValue)
            ->whereNotIn('id', $user->userBadges()->pluck('badge_id'))
            ->get();

        foreach ($eligibleBadges as $badge) {
            $this->award($user, $badge);
        }
    }

    /*
    | award() — Create the user_badge record.
    | Public so admin controller can call it directly.
    */
    public function award(User $user, Badge $badge, ?int $awardedBy = null): bool
    {
        if ($user->hasBadge($badge->id)) {
            return false;
        }

        UserBadge::create([
            'user_id'    => $user->id,
            'badge_id'   => $badge->id,
            'earned_at'  => now(),
            'awarded_by' => $awardedBy,
        ]);

        return true;
    }

    /*
    | revoke() — Remove a badge from a user (admin only).
    */
    public function revoke(User $user, Badge $badge): bool
    {
        return (bool) UserBadge::where('user_id', $user->id)
            ->where('badge_id', $badge->id)
            ->delete();
    }
}
