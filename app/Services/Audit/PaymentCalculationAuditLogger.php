<?php

namespace App\Services\Audit;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\Payment\PaymentCalculationResult;
use Carbon\Carbon;

class PaymentCalculationAuditLogger
{
    /**
     * Cache keys for audit data
     */
    private const AUDIT_PREFIX = 'payment_calc_audit_';
    private const AUDIT_SUMMARY_PREFIX = 'payment_calc_audit_summary_';
    
    /**
     * Log a comprehensive audit trail for payment calculation
     */
    public function logCalculationAudit(
        string $calculationId,
        array $inputs,
        PaymentCalculationResult $result,
        array $context = []
    ): void {
        $auditData = [
            'calculation_id' => $calculationId,
            'timestamp' => now()->toISOString(),
            'inputs' => $this->sanitizeInputs($inputs),
            'result' => $this->extractResultData($result),
            'context' => $this->sanitizeContext($context),
            'system_info' => $this->getSystemInfo(),
            'user_info' => $this->getUserInfo(),
            'request_info' => $this->getRequestInfo()
        ];
        
        // Log to audit channel
        Log::channel('payment_audit')->info('Payment calculation audit', $auditData);
        
        // Cache audit data for analysis
        $this->cacheAuditData($calculationId, $auditData);
        
        // Update audit summary metrics
        $this->updateAuditSummary($auditData);
    }
    
    /**
     * Log configuration changes
     */
    public function logConfigurationChange(
        string $changeType,
        array $oldConfig,
        array $newConfig,
        ?string $userId = null
    ): void {
        $auditData = [
            'event_type' => 'configuration_change',
            'change_type' => $changeType,
            'timestamp' => now()->toISOString(),
            'old_configuration' => $this->sanitizeConfig($oldConfig),
            'new_configuration' => $this->sanitizeConfig($newConfig),
            'changes_detected' => $this->detectConfigChanges($oldConfig, $newConfig),
            'user_id' => $userId,
            'user_info' => $this->getUserInfo(),
            'system_info' => $this->getSystemInfo(),
            'request_info' => $this->getRequestInfo()
        ];
        
        Log::channel('payment_audit')->warning('Payment calculation configuration changed', $auditData);
        
        // Cache configuration change for monitoring
        $this->cacheConfigurationChange($auditData);
    }
    
    /**
     * Log security events related to payment calculations
     */
    public function logSecurityEvent(
        string $eventType,
        string $severity,
        array $details,
        ?string $userId = null
    ): void {
        $auditData = [
            'event_type' => 'security_event',
            'security_event_type' => $eventType,
            'severity' => $severity,
            'timestamp' => now()->toISOString(),
            'details' => $this->sanitizeSecurityDetails($details),
            'user_id' => $userId,
            'user_info' => $this->getUserInfo(),
            'system_info' => $this->getSystemInfo(),
            'request_info' => $this->getRequestInfo(),
            'ip_address' => request()->ip() ?? 'unknown',
            'user_agent' => request()->userAgent() ?? 'unknown'
        ];
        
        $logLevel = $this->getLogLevelForSeverity($severity);
        Log::channel('payment_audit')->{$logLevel}('Payment calculation security event', $auditData);
        
        // Cache security event for immediate analysis
        $this->cacheSecurityEvent($auditData);
    }
    
    /**
     * Log performance anomalies
     */
    public function logPerformanceAnomaly(
        string $calculationId,
        float $executionTimeMs,
        array $inputs,
        string $anomalyType
    ): void {
        $auditData = [
            'event_type' => 'performance_anomaly',
            'calculation_id' => $calculationId,
            'anomaly_type' => $anomalyType,
            'execution_time_ms' => $executionTimeMs,
            'timestamp' => now()->toISOString(),
            'inputs' => $this->sanitizeInputs($inputs),
            'system_info' => $this->getSystemInfo(),
            'performance_context' => $this->getPerformanceContext()
        ];
        
        Log::channel('payment_audit')->warning('Payment calculation performance anomaly', $auditData);
        
        // Cache performance anomaly for analysis
        $this->cachePerformanceAnomaly($auditData);
    }
    
    /**
     * Get audit trail for a specific calculation
     */
    public function getCalculationAuditTrail(string $calculationId): ?array
    {
        return Cache::get(self::AUDIT_PREFIX . $calculationId);
    }
    
