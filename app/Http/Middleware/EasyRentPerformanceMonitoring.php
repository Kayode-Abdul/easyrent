<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Logging\EasyRentLogger;

class EasyRentPerformanceMonitoring
{
    protected $logger;

    public function __construct(EasyRentLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Handle an incoming request and monitor performance
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        $response = $next($request);

        $executionTime = microtime(true) - $startTime;
        $memoryUsed = memory_get_usage(true) - $startMemory;

        // Only log performance for EasyRent Link Authentication routes
        if ($this->shouldLogPerformance($request)) {
            $this->logger->logPerformanceMetric(
                $this->getOperationName($request),
                $executionTime,
                $request,
                [
                    'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2),
                    'response_status' => $response->getStatusCode(),
                    'response_size_bytes' => strlen($response->getContent()),
                ]
            );
        }

        return $response;
    }

    /**
     * Determine if performance should be logged for this request
     */
    protected function shouldLogPerformance(Request $request): bool
    {
        $easyRentRoutes = [
            'apartment.invitation.show',
            'apartment.invitation.apply',
            'apartment.invitation.payment',
            'login',
            'register',
            'payment.process',
        ];

        $routeName = $request->route()?->getName();
        
        return in_array($routeName, $easyRentRoutes) || 
               str_contains($request->path(), 'apartment/invite/') ||
               $request->session()->has('invitation_context');
    }

    /**
     * Get a descriptive operation name for the request
     */
    protected function getOperationName(Request $request): string
    {
        $routeName = $request->route()?->getName();
        
        if ($routeName) {
            return $routeName;
        }

        $path = $request->path();
        
        if (str_contains($path, 'apartment/invite/')) {
            return 'apartment_invitation_access';
        }

        return $request->method() . ' ' . $path;
    }
}