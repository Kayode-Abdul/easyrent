<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ApartmentInvitation;
use Carbon\Carbon;

class CleanupInvitationDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'easyrent:cleanup-invitations 
                           {--dry-run : Show what would be cleaned without making changes}
                           {--force : Force cleanup without confirmation}
                           {--days=30 : Number of days to keep completed invitations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired invitation data, sessions, and perform database maintenance for EasyRent Link Authentication System';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $keepDays = (int) $this->option('days');
        
        $this->info('EasyRent Invitation Database Cleanup');
        $this->info('=====================================');
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        
        $startTime = microtime(true);
        
        // Analyze current state
        $stats = $this->analyzeCurrentState();
        $this->displayCurrentStats($stats);
        
        if (!$force && !$dryRun) {
            if (!$this->confirm('Do you want to proceed with the cleanup?')) {
                $this->info('Cleanup cancelled.');
                return 0;
            }
        }
        
        $cleanupResults = [
            'expired_sessions' => 0,
            'expired_invitations' => 0,
            'reset_rate_limits' => 0,
            'old_completed_invitations' => 0,
            'orphaned_session_data' => 0
        ];
        
        DB::beginTransaction();
        
        try {
            // 1. Clean up expired session data
            $cleanupResults['expired_sessions'] = $this->cleanupExpiredSessions($dryRun);
            
            // 2. Expire old invitations
            $cleanupResults['expired_invitations'] = $this->expireOldInvitations($dryRun);
            
            // 3. Reset expired rate limits
            $cleanupResults['reset_rate_limits'] = $this->resetExpiredRateLimits($dryRun);
            
            // 4. Clean up old completed invitations (optional)
            $cleanupResults['old_completed_invitations'] = $this->cleanupOldCompletedInvitations($keepDays, $dryRun);
            
            // 5. Clean up orphaned session data
            $cleanupResults['orphaned_session_data'] = $this->cleanupOrphanedSessionData($dryRun);
            
            if (!$dryRun) {
                // 6. Log cleanup operation
                $this->logCleanupOperation($cleanupResults, $startTime);
                
                // 7. Record cleanup history
                $this->recordCleanupHistory($cleanupResults);
                
                DB::commit();
                $this->info('✅ Cleanup completed successfully!');
            } else {
                DB::rollBack();
                $this->info('✅ Dry run completed - no changes made');
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Cleanup failed: ' . $e->getMessage());
            Log::error('Invitation cleanup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
        
        $this->displayCleanupResults($cleanupResults);
        
        // 8. Calculate and display performance metrics
        $this->calculatePerformanceMetrics();
        
        $executionTime = round(microtime(true) - $startTime, 3);
        $this->info("Total execution time: {$executionTime} seconds");
        
        return 0;
    }
    
    /**
     * Analyze current database state
     */
    private function analyzeCurrentState(): array
    {
        return [
            'total_invitations' => ApartmentInvitation::count(),
            'active_invitations' => ApartmentInvitation::where('status', 'active')->count(),
            'expired_invitations' => ApartmentInvitation::where('status', 'expired')->count(),
            'used_invitations' => ApartmentInvitation::where('status', 'used')->count(),
            'invitations_with_sessions' => ApartmentInvitation::whereNotNull('session_data')->count(),
            'expired_sessions' => ApartmentInvitation::where('session_expires_at', '<=', now())->count(),
            'high_access_invitations' => ApartmentInvitation::where('access_count', '>', 50)->count(),
            'rate_limited_invitations' => ApartmentInvitation::where('rate_limit_count', '>', 0)->count(),
        ];
    }
    
    /**
     * Display current database statistics
     */
    private function displayCurrentStats(array $stats): void
    {
        $this->info('Current Database State:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Invitations', number_format($stats['total_invitations'])],
                ['Active Invitations', number_format($stats['active_invitations'])],
                ['Expired Invitations', number_format($stats['expired_invitations'])],
                ['Used Invitations', number_format($stats['used_invitations'])],
                ['Invitations with Sessions', number_format($stats['invitations_with_sessions'])],
                ['Expired Sessions', number_format($stats['expired_sessions'])],
                ['High Access Count (>50)', number_format($stats['high_access_invitations'])],
                ['Rate Limited', number_format($stats['rate_limited_invitations'])],
            ]
        );
    }
    
    /**
     * Clean up expired session data
     */
    private function cleanupExpiredSessions(bool $dryRun): int
    {
        $query = ApartmentInvitation::whereNotNull('session_expires_at')
            ->where('session_expires_at', '<=', now())
            ->whereNotNull('session_data');
            
        $count = $query->count();
        
        if ($count > 0) {
            $this->info("🧹 Found {$count} expired sessions to clean up");
            
            if (!$dryRun) {
                $query->update([
                    'session_data' => null,
                    'session_expires_at' => null,
                    'updated_at' => now()
                ]);
            }
        }
        
        return $count;
    }
    
    /**
     * Expire old invitations
     */
    private function expireOldInvitations(bool $dryRun): int
    {
        $query = ApartmentInvitation::where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
            
        $count = $query->count();
        
        if ($count > 0) {
            $this->info("⏰ Found {$count} invitations to expire");
            
            if (!$dryRun) {
                $query->update([
                    'status' => 'expired',
                    'session_data' => null,
                    'session_expires_at' => null,
                    'updated_at' => now()
                ]);
            }
        }
        
        return $count;
    }
    
    /**
     * Reset expired rate limits
     */
    private function resetExpiredRateLimits(bool $dryRun): int
    {
        $query = ApartmentInvitation::whereNotNull('rate_limit_reset_at')
            ->where('rate_limit_reset_at', '<=', now())
            ->where('rate_limit_count', '>', 0);
            
        $count = $query->count();
        
        if ($count > 0) {
            $this->info("🚦 Found {$count} rate limits to reset");
            
            if (!$dryRun) {
                $query->update([
                    'rate_limit_count' => 0,
                    'rate_limit_reset_at' => now()->addHour(),
                    'updated_at' => now()
                ]);
            }
        }
        
        return $count;
    }
    
    /**
     * Clean up old completed invitations
     */
    private function cleanupOldCompletedInvitations(int $keepDays, bool $dryRun): int
    {
        $cutoffDate = now()->subDays($keepDays);
        
        $query = ApartmentInvitation::where('status', 'used')
            ->whereNotNull('payment_completed_at')
            ->where('payment_completed_at', '<', $cutoffDate)
            ->whereNotNull('session_data');
            
        $count = $query->count();
        
        if ($count > 0) {
            $this->info("🗂️  Found {$count} old completed invitations to clean (older than {$keepDays} days)");
            
            if (!$dryRun) {
                // Only clean session data, keep the invitation record for analytics
                $query->update([
                    'session_data' => null,
                    'session_expires_at' => null,
                    'updated_at' => now()
                ]);
            }
        }
        
        return $count;
    }
    
    /**
     * Clean up orphaned session data
     */
    private function cleanupOrphanedSessionData(bool $dryRun): int
    {
        // Find invitations with session data but no session expiration
        $query = ApartmentInvitation::whereNotNull('session_data')
            ->whereNull('session_expires_at');
            
        $count = $query->count();
        
        if ($count > 0) {
            $this->info("🔍 Found {$count} orphaned session data entries");
            
            if (!$dryRun) {
                $query->update([
                    'session_data' => null,
                    'updated_at' => now()
                ]);
            }
        }
        
        return $count;
    }
    
    /**
     * Log cleanup operation to database
     */
    private function logCleanupOperation(array $results, float $startTime): void
    {
        $executionTime = round(microtime(true) - $startTime, 3);
        $totalRecords = array_sum($results);
        
        DB::table('database_maintenance_logs')->insert([
            'operation_type' => 'cleanup',
            'table_name' => 'apartment_invitations',
            'description' => 'Automated invitation database cleanup via console command',
            'operation_details' => json_encode($results),
            'records_affected' => $totalRecords,
            'execution_time_seconds' => $executionTime,
            'status' => 'completed',
            'started_at' => now()->subSeconds($executionTime),
            'completed_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    
    /**
     * Record cleanup history
     */
    private function recordCleanupHistory(array $results): void
    {
        DB::table('session_cleanup_history')->insert([
            'cleanup_date' => now(),
            'expired_sessions_found' => $results['expired_sessions'],
            'sessions_cleaned' => $results['expired_sessions'],
            'invitations_expired' => $results['expired_invitations'],
            'rate_limits_reset' => $results['reset_rate_limits'],
            'cleanup_duration_seconds' => 0, // Will be updated by maintenance log
            'cleanup_details' => json_encode($results),
            'cleanup_type' => 'manual',
            'initiated_by' => 'console_command',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    
    /**
     * Display cleanup results
     */
    private function displayCleanupResults(array $results): void
    {
        $this->info('Cleanup Results:');
        $this->table(
            ['Operation', 'Records Affected'],
            [
                ['Expired Sessions Cleaned', number_format($results['expired_sessions'])],
                ['Invitations Expired', number_format($results['expired_invitations'])],
                ['Rate Limits Reset', number_format($results['reset_rate_limits'])],
                ['Old Completed Invitations Cleaned', number_format($results['old_completed_invitations'])],
                ['Orphaned Session Data Cleaned', number_format($results['orphaned_session_data'])],
                ['Total Records Affected', number_format(array_sum($results))],
            ]
        );
    }
    
    /**
     * Calculate and display performance metrics
     */
    private function calculatePerformanceMetrics(): void
    {
        $today = now()->format('Y-m-d');
        
        // Calculate today's metrics
        $metrics = DB::table('apartment_invitations')
            ->selectRaw('
                COUNT(*) as total_created,
                COUNT(CASE WHEN viewed_at IS NOT NULL THEN 1 END) as total_viewed,
                COUNT(CASE WHEN payment_initiated_at IS NOT NULL THEN 1 END) as total_payment_initiated,
                COUNT(CASE WHEN payment_completed_at IS NOT NULL THEN 1 END) as total_payment_completed,
                AVG(access_count) as avg_access_count
            ')
            ->whereDate('created_at', $today)
            ->first();
            
        if ($metrics && $metrics->total_created > 0) {
            $viewRate = $metrics->total_viewed > 0 ? round(($metrics->total_viewed / $metrics->total_created) * 100, 2) : 0;
            $conversionRate = $metrics->total_viewed > 0 ? round(($metrics->total_payment_completed / $metrics->total_viewed) * 100, 2) : 0;
            
            $this->info("Today's Performance Metrics:");
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Invitations Created', number_format($metrics->total_created)],
                    ['Invitations Viewed', number_format($metrics->total_viewed)],
                    ['Payments Initiated', number_format($metrics->total_payment_initiated)],
                    ['Payments Completed', number_format($metrics->total_payment_completed)],
                    ['View Rate', $viewRate . '%'],
                    ['Conversion Rate', $conversionRate . '%'],
                    ['Average Access Count', round($metrics->avg_access_count, 2)],
                ]
            );
        }
    }
}