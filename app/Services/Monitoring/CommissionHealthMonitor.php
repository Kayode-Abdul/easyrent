<?php

namespace App\Services\Monitoring;

use App\Models\CommissionPayment;
use App\Models\ReferralChain;
use App\Models\User;
use App\Services\Audit\CommissionAuditService;
use App\Services\Fraud\FraudDetectionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class CommissionHealthMonitor
{
    protected CommissionAuditService $auditService;
    protected FraudDetectionService $fraudService;

    public function __construct(
        CommissionAuditService $auditService,
        FraudDetectionService $fraudService
    ) {
        $this->auditService = $auditService;
        $this->fraudService = $fraudService;
    }

    /**
     * Get real-time commission system health metrics
     *
     * @return array
     */
    public function getSystemHealthMetrics(): array
    {
        $cacheKey = 'commission_health_metrics';
        
        return Cache::remember($cacheKey, 300, function () { // Cache for 5 minutes
            return [
                'calculation_health' => $this->getCalculationHealthMetrics(),
                'payment_processing' => $this->getPaymentProcessingMetrics(),
                'fraud_detection' => $this->getFraudDetectionMetrics(),
                'system_performance' => $this->getSystemPerformanceMetrics(),
                'error_rates' => $this->getErrorRateMetrics(),
                'last_updated' => now()->toISOString()
            ];
        });
    }

    /**
     * Monitor commission calculation health
     *
     * @return array
     */
    public function getCalculationHealthMetrics(): array
    {
        $last24Hours = Carbon::now()->subHours(24);
        
        // Get calculation statistics
        $totalCalculations = DB::table('audit_logs')
            ->where('audit_type', 'commission_calculation')
            ->where('created_at', '>=', $last24Hours)
            ->count();

        $failedCalculations = DB::table('audit_logs')
            ->where('audit_type', 'commission_error')
            ->where('created_at', '>=', $last24Hours)
            ->whereRaw("JSON_EXTRACT(audit_data, '$.severity') IN ('error', 'critical')")
            ->count();

        $successRate = $totalCalculations > 0 ? 
            round((($totalCalculations - $failedCalculations) / $totalCalculations) * 100, 2) : 100;

        // Get average calculation time
        $avgCalculationTime = DB::table('performance_logs')
            ->where('controller_action', 'LIKE', '%Commission%')
            ->where('created_at', '>=', $last24Hours)
            ->avg('execution_time');

        // Check for calculation anomalies
        $anomalies = $this->detectCalculationAnomalies();

        return [
            'total_calculations' => $totalCalculations,
            'failed_calculations' => $failedCalculations,
            'success_rate' => $successRate,
            'average_calculation_time_ms' => round($avgCalculationTime ?? 0, 2),
            'anomalies_detected' => count($anomalies),
            'anomalies' => $anomalies,
            'health_status' => $this->determineCalculationHealthStatus($successRate, $anomalies)
        ];
    }

    /**
     * Monitor payment processing success rates
     *
     * @return array
     */
    public function getPaymentProcessingMetrics(): array
    {
        $last24Hours = Carbon::now()->subHours(24);
        
        // Get payment statistics
        $totalPayments = CommissionPayment::where('created_at', '>=', $last24Hours)->count();
        $successfulPayments = CommissionPayment::where('created_at', '>=', $last24Hours)
            ->where('payment_status', 'completed')
            ->count();
        $failedPayments = CommissionPayment::where('created_at', '>=', $last24Hours)
            ->where('payment_status', 'failed')
            ->count();
        $pendingPayments = CommissionPayment::where('created_at', '>=', $last24Hours)
            ->where('payment_status', 'pending')
            ->count();

        $successRate = $totalPayments > 0 ? 
            round(($successfulPayments / $totalPayments) * 100, 2) : 100;

        // Get payment processing times
        $avgProcessingTime = DB::table('commission_payments')
            ->where('created_at', '>=', $last24Hours)
            ->where('payment_status', 'completed')
            ->whereNotNull('payment_date')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, payment_date)) as avg_time')
            ->value('avg_time');

        // Check for payment bottlenecks
        $bottlenecks = $this->detectPaymentBottlenecks();

        return [
            'total_payments' => $totalPayments,
            'successful_payments' => $successfulPayments,
            'failed_payments' => $failedPayments,
            'pending_payments' => $pendingPayments,
            'success_rate' => $successRate,
            'average_processing_time_seconds' => round($avgProcessingTime ?? 0, 2),
            'bottlenecks_detected' => count($bottlenecks),
            'bottlenecks' => $bottlenecks,
            'health_status' => $this->determinePaymentHealthStatus($successRate, $pendingPayments, $failedPayments)
        ];
    }

    /**
     * Monitor fraud detection system
     *
     * @return array
     */
    public function getFraudDetectionMetrics(): array
    {
        $last24Hours = Carbon::now()->subHours(24);
        
        // Get fraud detection statistics
        $totalChecks = DB::table('audit_logs')
            ->where('audit_type', 'fraud_check')
            ->where('created_at', '>=', $last24Hours)
            ->count();

        $flaggedAccounts = User::where('flagged_for_review', true)
            ->where('flagged_at', '>=', $last24Hours)
            ->count();

        $suspiciousReferrals = DB::table('referrals')
            ->where('is_flagged', true)
            ->where('created_at', '>=', $last24Hours)
            ->count();

        // Get fraud detection alerts
        $alerts = $this->getActiveFraudAlerts();

        // Calculate fraud detection rate
        $totalReferrals = DB::table('referrals')
            ->where('created_at', '>=', $last24Hours)
            ->count();

        $detectionRate = $totalReferrals > 0 ? 
            round(($suspiciousReferrals / $totalReferrals) * 100, 2) : 0;

        return [
            'total_fraud_checks' => $totalChecks,
            'flagged_accounts' => $flaggedAccounts,
            'suspicious_referrals' => $suspiciousReferrals,
            'active_alerts' => count($alerts),
            'alerts' => $alerts,
            'detection_rate' => $detectionRate,
            'health_status' => $this->determineFraudHealthStatus($alerts, $detectionRate)
        ];
    }

    /**
     * Monitor system performance metrics
     *
     * @return array
     */
    public function getSystemPerformanceMetrics(): array
    {
        $last24Hours = Carbon::now()->subHours(24);
        
        // Get performance statistics from performance_logs
        $performanceStats = DB::table('performance_logs')
            ->where('created_at', '>=', $last24Hours)
            ->selectRaw('
                AVG(execution_time) as avg_execution_time,
                MAX(execution_time) as max_execution_time,
                AVG(memory_usage) as avg_memory_usage,
                MAX(memory_usage) as max_memory_usage,
                AVG(query_count) as avg_query_count,
                MAX(query_count) as max_query_count,
                COUNT(*) as total_requests
            ')
            ->first();

        // Get slow requests
        $slowRequests = DB::table('performance_logs')
            ->where('created_at', '>=', $last24Hours)
            ->where('execution_time', '>', 1000) // Slower than 1 second
            ->count();

        // Get database performance
        $dbPerformance = $this->getDatabasePerformanceMetrics();

        return [
            'average_response_time_ms' => round($performanceStats->avg_execution_time ?? 0, 2),
            'max_response_time_ms' => round($performanceStats->max_execution_time ?? 0, 2),
            'average_memory_usage_bytes' => round($performanceStats->avg_memory_usage ?? 0),
            'max_memory_usage_bytes' => round($performanceStats->max_memory_usage ?? 0),
            'average_query_count' => round($performanceStats->avg_query_count ?? 0, 2),
            'max_query_count' => $performanceStats->max_query_count ?? 0,
            'total_requests' => $performanceStats->total_requests ?? 0,
            'slow_requests' => $slowRequests,
            'database_performance' => $dbPerformance,
            'health_status' => $this->determinePerformanceHealthStatus($performanceStats, $slowRequests)
        ];
    }

    /**
     * Get error rate metrics
     *
     * @return array
     */
    public function getErrorRateMetrics(): array
    {
        $last24Hours = Carbon::now()->subHours(24);
        
        // Get error statistics from logs
        $errorStats = DB::table('audit_logs')
            ->where('created_at', '>=', $last24Hours)
            ->selectRaw('
                COUNT(*) as total_logs,
                SUM(CASE WHEN audit_type = "commission_error" THEN 1 ELSE 0 END) as commission_errors,
                SUM(CASE WHEN audit_type = "commission_error" AND JSON_EXTRACT(audit_data, "$.severity") = "critical" THEN 1 ELSE 0 END) as critical_errors,
                SUM(CASE WHEN audit_type = "commission_error" AND JSON_EXTRACT(audit_data, "$.severity") = "error" THEN 1 ELSE 0 END) as errors,
                SUM(CASE WHEN audit_type = "commission_error" AND JSON_EXTRACT(audit_data, "$.severity") = "warning" THEN 1 ELSE 0 END) as warnings
            ')
            ->first();

        // Get HTTP error rates
        $httpErrors = DB::table('performance_logs')
            ->where('created_at', '>=', $last24Hours)
            ->selectRaw('
                COUNT(*) as total_requests,
                SUM(CASE WHEN status_code >= 400 AND status_code < 500 THEN 1 ELSE 0 END) as client_errors,
                SUM(CASE WHEN status_code >= 500 THEN 1 ELSE 0 END) as server_errors
            ')
            ->first();

        $totalRequests = $httpErrors->total_requests ?? 0;
        $errorRate = $totalRequests > 0 ? 
            round((($httpErrors->client_errors + $httpErrors->server_errors) / $totalRequests) * 100, 2) : 0;

        return [
            'total_logs' => $errorStats->total_logs ?? 0,
            'commission_errors' => $errorStats->commission_errors ?? 0,
            'critical_errors' => $errorStats->critical_errors ?? 0,
            'errors' => $errorStats->errors ?? 0,
            'warnings' => $errorStats->warnings ?? 0,
            'http_error_rate' => $errorRate,
            'client_errors' => $httpErrors->client_errors ?? 0,
            'server_errors' => $httpErrors->server_errors ?? 0,
            'health_status' => $this->determineErrorHealthStatus($errorStats, $errorRate)
        ];
    }

    /**
     * Create real-time alert for critical issues
     *
     * @param string $alertType
     * @param array $alertData
     * @param string $severity
     * @return void
     */
    public function createAlert(string $alertType, array $alertData, string $severity = 'warning'): void
    {
        $alert = [
            'type' => $alertType,
            'severity' => $severity,
            'data' => $alertData,
            'created_at' => now()->toISOString(),
            'status' => 'active'
        ];

        // Store alert in cache for real-time access
        $alertKey = "commission_alert_{$alertType}_" . time();
        Cache::put($alertKey, $alert, 3600); // Store for 1 hour

        // Log the alert
        Log::channel('commission_monitoring')->{$severity}('Commission system alert', $alert);

        // Send notifications for critical alerts
        if ($severity === 'critical') {
            $this->sendCriticalAlert($alertType, $alertData);
        }

        // Store in database for historical tracking
        DB::table('audit_logs')->insert([
            'audit_type' => 'system_alert',
            'reference_type' => $alertType,
            'audit_data' => json_encode($alert),
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent() ?? 'System',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Get active system alerts
     *
     * @return array
     */
    public function getActiveAlerts(): array
    {
        $alertKeys = Cache::get('commission_alert_keys', []);
        $alerts = [];

        foreach ($alertKeys as $key) {
            $alert = Cache::get($key);
            if ($alert && $alert['status'] === 'active') {
                $alerts[] = $alert;
            }
        }

        // Sort by severity and creation time
        usort($alerts, function($a, $b) {
            $severityOrder = ['critical' => 3, 'error' => 2, 'warning' => 1];
            $aSeverity = $severityOrder[$a['severity']] ?? 0;
            $bSeverity = $severityOrder[$b['severity']] ?? 0;
            
            if ($aSeverity === $bSeverity) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            }
            
            return $bSeverity - $aSeverity;
        });

        return $alerts;
    }

    /**
     * Detect calculation anomalies
     *
     * @return array
     */
    private function detectCalculationAnomalies(): array
    {
        $anomalies = [];
        $last24Hours = Carbon::now()->subHours(24);

        // Check for unusual commission amounts
        $unusualAmounts = CommissionPayment::where('created_at', '>=', $last24Hours)
            ->where(function($query) {
                $query->where('total_amount', '>', 1000) // Unusually high commission
                      ->orWhere('total_amount', '<', 0.01); // Unusually low commission
            })
            ->count();

        if ($unusualAmounts > 0) {
            $anomalies[] = [
                'type' => 'unusual_commission_amounts',
                'count' => $unusualAmounts,
                'description' => 'Detected commission payments with unusual amounts'
            ];
        }

        // Check for rate calculation errors
        $rateErrors = DB::table('audit_logs')
            ->where('audit_type', 'commission_error')
            ->where('created_at', '>=', $last24Hours)
            ->whereRaw("JSON_EXTRACT(audit_data, '$.error_type') = 'rate_calculation'")
            ->count();

        if ($rateErrors > 0) {
            $anomalies[] = [
                'type' => 'rate_calculation_errors',
                'count' => $rateErrors,
                'description' => 'Detected errors in commission rate calculations'
            ];
        }

        return $anomalies;
    }

    /**
     * Detect payment processing bottlenecks
     *
     * @return array
     */
    private function detectPaymentBottlenecks(): array
    {
        $bottlenecks = [];
        $last24Hours = Carbon::now()->subHours(24);

        // Check for payments stuck in pending status
        $stuckPayments = CommissionPayment::where('payment_status', 'pending')
            ->where('created_at', '<', Carbon::now()->subHours(2))
            ->count();

        if ($stuckPayments > 0) {
            $bottlenecks[] = [
                'type' => 'stuck_pending_payments',
                'count' => $stuckPayments,
                'description' => 'Payments stuck in pending status for over 2 hours'
            ];
        }

        // Check for high failure rates
        $recentFailures = CommissionPayment::where('payment_status', 'failed')
            ->where('created_at', '>=', $last24Hours)
            ->count();

        $totalRecent = CommissionPayment::where('created_at', '>=', $last24Hours)->count();
        
        if ($totalRecent > 0 && ($recentFailures / $totalRecent) > 0.1) { // More than 10% failure rate
            $bottlenecks[] = [
                'type' => 'high_failure_rate',
                'failure_rate' => round(($recentFailures / $totalRecent) * 100, 2),
                'description' => 'High payment failure rate detected'
            ];
        }

        return $bottlenecks;
    }

    /**
     * Get active fraud alerts
     *
     * @return array
     */
    private function getActiveFraudAlerts(): array
    {
        $alerts = [];
        $last24Hours = Carbon::now()->subHours(24);

        // Check for high-risk users
        $highRiskUsers = User::where('flagged_for_review', true)
            ->where('flagged_at', '>=', $last24Hours)
            ->count();

        if ($highRiskUsers > 5) { // More than 5 users flagged in 24 hours
            $alerts[] = [
                'type' => 'high_risk_users',
                'count' => $highRiskUsers,
                'description' => 'Unusual number of users flagged for review'
            ];
        }

        // Check for circular referral attempts
        $circularAttempts = DB::table('audit_logs')
            ->where('audit_type', 'fraud_check')
            ->where('created_at', '>=', $last24Hours)
            ->whereRaw("JSON_EXTRACT(audit_data, '$.type') = 'circular_referral'")
            ->count();

        if ($circularAttempts > 0) {
            $alerts[] = [
                'type' => 'circular_referral_attempts',
                'count' => $circularAttempts,
                'description' => 'Detected attempts to create circular referrals'
            ];
        }

        return $alerts;
    }

    /**
     * Get database performance metrics
     *
     * @return array
     */
    private function getDatabasePerformanceMetrics(): array
    {
        try {
            // Get slow query count
            $slowQueries = DB::table('performance_logs')
                ->where('created_at', '>=', Carbon::now()->subHours(24))
                ->where('query_count', '>', 50) // More than 50 queries per request
                ->count();

            // Get connection pool status (simplified)
            $connectionCount = DB::select("SHOW STATUS LIKE 'Threads_connected'")[0]->Value ?? 0;
            $maxConnections = DB::select("SHOW VARIABLES LIKE 'max_connections'")[0]->Value ?? 100;

            return [
                'slow_queries' => $slowQueries,
                'active_connections' => $connectionCount,
                'max_connections' => $maxConnections,
                'connection_usage_percent' => round(($connectionCount / $maxConnections) * 100, 2)
            ];
        } catch (\Exception $e) {
            Log::warning('Failed to get database performance metrics', ['error' => $e->getMessage()]);
            return [
                'slow_queries' => 0,
                'active_connections' => 0,
                'max_connections' => 0,
                'connection_usage_percent' => 0
            ];
        }
    }

    /**
     * Determine calculation health status
     *
     * @param float $successRate
     * @param array $anomalies
     * @return string
     */
    private function determineCalculationHealthStatus(float $successRate, array $anomalies): string
    {
        if ($successRate < 90 || count($anomalies) > 5) {
            return 'critical';
        } elseif ($successRate < 95 || count($anomalies) > 2) {
            return 'warning';
        }
        return 'healthy';
    }

    /**
     * Determine payment health status
     *
     * @param float $successRate
     * @param int $pendingPayments
     * @param int $failedPayments
     * @return string
     */
    private function determinePaymentHealthStatus(float $successRate, int $pendingPayments, int $failedPayments): string
    {
        if ($successRate < 85 || $failedPayments > 10) {
            return 'critical';
        } elseif ($successRate < 95 || $pendingPayments > 20) {
            return 'warning';
        }
        return 'healthy';
    }

    /**
     * Determine fraud detection health status
     *
     * @param array $alerts
     * @param float $detectionRate
     * @return string
     */
    private function determineFraudHealthStatus(array $alerts, float $detectionRate): string
    {
        $criticalAlerts = array_filter($alerts, function($alert) {
            return $alert['type'] === 'circular_referral_attempts' || 
                   ($alert['type'] === 'high_risk_users' && $alert['count'] > 10);
        });

        if (count($criticalAlerts) > 0 || $detectionRate > 15) {
            return 'critical';
        } elseif (count($alerts) > 2 || $detectionRate > 10) {
            return 'warning';
        }
        return 'healthy';
    }

    /**
     * Determine performance health status
     *
     * @param object $performanceStats
     * @param int $slowRequests
     * @return string
     */
    private function determinePerformanceHealthStatus($performanceStats, int $slowRequests): string
    {
        $avgTime = $performanceStats->avg_execution_time ?? 0;
        $maxTime = $performanceStats->max_execution_time ?? 0;

        if ($avgTime > 2000 || $maxTime > 10000 || $slowRequests > 50) {
            return 'critical';
        } elseif ($avgTime > 1000 || $maxTime > 5000 || $slowRequests > 20) {
            return 'warning';
        }
        return 'healthy';
    }

    /**
     * Determine error health status
     *
     * @param object $errorStats
     * @param float $errorRate
     * @return string
     */
    private function determineErrorHealthStatus($errorStats, float $errorRate): string
    {
        $criticalErrors = $errorStats->critical_errors ?? 0;
        $totalErrors = $errorStats->commission_errors ?? 0;

        if ($criticalErrors > 0 || $errorRate > 5 || $totalErrors > 20) {
            return 'critical';
        } elseif ($errorRate > 2 || $totalErrors > 10) {
            return 'warning';
        }
        return 'healthy';
    }

    /**
     * Send critical alert notification
     *
     * @param string $alertType
     * @param array $alertData
     * @return void
     */
    private function sendCriticalAlert(string $alertType, array $alertData): void
    {
        try {
            // Get admin users
            $adminUsers = User::whereHas('roles', function($q) {
                $q->where('name', 'admin');
            })->get();

            foreach ($adminUsers as $admin) {
                $admin->notify(new \App\Notifications\CriticalCommissionError($alertType, $alertData));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send critical alert notification', [
                'alert_type' => $alertType,
                'error' => $e->getMessage()
            ]);
        }
    }
}