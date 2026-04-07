<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\Logging\EasyRentLogger;

class InvitationRateLimitMiddleware
{
    /**
     * Rate limiting configuration
     */
    const MAX_REQUESTS_PER_MINUTE = 10;
    const MAX_REQUESTS_PER_HOUR = 50;
    const SUSPICIOUS_THRESHOLD = 20; // Requests per 5 minutes
    const BLOCK_DURATION = 3600; // 1 hour in seconds
    
    /**
     * EasyRent Logger
     */
    protected $logger;

    public function __construct(EasyRentLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $route = $request->route()->getName();
        
        // Only apply rate limiting to invitation-related routes
        if (!str_contains($route, 'apartment.invite')) {
            return $next($request);
        }
        
        // Skip rate limiting for authenticated admin users
        if ($request->user() && method_exists($request->user(), 'hasRole') && $request->user()->hasRole('admin')) {
            return $next($request);
        }
        
        // Check if IP is currently blocked
        if ($this->isBlocked($ipAddress)) {
            $this->logger->logRateLimitExceeded($request, 'invitation_access_blocked');
            
            return response()->view('apartment.invite.rate-limited', [
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $this->getBlockTimeRemaining($ipAddress)
            ], 429);
        }
        
        // Check rate limits
        $rateLimitResult = $this->checkRateLimits($ipAddress, $userAgent, $route);
        
        if (!$rateLimitResult['allowed']) {
            // Block the IP if suspicious activity detected
            if ($rateLimitResult['suspicious']) {
                $this->blockIp($ipAddress, $rateLimitResult['reason']);
                
                $this->logger->logSuspiciousActivity($request, 'Rate limit exceeded - IP blocked', [
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                    'requests_per_minute' => $rateLimitResult['requests_per_minute'],
                    'requests_per_hour' => $rateLimitResult['requests_per_hour'],
                    'reason' => $rateLimitResult['reason']
                ]);
                
                return response()->view('apartment.invite.security-blocked', [
                    'message' => 'Suspicious activity detected. Access has been temporarily blocked.',
                    'contact_support' => true
                ], 429);
            }
            
            $this->logger->logRateLimitExceeded($request, 'invitation_rate_limit');
            
            return response()->view('apartment.invite.rate-limited', [
                'message' => 'Rate limit exceeded. Please slow down your requests.',
                'retry_after' => 60
            ], 429);
        }
        
        // Record the request
        $this->recordRequest($ipAddress, $userAgent, $route);
        
        return $next($request);
    }
    
    /**
     * Check if IP address is currently blocked
     */
    protected function isBlocked(string $ipAddress): bool
    {
        return Cache::has("blocked_ip:{$ipAddress}");
    }
    
    /**
     * Get remaining block time for IP
     */
    protected function getBlockTimeRemaining(string $ipAddress): int
    {
        $blockKey = "blocked_ip:{$ipAddress}";
        $blockData = Cache::get($blockKey);
        
        if (!$blockData) {
            return 0;
        }
        
        // Calculate remaining time based on when it was blocked
        $blockedAt = isset($blockData['blocked_at']) ? 
            \Carbon\Carbon::parse($blockData['blocked_at']) : 
            now();
        
        $remainingSeconds = self::BLOCK_DURATION - $blockedAt->diffInSeconds(now());
        return max(0, $remainingSeconds);
    }
    
    /**
     * Block an IP address
     */
    protected function blockIp(string $ipAddress, string $reason): void
    {
        $blockKey = "blocked_ip:{$ipAddress}";
        Cache::put($blockKey, [
            'blocked_at' => now()->toISOString(),
            'reason' => $reason,
            'user_agent' => request()->userAgent()
        ], self::BLOCK_DURATION);
        
        Log::warning('IP address blocked for suspicious activity', [
            'ip_address' => $ipAddress,
            'reason' => $reason,
            'blocked_until' => now()->addSeconds(self::BLOCK_DURATION),
            'user_agent' => request()->userAgent()
        ]);
    }
    
    /**
     * Check rate limits for IP address
     */
    protected function checkRateLimits(string $ipAddress, string $userAgent, string $route): array
    {
        $minuteKey = "rate_limit:minute:{$ipAddress}";
        $hourKey = "rate_limit:hour:{$ipAddress}";
        $suspiciousKey = "rate_limit:suspicious:{$ipAddress}";
        
        // Get current counts
        $requestsPerMinute = Cache::get($minuteKey, 0);
        $requestsPerHour = Cache::get($hourKey, 0);
        $suspiciousRequests = Cache::get($suspiciousKey, 0);
        
        // Check for suspicious activity (rapid requests)
        if ($suspiciousRequests >= self::SUSPICIOUS_THRESHOLD) {
            return [
                'allowed' => false,
                'suspicious' => true,
                'reason' => 'Excessive requests in short time period',
                'requests_per_minute' => $requestsPerMinute,
                'requests_per_hour' => $requestsPerHour,
                'suspicious_requests' => $suspiciousRequests
            ];
        }
        
        // Check minute limit
        if ($requestsPerMinute >= self::MAX_REQUESTS_PER_MINUTE) {
            return [
                'allowed' => false,
                'suspicious' => $requestsPerMinute > (self::MAX_REQUESTS_PER_MINUTE * 2),
                'reason' => 'Too many requests per minute',
                'requests_per_minute' => $requestsPerMinute,
                'requests_per_hour' => $requestsPerHour
            ];
        }
        
        // Check hour limit
        if ($requestsPerHour >= self::MAX_REQUESTS_PER_HOUR) {
            return [
                'allowed' => false,
                'suspicious' => $requestsPerHour > (self::MAX_REQUESTS_PER_HOUR * 1.5),
                'reason' => 'Too many requests per hour',
                'requests_per_minute' => $requestsPerMinute,
                'requests_per_hour' => $requestsPerHour
            ];
        }
        
        return [
            'allowed' => true,
            'suspicious' => false,
            'requests_per_minute' => $requestsPerMinute,
            'requests_per_hour' => $requestsPerHour
        ];
    }
    
    /**
     * Record a request for rate limiting
     */
    protected function recordRequest(string $ipAddress, string $userAgent, string $route): void
    {
        $minuteKey = "rate_limit:minute:{$ipAddress}";
        $hourKey = "rate_limit:hour:{$ipAddress}";
        $suspiciousKey = "rate_limit:suspicious:{$ipAddress}";
        
        // Increment counters with appropriate TTL
        $minuteCount = Cache::get($minuteKey, 0) + 1;
        Cache::put($minuteKey, $minuteCount, 60); // 1 minute
        
        $hourCount = Cache::get($hourKey, 0) + 1;
        Cache::put($hourKey, $hourCount, 3600); // 1 hour
        
        $suspiciousCount = Cache::get($suspiciousKey, 0) + 1;
        Cache::put($suspiciousKey, $suspiciousCount, 300); // 5 minutes
        
        // Log the request for monitoring
        Log::debug('Request recorded for rate limiting', [
            'ip_address' => $ipAddress,
            'user_agent' => substr($userAgent, 0, 100),
            'route' => $route,
            'timestamp' => now()->toISOString()
        ]);
    }
}