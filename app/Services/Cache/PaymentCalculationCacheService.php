<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\Payment\PaymentCalculationResult;
use Carbon\Carbon;

class PaymentCalculationCacheService
{
    /**
     * Cache key prefixes for different calculation types
     */
    private const CALCULATION_PREFIX = 'payment_calc_';
    private const PRICING_CONFIG_PREFIX = 'pricing_config_';
    private const APARTMENT_PRICING_PREFIX = 'apartment_pricing_';
    private const BULK_CALCULATION_PREFIX = 'bulk_calc_';
    private const PERFORMANCE_METRICS_PREFIX = 'calc_perf_';
    
    /**
     * Cache TTL configurations in minutes
     */
    private const CALCULATION_TTL = 60; // 1 hour for calculation results
    private const PRICING_CONFIG_TTL = 240; // 4 hours for pricing configurations
    private const APARTMENT_PRICING_TTL = 120; // 2 hours for apartment pricing data
    private const BULK_CALCULATION_TTL = 30; // 30 minutes for bulk calculations
    private const PERFORMANCE_METRICS_TTL = 15; // 15 minutes for performance metrics
    
    /**
     * Cache size limits
     */
    private const MAX_CACHE_KEY_LENGTH = 250;
    private const MAX_CACHED_CALCULATIONS_PER_HOUR = 10000;
    
    /**
     * Cache a payment calculation result
     */
    public function cacheCalculationResult(
        float $apartmentPrice,
        int $rentalDuration,
        string $pricingType,
        PaymentCalculationResult $result
    ): void {
        if (!config('payment_calculation.caching.enable_result_caching', false)) {
            return;
        }
        
        $cacheKey = $this->generateCalculationCacheKey($apartmentPrice, $rentalDuration, $pricingType);
        
        $cacheData = [
            'result' => [
                'total_amount' => $result->totalAmount,
                'calculation_method' => $result->calculationMethod,
                'is_valid' => $result->isValid,
                'error_message' => $result->errorMessage,
                'calculation_steps' => $result->calculationSteps
            ],
            'inputs' => [
                'apartment_price' => $apartmentPrice,
                'rental_duration' => $rentalDuration,
                'pricing_type' => $pricingType
            ],
            'cached_at' => now()->toISOString(),
            'cache_version' => '1.0'
        ];
        
        $ttl = config('payment_calculation.caching.cache_ttl', self::CALCULATION_TTL);
        Cache::put($cacheKey, $cacheData, $ttl);
        
        // Track cache usage metrics
        $this->recordCacheMetrics('calculation_cached', [
            'cache_key' => $cacheKey,
            'result_valid' => $result->isValid,
            'pricing_type' => $pricingType,
            'ttl_minutes' => $ttl
        ]);
        
        Log::debug('Payment calculation result cached', [
            'cache_key' => substr($cacheKey, 0, 50) . '...',
            'apartment_price' => $apartmentPrice,
            'rental_duration' => $rentalDuration,
            'pricing_type' => $pricingType,
            'result_valid' => $result->isValid,
            'ttl_minutes' => $ttl
        ]);
    }
    
