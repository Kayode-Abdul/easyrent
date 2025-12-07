<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Monitoring\PerformanceMonitoringService;
use App\Services\Cache\EasyRentCacheInterface;
use Illuminate\Support\Facades\Log;

class MonitorEasyRentPerformance extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'easyrent:monitor-performance 
                            {--hours=24 : Number of hours to analyze}
                            {--summary : Show performance summary}
                            {--recommendations : Generate performance recommendations}
                            {--export= : Export results to file}
                            {--all : Run all monitoring tasks}';

    /**
     * The console command description.
     */
    protected $description = 'Monitor EasyRent system performance and generate recommendations';

    protected $performanceMonitor;
    protected $cacheService;

    public function __construct(
        PerformanceMonitoringService $performanceMonitor,
        EasyRentCacheInterface $cacheService
    ) {
        parent::__construct();
        $this->performanceMonitor = $performanceMonitor;
        $this->cacheService = $cacheService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = (int) $this->option('hours');
        $this->info("Monitoring EasyRent performance for the last {$hours} hours...");

        if ($this->option('all')) {
            $this->showPerformanceSummary($hours);
            $this->generateRecommendations();
            $this->showCacheStatistics();
        } else {
            if ($this->option('summary')) {
                $this->showPerformanceSummary($hours);
            }

            if ($this->option('recommendations')) {
                $this->generateRecommendations();
            }
        }

        if ($this->option('export')) {
            $this->exportResults($hours);
        }

        $this->info('Performance monitoring completed!');
        return 0;
    }

    /**
     * Show performance summary
     */
    protected function showPerformanceSummary(int $hours): void
    {
        $this->info('Performance Summary:');
        $this->newLine();

        try {
            $summary = $this->performanceMonitor->getPerformanceSummary($hours);

            // Database Performance
            if (!empty($summary['database_performance'])) {
                $this->info('Database Performance:');
                $dbData = [];
                foreach ($summary['database_performance'] as $queryType => $metrics) {
                    $dbData[] = [
                        ucfirst($queryType),
                        $metrics['total_queries'] ?? 0,
                        $metrics['avg_execution_time_ms'] ?? 0,
                        $metrics['success_rate'] ?? '100%'
                    ];
                }
                $this->table(
                    ['Query Type', 'Total Queries', 'Avg Time (ms)', 'Success Rate'],
                    $dbData
                );
                $this->newLine();
            }

            // Memory Performance
            if (!empty($summary['memory_performance'])) {
                $this->info('Memory Performance:');
                $memoryData = [];
                foreach ($summary['memory_performance'] as $operation => $metrics) {
                    $memoryData[] = [
                        $operation,
                        $metrics['total_operations'] ?? 0,
                        $metrics['avg_memory_mb'] ?? 0,
                        $metrics['max_memory_mb'] ?? 0
                    ];
                }
                $this->table(
                    ['Operation', 'Total Ops', 'Avg Memory (MB)', 'Max Memory (MB)'],
                    $memoryData
                );
                $this->newLine();
            }

            // Cache Performance
            if (!empty($summary['cache_performance'])) {
                $this->info('Cache Performance:');
                $cacheData = [];
                foreach ($summary['cache_performance'] as $operation => $metrics) {
                    $cacheData[] = [
                        $operation,
                        $metrics['total_requests'] ?? 0,
                        $metrics['cache_hit_rate'] ?? '0%',
                        $metrics['success_rate'] ?? '100%'
                    ];
                }
                $this->table(
                    ['Cache Operation', 'Total Requests', 'Hit Rate', 'Success Rate'],
                    $cacheData
                );
                $this->newLine();
            }

            // Slow Queries
            if (!empty($summary['slow_queries']) && $summary['slow_queries']['total_slow_queries'] > 0) {
                $this->warn("Slow Queries: {$summary['slow_queries']['total_slow_queries']} detected");
                $this->info("Average execution time: {$summary['slow_queries']['avg_execution_time']}ms");
                
                if (!empty($summary['slow_queries']['slowest_queries'])) {
                    $this->info('Top 5 Slowest Queries:');
                    $slowQueryData = [];
                    foreach (array_slice($summary['slow_queries']['slowest_queries'], 0, 5) as $query) {
                        $slowQueryData[] = [
                            substr($query['sql'], 0, 50) . '...',
                            round($query['execution_time'], 2) . 'ms',
                            $query['detected_at']
                        ];
                    }
                    $this->table(
                        ['Query', 'Execution Time', 'Detected At'],
                        $slowQueryData
                    );
                }
                $this->newLine();
            }

        } catch (\Exception $e) {
            $this->error('Failed to retrieve performance summary: ' . $e->getMessage());
            Log::error('Performance monitoring failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Generate and display performance recommendations
     */
    protected function generateRecommendations(): void
    {
        $this->info('Performance Recommendations:');
        $this->newLine();

        try {
            $recommendations = $this->performanceMonitor->generatePerformanceRecommendations();

            if (empty($recommendations['recommendations'])) {
                $this->info('✅ No performance issues detected. System is running optimally!');
                return;
            }

            $this->warn("Found {$recommendations['total_recommendations']} performance recommendations:");
            $this->newLine();

            foreach ($recommendations['recommendations'] as $index => $recommendation) {
                $severityColor = match($recommendation['severity']) {
                    'high' => 'error',
                    'medium' => 'warn',
                    'low' => 'info',
                    default => 'line'
                };

                $this->{$severityColor}(($index + 1) . ". [{$recommendation['type']}] {$recommendation['message']}");
            }

            $this->newLine();
            $this->info('💡 Run `php artisan easyrent:optimize-cache --all` to apply automatic optimizations.');

        } catch (\Exception $e) {
            $this->error('Failed to generate recommendations: ' . $e->getMessage());
            Log::error('Performance recommendations failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Show cache statistics
     */
    protected function showCacheStatistics(): void
    {
        $this->info('Cache Statistics:');
        $this->newLine();

        try {
            $stats = $this->cacheService->getCacheStatistics();

            $this->table(
                ['Metric', 'Value'],
                [
                    ['Cache Driver', $stats['cache_driver']],
                    ['Cache Prefix', $stats['cache_prefix'] ?: 'None'],
                    ['Generated At', $stats['statistics_generated_at']],
                ]
            );

            // Redis-specific statistics
            if (isset($stats['redis_stats'])) {
                $this->newLine();
                $this->info('Redis Statistics:');
                $redisData = [];
                foreach ($stats['redis_stats'] as $key => $value) {
                    $redisData[] = [ucwords(str_replace('_', ' ', $key)), $value];
                }
                $this->table(['Metric', 'Value'], $redisData);
            }

            // EasyRent cache configuration
            if (isset($stats['easyrent_cache_metrics'])) {
                $this->newLine();
                $this->info('EasyRent Cache Configuration:');
                $configData = [];
                foreach ($stats['easyrent_cache_metrics'] as $key => $value) {
                    $configData[] = [ucwords(str_replace('_', ' ', $key)), $value];
                }
                $this->table(['Setting', 'Value'], $configData);
            }

        } catch (\Exception $e) {
            $this->error('Failed to retrieve cache statistics: ' . $e->getMessage());
        }
    }

    /**
     * Export performance results to file
     */
    protected function exportResults(int $hours): void
    {
        $exportFile = $this->option('export');
        $this->info("Exporting performance data to {$exportFile}...");

        try {
            $summary = $this->performanceMonitor->getPerformanceSummary($hours);
            $recommendations = $this->performanceMonitor->generatePerformanceRecommendations();
            $cacheStats = $this->cacheService->getCacheStatistics();

            $exportData = [
                'export_info' => [
                    'generated_at' => now()->toISOString(),
                    'period_hours' => $hours,
                    'command' => $this->signature
                ],
                'performance_summary' => $summary,
                'recommendations' => $recommendations,
                'cache_statistics' => $cacheStats
            ];

            $jsonData = json_encode($exportData, JSON_PRETTY_PRINT);
            
            if (file_put_contents($exportFile, $jsonData)) {
                $this->info("✅ Performance data exported successfully to {$exportFile}");
                $this->info("📊 File size: " . round(filesize($exportFile) / 1024, 2) . " KB");
            } else {
                $this->error("❌ Failed to write to {$exportFile}");
            }

        } catch (\Exception $e) {
            $this->error("Export failed: " . $e->getMessage());
            Log::error('Performance export failed', [
                'file' => $exportFile,
                'error' => $e->getMessage()
            ]);
        }
    }
}