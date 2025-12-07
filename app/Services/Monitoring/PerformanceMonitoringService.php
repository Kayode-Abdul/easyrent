<?php

namespace App\Services\Monitoring;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\Cache\EasyRentCacheInterface;
use Carbon\Carbon;

class PerformanceMonitoringService
{
    protected $cacheService;
    
    /**
     * Performance thresholds in milliseconds
     */
    private const SLOW_QUERY_THRESHOLD = 1000; // 1 second
    private const VERY_SLOW_QUERY_THRESHOLD = 3000; // 3 seconds
    private const HIGH_MEMORY_THRESHOLD = 50 * 1024 * 1024; // 50MB
    
    /**
     * Cache keys for performance metrics
     */
    private const PERF_METRICS_PREFIX = 'perf_metrics_';
    private const SLOW_QUERIES_PREFIX = 'slow_queries_';
    private const MEMORY_USAGE_PREFIX = 'memory_usage_';
    
    public function __construct(EasyRentCacheInterface $cacheService)
    {
        $this->cacheService = $cacheService;
    }
    
    /**
     * Monitor database query performance
     */
    public function monitorDatabasePerformance(): void
    {
        DB::listen(function ($query) {
            $executionTime = $query->time;
            
            // Log slow queries
            if ($executionTime > self::SLOW_QUERY_THRESHOLD) {
                $this->logSlowQuery($query->sql, $executionTime, $query->bindings);
            }
            
            // Cache query performance metrics
            $this->cacheQueryMetrics($query->sql, $executionTime);
        });
    }
    
    /**
     * Log slow database queries
     */
    protected function logSlowQuery(string $sql, float $executionTime, array $bindings): void
    {
        $severity = $executionTime > self::VERY_SLOW_QUERY_THRESHOLD ? 'critical' : 'warning';
        
        Log::channel('performance')->{$severity}('Slow database query detected', [
            'sql' => $sql,
            'execution_time_ms' => $executionTime,
            'bindings' => $bindings,
            'threshold' => self::SLOW_QUERY_THRESHOLD,
            'detected_at' => now()->toISOString()
        ]);
        
        // Cache slow query for analysis
        $cacheKey = self::SLOW_QUERIES_PREFIX . now()->format('Y-m-d-H');
        $slowQueries = Cache::get($cacheKey, []);
        
        $slowQueries[] = [
            'sql' => $sql,
            'execution_time' => $executionTime,
            'bindings' => $bindings,
            'detected_at' => now()->toISOString()
        ];
        
        Cache::put($cacheKey, $slowQueries, 1440); // 24 hours
    }
    
    /**
     * Cache query performance metrics
     */
    protected function cacheQueryMetrics(string $sql, float $executionTime): void
    {
        // Extract query type (SELECT, INSERT, UPDATE, DELETE)
        $queryType = strtoupper(trim(explode(' ', $sql)[0]));
        
        $this->cacheService->cachePerformanceMetrics(
            'database_query_' . strtolower($queryType),
            [
                'execution_time' => $executionTime / 1000, // Convert to seconds
                'memory_used' => memory_get_usage(true),
                'success' => true
            ]
        );
    }
    
