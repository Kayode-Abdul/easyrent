<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $role
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Check if user has the required role
        if (!$this->hasRole($user, $role)) {
            abort(403, 'Unauthorized. Required role: ' . $role);
        }

        return $next($request);
    }

    /**
     * Check if user has the required role
     *
     * @param  \App\Models\User  $user
     * @param  string  $role
     * @return bool
     */
    private function hasRole($user, string $role): bool
    {
        // Map role names to role IDs based on your system
        $roleMap = [
            'admin' => 1,
            'super_admin' => 1, // Assuming super admin is role 1
            'landlord' => 2,
            'tenant' => 3,
            'agent' => 4,
            'property_manager' => 5,
            'marketer' => 6,
            'regional_manager' => 7,
            'super_marketer' => 9,
        ];

        $requiredRoleId = $roleMap[$role] ?? null;
        
        if ($requiredRoleId === null) {
            return false;
        }

        return $user->role_id == $requiredRoleId;
    }
}