    /**
     * Get audit summary for a time period
     */
    public function getAuditSummary(int $hours = 24): array
    {
        $summary = [
            'period_hours' => $hours,
            'generated_at' => now()->toISOString(),
            'total_calculations' => 0,
            'successful_calculations' => 0,
            'failed_calculations' => 0,
            'configuration_changes' => 0,
            'security_events' => 0,
            'performance_anomalies' => 0,
            'user_activity' => [],
            'system_activity' => []
        ];
        
        for ($i = 0; $i < $hours; $i++) {
            $hour = now()->subHours($i)->format('Y-m-d-H');
            $hourSummary = Cache::get(self::AUDIT_SUMMARY_PREFIX . $hour, []);
            
            if (!empty($hourSummary)) {
                $summary['total_calculations'] += $hourSummary['total_calculations'] ?? 0;
                $summary['successful_calculations'] += $hourSummary['successful_calculations'] ?? 0;
                $summary['failed_calculations'] += $hourSummary['failed_calculations'] ?? 0;
                $summary['configuration_changes'] += $hourSummary['configuration_changes'] ?? 0;
                $summary['security_events'] += $hourSummary['security_events'] ?? 0;
                $summary['performance_anomalies'] += $hourSummary['performance_anomalies'] ?? 0;
                
                // Aggregate user activity
                foreach ($hourSummary['user_activity'] ?? [] as $userId => $count) {
                    $summary['user_activity'][$userId] = ($summary['user_activity'][$userId] ?? 0) + $count;
                }
            }
        }
        
        return $summary;
    }
    
    /**
     * Search audit logs by criteria
     */
    public function searchAuditLogs(array $criteria, int $limit = 100): array
    {
        // This is a simplified implementation
        // In a production system, you might want to use a proper search engine
        $results = [];
        $searchKeys = Cache::get('audit_search_keys', []);
        
        foreach (array_slice($searchKeys, 0, $limit) as $key) {
            $auditData = Cache::get($key);
            if ($auditData && $this->matchesCriteria($auditData, $criteria)) {
                $results[] = $auditData;
            }
        }
        
        return array_slice($results, 0, $limit);
    }
    
    // Private helper methods
    
    private function sanitizeInputs(array $inputs): array
    {
        return [
            'apartment_price' => $inputs['apartment_price'] ?? null,
            'rental_duration' => $inputs['rental_duration'] ?? null,
            'pricing_type' => $inputs['pricing_type'] ?? null,
            'additional_charges' => isset($inputs['additional_charges']) ? 
                array_keys($inputs['additional_charges']) : null
        ];
    }
    
    private function extractResultData(PaymentCalculationResult $result): array
    {
        return [
            'is_valid' => $result->isValid,
            'total_amount' => $result->totalAmount ?? null,
            'calculation_method' => $result->calculationMethod ?? null,
            'error_message' => $result->errorMessage ?? null,
            'steps_count' => count($result->calculationSteps),
            'has_fallback' => $this->detectFallbackUsage($result)
        ];
    }
    
    private function sanitizeContext(array $context): array
    {
        // Remove sensitive information from context
        $sanitized = $context;
        
        $sensitiveKeys = ['password', 'token', 'secret', 'key', 'auth'];
        foreach ($sensitiveKeys as $key) {
            if (isset($sanitized[$key])) {
                $sanitized[$key] = '[REDACTED]';
            }
        }
        
        return $sanitized;
    }
    
