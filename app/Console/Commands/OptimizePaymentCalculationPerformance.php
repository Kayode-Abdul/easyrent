<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Payment\PaymentCalculationService;
use App\Services\Cache\PaymentCalculationCacheService;
use App\Services\Payment\PaymentCalculationQueryOptimizer;
use App\Services\Monitoring\PaymentCalculationMonitoringService;

class OptimizePaymentCalculationPerformance extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'payment-calc:optimize 
                            {--cache-warmup : Warm up calculation cache with common scenarios}
                            {--query-optimize : Optimize database queries and indexes}
                            {--performance-analysis : Run performance analysis and generate report}
                            {--cleanup-cache : Clean up expired cache entries}
                            {--pre-calculate : Pre-calculate common scenarios}
                            {--all : Run all optimization tasks}';

    /**
     * The console command description.
     */
    protected $description = 'Optimize payment calculation performance through caching, query optimization, and monitoring';

    /**
     * Services
     */
    protected $calculationService;
    protected $cacheService;
    protected $queryOptimizer;
    protected $monitoringService;

    /**
     * Create a new command instance.
     */
    public function __construct(
        PaymentCalculationService $calculationService,
        PaymentCalculationCacheService $cacheService,
        PaymentCalculationQueryOptimizer $queryOptimizer,
        PaymentCalculationMonitoringService $monitoringService
    ) {
        parent::__construct();
        
        $this->calculationService = $calculationService;
        $this->cacheService = $cacheService;
        $this->queryOptimizer = $queryOptimizer;
        $this->monitoringService = $monitoringService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Payment Calculation Performance Optimization');
        $this->newLine();
        
        $startTime = microtime(true);
        
        // Determine which optimizations to run
        $runAll = $this->option('all');
        $tasks = [];
        
        if ($runAll || $this->option('cache-warmup')) {
            $tasks[] = 'cache-warmup';
        }
        
        if ($runAll || $this->option('query-optimize')) {
            $tasks[] = 'query-optimize';
        }
        
        if ($runAll || $this->option('performance-analysis')) {
            $tasks[] = 'performance-analysis';
        }
        
        if ($runAll || $this->option('cleanup-cache')) {
            $tasks[] = 'cleanup-cache';
        }
        
        if ($runAll || $this->option('pre-calculate')) {
            $tasks[] = 'pre-calculate';
        }
        
        if (empty($tasks)) {
            $this->error('No optimization tasks specified. Use --help to see available options.');
            return 1;
        }
        
        // Execute optimization tasks
        foreach ($tasks as $task) {
            $this->executeOptimizationTask($task);
        }
        
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        
        $this->newLine();
        $this->info("Payment calculation optimization completed in {$executionTime}ms");
        
        // Display summary
        $this->displayOptimizationSummary();
        
        return 0;
    }
    
    /**
     * Execute a specific optimization task
     */
    protected function executeOptimizationTask(string $task): void
    {
        $this->info("Executing: " . str_replace('-', ' ', ucfirst($task)));
        
        $taskStartTime = microtime(true);
        
        try {
            switch ($task) {
                case 'cache-warmup':
                    $this->executeCacheWarmup();
                    break;
                    
                case 'query-optimize':
                    $this->executeQueryOptimization();
                    break;
                    
                case 'performance-analysis':
                    $this->executePerformanceAnalysis();
                    break;
                    
                case 'cleanup-cache':
                    $this->executeCacheCleanup();
                    break;
                    
                case 'pre-calculate':
                    $this->executePreCalculation();
                    break;
                    
                default:
                    $this->warn("Unknown optimization task: {$task}");
                    return;
            }
            
            $taskTime = round((microtime(true) - $taskStartTime) * 1000, 2);
            $this->line("  ✓ Completed in {$taskTime}ms");
            
        } catch (\Exception $e) {
            $this->error("  ✗ Failed: " . $e->getMessage());
        }
        
        $this->newLine();
    }
    
    /**
     * Execute cache warmup optimization
     */
    protected function executeCacheWarmup(): void
    {
        $this->line('  • Warming up calculation cache...');
        
        // Warm up cache with common scenarios
        $this->cacheService->warmUpCalculationCache();
        
        // Get frequently used configurations
        $frequentConfigs = $this->queryOptimizer->getFrequentlyUsedPricingConfigurations(50);
        $this->line("  • Loaded {count($frequentConfigs)} frequent pricing configurations");
        
        $this->line('  • Cache warmup completed');
    }
    
    /**
     * Execute query optimization
     */
    protected function executeQueryOptimization(): void
    {
        $this->line('  • Optimizing database queries...');
        
        // Optimize pricing configuration queries
        $this->queryOptimizer->optimizePricingConfigurationQueries();
        
        // Create optimized indexes
        $this->queryOptimizer->createOptimizedIndexes();
        
        $this->line('  • Database query optimization completed');
    }
    
    /**
     * Execute performance analysis
     */
    protected function executePerformanceAnalysis(): void
    {
        $this->line('  • Running performance analysis...');
        
        // Get calculation statistics
        $stats = $this->queryOptimizer->getCalculationStatistics(30);
        $this->line("  • Analyzed {$stats['total_calculations']} calculations from last 30 days");
        
        // Get performance metrics
        $performanceMetrics = $this->calculationService->getPerformanceMetrics(24);
        $this->line("  • Performance metrics collected for last 24 hours");
        
        // Identify slow queries
        $slowQueries = $this->queryOptimizer->identifySlowCalculationQueries();
        $this->line("  • Identified performance optimization opportunities");
        
        // Generate performance report
        $this->generatePerformanceReport($stats, $performanceMetrics, $slowQueries);
    }
    
    /**
     * Execute cache cleanup
     */
    protected function executeCacheCleanup(): void
    {
        $this->line('  • Cleaning up expired cache entries...');
        
        $cleanedCount = $this->cacheService->cleanupCalculationCache();
        $this->line("  • Cleaned up {$cleanedCount} expired cache entries");
    }
    
    /**
     * Execute pre-calculation of common scenarios
     */
    protected function executePreCalculation(): void
    {
        $this->line('  • Pre-calculating common scenarios...');
        
        $this->calculationService->preCalculateCommonScenarios();
        
        $this->line('  • Common scenarios pre-calculated and cached');
    }
    
    /**
     * Generate performance report
     */
    protected function generatePerformanceReport(array $stats, array $performanceMetrics, array $slowQueries): void
    {
        $this->newLine();
        $this->info('Performance Analysis Report');
        $this->line('========================');
        
        // Calculation volume
        $this->line("Total calculations (30 days): {$stats['total_calculations']}");
        $this->line("Average daily calculations: " . round($stats['total_calculations'] / 30, 0));
        
        // Performance metrics
        if (isset($performanceMetrics['avg_execution_time_ms'])) {
            $this->line("Average execution time: {$performanceMetrics['avg_execution_time_ms']}ms");
        }
        
        if (isset($performanceMetrics['success_rate'])) {
            $this->line("Success rate: {$performanceMetrics['success_rate']}%");
        }
        
        // Cache performance
        if (isset($performanceMetrics['cache_performance']['cache_hit_rate'])) {
            $this->line("Cache hit rate: {$performanceMetrics['cache_performance']['cache_hit_rate']}%");
        }
        
        // High volume apartments
        if (!empty($slowQueries['high_volume_apartments'])) {
            $this->newLine();
            $this->line('High Volume Apartments:');
            foreach (array_slice($slowQueries['high_volume_apartments'], 0, 5) as $apartment) {
                $this->line("  • Apartment {$apartment['apartment_id']}: {$apartment['calculation_count']} calculations");
            }
        }
        
        // Pricing type distribution
        if (!empty($stats['pricing_type_distribution'])) {
            $this->newLine();
            $this->line('Pricing Type Distribution:');
            foreach ($stats['pricing_type_distribution'] as $typeStats) {
                $this->line("  • {$typeStats['pricing_type']}: {$typeStats['apartment_count']} apartments");
            }
        }
    }
    
    /**
     * Display optimization summary
     */
    protected function displayOptimizationSummary(): void
    {
        $this->info('Optimization Summary');
        $this->line('==================');
        
        // Get current cache statistics
        $cacheStats = $this->cacheService->getCacheUsageSummary();
        $this->line("Cache enabled: " . ($cacheStats['cache_enabled'] ? 'Yes' : 'No'));
        $this->line("Cache driver: {$cacheStats['cache_driver']}");
        
        // Get performance metrics
        $performanceMetrics = $this->monitoringService->getPerformanceMetrics(1); // Last hour
        $this->line("Recent calculations: {$performanceMetrics['total_calculations']}");
        $this->line("Success rate: {$performanceMetrics['success_rate']}%");
        
        if ($performanceMetrics['avg_execution_time_ms'] > 0) {
            $this->line("Average execution time: {$performanceMetrics['avg_execution_time_ms']}ms");
        }
        
        $this->newLine();
        $this->info('Recommendations:');
        
        // Generate recommendations based on current state
        $recommendations = $this->generateOptimizationRecommendations($performanceMetrics, $cacheStats);
        
        foreach ($recommendations as $recommendation) {
            $this->line("  • {$recommendation}");
        }
    }
    
    /**
     * Generate optimization recommendations
     */
    protected function generateOptimizationRecommendations(array $performanceMetrics, array $cacheStats): array
    {
        $recommendations = [];
        
        // Cache recommendations
        if (!$cacheStats['cache_enabled']) {
            $recommendations[] = 'Enable caching in payment_calculation.php config for better performance';
        }
        
        // Performance recommendations
        if (isset($performanceMetrics['avg_execution_time_ms']) && $performanceMetrics['avg_execution_time_ms'] > 50) {
            $recommendations[] = 'Consider optimizing slow calculations or increasing cache TTL';
        }
        
        if (isset($performanceMetrics['success_rate']) && $performanceMetrics['success_rate'] < 95) {
            $recommendations[] = 'Investigate calculation errors to improve success rate';
        }
        
        // Volume recommendations
        if ($performanceMetrics['total_calculations'] > 1000) {
            $recommendations[] = 'High calculation volume detected - consider implementing bulk calculation APIs';
        }
        
        // Default recommendations
        if (empty($recommendations)) {
            $recommendations[] = 'System performance is optimal - continue monitoring';
            $recommendations[] = 'Run this optimization command regularly for best performance';
        }
        
        return $recommendations;
    }
}