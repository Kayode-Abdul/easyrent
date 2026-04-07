<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Cache\EasyRentCacheInterface;
use App\Models\ApartmentInvitation;
use Illuminate\Support\Facades\Log;

class OptimizeEasyRentCache extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'easyrent:optimize-cache 
                            {--warmup : Warm up cache with frequently accessed data}
                            {--cleanup : Clean up expired cache entries}
                            {--stats : Show cache statistics}
                            {--all : Run all optimization tasks}';

    /**
     * The console command description.
     */
    protected $description = 'Optimize EasyRent cache for better performance';

    protected $cacheService;

    public function __construct(EasyRentCacheInterface $cacheService)
    {
        parent::__construct();
        $this->cacheService = $cacheService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting EasyRent cache optimization...');

        if ($this->option('all')) {
            $this->warmUpCache();
            $this->cleanupCache();
            $this->showCacheStats();
            $this->optimizePerformance();
        } else {
            if ($this->option('warmup')) {
                $this->warmUpCache();
            }

            if ($this->option('cleanup')) {
                $this->cleanupCache();
            }

            if ($this->option('stats')) {
                $this->showCacheStats();
            }
        }

        $this->info('Cache optimization completed!');
        return 0;
    }

    /**
     * Warm up cache with frequently accessed data
     */
    protected function warmUpCache(): void
    {
        $this->info('Warming up cache...');

        try {
            // Warm up active invitations
            $activeInvitations = ApartmentInvitation::active()
                ->where('access_count', '>', 0)
                ->orderBy('access_count', 'desc')
                ->limit(100)
                ->get();

            $warmedCount = 0;
            $bar = $this->output->createProgressBar($activeInvitations->count());

            foreach ($activeInvitations as $invitation) {
                try {
                    // Cache invitation data
                    $this->cacheService->cacheInvitationData($invitation->invitation_token);
                    
                    // Cache apartment data
                    $this->cacheService->cacheApartmentData($invitation->apartment_id);
                    
                    $warmedCount++;
                    $bar->advance();
                } catch (\Exception $e) {
                    Log::warning('Failed to warm cache for invitation', [
                        'invitation_id' => $invitation->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $bar->finish();
            $this->newLine();
            $this->info("Cache warmed for {$warmedCount} invitations");

            // Warm up frequently accessed users
            $this->warmUpUserCache();

        } catch (\Exception $e) {
            $this->error('Cache warm-up failed: ' . $e->getMessage());
            Log::error('Cache warm-up failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Warm up user cache for active users
     */
    protected function warmUpUserCache(): void
    {
        $this->info('Warming up user cache...');

        try {
            // Get users who have accessed invitations recently
            $activeUserIds = ApartmentInvitation::whereNotNull('tenant_user_id')
                ->where('last_accessed_at', '>', now()->subDays(7))
                ->distinct()
                ->pluck('tenant_user_id')
                ->take(50);

            $warmedUsers = 0;
            foreach ($activeUserIds as $userId) {
                try {
                    $this->cacheService->cacheUserData($userId);
                    $warmedUsers++;
                } catch (\Exception $e) {
                    Log::warning('Failed to warm user cache', [
                        'user_id' => $userId,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $this->info("User cache warmed for {$warmedUsers} users");

        } catch (\Exception $e) {
            $this->error('User cache warm-up failed: ' . $e->getMessage());
        }
    }

    /**
     * Clean up expired cache entries
     */
    protected function cleanupCache(): void
    {
        $this->info('Cleaning up expired cache entries...');

        try {
            $cleanedCount = $this->cacheService->cleanupExpiredCache();
            $this->info("Cleaned up {$cleanedCount} expired cache entries");

            // Also clean up expired session data
            $expiredSessions = ApartmentInvitation::cleanupExpiredSessions();
            $this->info("Cleaned up {$expiredSessions} expired session records");

        } catch (\Exception $e) {
            $this->error('Cache cleanup failed: ' . $e->getMessage());
            Log::error('Cache cleanup failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Show cache statistics
     */
    protected function showCacheStats(): void
    {
        $this->info('Cache Statistics:');
        $this->newLine();

        try {
            $stats = $this->cacheService->getCacheStatistics();
            
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Cache Driver', $stats['cache_driver']],
                    ['Cache Prefix', $stats['cache_prefix']],
                    ['Generated At', $stats['statistics_generated_at']],
                ]
            );

            // Show performance metrics for key operations
            $this->showPerformanceMetrics();

        } catch (\Exception $e) {
            $this->error('Failed to retrieve cache statistics: ' . $e->getMessage());
        }
    }

    /**
     * Show performance metrics
     */
    protected function showPerformanceMetrics(): void
    {
        $this->info('Performance Metrics (Last 24 hours):');
        $this->newLine();

        $operations = [
            'apartment.invite.show',
            'apartment_invitation_access',
            'login',
            'register'
        ];

        $metricsData = [];
        
        foreach ($operations as $operation) {
            try {
                $metrics = $this->cacheService->getPerformanceMetricsRange($operation, 24);
                
                if (!empty($metrics)) {
                    $totalRequests = array_sum(array_column($metrics, 'total_requests'));
                    $avgExecutionTime = array_sum(array_column($metrics, 'avg_execution_time')) / count($metrics);
                    $avgSuccessRate = array_sum(array_column($metrics, 'success_rate')) / count($metrics);
                    
                    $metricsData[] = [
                        $operation,
                        $totalRequests,
                        round($avgExecutionTime * 1000, 2) . 'ms',
                        round($avgSuccessRate * 100, 1) . '%'
                    ];
                }
            } catch (\Exception $e) {
                // Skip operations with no metrics
                continue;
            }
        }

        if (!empty($metricsData)) {
            $this->table(
                ['Operation', 'Total Requests', 'Avg Response Time', 'Success Rate'],
                $metricsData
            );
        } else {
            $this->info('No performance metrics available yet.');
        }
    }

    /**
     * Optimize performance based on current metrics
     */
    protected function optimizePerformance(): void
    {
        $this->info('Analyzing performance and optimizing...');

        try {
            // Batch cache frequently accessed apartments
            $this->batchCachePopularApartments();
            
            // Pre-cache property data for active listings
            $this->preCacheActiveProperties();
            
            // Optimize session data storage
            $this->optimizeSessionStorage();
            
            $this->info('Performance optimization completed');

        } catch (\Exception $e) {
            $this->error('Performance optimization failed: ' . $e->getMessage());
            Log::error('Performance optimization failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Batch cache popular apartments
     */
    protected function batchCachePopularApartments(): void
    {
        $this->info('Caching popular apartments...');

        try {
            // Get apartments from most accessed invitations
            $popularApartmentIds = ApartmentInvitation::where('access_count', '>', 5)
                ->where('last_accessed_at', '>', now()->subDays(7))
                ->orderBy('access_count', 'desc')
                ->limit(50)
                ->pluck('apartment_id')
                ->unique()
                ->toArray();

            if (!empty($popularApartmentIds)) {
                $cached = $this->cacheService->batchCacheApartments($popularApartmentIds);
                $this->info("Batch cached {count($cached)} popular apartments");
            } else {
                $this->info('No popular apartments found to cache');
            }

        } catch (\Exception $e) {
            $this->error('Failed to batch cache apartments: ' . $e->getMessage());
        }
    }

    /**
     * Pre-cache active property data
     */
    protected function preCacheActiveProperties(): void
    {
        $this->info('Pre-caching active properties...');

        try {
            // Get properties with available apartments
            $activePropertyIds = \App\Models\Property::whereHas('apartments', function($query) {
                $query->where('status', 'available');
            })
            ->limit(30)
            ->pluck('property_id')
            ->toArray();

            $cachedCount = 0;
            foreach ($activePropertyIds as $propertyId) {
                try {
                    $this->cacheService->cachePropertyData($propertyId);
                    $cachedCount++;
                } catch (\Exception $e) {
                    Log::warning('Failed to cache property data', [
                        'property_id' => $propertyId,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $this->info("Pre-cached {$cachedCount} active properties");

        } catch (\Exception $e) {
            $this->error('Failed to pre-cache properties: ' . $e->getMessage());
        }
    }

    /**
     * Optimize session storage
     */
    protected function optimizeSessionStorage(): void
    {
        $this->info('Optimizing session storage...');

        try {
            // Clean up expired session data
            $expiredSessions = ApartmentInvitation::cleanupExpiredSessions();
            
            // Cache active session data for quick access
            $activeSessions = ApartmentInvitation::whereNotNull('session_data')
                ->where('session_expires_at', '>', now())
                ->limit(100)
                ->get();

            $cachedSessions = 0;
            foreach ($activeSessions as $invitation) {
                if ($invitation->session_data) {
                    $sessionId = $invitation->session_data['session_id'] ?? session()->getId();
                    $this->cacheService->cacheSessionData($sessionId, $invitation->session_data);
                    $cachedSessions++;
                }
            }

            $this->info("Cleaned {$expiredSessions} expired sessions and cached {$cachedSessions} active sessions");

        } catch (\Exception $e) {
            $this->error('Failed to optimize session storage: ' . $e->getMessage());
        }
    }
}