<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CustomRedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     * Redirect to dashboard if already logged in (custom session-based auth).
     */
    public function handle(Request $request, Closure $next)
    {
        if (session('loggedIn')) {
            return redirect('/dashboard');
        }
        return $next($request);
    }
}