    /**
     * Get cached calculation result
     */
    public function getCachedCalculationResult(
        float $apartmentPrice,
        int $rentalDuration,
        string $pricingType
    ): ?PaymentCalculationResult {
        if (!config('payment_calculation.caching.enable_result_caching', false)) {
            return null;
        }
        
        $cacheKey = $this->generateCalculationCacheKey($apartmentPrice, $rentalDuration, $pricingType);
        $cachedData = Cache::get($cacheKey);
        
        if (!$cachedData) {
            $this->recordCacheMetrics('calculation_cache_miss', [
                'cache_key' => $cacheKey,
                'pricing_type' => $pricingType
            ]);
            return null;
        }
        
        // Validate cache data structure
        if (!isset($cachedData['result']) || !isset($cachedData['inputs'])) {
            Log::warning('Invalid cached calculation data structure', [
                'cache_key' => $cacheKey,
                'data_keys' => array_keys($cachedData)
            ]);
            Cache::forget($cacheKey);
            return null;
        }
        
        $resultData = $cachedData['result'];
        
        // Reconstruct PaymentCalculationResult from cached data
        if ($resultData['is_valid']) {
            $result = PaymentCalculationResult::success(
                $resultData['total_amount'],
                $resultData['calculation_method'],
                $resultData['calculation_steps'] ?? []
            );
        } else {
            $result = PaymentCalculationResult::failure(
                $resultData['error_message'] ?? 'Unknown error'
            );
        }
        
        $this->recordCacheMetrics('calculation_cache_hit', [
            'cache_key' => $cacheKey,
            'result_valid' => $result->isValid,
            'pricing_type' => $pricingType,
            'cached_at' => $cachedData['cached_at'] ?? 'unknown'
        ]);
        
        Log::debug('Payment calculation result retrieved from cache', [
            'cache_key' => substr($cacheKey, 0, 50) . '...',
            'apartment_price' => $apartmentPrice,
            'rental_duration' => $rentalDuration,
            'pricing_type' => $pricingType,
            'result_valid' => $result->isValid,
            'cached_at' => $cachedData['cached_at'] ?? 'unknown'
        ]);
        
        return $result;
    }
    
    /**
     * Cache apartment pricing configuration
     */
    public function cacheApartmentPricingConfig(int $apartmentId, array $pricingConfig): void
    {
        $cacheKey = self::APARTMENT_PRICING_PREFIX . $apartmentId;
        
        $cacheData = [
            'apartment_id' => $apartmentId,
            'pricing_config' => $pricingConfig,
            'cached_at' => now()->toISOString(),
            'cache_version' => '1.0'
        ];
        
        Cache::put($cacheKey, $cacheData, self::APARTMENT_PRICING_TTL);
        
        Log::debug('Apartment pricing configuration cached', [
            'apartment_id' => $apartmentId,
            'cache_key' => $cacheKey,
            'pricing_type' => $pricingConfig['pricing_type'] ?? 'unknown',
            'ttl_minutes' => self::APARTMENT_PRICING_TTL
        ]);
    }
    
    /**
     * Get cached apartment pricing configuration
     */
    public function getCachedApartmentPricingConfig(int $apartmentId): ?array
    {
        $cacheKey = self::APARTMENT_PRICING_PREFIX . $apartmentId;
        $cachedData = Cache::get($cacheKey);
        
        if (!$cachedData) {
            return null;
        }
        
        return $cachedData['pricing_config'] ?? null;
    }
    
    /**
     * Cache bulk calculation results for performance
     */
    public function cacheBulkCalculationResults(string $bulkId, array $calculations): void
    {
        $cacheKey = self::BULK_CALCULATION_PREFIX . $bulkId;
        
        $cacheData = [
            'bulk_id' => $bulkId,
            'calculations' => $calculations,
            'calculation_count' => count($calculations),
            'cached_at' => now()->toISOString(),
            'cache_version' => '1.0'
        ];
        
        Cache::put($cacheKey, $cacheData, self::BULK_CALCULATION_TTL);
        
        Log::info('Bulk calculation results cached', [
            'bulk_id' => $bulkId,
            'calculation_count' => count($calculations),
            'cache_key' => $cacheKey,
            'ttl_minutes' => self::BULK_CALCULATION_TTL
        ]);
    }
    
    /**
     * Get cached bulk calculation results
     */
    public function getCachedBulkCalculationResults(string $bulkId): ?array
    {
        $cacheKey = self::BULK_CALCULATION_PREFIX . $bulkId;
        $cachedData = Cache::get($cacheKey);
        
        if (!$cachedData) {
            return null;
        }
        
        return $cachedData['calculations'] ?? null;
    }
    
