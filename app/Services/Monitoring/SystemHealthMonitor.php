<?php

namespace App\Services\Monitoring;

use Throwable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SystemHealthMonitor
{
    /**
     * Record a system error for health monitoring
     */
    public function recordSystemError(string $errorType, Throwable $exception): void
    {
        $errorData = [
            'error_type' => $errorType,
            'exception_class' => get_class($exception),
            'message' => $exception->getMessage(),
            'timestamp' => now()->toISOString(),
            'severity' => $this->determineSeverity($errorType, $exception)
        ];

        // Store error for health analysis
        $this->storeHealthError($errorData);
        
        // Update health metrics
        $this->updateHealthMetrics($errorType, $errorData['severity']);
        
        // Check if system health is degraded
        $this->checkSystemHealth();
    }

    /**
     * Get current system health status
     */
    public function getHealthStatus(): array
    {
        $recentErrors = $this->getRecentErrors(60); // Last hour
        $criticalErrors = $this->getCriticalErrors(60);
        $errorRate = $this->calculateErrorRate();
        
        $status = 'healthy';
        $issues = [];
        
        if (count($criticalErrors) > 3) {
            $status = 'critical';
            $issues[] = 'Multiple critical errors detected';
        } elseif (count($recentErrors) > 50) {
            $status = 'degraded';
            $issues[] = 'High error rate detected';
        } elseif ($errorRate > 0.05) {
            $status = 'degraded';
            $issues[] = 'Error rate above threshold';
        }
        
        return [
            'status' => $status,
            'issues' => $issues,
            'metrics' => [
                'recent_errors' => count($recentErrors),
                'critical_errors' => count($criticalErrors),
                'error_rate' => $errorRate
            ],
            'last_checked' => now()->toISOString()
        ];
    }

    /**
     * Check if system requires maintenance
     */
    public function requiresMaintenance(): bool
    {
        $healthStatus = $this->getHealthStatus();
        return $healthStatus['status'] === 'critical';
    }

    // Private helper methods

    private function determineSeverity(string $errorType, Throwable $exception): string
    {
        $criticalTypes = [
            'database_error',
            'payment_gateway_down',
            'security_breach_detected'
        ];
        
        if (in_array($errorType, $criticalTypes)) {
            return 'critical';
        }
        
        $message = strtolower($exception->getMessage());
        if (strpos($message, 'critical') !== false || 
            strpos($message, 'fatal') !== false) {
            return 'critical';
        }
        
        if (strpos($message, 'warning') !== false) {
            return 'warning';
        }
        
        return 'error';
    }

    private function storeHealthError(array $errorData): void
    {
        $errorKey = "health_error_" . time() . "_" . substr(md5(json_encode($errorData)), 0, 8);
        Cache::put($errorKey, $errorData, now()->addDays(1));
        
        // Add to health error list
        $healthErrors = Cache::get('health_errors', []);
        $healthErrors[] = $errorKey;
        
        // Keep only last 500 errors
        if (count($healthErrors) > 500) {
            $oldestKey = array_shift($healthErrors);
            Cache::forget($oldestKey);
        }
        
        Cache::put('health_errors', $healthErrors, now()->addDays(1));
    }

    private function updateHealthMetrics(string $errorType, string $severity): void
    {
        $hour = now()->format('Y-m-d-H');
        
        // Update hourly counters
        Cache::increment("health_errors_hourly_{$hour}");
        Cache::increment("health_errors_hourly_{$hour}_{$severity}");
        Cache::increment("health_errors_hourly_{$hour}_{$errorType}");
        
        // Set expiration
        Cache::put("health_errors_hourly_{$hour}", Cache::get("health_errors_hourly_{$hour}", 0), now()->addDays(7));
    }

    private function checkSystemHealth(): void
    {
        $healthStatus = $this->getHealthStatus();
        
        if ($healthStatus['status'] === 'critical') {
            Log::critical('System health is critical', $healthStatus);
            Cache::put('system_health_critical', true, now()->addMinutes(30));
        } elseif ($healthStatus['status'] === 'degraded') {
            Log::warning('System health is degraded', $healthStatus);
            Cache::put('system_health_degraded', true, now()->addMinutes(15));
        } else {
            Cache::forget('system_health_critical');
            Cache::forget('system_health_degraded');
        }
    }

    private function getRecentErrors(int $minutes): array
    {
        $cutoff = now()->subMinutes($minutes);
        $healthErrors = Cache::get('health_errors', []);
        $recentErrors = [];
        
        foreach ($healthErrors as $errorKey) {
            $errorData = Cache::get($errorKey);
            if ($errorData && Carbon::parse($errorData['timestamp'])->gte($cutoff)) {
                $recentErrors[] = $errorData;
            }
        }
        
        return $recentErrors;
    }

    private function getCriticalErrors(int $minutes): array
    {
        $recentErrors = $this->getRecentErrors($minutes);
        
        return array_filter($recentErrors, function($error) {
            return $error['severity'] === 'critical';
        });
    }

    private function calculateErrorRate(): float
    {
        $totalRequests = Cache::get('total_requests_last_hour', 1);
        $recentErrors = $this->getRecentErrors(60);
        
        return count($recentErrors) / $totalRequests;
    }
}