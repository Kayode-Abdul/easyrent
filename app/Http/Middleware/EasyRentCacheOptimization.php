<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Cache\EasyRentCacheInterface;
use App\Services\Logging\EasyRentLogger;

class EasyRentCacheOptimization
{
    protected $cacheService;
    protected $logger;

    public function __construct(EasyRentCacheInterface $cacheService, EasyRentLogger $logger)
    {
        $this->cacheService = $cacheService;
        $this->logger = $logger;
    }

    /**
     * Handle an incoming request with cache optimization
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        $cacheHit = false;
        $cachePrewarmed = false;

        // Pre-warm cache for invitation routes
        if ($this->shouldOptimizeForInvitation($request)) {
            $cacheResult = $this->preWarmInvitationCache($request);
            $cacheHit = $cacheResult['cache_hit'] ?? false;
            $cachePrewarmed = $cacheResult['cache_prewarmed'] ?? false;
        }

        // Optimize database queries for EasyRent operations
        if ($this->shouldOptimizeQueries($request)) {
            $this->optimizeForEasyRentQueries($request);
        }

        $response = $next($request);

        $executionTime = microtime(true) - $startTime;
        $memoryUsed = memory_get_usage(true) - $startMemory;

        // Enhanced performance metrics
        if ($this->shouldCacheMetrics($request)) {
            $this->cacheService->cachePerformanceMetrics(
                $this->getOperationName($request),
                [
                    'execution_time' => $executionTime,
                    'memory_used' => $memoryUsed,
                    'success' => $response->getStatusCode() < 400,
                    'response_size' => strlen($response->getContent()),
                    'cache_hit' => $cacheHit,
                    'cache_prewarmed' => $cachePrewarmed,
                    'status_code' => $response->getStatusCode(),
                    'user_authenticated' => auth()->check(),
                    'session_has_invitation' => session()->has('invitation_context')
                ]
            );
        }

        // Add performance headers for debugging (only in non-production)
        if (!app()->environment('production')) {
            $response->headers->set('X-EasyRent-Execution-Time', round($executionTime * 1000, 2) . 'ms');
            $response->headers->set('X-EasyRent-Memory-Used', round($memoryUsed / 1024 / 1024, 2) . 'MB');
            $response->headers->set('X-EasyRent-Cache-Hit', $cacheHit ? 'true' : 'false');
        }

        return $response;
    }

    /**
     * Determine if request should be optimized for invitation access
     */
    protected function shouldOptimizeForInvitation(Request $request): bool
    {
        return str_contains($request->path(), 'apartment/invite/') ||
               $request->route()?->getName() === 'apartment.invite.show';
    }

    /**
     * Pre-warm cache for invitation-related data
     */
    protected function preWarmInvitationCache(Request $request): array
    {
        $result = ['cache_hit' => false, 'cache_prewarmed' => false];
        
        try {
            // Extract token from route parameters
            $token = $request->route('token');
            
            if ($token) {
                // Check if invitation data is already cached
                $cachedData = $this->cacheService->getCachedInvitationData($token);
                
                if (!$cachedData) {
                    // Pre-load invitation data into cache
                    $invitationData = $this->cacheService->cacheInvitationData($token);
                    
                    if ($invitationData) {
                        $result['cache_prewarmed'] = true;
                        $request->attributes->set('cache_prewarmed', true);
                        
                        // Also pre-warm apartment data if not cached
                        if (isset($invitationData['invitation']['apartment_id'])) {
                            $apartmentId = $invitationData['invitation']['apartment_id'];
                            if (!$this->cacheService->getCachedApartmentData($apartmentId)) {
                                $this->cacheService->cacheApartmentData($apartmentId);
                            }
                        }
                    }
                } else {
                    $result['cache_hit'] = true;
                    $request->attributes->set('cache_hit', true);
                }
            }
        } catch (\Exception $e) {
            // Log cache warming failure but don't break the request
            $this->logger->logError('Cache pre-warming failed', $e, [
                'path' => $request->path(),
                'token' => isset($token) ? substr($token, 0, 8) . '...' : 'none'
            ]);
        }
        
        return $result;
    }

    /**
     * Determine if request should optimize database queries
     */
    protected function shouldOptimizeQueries(Request $request): bool
    {
        return $this->shouldOptimizeForInvitation($request) ||
               str_contains($request->path(), 'dashboard') ||
               str_contains($request->path(), 'property');
    }

    /**
     * Optimize database queries for EasyRent operations
     */
    protected function optimizeForEasyRentQueries(Request $request): void
    {
        try {
            // Pre-load frequently accessed data based on route
            if (str_contains($request->path(), 'apartment/invite/')) {
                // Pre-cache active invitations list for admin views
                $this->cacheService->cacheActiveInvitations(50);
            }
            
            if (str_contains($request->path(), 'dashboard') && auth()->check()) {
                // Pre-cache user data for dashboard
                $this->cacheService->cacheUserData(auth()->id());
            }
            
        } catch (\Exception $e) {
            // Log optimization failure but don't break the request
            $this->logger->logError('Query optimization failed', $e, [
                'path' => $request->path(),
                'user_id' => auth()->id()
            ]);
        }
    }

    /**
     * Determine if performance metrics should be cached
     */
    protected function shouldCacheMetrics(Request $request): bool
    {
        $easyRentRoutes = [
            'apartment.invite.show',
            'apartment.invite.apply',
            'apartment.invite.payment',
            'login',
            'register'
        ];

        return in_array($request->route()?->getName(), $easyRentRoutes) ||
               str_contains($request->path(), 'apartment/invite/');
    }

    /**
     * Get operation name for metrics
     */
    protected function getOperationName(Request $request): string
    {
        $routeName = $request->route()?->getName();
        
        if ($routeName) {
            return $routeName;
        }

        if (str_contains($request->path(), 'apartment/invite/')) {
            return 'apartment_invitation_access';
        }

        return $request->method() . '_' . str_replace('/', '_', $request->path());
    }
}