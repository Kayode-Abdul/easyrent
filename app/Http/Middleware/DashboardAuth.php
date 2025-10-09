<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardAuth
{
    /**
     * Handle an incoming request to dashboard areas.
     * Provides enhanced authentication handling with better user experience.
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            // If it's an AJAX request, return JSON response
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'Authentication required',
                    'redirect' => route('login')
                ], 401);
            }
            
            // For regular requests, redirect to login with a friendly message
            return redirect()->route('login')
                ->with('info', 'Please login to access your dashboard.');
        }

        $user = Auth::user();

        // Verify user object integrity
        if (!$user || !isset($user->user_id) || !isset($user->role)) {
            Auth::logout();
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'Invalid session',
                    'redirect' => route('login')
                ], 401);
            }
            
            return redirect()->route('login')
                ->with('warning', 'Your session was invalid. Please login again.');
        }

        return $next($request);
    }
}
