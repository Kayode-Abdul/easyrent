<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CalculateInvitationMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'easyrent:calculate-metrics 
                           {--date= : Specific date to calculate metrics for (YYYY-MM-DD)}
                           {--days=7 : Number of days to calculate metrics for (from today backwards)}
                           {--force : Recalculate existing metrics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and store invitation performance metrics for EasyRent Link Authentication System';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $specificDate = $this->option('date');
        $days = (int) $this->option('days');
        $force = $this->option('force');
        
        $this->info('EasyRent Invitation Metrics Calculator');
        $this->info('====================================');
        
        if ($specificDate) {
            // Calculate for specific date
            try {
                $date = Carbon::createFromFormat('Y-m-d', $specificDate);
                $this->calculateMetricsForDate($date, $force);
            } catch (\Exception $e) {
                $this->error('Invalid date format. Please use YYYY-MM-DD format.');
                return 1;
            }
        } else {
            // Calculate for multiple days
            $this->info("Calculating metrics for the last {$days} days...");
            
            for ($i = 0; $i < $days; $i++) {
                $date = now()->subDays($i);
                $this->calculateMetricsForDate($date, $force);
            }
        }
        
        $this->info('✅ Metrics calculation completed!');
        
        // Display recent metrics summary
        $this->displayRecentMetrics();
        
        return 0;
    }
    
    /**
     * Calculate metrics for a specific date
     */
    private function calculateMetricsForDate(Carbon $date, bool $force): void
    {
        $dateString = $date->format('Y-m-d');
        
        // Check if metrics already exist
        $existingMetrics = DB::table('invitation_performance_metrics')
            ->where('metric_date', $dateString)
            ->first();
            
        if ($existingMetrics && !$force) {
            $this->line("📊 Metrics for {$dateString} already exist (use --force to recalculate)");
            return;
        }
        
        $this->line("📊 Calculating metrics for {$dateString}...");
        
        // Calculate basic metrics
        $basicMetrics = DB::table('apartment_invitations')
            ->selectRaw('
                COUNT(*) as total_created,
                COUNT(CASE WHEN viewed_at IS NOT NULL THEN 1 END) as total_viewed,
                COUNT(CASE WHEN payment_initiated_at IS NOT NULL THEN 1 END) as total_payment_initiated,
                COUNT(CASE WHEN payment_completed_at IS NOT NULL THEN 1 END) as total_payment_completed,
                COUNT(CASE WHEN session_data IS NOT NULL THEN 1 END) as total_sessions_created,
                COUNT(CASE WHEN session_expires_at IS NOT NULL AND session_expires_at <= NOW() THEN 1 END) as total_sessions_expired,
                COUNT(CASE WHEN rate_limit_count >= 50 THEN 1 END) as total_rate_limit_hits,
                COUNT(CASE WHEN access_count > 100 THEN 1 END) as total_security_blocks,
                AVG(access_count) as avg_access_count
            ')
            ->whereDate('created_at', $dateString)
            ->first();
            
        // Calculate conversion rates
        $viewToPaymentRate = $basicMetrics->total_viewed > 0 
            ? round(($basicMetrics->total_payment_initiated * 100.0) / $basicMetrics->total_viewed, 2)
            : 0;
            
        $initiateToCompleteRate = $basicMetrics->total_payment_initiated > 0
            ? round(($basicMetrics->total_payment_completed * 100.0) / $basicMetrics->total_payment_initiated, 2)
            : 0;
            
        // Calculate average session duration
        $avgSessionDuration = $this->calculateAverageSessionDuration($date);
        
        // Calculate hourly distribution
        $hourlyDistribution = $this->calculateHourlyDistribution($date);
        
        // Get top accessing IPs (anonymized)
        $topAccessingIPs = $this->getTopAccessingIPs($date);
        
        // Prepare metrics data
        $metricsData = [
            'metric_date' => $dateString,
            'total_invitations_created' => $basicMetrics->total_created,
            'total_invitations_viewed' => $basicMetrics->total_viewed,
            'total_payments_initiated' => $basicMetrics->total_payment_initiated,
            'total_payments_completed' => $basicMetrics->total_payment_completed,
            'total_sessions_created' => $basicMetrics->total_sessions_created,
            'total_sessions_expired' => $basicMetrics->total_sessions_expired,
            'total_rate_limit_hits' => $basicMetrics->total_rate_limit_hits,
            'total_security_blocks' => $basicMetrics->total_security_blocks,
            'avg_access_count' => round($basicMetrics->avg_access_count ?? 0, 2),
            'conversion_rate_view_to_payment' => $viewToPaymentRate,
            'conversion_rate_initiate_to_complete' => $initiateToCompleteRate,
            'avg_session_duration_minutes' => $avgSessionDuration,
            'hourly_distribution' => json_encode($hourlyDistribution),
            'top_accessing_ips' => json_encode($topAccessingIPs),
            'created_at' => now(),
            'updated_at' => now()
        ];
        
        // Insert or update metrics
        if ($existingMetrics) {
            DB::table('invitation_performance_metrics')
                ->where('metric_date', $dateString)
                ->update($metricsData);
            $this->line("   ✅ Updated existing metrics for {$dateString}");
        } else {
            DB::table('invitation_performance_metrics')->insert($metricsData);
            $this->line("   ✅ Created new metrics for {$dateString}");
        }
        
        // Log the metrics calculation
        DB::table('database_maintenance_logs')->insert([
            'operation_type' => 'metrics_calculation',
            'table_name' => 'invitation_performance_metrics',
            'description' => "Calculated invitation metrics for {$dateString}",
            'operation_details' => json_encode([
                'date' => $dateString,
                'total_invitations' => $basicMetrics->total_created,
                'conversion_rate' => $viewToPaymentRate,
                'forced_recalculation' => $force
            ]),
            'records_affected' => 1,
            'status' => 'completed',
            'started_at' => now(),
            'completed_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    
    /**
     * Calculate average session duration in minutes
     */
    private function calculateAverageSessionDuration(Carbon $date): int
    {
        $sessions = DB::table('apartment_invitations')
            ->selectRaw('
                TIMESTAMPDIFF(MINUTE, created_at, 
                    COALESCE(payment_completed_at, viewed_at, last_accessed_at, NOW())
                ) as duration_minutes
            ')
            ->whereDate('created_at', $date->format('Y-m-d'))
            ->whereNotNull('session_data')
            ->get();
            
        if ($sessions->isEmpty()) {
            return 0;
        }
        
        $totalDuration = $sessions->sum('duration_minutes');
        return round($totalDuration / $sessions->count());
    }
    
    /**
     * Calculate hourly access distribution
     */
    private function calculateHourlyDistribution(Carbon $date): array
    {
        $hourlyData = DB::table('apartment_invitations')
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->whereDate('created_at', $date->format('Y-m-d'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->pluck('count', 'hour')
            ->toArray();
            
        // Fill in missing hours with 0
        $distribution = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $distribution[$hour] = $hourlyData[$hour] ?? 0;
        }
        
        return $distribution;
    }
    
    /**
     * Get top accessing IPs (anonymized for privacy)
     */
    private function getTopAccessingIPs(Carbon $date): array
    {
        $topIPs = DB::table('apartment_invitations')
            ->selectRaw('last_accessed_ip, COUNT(*) as access_count')
            ->whereDate('created_at', $date->format('Y-m-d'))
            ->whereNotNull('last_accessed_ip')
            ->groupBy('last_accessed_ip')
            ->orderByDesc('access_count')
            ->limit(10)
            ->get();
            
        return $topIPs->map(function ($item) {
            return [
                'ip' => $this->anonymizeIP($item->last_accessed_ip),
                'count' => $item->access_count
            ];
        })->toArray();
    }
    
    /**
     * Anonymize IP address for privacy
     */
    private function anonymizeIP(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // IPv4: Replace last octet with XX
            $parts = explode('.', $ip);
            $parts[3] = 'XX';
            return implode('.', $parts);
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // IPv6: Replace last 4 groups with XXXX
            $parts = explode(':', $ip);
            for ($i = max(0, count($parts) - 4); $i < count($parts); $i++) {
                $parts[$i] = 'XXXX';
            }
            return implode(':', $parts);
        }
        
        return 'UNKNOWN';
    }
    
    /**
     * Display recent metrics summary
     */
    private function displayRecentMetrics(): void
    {
        $recentMetrics = DB::table('invitation_performance_metrics')
            ->orderByDesc('metric_date')
            ->limit(7)
            ->get();
            
        if ($recentMetrics->isEmpty()) {
            $this->warn('No metrics data available to display.');
            return;
        }
        
        $this->info('Recent Metrics Summary (Last 7 Days):');
        
        $tableData = $recentMetrics->map(function ($metric) {
            return [
                $metric->metric_date,
                number_format($metric->total_invitations_created),
                number_format($metric->total_invitations_viewed),
                number_format($metric->total_payments_completed),
                $metric->conversion_rate_view_to_payment . '%',
                $metric->avg_session_duration_minutes . 'm'
            ];
        })->toArray();
        
        $this->table(
            ['Date', 'Created', 'Viewed', 'Completed', 'Conversion', 'Avg Session'],
            $tableData
        );
        
        // Calculate totals
        $totals = [
            'created' => $recentMetrics->sum('total_invitations_created'),
            'viewed' => $recentMetrics->sum('total_invitations_viewed'),
            'completed' => $recentMetrics->sum('total_payments_completed'),
        ];
        
        $overallConversion = $totals['viewed'] > 0 
            ? round(($totals['completed'] / $totals['viewed']) * 100, 2)
            : 0;
            
        $this->info("7-Day Totals: {$totals['created']} created, {$totals['viewed']} viewed, {$totals['completed']} completed");
        $this->info("Overall Conversion Rate: {$overallConversion}%");
    }
}