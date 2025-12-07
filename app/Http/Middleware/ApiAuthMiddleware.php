<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request for API authentication.
     * This middleware supports both API key and Bearer token authentication.
     */
    public function handle(Request $request, Closure $next)
    {
        // Check for API key in header
        $apiKey = $request->header('X-API-Key');
        
        // Check for Bearer token
        $bearerToken = $request->bearerToken();
        
        // Check for API key in query parameter (fallback)
        if (!$apiKey && !$bearerToken) {
            $apiKey = $request->query('api_key');
        }
        
        // For mobile apps, we'll use a simple API key validation
        // In production, this should be more sophisticated
        $validApiKeys = [
            config('app.mobile_api_key', 'easyrent_mobile_2024'),
            config('app.admin_api_key', 'easyrent_admin_2024')
        ];
        
        // If Bearer token is provided, validate it as a user session
        if ($bearerToken) {
            try {
                // For Laravel Sanctum or similar token-based auth
                $user = \Laravel\Sanctum\PersonalAccessToken::findToken($bearerToken)?->tokenable;
                if ($user) {
                    auth()->setUser($user);
                    return $next($request);
                }
            } catch (\Exception $e) {
                // Token validation failed, continue to API key check
            }
        }
        
        // Validate API key
        if (!$apiKey || !in_array($apiKey, $validApiKeys)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Valid API key or Bearer token required.',
                'error_code' => 'INVALID_API_KEY'
            ], 401);
        }
        
        return $next($request);
    }
}