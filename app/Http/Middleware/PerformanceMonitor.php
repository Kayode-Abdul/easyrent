<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PerformanceMonitor
{
    /**
     * Handle an incoming request and monitor performance
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        // Count initial DB queries
        $initialQueryCount = count(DB::getQueryLog());
        DB::enableQueryLog();

        $response = $next($request);

        // Calculate performance metrics
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        $memoryUsage = $endMemory - $startMemory;
        $queryCount = count(DB::getQueryLog()) - $initialQueryCount;

        // Log performance data
        $this->logPerformanceData($request, $response, [
            'execution_time' => $executionTime,
            'memory_usage' => $memoryUsage,
            'query_count' => $queryCount,
            'status_code' => $response->getStatusCode()
        ]);

        // Add performance headers for debugging
        if (config('app.debug')) {
            $response->headers->set('X-Execution-Time', round($executionTime, 2) . 'ms');
            $response->headers->set('X-Memory-Usage', $this->formatBytes($memoryUsage));
            $response->headers->set('X-Query-Count', $queryCount);
        }

        return $response;
    }

    /**
     * Log performance data to database
     */
    private function logPerformanceData(Request $request, $response, array $metrics)
    {
        try {
            // Create performance_logs table if it doesn't exist
            if (!DB::getSchemaBuilder()->hasTable('performance_logs')) {
                DB::statement("
                    CREATE TABLE performance_logs (
                        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        user_id BIGINT UNSIGNED NULL,
                        method VARCHAR(10) NOT NULL,
                        url VARCHAR(500) NOT NULL,
                        route_name VARCHAR(255) NULL,
                        controller_action VARCHAR(255) NULL,
                        status_code INT NOT NULL,
                        execution_time DECIMAL(8,2) NOT NULL,
                        memory_usage BIGINT NOT NULL,
                        query_count INT NOT NULL,
                        ip_address VARCHAR(45) NOT NULL,
                        user_agent TEXT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_execution_time (execution_time),
                        INDEX idx_created_at (created_at),
                        INDEX idx_status_code (status_code),
                        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
                    )
                ");
            }

            // Only log if execution time is significant or there's an error
            if ($metrics['execution_time'] > 100 || $metrics['status_code'] >= 400) {
                DB::table('performance_logs')->insert([
                    'user_id' => auth()->check() ? auth()->user()->user_id : null,
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'route_name' => $request->route() ? $request->route()->getName() : null,
                    'controller_action' => $this->getControllerAction($request),
                    'status_code' => $metrics['status_code'],
                    'execution_time' => $metrics['execution_time'],
                    'memory_usage' => $metrics['memory_usage'],
                    'query_count' => $metrics['query_count'],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => now()
                ]);
            }

            // Log slow queries separately
            if ($metrics['execution_time'] > 1000) { // Slower than 1 second
                Log::warning('Slow request detected', [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'execution_time' => $metrics['execution_time'] . 'ms',
                    'memory_usage' => $this->formatBytes($metrics['memory_usage']),
                    'query_count' => $metrics['query_count'],
                    'user_id' => auth()->check() ? auth()->user()->user_id : null
                ]);
            }

        } catch (\Exception $e) {
            // Don't let performance monitoring break the application
            Log::error('Performance monitoring failed: ' . $e->getMessage());
        }
    }

    /**
     * Get controller action from request
     */
    private function getControllerAction(Request $request): ?string
    {
        if ($request->route()) {
            $action = $request->route()->getAction();
            if (isset($action['controller'])) {
                return $action['controller'];
            }
        }
        return null;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}