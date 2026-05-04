<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        /*
        | Only admins can see the activity log.
        */
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $query = ActivityLog::with('user')
            ->latest('created_at');

        /*
        | Filter by action type
        */
        if ($request->filled('action')) {
            $query->where('action', 'like', $request->action . '%');
        }

        /*
        | Filter by specific user
        */
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        /*
        | Filter by date range
        */
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs  = $query->paginate(50);
        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        /*
        | Get unique action prefixes for the filter dropdown.
        | We group by the first segment (post, user, comment, etc.)
        */
        $actionGroups = [
            'post'       => 'Post Actions',
            'user'       => 'User Actions',
            'comment'    => 'Comment Actions',
            'role'       => 'Role Actions',
            'permission' => 'Permission Actions',
            'category'   => 'Category Actions',
            'tag'        => 'Tag Actions',
        ];

        return view('admin.activity.index', compact(
            'logs', 'users', 'actionGroups'
        ));
    }
}
