<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Add a new role to the user (Restricted to Artisan and Property Manager)
     */
    public function addRole(Request $request)
    {
        $request->validate([
            'role_name' => 'required|string|in:Artisan,property_manager',
        ]);

        $user = Auth::user();
        $roleName = $request->role_name;
        
        // Find role ID
        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            return response()->json([
                'success' => false, 
                'message' => "Role $roleName not found in system."
            ], 404);
        }

        // Check if user already has this role
        if ($user->hasRole($roleName)) {
            return response()->json([
                'success' => false, 
                'message' => "You already have the $roleName role."
            ], 422);
        }

        try {
            DB::beginTransaction();
            
            // Attach role in pivot table
            // Note: User primary key is user_id
            $user->roles()->attach($role->id);
            
            // If it's the legacy role field that needs updating as well:
            // (Only update if it's currently a low-priority role or if we want to sync)
            // For now, we rely on hasRole() check in the system.

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => "Role " . ($role->display_name ?? $roleName) . " added successfully! You can now switch to your new dashboard."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => "An error occurred: " . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Switch current active role (session-based)
     */
    public function switchRole(Request $request)
    {
        $request->validate([
            'role' => 'required|string'
        ]);

        $role = $request->role;
        $user = Auth::user();

        // Validate user has this role
        if (!$user->hasRole($role) && $user->role != User::getRoleId($role)) {
             // Special case for admin/legacy
             if ($role === 'admin' && !($user->admin == 1 || $user->role == 7)) {
                 return back()->with('error', 'Unauthorized role switch.');
             }
        }

        session(['dashboard_mode' => $role]);
        
        return back()->with('success', "Switched to " . ucfirst($role) . " mode.");
    }
}
