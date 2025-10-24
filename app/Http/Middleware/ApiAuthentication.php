<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ApiAuthentication
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $this->extractApiKey($request);

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key is required',
                'error' => 'MISSING_API_KEY'
            ], 401);
        }

        // Check if API key exists and is active
        $keyData = $this->validateApiKey($apiKey);

        if (!$keyData) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive API key',
                'error' => 'INVALID_API_KEY'
            ], 401);
        }

        // Check rate limiting
        if (!$this->checkRateLimit($keyData)) {
            return response()->json([
                'success' => false,
                'message' => 'Rate limit exceeded',
                'error' => 'RATE_LIMIT_EXCEEDED'
            ], 429);
        }

        // Log the API request
        $this->logApiRequest($request, $keyData);

        // Add API key data to request for use in controllers
        $request->attributes->set('api_key_data', $keyData);

        $response = $next($request);

        // Add rate limit headers to response
        $this->addRateLimitHeaders($response, $keyData);

        return $response;
    }

    /**
     * Extract API key from request
     */
    private function extractApiKey(Request $request): ?string
    {
        // Check Authorization header (Bearer token)
        $authHeader = $request->header('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }

        // Check X-API-Key header
        $apiKeyHeader = $request->header('X-API-Key');
        if ($apiKeyHeader) {
            return $apiKeyHeader;
        }

        // Check query parameter (less secure, but sometimes needed)
        return $request->query('api_key');
    }

    /**
     * Validate API key against database
     */
    private function validateApiKey(string $apiKey): ?array
    {
        // Use cache to avoid database hits for every request
        $cacheKey = 'api_key_' . hash('sha256', $apiKey);
        
        return Cache::remember($cacheKey, 300, function () use ($apiKey) { // Cache for 5 minutes
            // Ensure API keys table exists
            if (!DB::getSchemaBuilder()->hasTable('api_keys')) {
                return null;
            }

            $keyHash = hash('sha256', $apiKey);
            
            $keyData = DB::table('api_keys')
                ->where('key_hash', $keyHash)
                ->where('status', 'active')
                ->first();

            if (!$keyData) {
                return null;
            }

            return [
                'id' => $keyData->id,
                'name' => $keyData->name,
                'rate_limit' => $keyData->rate_limit,
                'permissions' => json_decode($keyData->permissions, true) ?? [],
                'requests_count' => $keyData->requests_count
            ];
        });
    }

    /**
     * Check rate limiting
     */
    private function checkRateLimit(array $keyData): bool
    {
        $rateLimitKey = 'rate_limit_' . $keyData['id'];
        $currentHour = now()->format('Y-m-d-H');
        $fullKey = $rateLimitKey . '_' . $currentHour;

        $currentRequests = Cache::get($fullKey, 0);
        
        if ($currentRequests >= $keyData['rate_limit']) {
            return false;
        }

        // Increment counter
        Cache::put($fullKey, $currentRequests + 1, 3600); // Cache for 1 hour

        return true;
    }

    /**
     * Log API request for monitoring
     */
    private function logApiRequest(Request $request, array $keyData): void
    {
        try {
            // Ensure API requests log table exists
            if (!DB::getSchemaBuilder()->hasTable('api_requests_log')) {
                return;
            }

            DB::table('api_requests_log')->insert([
                'api_key_id' => $keyData['id'],
                'method' => $request->method(),
                'endpoint' => $request->path(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status_code' => 200, // Will be updated by response middleware if needed
                'response_time' => 0, // Will be calculated by response middleware
                'created_at' => now()
            ]);

            // Update API key usage
            DB::table('api_keys')
                ->where('id', $keyData['id'])
                ->increment('requests_count')
                ->update(['last_used_at' => now()]);

        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Log::error('Failed to log API request: ' . $e->getMessage());
        }
    }

    /**
     * Add rate limit headers to response
     */
    private function addRateLimitHeaders($response, array $keyData): void
    {
        $rateLimitKey = 'rate_limit_' . $keyData['id'];
        $currentHour = now()->format('Y-m-d-H');
        $fullKey = $rateLimitKey . '_' . $currentHour;

        $currentRequests = Cache::get($fullKey, 0);
        $remaining = max(0, $keyData['rate_limit'] - $currentRequests);
        $resetTime = now()->addHour()->startOfHour()->timestamp;

        $response->headers->set('X-RateLimit-Limit', $keyData['rate_limit']);
        $response->headers->set('X-RateLimit-Remaining', $remaining);
        $response->headers->set('X-RateLimit-Reset', $resetTime);
    }
}