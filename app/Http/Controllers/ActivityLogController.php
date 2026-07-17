<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        // Only Super Admin and Landlords can view logs
        if (!auth()->user()->admin && !in_array(auth()->user()->role, [1])) {
            abort(403, 'Unauthorized action.');
        }

        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        // If the user is a Landlord, they probably should only see logs related to their properties? 
        // Or if they should see all logs, let's just show all for now since they said "both should see the activity logs".
        // To be safe, if admin, show all. If landlord, maybe we should filter? But let's show all as requested, or at least logs they have access to. 
        // For now, let's just paginate all logs.
        $logs = $query->paginate(20);

        return view('admin.activity-logs', compact('logs'));
    }
}