    private function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'server_time' => now()->toISOString(),
            'timezone' => config('app.timezone'),
            'environment' => app()->environment()
        ];
    }
    
    private function getUserInfo(): ?array
    {
        if (!auth()->check()) {
            return null;
        }
        
        $user = auth()->user();
        return [
            'user_id' => $user->id ?? null,
            'email' => $user->email ?? null,
            'role' => $user->role ?? null,
            'is_admin' => $user->is_admin ?? false
        ];
    }
    
    private function getRequestInfo(): array
    {
        if (!request()) {
            return ['source' => 'console'];
        }
        
        return [
            'source' => 'web',
            'method' => request()->method(),
            'url' => request()->fullUrl(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referer' => request()->header('referer')
        ];
    }
    
    private function getPerformanceContext(): array
    {
        return [
            'current_memory' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'server_load' => sys_getloadavg()[0] ?? null,
            'timestamp' => now()->toISOString()
        ];
    }
    
    private function cacheAuditData(string $calculationId, array $auditData): void
    {
        $key = self::AUDIT_PREFIX . $calculationId;
        Cache::put($key, $auditData, now()->addDays(30)); // Keep for 30 days
        
        // Add to searchable keys
        $searchKeys = Cache::get('audit_search_keys', []);
        $searchKeys[] = $key;
        
        // Keep only last 10000 keys
        if (count($searchKeys) > 10000) {
            $oldKey = array_shift($searchKeys);
            Cache::forget($oldKey);
        }
        
        Cache::put('audit_search_keys', $searchKeys, now()->addDays(30));
    }
    
    private function updateAuditSummary(array $auditData): void
    {
        $hour = now()->format('Y-m-d-H');
        $key = self::AUDIT_SUMMARY_PREFIX . $hour;
        
        $summary = Cache::get($key, [
            'hour' => $hour,
            'total_calculations' => 0,
            'successful_calculations' => 0,
            'failed_calculations' => 0,
            'configuration_changes' => 0,
            'security_events' => 0,
            'performance_anomalies' => 0,
            'user_activity' => []
        ]);
        
        $summary['total_calculations']++;
        
        if ($auditData['result']['is_valid']) {
            $summary['successful_calculations']++;
        } else {
            $summary['failed_calculations']++;
        }
        
        // Track user activity
        $userId = $auditData['user_info']['user_id'] ?? 'anonymous';
        $summary['user_activity'][$userId] = ($summary['user_activity'][$userId] ?? 0) + 1;
        
        Cache::put($key, $summary, now()->addDays(7));
    }
    
    private function cacheConfigurationChange(array $auditData): void
    {
        $hour = now()->format('Y-m-d-H');
        $key = self::AUDIT_SUMMARY_PREFIX . $hour;
        
        $summary = Cache::get($key, []);
        $summary['configuration_changes'] = ($summary['configuration_changes'] ?? 0) + 1;
        
        Cache::put($key, $summary, now()->addDays(7));
        
        // Also cache the specific change
        $changeKey = 'config_change_' . time() . '_' . substr(md5(json_encode($auditData)), 0, 8);
        Cache::put($changeKey, $auditData, now()->addDays(30));
    }
    
    private function cacheSecurityEvent(array $auditData): void
    {
        $hour = now()->format('Y-m-d-H');
        $key = self::AUDIT_SUMMARY_PREFIX . $hour;
        
        $summary = Cache::get($key, []);
        $summary['security_events'] = ($summary['security_events'] ?? 0) + 1;
        
        Cache::put($key, $summary, now()->addDays(7));
        
        // Cache the specific security event
        $eventKey = 'security_event_' . time() . '_' . substr(md5(json_encode($auditData)), 0, 8);
        Cache::put($eventKey, $auditData, now()->addDays(30));
    }
    
    private function cachePerformanceAnomaly(array $auditData): void
    {
        $hour = now()->format('Y-m-d-H');
        $key = self::AUDIT_SUMMARY_PREFIX . $hour;
        
        $summary = Cache::get($key, []);
        $summary['performance_anomalies'] = ($summary['performance_anomalies'] ?? 0) + 1;
        
        Cache::put($key, $summary, now()->addDays(7));
        
        // Cache the specific anomaly
        $anomalyKey = 'performance_anomaly_' . time() . '_' . substr(md5(json_encode($auditData)), 0, 8);
        Cache::put($anomalyKey, $auditData, now()->addDays(7));
    }
    
    private function sanitizeConfig(array $config): array
    {
        // Remove sensitive configuration values
        $sanitized = $config;
        
        $sensitiveKeys = ['password', 'secret', 'key', 'token', 'api_key'];
        array_walk_recursive($sanitized, function(&$value, $key) use ($sensitiveKeys) {
            if (in_array(strtolower($key), $sensitiveKeys)) {
                $value = '[REDACTED]';
            }
        });
        
        return $sanitized;
    }
    
    private function detectConfigChanges(array $oldConfig, array $newConfig): array
    {
        $changes = [];
        
        // Simple diff implementation
        foreach ($newConfig as $key => $value) {
            if (!isset($oldConfig[$key]) || $oldConfig[$key] !== $value) {
                $changes[$key] = [
                    'old' => $oldConfig[$key] ?? null,
                    'new' => $value
                ];
            }
        }
        
        foreach ($oldConfig as $key => $value) {
            if (!isset($newConfig[$key])) {
                $changes[$key] = [
                    'old' => $value,
                    'new' => null
                ];
            }
        }
        
        return $changes;
    }
    
    private function sanitizeSecurityDetails(array $details): array
    {
        $sanitized = $details;
        
        // Remove potentially sensitive information
        $sensitiveKeys = ['password', 'token', 'session_id', 'cookie'];
        foreach ($sensitiveKeys as $key) {
            if (isset($sanitized[$key])) {
                $sanitized[$key] = '[REDACTED]';
            }
        }
        
        return $sanitized;
    }
    
    private function getLogLevelForSeverity(string $severity): string
    {
        return match(strtolower($severity)) {
            'critical', 'high' => 'critical',
            'medium', 'warning' => 'warning',
            'low', 'info' => 'info',
            default => 'info'
        };
    }
    
    private function detectFallbackUsage(PaymentCalculationResult $result): bool
    {
        foreach ($result->calculationSteps as $step) {
            if (isset($step['fallback_applied']) && $step['fallback_applied']) {
                return true;
            }
        }
        return false;
    }
    
    private function matchesCriteria(array $auditData, array $criteria): bool
    {
        foreach ($criteria as $key => $value) {
            if (!$this->matchesValue(data_get($auditData, $key), $value)) {
                return false;
            }
        }
        return true;
    }
    
    private function matchesValue($actual, $expected): bool
    {
        if (is_array($expected)) {
            return in_array($actual, $expected);
        }
        
        if (is_string($expected) && str_contains($expected, '*')) {
            return fnmatch($expected, $actual);
        }
        
        return $actual === $expected;
    }
}