    /**
     * Cache frequently used pricing configurations
     */
    public function cachePricingConfiguration(string $configKey, array $config): void
    {
        $cacheKey = self::PRICING_CONFIG_PREFIX . $configKey;
        
        $cacheData = [
            'config_key' => $configKey,
            'configuration' => $config,
            'cached_at' => now()->toISOString(),
            'cache_version' => '1.0'
        ];
        
        Cache::put($cacheKey, $cacheData, self::PRICING_CONFIG_TTL);
        
        Log::debug('Pricing configuration cached', [
            'config_key' => $configKey,
            'cache_key' => $cacheKey,
            'ttl_minutes' => self::PRICING_CONFIG_TTL
        ]);
    }
    
    /**
     * Get cached pricing configuration
     */
    public function getCachedPricingConfiguration(string $configKey): ?array
    {
        $cacheKey = self::PRICING_CONFIG_PREFIX . $configKey;
        $cachedData = Cache::get($cacheKey);
        
        if (!$cachedData) {
            return null;
        }
        
        return $cachedData['configuration'] ?? null;
    }
    
    /**
     * Warm up cache with frequently used calculations
     */
    public function warmUpCalculationCache(array $commonCalculations = []): void
    {
        Log::info('Starting payment calculation cache warm-up');
        
        // Default common calculations if none provided
        if (empty($commonCalculations)) {
            $commonCalculations = [
                ['price' => 100000, 'duration' => 12, 'type' => 'total'],
                ['price' => 50000, 'duration' => 6, 'type' => 'total'],
                ['price' => 25000, 'duration' => 12, 'type' => 'monthly'],
                ['price' => 30000, 'duration' => 6, 'type' => 'monthly'],
                ['price' => 75000, 'duration' => 24, 'type' => 'total'],
            ];
        }
        
        $warmedCount = 0;
        
        foreach ($commonCalculations as $calc) {
            $cacheKey = $this->generateCalculationCacheKey(
                $calc['price'],
                $calc['duration'],
                $calc['type']
            );
            
            // Only warm up if not already cached
            if (!Cache::has($cacheKey)) {
                // Create a mock successful result for warm-up
                $mockResult = PaymentCalculationResult::success(
                    $calc['type'] === 'monthly' ? $calc['price'] * $calc['duration'] : $calc['price'],
                    $calc['type'] === 'monthly' ? 'monthly_price_with_duration_multiplication' : 'total_price_no_multiplication',
                    [
                        [
                            'step' => 'cache_warmup',
                            'note' => 'Pre-calculated result for performance optimization',
                            'timestamp' => now()->toISOString()
                        ]
                    ]
                );
                
                $this->cacheCalculationResult(
                    $calc['price'],
                    $calc['duration'],
                    $calc['type'],
                    $mockResult
                );
                
                $warmedCount++;
            }
        }
        
        Log::info('Payment calculation cache warm-up completed', [
            'warmed_calculations' => $warmedCount,
            'total_calculations' => count($commonCalculations)
        ]);
    }
    
    /**
     * Clean up expired and invalid cache entries
     */
    public function cleanupCalculationCache(): int
    {
        $cleanedCount = 0;
        
        // This would typically be handled by the cache driver,
        // but we can implement custom cleanup for specific patterns
        
        Log::info('Payment calculation cache cleanup completed', [
            'cleaned_entries' => $cleanedCount
        ]);
        
        return $cleanedCount;
    }
    
