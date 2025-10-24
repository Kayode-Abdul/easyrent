<?php

namespace App\Services\Monitoring;

use App\Models\CommissionPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PaymentSuccessTracker
{
    /**
     * Track payment processing attempt
     *
     * @param int $paymentId
     * @param string $status
     * @param array $metadata
     * @return void
     */
    public function trackPaymentAttempt(int $paymentId, string $status, array $metadata = []): void
    {
        $trackingData = [
            'payment_id' => $paymentId,
            'status' => $status,
            'timestamp' => now()->toISOString(),
            'metadata' => $metadata
        ];

        // Store in database for historical tracking
        DB::table('payment_tracking')->insert([
            'payment_id' => $paymentId,
            'status' => $status,
            'metadata' => json_encode($metadata),
            'tracked_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Update real-time metrics in cache
        $this->updateRealTimeMetrics($status);

        // Log the tracking event
        Log::channel('payment_tracking')->info('Payment attempt tracked', $trackingData);
    }

    /**
     * Get payment success rate for a given period
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string|null $region
     * @return array
     */
    public function getSuccessRate(Carbon $startDate, Carbon $endDate, string $region = null): array
    {
        $query = CommissionPayment::whereBetween('created_at', [$startDate, $endDate]);
        
        if ($region) {
            $query->whereHas('marketer', function($q) use ($region) {
                $q->where('state', $region);
            });
        }

        $payments = $query->get();
        
        $totalPayments = $payments->count();
        $successfulPayments = $payments->where('payment_status', 'completed')->count();
        $failedPayments = $payments->where('payment_status', 'failed')->count();
        $pendingPayments = $payments->where('payment_status', 'pending')->count();

        $successRate = $totalPayments > 0 ? 
            round(($successfulPayments / $totalPayments) * 100, 2) : 100;

        return [
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
                'region' => $region
            ],
            'total_payments' => $totalPayments,
            'successful_payments' => $successfulPayments,
            'failed_payments' => $failedPayments,
            'pending_payments' => $pendingPayments,
            'success_rate' => $successRate,
            'failure_rate' => $totalPayments > 0 ? 
                round(($failedPayments / $totalPayments) * 100, 2) : 0,
            'pending_rate' => $totalPayments > 0 ? 
                round(($pendingPayments / $totalPayments) * 100, 2) : 0
        ];
    }

    /**
     * Get payment processing trends
     *
     * @param int $days
     * @return array
     */
    public function getProcessingTrends(int $days = 7): array
    {
        $trends = [];
        $endDate = Carbon::now();
        
        for ($i = 0; $i < $days; $i++) {
            $date = $endDate->copy()->subDays($i);
            $startOfDay = $date->copy()->startOfDay();
            $endOfDay = $date->copy()->endOfDay();
            
            $dayMetrics = $this->getSuccessRate($startOfDay, $endOfDay);
            $trends[] = [
                'date' => $date->toDateString(),
                'success_rate' => $dayMetrics['success_rate'],
                'total_payments' => $dayMetrics['total_payments'],
                'failed_payments' => $dayMetrics['failed_payments']
            ];
        }

        return array_reverse($trends); // Return in chronological order
    }

    /**
     * Get payment failure analysis
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getFailureAnalysis(Carbon $startDate, Carbon $endDate): array
    {
        // Get failed payments with tracking data
        $failedPayments = DB::table('commission_payments')
            ->leftJoin('payment_tracking', 'commission_payments.id', '=', 'payment_tracking.payment_id')
            ->where('commission_payments.payment_status', 'failed')
            ->whereBetween('commission_payments.created_at', [$startDate, $endDate])
            ->select([
                'commission_payments.*',
                'payment_tracking.metadata',
                'payment_tracking.tracked_at'
            ])
            ->get();

        // Analyze failure reasons
        $failureReasons = [];
        $failuresByHour = [];
        $failuresByRegion = [];

        foreach ($failedPayments as $payment) {
            $metadata = json_decode($payment->metadata ?? '{}', true);
            $reason = $metadata['failure_reason'] ?? 'unknown';
            
            // Count failure reasons
            $failureReasons[$reason] = ($failureReasons[$reason] ?? 0) + 1;
            
            // Count failures by hour
            $hour = Carbon::parse($payment->created_at)->format('H:00');
            $failuresByHour[$hour] = ($failuresByHour[$hour] ?? 0) + 1;
            
            // Count failures by region (if available)
            if (isset($metadata['region'])) {
                $region = $metadata['region'];
                $failuresByRegion[$region] = ($failuresByRegion[$region] ?? 0) + 1;
            }
        }

        return [
            'total_failures' => $failedPayments->count(),
            'failure_reasons' => $failureReasons,
            'failures_by_hour' => $failuresByHour,
            'failures_by_region' => $failuresByRegion,
            'analysis_period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString()
            ]
        ];
    }

    /**
     * Get real-time payment metrics
     *
     * @return array
     */
    public function getRealTimeMetrics(): array
    {
        $cacheKey = 'payment_realtime_metrics';
        
        return Cache::get($cacheKey, [
            'successful_payments_last_hour' => 0,
            'failed_payments_last_hour' => 0,
            'pending_payments_last_hour' => 0,
            'average_processing_time_minutes' => 0,
            'current_success_rate' => 100,
            'last_updated' => now()->toISOString()
        ]);
    }

    /**
     * Track payment processing time
     *
     * @param int $paymentId
     * @param Carbon $startTime
     * @param Carbon $endTime
     * @return void
     */
    public function trackProcessingTime(int $paymentId, Carbon $startTime, Carbon $endTime): void
    {
        $processingTimeMinutes = $endTime->diffInMinutes($startTime);
        
        // Update payment record
        CommissionPayment::where('id', $paymentId)->update([
            'processing_started_at' => $startTime,
            'processed_at' => $endTime,
            'processing_time_minutes' => $processingTimeMinutes
        ]);

        // Track in payment tracking table
        $this->trackPaymentAttempt($paymentId, 'processing_time_recorded', [
            'processing_time_minutes' => $processingTimeMinutes,
            'started_at' => $startTime->toISOString(),
            'completed_at' => $endTime->toISOString()
        ]);

        // Update average processing time cache
        $this->updateAverageProcessingTime($processingTimeMinutes);
    }

    /**
     * Get payment processing bottlenecks
     *
     * @return array
     */
    public function getProcessingBottlenecks(): array
    {
        $bottlenecks = [];
        $now = Carbon::now();
        
        // Check for payments stuck in pending status
        $stuckPending = CommissionPayment::where('payment_status', 'pending')
            ->where('created_at', '<', $now->copy()->subHours(2))
            ->count();

        if ($stuckPending > 0) {
            $bottlenecks[] = [
                'type' => 'stuck_pending_payments',
                'count' => $stuckPending,
                'severity' => $stuckPending > 10 ? 'critical' : 'warning',
                'description' => "Found {$stuckPending} payments stuck in pending status for over 2 hours"
            ];
        }

        // Check for high processing times
        $slowPayments = CommissionPayment::where('processing_time_minutes', '>', 30)
            ->where('created_at', '>=', $now->copy()->subHours(24))
            ->count();

        if ($slowPayments > 0) {
            $bottlenecks[] = [
                'type' => 'slow_processing',
                'count' => $slowPayments,
                'severity' => $slowPayments > 20 ? 'critical' : 'warning',
                'description' => "Found {$slowPayments} payments with processing time over 30 minutes"
            ];
        }

        // Check for repeated failures
        $repeatedFailures = DB::table('commission_payments')
            ->select('marketer_id', DB::raw('COUNT(*) as failure_count'))
            ->where('payment_status', 'failed')
            ->where('created_at', '>=', $now->copy()->subHours(24))
            ->groupBy('marketer_id')
            ->having('failure_count', '>', 3)
            ->get();

        if ($repeatedFailures->count() > 0) {
            $bottlenecks[] = [
                'type' => 'repeated_failures',
                'count' => $repeatedFailures->count(),
                'severity' => 'warning',
                'description' => "Found {$repeatedFailures->count()} marketers with more than 3 payment failures in 24 hours",
                'affected_marketers' => $repeatedFailures->pluck('marketer_id')->toArray()
            ];
        }

        return $bottlenecks;
    }

    /**
     * Create payment processing alert
     *
     * @param string $alertType
     * @param array $data
     * @param string $severity
     * @return void
     */
    public function createProcessingAlert(string $alertType, array $data, string $severity = 'warning'): void
    {
        $alert = [
            'type' => $alertType,
            'severity' => $severity,
            'data' => $data,
            'created_at' => now()->toISOString(),
            'component' => 'payment_processing'
        ];

        // Store alert in cache
        $alertKey = "payment_alert_{$alertType}_" . time();
        Cache::put($alertKey, $alert, 3600); // Store for 1 hour

        // Log the alert
        Log::channel('payment_monitoring')->{$severity}('Payment processing alert', $alert);

        // Store in database
        DB::table('audit_logs')->insert([
            'audit_type' => 'payment_alert',
            'reference_type' => $alertType,
            'audit_data' => json_encode($alert),
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent() ?? 'System',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Update real-time metrics in cache
     *
     * @param string $status
     * @return void
     */
    private function updateRealTimeMetrics(string $status): void
    {
        $cacheKey = 'payment_realtime_metrics';
        $metrics = Cache::get($cacheKey, [
            'successful_payments_last_hour' => 0,
            'failed_payments_last_hour' => 0,
            'pending_payments_last_hour' => 0,
            'current_success_rate' => 100
        ]);

        // Increment appropriate counter
        switch ($status) {
            case 'completed':
                $metrics['successful_payments_last_hour']++;
                break;
            case 'failed':
                $metrics['failed_payments_last_hour']++;
                break;
            case 'pending':
                $metrics['pending_payments_last_hour']++;
                break;
        }

        // Recalculate success rate
        $total = $metrics['successful_payments_last_hour'] + $metrics['failed_payments_last_hour'];
        if ($total > 0) {
            $metrics['current_success_rate'] = round(
                ($metrics['successful_payments_last_hour'] / $total) * 100, 
                2
            );
        }

        $metrics['last_updated'] = now()->toISOString();

        // Store updated metrics
        Cache::put($cacheKey, $metrics, 3600); // Cache for 1 hour
    }

    /**
     * Update average processing time
     *
     * @param float $newProcessingTime
     * @return void
     */
    private function updateAverageProcessingTime(float $newProcessingTime): void
    {
        $cacheKey = 'payment_avg_processing_time';
        $currentAvg = Cache::get($cacheKey, ['average' => 0, 'count' => 0]);
        
        $newCount = $currentAvg['count'] + 1;
        $newAverage = (($currentAvg['average'] * $currentAvg['count']) + $newProcessingTime) / $newCount;
        
        Cache::put($cacheKey, [
            'average' => round($newAverage, 2),
            'count' => $newCount,
            'last_updated' => now()->toISOString()
        ], 3600);

        // Update real-time metrics
        $metricsKey = 'payment_realtime_metrics';
        $metrics = Cache::get($metricsKey, []);
        $metrics['average_processing_time_minutes'] = round($newAverage, 2);
        Cache::put($metricsKey, $metrics, 3600);
    }

    /**
     * Reset hourly metrics (called by scheduler)
     *
     * @return void
     */
    public function resetHourlyMetrics(): void
    {
        $cacheKey = 'payment_realtime_metrics';
        $metrics = Cache::get($cacheKey, []);
        
        // Reset hourly counters but keep other metrics
        $metrics['successful_payments_last_hour'] = 0;
        $metrics['failed_payments_last_hour'] = 0;
        $metrics['pending_payments_last_hour'] = 0;
        $metrics['last_reset'] = now()->toISOString();
        
        Cache::put($cacheKey, $metrics, 3600);
        
        Log::info('Payment tracking hourly metrics reset');
    }
}