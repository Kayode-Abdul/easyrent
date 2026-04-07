<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Apartment;
use App\Models\ProfomaReceipt;

class PaymentCalculationQueryOptimizer
{
    /**
     * Cache TTL for optimized queries in minutes
     */
    private const QUERY_CACHE_TTL = 30;
    private const APARTMENT_BATCH_SIZE = 100;
    private const PRICING_CONFIG_CACHE_TTL = 120;
    
    /**
     * Get apartment pricing configurations in batch for performance
     */
    public function getApartmentPricingConfigurationsBatch(array $apartmentIds): array
    {
        if (empty($apartmentIds)) {
            return [];
        }
        
        $cacheKey = 'apartment_pricing_batch_' . md5(implode(',', sort($apartmentIds)));
        
        return Cache::remember($cacheKey, self::PRICING_CONFIG_CACHE_TTL, function () use ($apartmentIds) {
            // Optimized query with selective field loading
            $apartments = Apartment::select([
                'apartment_id',
                'pricing_type',
                'price_configuration',
                'amount',
                'apartment_type',
                'apartment_type_id'
            ])
            ->whereIn('apartment_id', $apartmentIds)
            ->get();
            
            $configurations = [];
            
            foreach ($apartments as $apartment) {
                $configurations[$apartment->apartment_id] = [
                    'pricing_type' => $apartment->pricing_type ?? 'total',
                    'price_configuration' => $apartment->price_configuration ?? [],
                    'amount' => $apartment->amount,
                    'apartment_type' => $apartment->apartment_type,
                    'apartment_type_id' => $apartment->apartment_type_id
                ];
            }
            
            Log::debug('Batch loaded apartment pricing configurations', [
                'apartment_count' => count($apartmentIds),
                'configurations_loaded' => count($configurations),
                'cache_key' => $cacheKey
            ]);
            
            return $configurations;
        });
    }
    
    /**
     * Get frequently used pricing configurations for caching
     */
    public function getFrequentlyUsedPricingConfigurations(int $limit = 50): array
    {
        $cacheKey = 'frequent_pricing_configs_' . $limit;
        
        return Cache::remember($cacheKey, self::QUERY_CACHE_TTL, function () use ($limit) {
            // Query apartments that have been used in calculations recently
            $frequentApartments = DB::table('profoma_receipts')
                ->select('apartment_id', DB::raw('COUNT(*) as usage_count'))
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('apartment_id')
                ->orderBy('usage_count', 'desc')
                ->limit($limit)
                ->get();
            
            $apartmentIds = $frequentApartments->pluck('apartment_id')->toArray();
            
            if (empty($apartmentIds)) {
                return [];
            }
            
            return $this->getApartmentPricingConfigurationsBatch($apartmentIds);
        });
    }
    
    /**
     * Optimize database queries for pricing configuration lookups
     */
    public function optimizePricingConfigurationQueries(): void
    {
        // Add database indexes for performance if they don't exist
        $this->ensurePricingConfigurationIndexes();
        
        // Pre-load frequently used configurations
        $this->preloadFrequentConfigurations();
        
        Log::info('Payment calculation database queries optimized');
    }
    
    /**
     * Get calculation statistics for performance analysis
     */
    public function getCalculationStatistics(int $days = 30): array
    {
        $cacheKey = 'calculation_statistics_' . $days;
        
        return Cache::remember($cacheKey, self::QUERY_CACHE_TTL, function () use ($days) {
            $startDate = now()->subDays($days);
            
            // Get calculation volume statistics
            $volumeStats = DB::table('profoma_receipts')
                ->select([
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as calculation_count'),
                    DB::raw('AVG(amount) as avg_amount'),
                    DB::raw('SUM(amount) as total_amount')
                ])
                ->where('created_at', '>=', $startDate)
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date', 'desc')
                ->get();
            
            // Get pricing type distribution
            $pricingTypeStats = DB::table('apartments')
                ->select([
                    'pricing_type',
                    DB::raw('COUNT(*) as apartment_count')
                ])
                ->whereNotNull('pricing_type')
                ->groupBy('pricing_type')
                ->get();
            
            // Get apartment type usage
            $apartmentTypeStats = DB::table('profoma_receipts as pr')
                ->join('apartments as a', 'pr.apartment_id', '=', 'a.apartment_id')
                ->select([
                    'a.apartment_type',
                    DB::raw('COUNT(*) as usage_count'),
                    DB::raw('AVG(pr.amount) as avg_amount')
                ])
                ->where('pr.created_at', '>=', $startDate)
                ->groupBy('a.apartment_type')
                ->orderBy('usage_count', 'desc')
                ->get();
            
            return [
                'period_days' => $days,
                'volume_statistics' => $volumeStats->toArray(),
                'pricing_type_distribution' => $pricingTypeStats->toArray(),
                'apartment_type_usage' => $apartmentTypeStats->toArray(),
                'total_calculations' => $volumeStats->sum('calculation_count'),
                'generated_at' => now()->toISOString()
            ];
        });
    }
    
