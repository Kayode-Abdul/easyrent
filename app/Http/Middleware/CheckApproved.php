<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckApproved
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
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Check Artisan verification
        if ($user->isArtisan() && session('dashboard_mode') === 'artisan' && !$user->is_artisan_verified) {
            return response()->view('pending-approval');
        }

        // Check Marketer status - Relaxed to allow pending marketers to see dashboard
        if ($user->isMarketer() && session('dashboard_mode') === 'marketer' && $user->marketer_status === 'pending') {
            // Allow access even if pending, or you could return $next($request) here explicitly
            // For now, we just remove the block.
            return $next($request);
        }


        return $next($request);
    }
}