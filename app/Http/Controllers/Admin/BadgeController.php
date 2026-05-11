<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\User;
use App\Models\UserBadge;
use App\Services\BadgeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    public function __construct(
        private BadgeService $badgeService
    ) {}

    /*
    | Show all badges and which users have each one.
    */
    public function index(): \Illuminate\View\View
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $badges = Badge::withCount('userBadges')->orderBy('name')->get();

        return view('admin.badges.index', compact('badges'));
    }

    /*
    | Award a badge to a user manually.
    */
    public function award(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $request->validate([
            'user_id'  => ['required', 'integer', 'exists:users,id'],
            'badge_id' => ['required', 'integer', 'exists:badges,id'],
        ]);

        $user  = User::findOrFail($request->user_id);
        $badge = Badge::findOrFail($request->badge_id);

        $awarded = $this->badgeService->award($user, $badge, auth()->id());

        if ($awarded) {
            return back()->with('success', "Badge \"{$badge->name}\" awarded to {$user->name}.");
        }

        return back()->with('error', "{$user->name} already has the \"{$badge->name}\" badge.");
    }

    /*
    | Revoke a badge from a user.
    */
    public function revoke(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $request->validate([
            'user_id'  => ['required', 'integer', 'exists:users,id'],
            'badge_id' => ['required', 'integer', 'exists:badges,id'],
        ]);

        $user  = User::findOrFail($request->user_id);
        $badge = Badge::findOrFail($request->badge_id);

        $this->badgeService->revoke($user, $badge);

        return back()->with('success', "Badge \"{$badge->name}\" revoked from {$user->name}.");
    }
}