    /**
     * Get cache performance statistics
     */
    public function getCachePerformanceStatistics(int $hours = 24): array
    {
        $stats = [
            'period_hours' => $hours,
            'generated_at' => now()->toISOString(),
            'cache_enabled' => config('payment_calculation.caching.enable_result_caching', false),
            'cache_ttl_minutes' => config('payment_calculation.caching.cache_ttl', self::CALCULATION_TTL),
            'total_cache_hits' => 0,
            'total_cache_misses' => 0,
            'cache_hit_rate' => 0,
            'cached_calculations' => 0,
            'cache_size_estimate' => 0,
            'pricing_types_cached' => [],
            'hourly_trends' => []
        ];
        
        // Collect metrics from the last N hours
        for ($i = 0; $i < $hours; $i++) {
            $hour = now()->subHours($i)->format('Y-m-d-H');
            $hourMetrics = $this->getCachedPerformanceMetrics($hour);
            
            if ($hourMetrics) {
                $stats['total_cache_hits'] += $hourMetrics['cache_hits'] ?? 0;
                $stats['total_cache_misses'] += $hourMetrics['cache_misses'] ?? 0;
                $stats['cached_calculations'] += $hourMetrics['calculations_cached'] ?? 0;
                
                // Aggregate pricing types
                foreach ($hourMetrics['pricing_types'] ?? [] as $type => $count) {
                    $stats['pricing_types_cached'][$type] = 
                        ($stats['pricing_types_cached'][$type] ?? 0) + $count;
                }
                
                $stats['hourly_trends'][] = [
                    'hour' => $hour,
                    'cache_hits' => $hourMetrics['cache_hits'] ?? 0,
                    'cache_misses' => $hourMetrics['cache_misses'] ?? 0,
                    'calculations_cached' => $hourMetrics['calculations_cached'] ?? 0
                ];
            }
        }
        
        // Calculate derived metrics
        $totalRequests = $stats['total_cache_hits'] + $stats['total_cache_misses'];
        if ($totalRequests > 0) {
            $stats['cache_hit_rate'] = round(
                ($stats['total_cache_hits'] / $totalRequests) * 100,
                2
            );
        }
        
        // Reverse hourly trends for chronological order
        $stats['hourly_trends'] = array_reverse($stats['hourly_trends']);
        
        return $stats;
    }
    
    /**
     * Invalidate calculation cache for specific parameters
     */
    public function invalidateCalculationCache(
        float $apartmentPrice,
        int $rentalDuration,
        string $pricingType
    ): void {
        $cacheKey = $this->generateCalculationCacheKey($apartmentPrice, $rentalDuration, $pricingType);
        Cache::forget($cacheKey);
        
        Log::debug('Payment calculation cache invalidated', [
            'cache_key' => substr($cacheKey, 0, 50) . '...',
            'apartment_price' => $apartmentPrice,
            'rental_duration' => $rentalDuration,
            'pricing_type' => $pricingType
        ]);
    }
    
    /**
     * Invalidate apartment pricing configuration cache
     */
    public function invalidateApartmentPricingCache(int $apartmentId): void
    {
        $cacheKey = self::APARTMENT_PRICING_PREFIX . $apartmentId;
        Cache::forget($cacheKey);
        
        Log::debug('Apartment pricing configuration cache invalidated', [
            'apartment_id' => $apartmentId,
            'cache_key' => $cacheKey
        ]);
    }
    
    /**
     * Batch invalidate multiple calculation caches
     */
    public function batchInvalidateCalculationCache(array $calculations): void
    {
        $invalidatedCount = 0;
        
        foreach ($calculations as $calc) {
            if (isset($calc['price'], $calc['duration'], $calc['type'])) {
                $this->invalidateCalculationCache(
                    $calc['price'],
                    $calc['duration'],
                    $calc['type']
                );
                $invalidatedCount++;
            }
        }
        
        Log::info('Batch invalidated payment calculation caches', [
            'invalidated_count' => $invalidatedCount,
            'total_requested' => count($calculations)
        ]);
    }
    