    /**
     * Get slow calculation queries for optimization
     */
    public function identifySlowCalculationQueries(): array
    {
        // This would typically integrate with query performance monitoring
        // For now, we'll identify potentially slow patterns
        
        $slowPatterns = [
            'high_volume_apartments' => $this->getHighVolumeApartments(),
            'complex_pricing_configurations' => $this->getComplexPricingConfigurations(),
            'frequent_calculation_patterns' => $this->getFrequentCalculationPatterns()
        ];
        
        return $slowPatterns;
    }
    
    /**
     * Optimize apartment model queries for calculation performance
     */
    public function optimizeApartmentQueries(array $apartmentIds): array
    {
        // Batch load apartments with optimized field selection
        $apartments = Apartment::select([
            'apartment_id',
            'amount',
            'pricing_type',
            'price_configuration',
            'apartment_type',
            'apartment_type_id',
            'property_id'
        ])
        ->whereIn('apartment_id', $apartmentIds)
        ->get()
        ->keyBy('apartment_id');
        
        Log::debug('Optimized apartment queries executed', [
            'requested_apartments' => count($apartmentIds),
            'loaded_apartments' => $apartments->count(),
            'fields_selected' => 7 // Minimal field selection for performance
        ]);
        
        return $apartments->toArray();
    }
    
    /**
     * Create optimized indexes for payment calculations
     */
    public function createOptimizedIndexes(): void
    {
        $indexes = [
            // Apartment pricing indexes
            'apartments_pricing_type_index' => [
                'table' => 'apartments',
                'columns' => ['pricing_type'],
                'type' => 'index'
            ],
            'apartments_amount_pricing_type_index' => [
                'table' => 'apartments',
                'columns' => ['amount', 'pricing_type'],
                'type' => 'index'
            ],
            // Proforma calculation indexes
            'profoma_receipts_apartment_created_index' => [
                'table' => 'profoma_receipts',
                'columns' => ['apartment_id', 'created_at'],
                'type' => 'index'
            ],
            'profoma_receipts_calculation_method_index' => [
                'table' => 'profoma_receipts',
                'columns' => ['calculation_method'],
                'type' => 'index'
            ]
        ];
        
        foreach ($indexes as $indexName => $indexConfig) {
            $this->createIndexIfNotExists($indexName, $indexConfig);
        }
        
        Log::info('Optimized database indexes created for payment calculations');
    }
    
    /**
     * Analyze query performance and suggest optimizations
     */
    public function analyzeQueryPerformance(): array
    {
        $analysis = [
            'query_analysis_date' => now()->toISOString(),
            'recommendations' => [],
            'performance_metrics' => [],
            'optimization_opportunities' => []
        ];
        
        // Analyze apartment table performance
        $apartmentTableStats = $this->analyzeTablePerformance('apartments');
        $analysis['performance_metrics']['apartments'] = $apartmentTableStats;
        
        // Analyze proforma receipts table performance
        $proformaTableStats = $this->analyzeTablePerformance('profoma_receipts');
        $analysis['performance_metrics']['profoma_receipts'] = $proformaTableStats;
        
        // Generate recommendations based on analysis
        $analysis['recommendations'] = $this->generatePerformanceRecommendations(
            $apartmentTableStats,
            $proformaTableStats
        );
        
        return $analysis;
    }
    
    // Private helper methods
    
    /**
     * Ensure required indexes exist for pricing configurations
     */
    private function ensurePricingConfigurationIndexes(): void
    {
        $requiredIndexes = [
            'apartments_pricing_type_idx',
            'apartments_amount_idx',
            'profoma_receipts_apartment_id_idx'
        ];
        
        foreach ($requiredIndexes as $indexName) {
            if (!$this->indexExists($indexName)) {
                Log::warning('Required index missing for payment calculations', [
                    'index_name' => $indexName,
                    'recommendation' => 'Create index for better performance'
                ]);
            }
        }
    }
    
