<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated and has admin role
        if (Auth::check()) {
            $user = Auth::user();
            $adminRoleId = \Illuminate\Support\Facades\DB::table('roles')->where('name', 'admin')->value('id');
            if ($user->admin == 1 || $user->role == $adminRoleId || $user->hasRole('admin')) {
                return $next($request);
            }
        }
        
        // If AJAX request, return JSON response
        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to perform this action.',
            ], 403);
        }
        
        // Otherwise redirect to error page
        return redirect('/errors/403')->with('message', 'You do not have permission to access this resource.');
    }
}