    /**
     * Get cache usage summary
     */
    public function getCacheUsageSummary(): array
    {
        return [
            'cache_enabled' => config('payment_calculation.caching.enable_result_caching', false),
            'cache_driver' => config('cache.default'),
            'cache_prefix' => config('cache.prefix'),
            'ttl_configurations' => [
                'calculation_ttl_minutes' => self::CALCULATION_TTL,
                'pricing_config_ttl_minutes' => self::PRICING_CONFIG_TTL,
                'apartment_pricing_ttl_minutes' => self::APARTMENT_PRICING_TTL,
                'bulk_calculation_ttl_minutes' => self::BULK_CALCULATION_TTL,
                'performance_metrics_ttl_minutes' => self::PERFORMANCE_METRICS_TTL
            ],
            'cache_key_prefixes' => [
                'calculation' => self::CALCULATION_PREFIX,
                'pricing_config' => self::PRICING_CONFIG_PREFIX,
                'apartment_pricing' => self::APARTMENT_PRICING_PREFIX,
                'bulk_calculation' => self::BULK_CALCULATION_PREFIX,
                'performance_metrics' => self::PERFORMANCE_METRICS_PREFIX
            ],
            'limits' => [
                'max_cache_key_length' => self::MAX_CACHE_KEY_LENGTH,
                'max_cached_calculations_per_hour' => self::MAX_CACHED_CALCULATIONS_PER_HOUR
            ],
            'generated_at' => now()->toISOString()
        ];
    }
    
    // Private helper methods
    
    /**
     * Generate a consistent cache key for calculation parameters
     */
    private function generateCalculationCacheKey(
        float $apartmentPrice,
        int $rentalDuration,
        string $pricingType
    ): string {
        $keyPrefix = config('payment_calculation.caching.cache_key_prefix', 'payment_calc');
        
        // Create a deterministic key based on input parameters
        $keyData = [
            'price' => number_format($apartmentPrice, 2, '.', ''),
            'duration' => $rentalDuration,
            'type' => strtolower(trim($pricingType))
        ];
        
        $keyString = $keyPrefix . '_' . implode('_', $keyData);
        
        // Ensure key length doesn't exceed limits
        if (strlen($keyString) > self::MAX_CACHE_KEY_LENGTH) {
            $keyString = $keyPrefix . '_' . md5(json_encode($keyData));
        }
        
        return $keyString;
    }
    
    /**
     * Record cache performance metrics
     */
    private function recordCacheMetrics(string $operation, array $data): void
    {
        $hour = now()->format('Y-m-d-H');
        $metricsKey = self::PERFORMANCE_METRICS_PREFIX . $hour;
        
        $existingMetrics = Cache::get($metricsKey, [
            'hour' => $hour,
            'cache_hits' => 0,
            'cache_misses' => 0,
            'calculations_cached' => 0,
            'pricing_types' => [],
            'operations' => []
        ]);
        
        // Update metrics based on operation
        switch ($operation) {
            case 'calculation_cache_hit':
                $existingMetrics['cache_hits']++;
                break;
            case 'calculation_cache_miss':
                $existingMetrics['cache_misses']++;
                break;
            case 'calculation_cached':
                $existingMetrics['calculations_cached']++;
                break;
        }
        
        // Track pricing types
        if (isset($data['pricing_type'])) {
            $pricingType = $data['pricing_type'];
            $existingMetrics['pricing_types'][$pricingType] = 
                ($existingMetrics['pricing_types'][$pricingType] ?? 0) + 1;
        }
        
        // Track operations
        $existingMetrics['operations'][$operation] = 
            ($existingMetrics['operations'][$operation] ?? 0) + 1;
        
        $existingMetrics['last_updated'] = now()->toISOString();
        
        Cache::put($metricsKey, $existingMetrics, self::PERFORMANCE_METRICS_TTL);
    }
    
    /**
     * Get cached performance metrics for a specific hour
     */
    private function getCachedPerformanceMetrics(string $hour): ?array
    {
        $metricsKey = self::PERFORMANCE_METRICS_PREFIX . $hour;
        return Cache::get($metricsKey);
    }
}