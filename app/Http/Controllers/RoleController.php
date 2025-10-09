<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // POST /switch-role
    public function switchRole(Request $request)
    {
        $request->validate(['role' => 'required|string']);
        $user = Auth::user();
        $requested = $request->input('role');

        // Build available roles from legacy numeric, admin flag, and pivot roles
        $available = [];
        $map = [
            7 => 'admin',
            2 => 'landlord',
            1 => 'tenant',
            6 => 'property_manager',
            3 => 'marketer',
            9 => 'regional_manager',
        ];
        if (isset($map[$user->role])) $available[] = $map[$user->role];
        if ($user->admin == 1) $available[] = 'admin';
        try {
            if (method_exists($user, 'roles')) {
                $available = array_unique(array_merge($available, $user->roles()->pluck('name')->toArray()));
            }
        } catch (\Throwable $e) {}

        if (!in_array($requested, $available)) {
            return back()->with('error', 'You do not have access to the requested role.');
        }

        session(['selected_role' => $requested]);
        return back()->with('success', 'Switched role to ' . str_replace('_',' ', $requested));
    }
}
