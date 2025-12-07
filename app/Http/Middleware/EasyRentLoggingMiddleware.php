<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Logging\EasyRentLinkLogger;
use Symfony\Component\HttpFoundation\Response;

class EasyRentLoggingMiddleware
{
    protected $logger;

    public function __construct(EasyRentLinkLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Handle an incoming request and log performance metrics
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        try {
            $response = $next($request);
            
            // Log successful request performance
            $this->logRequestPerformance($request, $startTime, $startMemory, $response->getStatusCode());
            
            return $response;
        } catch (\Throwable $exception) {
            // Log error with full context
            $this->logger->logError($exception, $request, [
                'middleware' => 'EasyRentLoggingMiddleware',
                'route_name' => $request->route()?->getName(),
                'route_action' => $request->route()?->getActionName(),
            ]);
            
            // Log performance even for failed requests
            $this->logRequestPerformance($request, $startTime, $startMemory, 500);
            
            throw $exception;
        }
    }

    /**
     * Log request performance metrics
     */
    protected function logRequestPerformance(Request $request, float $startTime, int $startMemory, int $statusCode): void
    {
        $executionTime = microtime(true) - $startTime;
        $memoryUsed = memory_get_usage(true) - $startMemory;
        
        $operation = $this->getOperationName($request);
        
        $metrics = [
            'route_name' => $request->route()?->getName(),
            'route_action' => $request->route()?->getActionName(),
            'http_method' => $request->method(),
            'status_code' => $statusCode,
            'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2),
            'query_count' => $this->getQueryCount(),
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
        ];

        $this->logger->logPerformanceMetrics($operation, $executionTime, $metrics);
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
        $method = $request->method();
        
        return "{$method} {$path}";
    }

    /**
     * Get database query count (if query logging is enabled)
     */
    protected function getQueryCount(): int
    {
        try {
            return count(\DB::getQueryLog());
        } catch (\Exception $e) {
            return 0;
        }
    }
}