<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentMonitoringAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Check if user has permission to view payment monitoring
        // This can be expanded based on your role/permission system
        if (!$this->hasPaymentMonitoringAccess($user)) {
            abort(403, 'Unauthorized access to payment monitoring.');
        }

        return $next($request);
    }

    /**
     * Check if user has access to payment monitoring
     */
    protected function hasPaymentMonitoringAccess($user): bool
    {
        // Check if user is admin or has specific monitoring role
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole(['admin', 'super_admin', 'system_monitor']);
        }
        
        // Fallback: check if user has admin-like properties
        if (isset($user->is_admin) && $user->is_admin) {
            return true;
        }
        
        if (isset($user->role) && in_array($user->role, ['admin', 'super_admin'])) {
            return true;
        }
        
        // Check user roles relationship if it exists
        if (method_exists($user, 'roles')) {
            $roles = $user->roles()->pluck('name')->toArray();
            return !empty(array_intersect($roles, ['admin', 'super_admin', 'system_monitor']));
        }
        
        return false;
    }
}