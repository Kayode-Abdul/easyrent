<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Allow if user has 'admin' boolean flag or has role 'admin' in roles relation
        $isAdminFlag = (bool) ($user->admin ?? false);
        $hasAdminRole = method_exists($user, 'hasRole') ? $user->hasRole('admin') : false;

        if (!$isAdminFlag && !$hasAdminRole) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
