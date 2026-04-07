<?php

namespace App\Services\Monitoring;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\Logging\EasyRentLogger;
use Carbon\Carbon;

class ErrorMonitoringService
{
    protected $logger;

    public function __construct(EasyRentLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Monitor and track error patterns
     */
    public function trackError(Throwable $exception, Request $request, string $errorType, array $context = []): void
    {
        $errorData = [
            'error_type' => $errorType,
            'exception_class' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'timestamp' => now()->toISOString(),
            'context' => $context
        ];

        // Store error for pattern analysis
        $this->storeErrorForAnalysis($errorData);
        
        // Update error metrics
        $this->updateErrorMetrics($errorType);
        
        // Check for error patterns
        $this->analyzeErrorPatterns($errorType, $request->ip());
        
        // Log comprehensive error details
        $this->logger->logError('Error tracked by monitoring service', $exception, $request, $errorData);
    }

    /**
     * Get error statistics and patterns
     */
    public function getErrorStatistics(int $hours = 24): array
    {
        $cacheKey = "error_stats_last_{$hours}_hours";
        
        return Cache::remember($cacheKey, now()->addMinutes(5), function() use ($hours) {
            $cutoff = now()->subHours($hours);
            
            return [
                'total_errors' => $this->getErrorCount($cutoff),
                'error_types' => $this->getErrorTypeBreakdown($cutoff),
                'error_trends' => $this->getErrorTrends($cutoff),
                'critical_errors' => $this->getCriticalErrors($cutoff),
                'recovery_success_rate' => $this->getRecoverySuccessRate($cutoff)
            ];
        });
    }

    /**
     * Check system health based on error patterns
     */
    public function checkSystemHealth(): array
    {
        $recentErrors = $this->getRecentErrorCount(15); // Last 15 minutes
        $errorRate = $this->calculateErrorRate();
        $criticalErrors = $this->getRecentCriticalErrors(60); // Last hour
        
        $healthStatus = 'healthy';
        $issues = [];
        
        if ($recentErrors > 50) {
            $healthStatus = 'degraded';
            $issues[] = 'High error rate detected';
        }
        
        if ($criticalErrors > 5) {
            $healthStatus = 'critical';
            $issues[] = 'Multiple critical errors detected';
        }
        
        if ($errorRate > 0.1) { // 10% error rate
            $healthStatus = 'degraded';
            $issues[] = 'Error rate exceeds threshold';
        }
        
        return [
            'status' => $healthStatus,
            'issues' => $issues,
            'metrics' => [
                'recent_errors' => $recentErrors,
                'error_rate' => $errorRate,
                'critical_errors' => $criticalErrors
            ],
            'checked_at' => now()->toISOString()
        ];
    }

    /**
     * Store error data for pattern analysis
     */
    private function storeErrorForAnalysis(array $errorData): void
    {
        $errorKey = "error_" . time() . "_" . substr(md5(json_encode($errorData)), 0, 8);
        Cache::put($errorKey, $errorData, now()->addDays(7));
        
        // Add to error list for quick retrieval
        $errorList = Cache::get('recent_errors', []);
        $errorList[] = $errorKey;
        
        // Keep only last 1000 errors
        if (count($errorList) > 1000) {
            $oldestKey = array_shift($errorList);
            Cache::forget($oldestKey);
        }
        
        Cache::put('recent_errors', $errorList, now()->addDays(7));
    }

    /**
     * Update error metrics counters
     */
    private function updateErrorMetrics(string $errorType): void
    {
        $today = now()->format('Y-m-d');
        $hour = now()->format('H');
        
        // Daily counters
        Cache::increment("errors_daily_{$today}");
        Cache::increment("errors_daily_{$today}_{$errorType}");
        
        // Hourly counters
        Cache::increment("errors_hourly_{$today}_{$hour}");
        Cache::increment("errors_hourly_{$today}_{$hour}_{$errorType}");
        
        // Set expiration for counters
        Cache::put("errors_daily_{$today}", Cache::get("errors_daily_{$today}", 0), now()->addDays(30));
        Cache::put("errors_hourly_{$today}_{$hour}", Cache::get("errors_hourly_{$today}_{$hour}", 0), now()->addDays(7));
    }

    /**
     * Analyze error patterns for anomalies
     */
    private function analyzeErrorPatterns(string $errorType, string $ipAddress): void
    {
        // Check for error spikes from same IP
        $ipErrorCount = Cache::increment("ip_errors_{$ipAddress}_" . now()->format('Y-m-d-H'), 1);
        Cache::put("ip_errors_{$ipAddress}_" . now()->format('Y-m-d-H'), $ipErrorCount, now()->addHours(2));
        
        if ($ipErrorCount > 20) { // Threshold for suspicious activity
            $this->flagSuspiciousActivity($ipAddress, $errorType, $ipErrorCount);
        }
        
        // Check for error type spikes
        $typeErrorCount = Cache::increment("type_errors_{$errorType}_" . now()->format('Y-m-d-H'), 1);
        Cache::put("type_errors_{$errorType}_" . now()->format('Y-m-d-H'), $typeErrorCount, now()->addHours(2));
        
        if ($typeErrorCount > 100) { // Threshold for system issues
            $this->flagSystemIssue($errorType, $typeErrorCount);
        }
    }

    /**
     * Get error count since cutoff time
     */
    private function getErrorCount(Carbon $cutoff): int
    {
        $errorList = Cache::get('recent_errors', []);
        $count = 0;
        
        foreach ($errorList as $errorKey) {
            $errorData = Cache::get($errorKey);
            if ($errorData && Carbon::parse($errorData['timestamp'])->gte($cutoff)) {
                $count++;
            }
        }
        
        return $count;
    }

    /**
     * Get error type breakdown
     */
    private function getErrorTypeBreakdown(Carbon $cutoff): array
    {
        $errorList = Cache::get('recent_errors', []);
        $breakdown = [];
        
        foreach ($errorList as $errorKey) {
            $errorData = Cache::get($errorKey);
            if ($errorData && Carbon::parse($errorData['timestamp'])->gte($cutoff)) {
                $type = $errorData['error_type'];
                $breakdown[$type] = ($breakdown[$type] ?? 0) + 1;
            }
        }
        
        return $breakdown;
    }

    /**
     * Get error trends over time
     */
    private function getErrorTrends(Carbon $cutoff): array
    {
        $trends = [];
        $current = $cutoff->copy();
        
        while ($current->lt(now())) {
            $hourKey = $current->format('Y-m-d-H');
            $trends[$hourKey] = Cache::get("errors_hourly_{$hourKey}", 0);
            $current->addHour();
        }
        
        return $trends;
    }

    /**
     * Get critical errors
     */
    private function getCriticalErrors(Carbon $cutoff): array
    {
        $errorList = Cache::get('recent_errors', []);
        $criticalErrors = [];
        
        $criticalTypes = [
            'database_error',
            'payment_gateway_down',
            'security_breach_detected',
            'system_error'
        ];
        
        foreach ($errorList as $errorKey) {
            $errorData = Cache::get($errorKey);
            if ($errorData && 
                Carbon::parse($errorData['timestamp'])->gte($cutoff) &&
                in_array($errorData['error_type'], $criticalTypes)) {
                $criticalErrors[] = $errorData;
            }
        }
        
        return $criticalErrors;
    }

    /**
     * Calculate recovery success rate
     */
    private function getRecoverySuccessRate(Carbon $cutoff): float
    {
        $recoveryAttempts = Cache::get('recovery_attempts', []);
        $successfulRecoveries = Cache::get('successful_recoveries', []);
        
        $recentAttempts = array_filter($recoveryAttempts, function($attempt) use ($cutoff) {
            return Carbon::parse($attempt['timestamp'])->gte($cutoff);
        });
        
        $recentSuccesses = array_filter($successfulRecoveries, function($success) use ($cutoff) {
            return Carbon::parse($success['timestamp'])->gte($cutoff);
        });
        
        if (empty($recentAttempts)) {
            return 1.0; // No attempts means 100% success rate
        }
        
        return count($recentSuccesses) / count($recentAttempts);
    }

    /**
     * Get recent error count
     */
    private function getRecentErrorCount(int $minutes): int
    {
        $cutoff = now()->subMinutes($minutes);
        return $this->getErrorCount($cutoff);
    }

    /**
     * Calculate current error rate
     */
    private function calculateErrorRate(): float
    {
        $totalRequests = Cache::get('total_requests_last_hour', 1);
        $totalErrors = $this->getRecentErrorCount(60);
        
        return $totalErrors / $totalRequests;
    }

    /**
     * Get recent critical errors
     */
    private function getRecentCriticalErrors(int $minutes): int
    {
        $cutoff = now()->subMinutes($minutes);
        $criticalErrors = $this->getCriticalErrors($cutoff);
        
        return count($criticalErrors);
    }

    /**
     * Flag suspicious activity
     */
    private function flagSuspiciousActivity(string $ipAddress, string $errorType, int $errorCount): void
    {
        $suspiciousActivity = [
            'ip_address' => $ipAddress,
            'error_type' => $errorType,
            'error_count' => $errorCount,
            'flagged_at' => now()->toISOString(),
            'requires_investigation' => true
        ];
        
        Cache::put("suspicious_activity_{$ipAddress}_" . time(), $suspiciousActivity, now()->addDays(1));
        
        Log::warning('Suspicious error activity detected', $suspiciousActivity);
    }

    /**
     * Flag system issue
     */
    private function flagSystemIssue(string $errorType, int $errorCount): void
    {
        $systemIssue = [
            'error_type' => $errorType,
            'error_count' => $errorCount,
            'flagged_at' => now()->toISOString(),
            'requires_immediate_attention' => true
        ];
        
        Cache::put("system_issue_{$errorType}_" . time(), $systemIssue, now()->addHours(6));
        
        Log::critical('System issue detected - high error rate', $systemIssue);
    }
}