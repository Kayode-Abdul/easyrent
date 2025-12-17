<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\Cache\PaymentCalculationCacheService;
use App\Services\Monitoring\PaymentCalculationMonitoringService;

class PaymentCalculationPerformanceMiddleware
{
    /**
     * Performance monitoring service
     */
    protected $monitoringService;
    protected $cacheService;
    
    /**
     * Performance thresholds
     */
    private const SLOW_REQUEST_THRESHOLD = 1000; // 1 second
    private const HIGH_MEMORY_THRESHOLD = 50 * 1024 * 1024; // 50MB
    
    public function __construct(
        PaymentCalculationMonitoringService $monitoringService,
        PaymentCalculationCacheService $cacheService
    ) {
        $this->monitoringService = $monitoringService;
        $this->cacheService = $cacheService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip monitoring if not enabled
        if (!config('payment_calculation.performance.enable_performance_monitoring', true)) {
            return $next($request);
        }
        
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        $requestId = uniqid('req_');
        
        // Add request ID to request for tracking
        $request->attributes->set('payment_calc_request_id', $requestId);
        
        // Log request start for payment calculation endpoints
        if ($this->isPaymentCalculationRequest($request)) {
            Log::debug('Payment calculation request started', [
                'request_id' => $requestId,
                'method' => $request->method(),
                'url' => $request->url(),
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip(),
                'memory_start' => $startMemory
            ]);
        }
        
        $response = $next($request);
        
        // Calculate performance metrics
        $executionTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
        $memoryUsed = memory_get_usage(true) - $startMemory;
        $peakMemory = memory_get_peak_usage(true);
        
        // Record performance metrics for payment calculation requests
        if ($this->isPaymentCalculationRequest($request)) {
            $this->recordRequestPerformance($request, $response, [
                'request_id' => $requestId,
                'execution_time_ms' => $executionTime,
                'memory_used' => $memoryUsed,
                'peak_memory' => $peakMemory,
                'response_status' => $response->getStatusCode()
            ]);
        }
        
        // Add performance headers to response for debugging
        if (config('app.debug') && $this->isPaymentCalculationRequest($request)) {
            $response->headers->set('X-Payment-Calc-Time', round($executionTime, 2) . 'ms');
            $response->headers->set('X-Payment-Calc-Memory', $this->formatBytes($memoryUsed));
            $response->headers->set('X-Payment-Calc-Request-ID', $requestId);
        }
        
        return $response;
    }
    
    /**
     * Check if request is related to payment calculations
     */
    protected function isPaymentCalculationRequest(Request $request): bool
    {
        $paymentCalculationRoutes = [
            'proforma',
            'payment',
            'apartment/invite',
            'api/payment',
            'api/proforma',
            'billing'
        ];
        
        $path = $request->path();
        
        foreach ($paymentCalculationRoutes as $route) {
            if (str_contains($path, $route)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Record request performance metrics
     */
    protected function recordRequestPerformance(Request $request, $response, array $metrics): void
    {
        $performanceData = [
            'request_id' => $metrics['request_id'],
            'method' => $request->method(),
            'path' => $request->path(),
            'execution_time_ms' => $metrics['execution_time_ms'],
            'memory_used' => $metrics['memory_used'],
            'peak_memory' => $metrics['peak_memory'],
            'response_status' => $metrics['response_status'],
            'success' => $response->isSuccessful(),
            'timestamp' => now()->toISOString()
        ];
        
        // Cache performance metrics
        $this->cacheService->cachePerformanceMetrics('http_request', $performanceData);
        
        // Log slow requests
        if ($metrics['execution_time_ms'] > self::SLOW_REQUEST_THRESHOLD) {
            Log::warning('Slow payment calculation request detected', [
                'request_id' => $metrics['request_id'],
                'execution_time_ms' => $metrics['execution_time_ms'],
                'threshold_ms' => self::SLOW_REQUEST_THRESHOLD,
                'path' => $request->path(),
                'method' => $request->method(),
                'memory_used' => $this->formatBytes($metrics['memory_used'])
            ]);
        }
        
        // Log high memory usage
        if ($metrics['memory_used'] > self::HIGH_MEMORY_THRESHOLD) {
            Log::warning('High memory usage in payment calculation request', [
                'request_id' => $metrics['request_id'],
                'memory_used' => $this->formatBytes($metrics['memory_used']),
                'peak_memory' => $this->formatBytes($metrics['peak_memory']),
                'threshold' => $this->formatBytes(self::HIGH_MEMORY_THRESHOLD),
                'path' => $request->path()
            ]);
        }
        
        // Record detailed metrics for monitoring dashboard
        $this->recordDetailedMetrics($request, $performanceData);
    }
    
    /**
     * Record detailed metrics for monitoring dashboard
     */
    protected function recordDetailedMetrics(Request $request, array $performanceData): void
    {
        // Extract calculation-specific parameters if available
        $calculationParams = $this->extractCalculationParameters($request);
        
        if (!empty($calculationParams)) {
            $performanceData['calculation_params'] = $calculationParams;
            
            // Record specific calculation performance
            $this->monitoringService->recordCalculationPerformance(
                $performanceData['request_id'],
                $performanceData['execution_time_ms'],
                // Create a mock result for HTTP request tracking
                new \App\Services\Payment\PaymentCalculationResult(
                    0, // We don't have the actual amount here
                    'http_request_tracking',
                    [],
                    $performanceData['success'],
                    $performanceData['success'] ? null : 'HTTP request failed'
                ),
                $calculationParams
            );
        }
    }
    
    /**
     * Extract calculation parameters from request
     */
    protected function extractCalculationParameters(Request $request): array
    {
        $params = [];
        
        // Extract common calculation parameters
        if ($request->has('apartment_price')) {
            $params['apartment_price'] = (float) $request->input('apartment_price');
        }
        
        if ($request->has('rental_duration')) {
            $params['rental_duration'] = (int) $request->input('rental_duration');
        }
        
        if ($request->has('pricing_type')) {
            $params['pricing_type'] = $request->input('pricing_type');
        }
        
        if ($request->has('apartment_id')) {
            $params['apartment_id'] = (int) $request->input('apartment_id');
        }
        
        // Extract from route parameters
        if ($request->route()) {
            $routeParams = $request->route()->parameters();
            
            if (isset($routeParams['apartment'])) {
                $params['apartment_id'] = (int) $routeParams['apartment'];
            }
            
            if (isset($routeParams['proforma'])) {
                $params['proforma_id'] = (int) $routeParams['proforma'];
            }
        }
        
        return $params;
    }
    
    /**
     * Format bytes to human readable format
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}