<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminOnly
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
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        $user = Auth::user();
        
        // Only allow super admin (admin = 1)
        if (!$user->admin || $user->admin !== 1) {
            abort(403, 'Access denied. Super Admin privileges required.');
        }

        return $next($request);
    }
}