    /**
     * Pre-load frequently used pricing configurations
     */
    private function preloadFrequentConfigurations(): void
    {
        $frequentConfigs = $this->getFrequentlyUsedPricingConfigurations(100);
        
        Log::info('Pre-loaded frequent pricing configurations', [
            'configurations_loaded' => count($frequentConfigs)
        ]);
    }
    
    /**
     * Get apartments with high calculation volume
     */
    private function getHighVolumeApartments(int $limit = 20): array
    {
        return DB::table('profoma_receipts')
            ->select('apartment_id', DB::raw('COUNT(*) as calculation_count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('apartment_id')
            ->having('calculation_count', '>', 10)
            ->orderBy('calculation_count', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
    
    /**
     * Get apartments with complex pricing configurations
     */
    private function getComplexPricingConfigurations(): array
    {
        return DB::table('apartments')
            ->select('apartment_id', 'pricing_type', 'price_configuration')
            ->whereNotNull('price_configuration')
            ->where('price_configuration', '!=', '{}')
            ->where('price_configuration', '!=', '[]')
            ->limit(50)
            ->get()
            ->toArray();
    }
    
    /**
     * Get frequent calculation patterns for optimization
     */
    private function getFrequentCalculationPatterns(): array
    {
        return DB::table('profoma_receipts')
            ->select([
                'duration',
                'calculation_method',
                DB::raw('COUNT(*) as pattern_count'),
                DB::raw('AVG(amount) as avg_amount')
            ])
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('duration', 'calculation_method')
            ->having('pattern_count', '>', 5)
            ->orderBy('pattern_count', 'desc')
            ->limit(20)
            ->get()
            ->toArray();
    }
    
    /**
     * Check if database index exists
     */
    private function indexExists(string $indexName): bool
    {
        try {
            $result = DB::select("SHOW INDEX FROM apartments WHERE Key_name = ?", [$indexName]);
            return !empty($result);
        } catch (\Exception $e) {
            Log::warning('Could not check index existence', [
                'index_name' => $indexName,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Create database index if it doesn't exist
     */
    private function createIndexIfNotExists(string $indexName, array $indexConfig): void
    {
        try {
            $table = $indexConfig['table'];
            $columns = implode(', ', $indexConfig['columns']);
            
            if (!$this->indexExists($indexName)) {
                DB::statement("CREATE INDEX {$indexName} ON {$table} ({$columns})");
                
                Log::info('Database index created for payment calculations', [
                    'index_name' => $indexName,
                    'table' => $table,
                    'columns' => $indexConfig['columns']
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to create database index', [
                'index_name' => $indexName,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Analyze table performance metrics
     */
    private function analyzeTablePerformance(string $tableName): array
    {
        try {
            $tableStats = DB::select("SHOW TABLE STATUS LIKE ?", [$tableName]);
            
            if (empty($tableStats)) {
                return ['error' => 'Table not found'];
            }
            
            $stats = (array) $tableStats[0];
            
            return [
                'table_name' => $tableName,
                'rows' => $stats['Rows'] ?? 0,
                'avg_row_length' => $stats['Avg_row_length'] ?? 0,
                'data_length' => $stats['Data_length'] ?? 0,
                'index_length' => $stats['Index_length'] ?? 0,
                'auto_increment' => $stats['Auto_increment'] ?? 0,
                'engine' => $stats['Engine'] ?? 'unknown'
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Generate performance recommendations based on table analysis
     */
    private function generatePerformanceRecommendations(array $apartmentStats, array $proformaStats): array
    {
        $recommendations = [];
        
        // Check apartment table size and recommend partitioning if needed
        if (isset($apartmentStats['rows']) && $apartmentStats['rows'] > 100000) {
            $recommendations[] = [
                'type' => 'partitioning',
                'table' => 'apartments',
                'description' => 'Consider partitioning apartments table by property_id or region for better performance',
                'priority' => 'medium'
            ];
        }
        
        // Check proforma table size and recommend archiving
        if (isset($proformaStats['rows']) && $proformaStats['rows'] > 500000) {
            $recommendations[] = [
                'type' => 'archiving',
                'table' => 'profoma_receipts',
                'description' => 'Consider archiving old proforma receipts to improve query performance',
                'priority' => 'high'
            ];
        }
        
        // Check index usage
        $recommendations[] = [
            'type' => 'indexing',
            'description' => 'Ensure all pricing-related queries use appropriate indexes',
            'priority' => 'high'
        ];
        
        return $recommendations;
    }
}