    /**
     * Monitor memory usage for specific operations
     */
    public function monitorMemoryUsage(string $operation, callable $callback)
    {
        $startMemory = memory_get_usage(true);
        $startPeakMemory = memory_get_peak_usage(true);
        
        try {
            $result = $callback();
            
            $endMemory = memory_get_usage(true);
            $endPeakMemory = memory_get_peak_usage(true);
            
            $memoryUsed = $endMemory - $startMemory;
            $peakMemoryUsed = $endPeakMemory - $startPeakMemory;
            
            // Log high memory usage
            if ($memoryUsed > self::HIGH_MEMORY_THRESHOLD) {
                Log::channel('performance')->warning('High memory usage detected', [
                    'operation' => $operation,
                    'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2),
                    'peak_memory_mb' => round($peakMemoryUsed / 1024 / 1024, 2),
                    'threshold_mb' => round(self::HIGH_MEMORY_THRESHOLD / 1024 / 1024, 2),
                    'detected_at' => now()->toISOString()
                ]);
            }
            
            // Cache memory metrics
            $this->cacheMemoryMetrics($operation, $memoryUsed, $peakMemoryUsed);
            
            return $result;
            
        } catch (\Exception $e) {
            $endMemory = memory_get_usage(true);
            $memoryUsed = $endMemory - $startMemory;
            
            Log::channel('performance')->error('Operation failed with memory usage', [
                'operation' => $operation,
                'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2),
                'error' => $e->getMessage(),
                'detected_at' => now()->toISOString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Cache memory usage metrics
     */
    protected function cacheMemoryMetrics(string $operation, int $memoryUsed, int $peakMemoryUsed): void
    {
        $cacheKey = self::MEMORY_USAGE_PREFIX . $operation . '_' . now()->format('Y-m-d-H');
        
        $existingMetrics = Cache::get($cacheKey, [
            'operation' => $operation,
            'hour' => now()->format('Y-m-d-H'),
            'total_operations' => 0,
            'total_memory_used' => 0,
            'total_peak_memory' => 0,
            'max_memory_used' => 0,
            'max_peak_memory' => 0
        ]);
        
        $existingMetrics['total_operations']++;
        $existingMetrics['total_memory_used'] += $memoryUsed;
        $existingMetrics['total_peak_memory'] += $peakMemoryUsed;
        $existingMetrics['max_memory_used'] = max($existingMetrics['max_memory_used'], $memoryUsed);
        $existingMetrics['max_peak_memory'] = max($existingMetrics['max_peak_memory'], $peakMemoryUsed);
        
        $existingMetrics['avg_memory_used'] = $existingMetrics['total_memory_used'] / $existingMetrics['total_operations'];
        $existingMetrics['avg_peak_memory'] = $existingMetrics['total_peak_memory'] / $existingMetrics['total_operations'];
        $existingMetrics['last_updated'] = now()->toISOString();
        
        Cache::put($cacheKey, $existingMetrics, 1440); // 24 hours
    }
    
    /**
     * Monitor cache performance
     */
    public function monitorCachePerformance(string $operation, callable $callback)
    {
        $startTime = microtime(true);
        $cacheHit = false;
        
        try {
            $result = $callback();
            
            // Detect cache hits by checking if callback executed quickly
            $executionTime = microtime(true) - $startTime;
            $cacheHit = $executionTime < 0.001; // Less than 1ms suggests cache hit
            
            $this->cacheService->cachePerformanceMetrics(
                'cache_' . $operation,
                [
                    'execution_time' => $executionTime,
                    'cache_hit' => $cacheHit,
                    'success' => true
                ]
            );
            
            return $result;
            
        } catch (\Exception $e) {
            $executionTime = microtime(true) - $startTime;
            
            $this->cacheService->cachePerformanceMetrics(
                'cache_' . $operation,
                [
                    'execution_time' => $executionTime,
                    'cache_hit' => false,
                    'success' => false
                ]
            );
            
            throw $e;
        }
    }
    
    /**
     * Get performance summary for a specific time period
     */
    public function getPerformanceSummary(int $hours = 24): array
    {
        $summary = [
            'period_hours' => $hours,
            'generated_at' => now()->toISOString(),
            'database_performance' => $this->getDatabasePerformanceSummary($hours),
            'memory_performance' => $this->getMemoryPerformanceSummary($hours),
            'cache_performance' => $this->getCachePerformanceSummary($hours),
            'slow_queries' => $this->getSlowQueriesSummary($hours)
        ];
        
        return $summary;
    }
    
    /**
     * Get database performance summary
     */
    protected function getDatabasePerformanceSummary(int $hours): array
    {
        $queryTypes = ['select', 'insert', 'update', 'delete'];
        $summary = [];
        
        foreach ($queryTypes as $type) {
            $metrics = $this->cacheService->getPerformanceMetricsRange(
                'database_query_' . $type, 
                $hours
            );
            
            if (!empty($metrics)) {
                $totalRequests = array_sum(array_column($metrics, 'total_requests'));
                $avgExecutionTime = array_sum(array_column($metrics, 'avg_execution_time')) / count($metrics);
                $successRate = array_sum(array_column($metrics, 'success_rate')) / count($metrics);
                
                $summary[$type] = [
                    'total_queries' => $totalRequests,
                    'avg_execution_time_ms' => round($avgExecutionTime * 1000, 2),
                    'success_rate' => round($successRate * 100, 1) . '%'
                ];
            }
        }
        
        return $summary;
    }
    
    /**
     * Get memory performance summary
     */
    protected function getMemoryPerformanceSummary(int $hours): array
    {
        $operations = ['apartment_invitation_access', 'login', 'register', 'payment_process'];
        $summary = [];
        
        foreach ($operations as $operation) {
            $memoryMetrics = [];
            
            for ($i = 0; $i < $hours; $i++) {
                $hour = now()->subHours($i)->format('Y-m-d-H');
                $cacheKey = self::MEMORY_USAGE_PREFIX . $operation . '_' . $hour;
                $hourMetrics = Cache::get($cacheKey);
                
                if ($hourMetrics) {
                    $memoryMetrics[] = $hourMetrics;
                }
            }
            
            if (!empty($memoryMetrics)) {
                $totalOperations = array_sum(array_column($memoryMetrics, 'total_operations'));
                $avgMemoryUsed = array_sum(array_column($memoryMetrics, 'avg_memory_used')) / count($memoryMetrics);
                $maxMemoryUsed = max(array_column($memoryMetrics, 'max_memory_used'));
                
                $summary[$operation] = [
                    'total_operations' => $totalOperations,
                    'avg_memory_mb' => round($avgMemoryUsed / 1024 / 1024, 2),
                    'max_memory_mb' => round($maxMemoryUsed / 1024 / 1024, 2)
                ];
            }
        }
        
        return $summary;
    }
    
    /**
     * Get cache performance summary
     */
    protected function getCachePerformanceSummary(int $hours): array
    {
        $cacheOperations = ['apartment_data', 'invitation_data', 'session_data', 'user_data'];
        $summary = [];
        
        foreach ($cacheOperations as $operation) {
            $metrics = $this->cacheService->getPerformanceMetricsRange(
                'cache_' . $operation, 
                $hours
            );
            
            if (!empty($metrics)) {
                $totalRequests = array_sum(array_column($metrics, 'total_requests'));
                $successCount = array_sum(array_column($metrics, 'success_count'));
                $cacheHitRate = $successCount > 0 ? ($successCount / $totalRequests) * 100 : 0;
                
                $summary[$operation] = [
                    'total_requests' => $totalRequests,
                    'cache_hit_rate' => round($cacheHitRate, 1) . '%',
                    'success_rate' => round(($successCount / $totalRequests) * 100, 1) . '%'
                ];
            }
        }
        
        return $summary;
    }
    
    /**
     * Get slow queries summary
     */
    protected function getSlowQueriesSummary(int $hours): array
    {
        $slowQueries = [];
        
        for ($i = 0; $i < $hours; $i++) {
            $hour = now()->subHours($i)->format('Y-m-d-H');
            $cacheKey = self::SLOW_QUERIES_PREFIX . $hour;
            $hourQueries = Cache::get($cacheKey, []);
            
            $slowQueries = array_merge($slowQueries, $hourQueries);
        }
        
        // Sort by execution time (slowest first)
        usort($slowQueries, function($a, $b) {
            return $b['execution_time'] <=> $a['execution_time'];
        });
        
        return [
            'total_slow_queries' => count($slowQueries),
            'slowest_queries' => array_slice($slowQueries, 0, 10), // Top 10 slowest
            'avg_execution_time' => count($slowQueries) > 0 ? 
                round(array_sum(array_column($slowQueries, 'execution_time')) / count($slowQueries), 2) : 0
        ];
    }
    
    /**
     * Generate performance recommendations
     */
    public function generatePerformanceRecommendations(): array
    {
        $summary = $this->getPerformanceSummary(24);
        $recommendations = [];
        
        // Database performance recommendations
        foreach ($summary['database_performance'] as $queryType => $metrics) {
            if (isset($metrics['avg_execution_time_ms']) && $metrics['avg_execution_time_ms'] > 500) {
                $recommendations[] = [
                    'type' => 'database',
                    'severity' => 'medium',
                    'message' => "Average {$queryType} query time is {$metrics['avg_execution_time_ms']}ms. Consider adding indexes or optimizing queries.",
                    'metric' => $metrics
                ];
            }
        }
        
        // Memory usage recommendations
        foreach ($summary['memory_performance'] as $operation => $metrics) {
            if (isset($metrics['avg_memory_mb']) && $metrics['avg_memory_mb'] > 25) {
                $recommendations[] = [
                    'type' => 'memory',
                    'severity' => 'medium',
                    'message' => "Operation '{$operation}' uses {$metrics['avg_memory_mb']}MB on average. Consider optimizing data structures or implementing pagination.",
                    'metric' => $metrics
                ];
            }
        }
        
        // Cache performance recommendations
        foreach ($summary['cache_performance'] as $operation => $metrics) {
            $hitRate = (float) str_replace('%', '', $metrics['cache_hit_rate']);
            if ($hitRate < 80) {
                $recommendations[] = [
                    'type' => 'cache',
                    'severity' => 'low',
                    'message' => "Cache hit rate for '{$operation}' is {$metrics['cache_hit_rate']}. Consider increasing cache TTL or pre-warming cache.",
                    'metric' => $metrics
                ];
            }
        }
        
        // Slow queries recommendations
        if ($summary['slow_queries']['total_slow_queries'] > 10) {
            $recommendations[] = [
                'type' => 'database',
                'severity' => 'high',
                'message' => "Found {$summary['slow_queries']['total_slow_queries']} slow queries in the last 24 hours. Review and optimize the slowest queries.",
                'metric' => $summary['slow_queries']
            ];
        }
        
        return [
            'recommendations' => $recommendations,
            'generated_at' => now()->toISOString(),
            'total_recommendations' => count($recommendations)
        ];
    }
}