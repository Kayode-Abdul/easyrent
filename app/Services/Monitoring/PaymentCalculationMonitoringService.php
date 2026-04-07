<?php

namespace App\Services\Monitoring;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\Payment\PaymentCalculationResult;
use Carbon\Carbon;

class PaymentCalculationMonitoringService
{
    /**
     * Performance thresholds
     */
    private const SLOW_CALCULATION_THRESHOLD = 100; // milliseconds
    private const HIGH_VALUE_THRESHOLD = 1000000.00; // $1M
    private const SUSPICIOUS_DURATION_THRESHOLD = 60; // months
    private const ERROR_RATE_THRESHOLD = 0.05; // 5%
    
    /**
     * Cache keys for metrics
     */
    private const METRICS_PREFIX = 'payment_calc_metrics_';
    private const PERFORMANCE_PREFIX = 'payment_calc_perf_';
    private const ACCURACY_PREFIX = 'payment_calc_accuracy_';
    private const ERROR_PREFIX = 'payment_calc_errors_';
    private const ALERT_PREFIX = 'payment_calc_alerts_';
    
    /**
     * Record calculation performance metrics
     */
    public function recordCalculationPerformance(
        string $calculationId,
        float $executionTimeMs,
        PaymentCalculationResult $result,
        array $inputs
    ): void {
        $timestamp = now();
        $hour = $timestamp->format('Y-m-d-H');
        
        // Record basic performance metrics
        $performanceData = [
            'calculation_id' => $calculationId,
            'execution_time_ms' => $executionTimeMs,
            'success' => $result->isValid,
            'pricing_type' => $inputs['pricing_type'] ?? 'unknown',
            'apartment_price' => $inputs['apartment_price'] ?? 0,
            'rental_duration' => $inputs['rental_duration'] ?? 0,
            'total_amount' => $result->totalAmount ?? 0,
            'timestamp' => $timestamp->toISOString()
        ];
        
        // Cache individual calculation data
        Cache::put(
            self::PERFORMANCE_PREFIX . $calculationId,
            $performanceData,
            now()->addDays(7)
        );
        
        // Update hourly aggregated metrics
        $this->updateHourlyPerformanceMetrics($hour, $performanceData);
        
        // Check for performance alerts
        $this->checkPerformanceAlerts($calculationId, $executionTimeMs, $result, $inputs);
        
        // Log slow calculations
        if ($executionTimeMs > self::SLOW_CALCULATION_THRESHOLD) {
            $this->logSlowCalculation($calculationId, $executionTimeMs, $inputs, $result);
        }
    }
    
    /**
     * Record calculation accuracy metrics
     */
    public function recordCalculationAccuracy(
        string $calculationId,
        PaymentCalculationResult $result,
        array $inputs,
        ?float $expectedResult = null
    ): void {
        $timestamp = now();
        $hour = $timestamp->format('Y-m-d-H');
        
        $accuracyData = [
            'calculation_id' => $calculationId,
            'success' => $result->isValid,
            'calculated_amount' => $result->totalAmount ?? 0,
            'expected_amount' => $expectedResult,
            'accuracy_verified' => $expectedResult !== null,
            'deviation' => $expectedResult !== null ? abs(($result->totalAmount ?? 0) - $expectedResult) : null,
            'pricing_type' => $inputs['pricing_type'] ?? 'unknown',
            'fallback_used' => $this->detectFallbackUsage($result),
            'timestamp' => $timestamp->toISOString()
        ];
        
        // Cache accuracy data
        Cache::put(
            self::ACCURACY_PREFIX . $calculationId,
            $accuracyData,
            now()->addDays(7)
        );
        
        // Update hourly accuracy metrics
        $this->updateHourlyAccuracyMetrics($hour, $accuracyData);
        
        // Check for accuracy alerts
        if ($expectedResult !== null) {
            $this->checkAccuracyAlerts($calculationId, $result->totalAmount ?? 0, $expectedResult, $inputs);
        }
    }
    
    /**
     * Record calculation errors
     */
    public function recordCalculationError(
        string $calculationId,
        string $errorType,
        string $errorMessage,
        array $inputs,
        array $context = []
    ): void {
        $timestamp = now();
        $hour = $timestamp->format('Y-m-d-H');
        
        $errorData = [
            'calculation_id' => $calculationId,
            'error_type' => $errorType,
            'error_message' => $errorMessage,
            'inputs' => $inputs,
            'context' => $context,
            'timestamp' => $timestamp->toISOString()
        ];
        
        // Cache error data
        Cache::put(
            self::ERROR_PREFIX . $calculationId,
            $errorData,
            now()->addDays(30) // Keep errors longer for analysis
        );
        
        // Update hourly error metrics
        $this->updateHourlyErrorMetrics($hour, $errorData);
        
        // Check for error pattern alerts
        $this->checkErrorPatternAlerts($errorType, $inputs);
        
        // Log critical errors immediately
        if ($this->isCriticalError($errorType)) {
            Log::critical('Critical payment calculation error', $errorData);
        }
    }
    
    /**
     * Get calculation performance metrics for dashboard
     */
    public function getPerformanceMetrics(int $hours = 24): array
    {
        $metrics = [
            'period_hours' => $hours,
            'generated_at' => now()->toISOString(),
            'total_calculations' => 0,
            'successful_calculations' => 0,
            'failed_calculations' => 0,
            'success_rate' => 0,
            'avg_execution_time_ms' => 0,
            'slow_calculations' => 0,
            'pricing_type_breakdown' => [],
            'hourly_trends' => []
        ];
        
        $hourlyData = [];
        
        for ($i = 0; $i < $hours; $i++) {
            $hour = now()->subHours($i)->format('Y-m-d-H');
            $hourMetrics = Cache::get(self::METRICS_PREFIX . 'hourly_' . $hour, []);
            
            if (!empty($hourMetrics)) {
                $hourlyData[] = $hourMetrics;
                
                $metrics['total_calculations'] += $hourMetrics['total_calculations'] ?? 0;
                $metrics['successful_calculations'] += $hourMetrics['successful_calculations'] ?? 0;
                $metrics['failed_calculations'] += $hourMetrics['failed_calculations'] ?? 0;
                
                // Aggregate pricing type data
                foreach ($hourMetrics['pricing_types'] ?? [] as $type => $count) {
                    $metrics['pricing_type_breakdown'][$type] = 
                        ($metrics['pricing_type_breakdown'][$type] ?? 0) + $count;
                }
            }
        }
        
        // Calculate derived metrics
        if ($metrics['total_calculations'] > 0) {
            $metrics['success_rate'] = round(
                ($metrics['successful_calculations'] / $metrics['total_calculations']) * 100, 
                2
            );
        }
        
        // Calculate average execution time
        $totalExecutionTime = array_sum(array_column($hourlyData, 'total_execution_time_ms'));
        if ($metrics['total_calculations'] > 0) {
            $metrics['avg_execution_time_ms'] = round(
                $totalExecutionTime / $metrics['total_calculations'], 
                2
            );
        }
        
        // Count slow calculations
        $metrics['slow_calculations'] = array_sum(array_column($hourlyData, 'slow_calculations'));
        
        // Prepare hourly trends
        $metrics['hourly_trends'] = array_reverse($hourlyData);
        
        return $metrics;
    }
    
    /**
     * Get calculation accuracy metrics
     */
    public function getAccuracyMetrics(int $hours = 24): array
    {
        $metrics = [
            'period_hours' => $hours,
            'generated_at' => now()->toISOString(),
            'total_verified_calculations' => 0,
            'accurate_calculations' => 0,
            'accuracy_rate' => 0,
            'avg_deviation' => 0,
            'fallback_usage_rate' => 0,
            'high_value_calculations' => 0,
            'suspicious_calculations' => 0
        ];
        
        $verifiedCalculations = [];
        $totalDeviations = 0;
        $fallbackCount = 0;
        
        for ($i = 0; $i < $hours; $i++) {
            $hour = now()->subHours($i)->format('Y-m-d-H');
            $hourMetrics = Cache::get(self::ACCURACY_PREFIX . 'hourly_' . $hour, []);
            
            if (!empty($hourMetrics)) {
                $metrics['total_verified_calculations'] += $hourMetrics['verified_calculations'] ?? 0;
                $metrics['accurate_calculations'] += $hourMetrics['accurate_calculations'] ?? 0;
                $metrics['high_value_calculations'] += $hourMetrics['high_value_calculations'] ?? 0;
                $metrics['suspicious_calculations'] += $hourMetrics['suspicious_calculations'] ?? 0;
                
                $totalDeviations += $hourMetrics['total_deviation'] ?? 0;
                $fallbackCount += $hourMetrics['fallback_usage'] ?? 0;
            }
        }
        
        // Calculate derived metrics
        if ($metrics['total_verified_calculations'] > 0) {
            $metrics['accuracy_rate'] = round(
                ($metrics['accurate_calculations'] / $metrics['total_verified_calculations']) * 100,
                2
            );
            
            $metrics['avg_deviation'] = round(
                $totalDeviations / $metrics['total_verified_calculations'],
                2
            );
            
            $metrics['fallback_usage_rate'] = round(
                ($fallbackCount / $metrics['total_verified_calculations']) * 100,
                2
            );
        }
        
        return $metrics;
    }
    
    /**
     * Get error metrics and patterns
     */
    public function getErrorMetrics(int $hours = 24): array
    {
        $metrics = [
            'period_hours' => $hours,
            'generated_at' => now()->toISOString(),
            'total_errors' => 0,
            'error_rate' => 0,
            'error_types' => [],
            'critical_errors' => 0,
            'recent_error_trend' => [],
            'top_error_messages' => []
        ];
        
        $totalCalculations = 0;
        $errorsByType = [];
        $errorMessages = [];
        
        for ($i = 0; $i < $hours; $i++) {
            $hour = now()->subHours($i)->format('Y-m-d-H');
            $hourMetrics = Cache::get(self::ERROR_PREFIX . 'hourly_' . $hour, []);
            
            if (!empty($hourMetrics)) {
                $metrics['total_errors'] += $hourMetrics['total_errors'] ?? 0;
                $metrics['critical_errors'] += $hourMetrics['critical_errors'] ?? 0;
                $totalCalculations += $hourMetrics['total_calculations'] ?? 0;
                
                // Aggregate error types
                foreach ($hourMetrics['error_types'] ?? [] as $type => $count) {
                    $errorsByType[$type] = ($errorsByType[$type] ?? 0) + $count;
                }
                
                // Collect error messages
                foreach ($hourMetrics['error_messages'] ?? [] as $message => $count) {
                    $errorMessages[$message] = ($errorMessages[$message] ?? 0) + $count;
                }
                
                $metrics['recent_error_trend'][] = [
                    'hour' => $hour,
                    'errors' => $hourMetrics['total_errors'] ?? 0,
                    'calculations' => $hourMetrics['total_calculations'] ?? 0
                ];
            }
        }
        
        // Calculate error rate
        if ($totalCalculations > 0) {
            $metrics['error_rate'] = round(
                ($metrics['total_errors'] / $totalCalculations) * 100,
                2
            );
        }
        
        // Sort and limit error types and messages
        arsort($errorsByType);
        arsort($errorMessages);
        
        $metrics['error_types'] = array_slice($errorsByType, 0, 10, true);
        $metrics['top_error_messages'] = array_slice($errorMessages, 0, 5, true);
        $metrics['recent_error_trend'] = array_reverse($metrics['recent_error_trend']);
        
        return $metrics;
    }
    
    /**
     * Generate alerts for calculation issues
     */
    public function generateAlerts(): array
    {
        $alerts = [];
        $timestamp = now();
        
        // Check error rate alert
        $recentMetrics = $this->getErrorMetrics(1); // Last hour
        if ($recentMetrics['error_rate'] > (self::ERROR_RATE_THRESHOLD * 100)) {
            $alerts[] = [
                'type' => 'high_error_rate',
                'severity' => 'critical',
                'message' => "Payment calculation error rate is {$recentMetrics['error_rate']}%, exceeding threshold of " . (self::ERROR_RATE_THRESHOLD * 100) . "%",
                'data' => $recentMetrics,
                'timestamp' => $timestamp->toISOString()
            ];
        }
        
        // Check for performance degradation
        $performanceMetrics = $this->getPerformanceMetrics(1);
        if ($performanceMetrics['avg_execution_time_ms'] > self::SLOW_CALCULATION_THRESHOLD * 2) {
            $alerts[] = [
                'type' => 'performance_degradation',
                'severity' => 'warning',
                'message' => "Average calculation time is {$performanceMetrics['avg_execution_time_ms']}ms, significantly above normal",
                'data' => $performanceMetrics,
                'timestamp' => $timestamp->toISOString()
            ];
        }
        
        // Check for accuracy issues
        $accuracyMetrics = $this->getAccuracyMetrics(1);
        if ($accuracyMetrics['accuracy_rate'] < 95 && $accuracyMetrics['total_verified_calculations'] > 10) {
            $alerts[] = [
                'type' => 'accuracy_degradation',
                'severity' => 'warning',
                'message' => "Calculation accuracy rate is {$accuracyMetrics['accuracy_rate']}%, below expected threshold",
                'data' => $accuracyMetrics,
                'timestamp' => $timestamp->toISOString()
            ];
        }
        
        // Check for suspicious activity patterns
        if ($accuracyMetrics['suspicious_calculations'] > 10) {
            $alerts[] = [
                'type' => 'suspicious_activity',
                'severity' => 'medium',
                'message' => "Detected {$accuracyMetrics['suspicious_calculations']} suspicious calculations in the last hour",
                'data' => ['suspicious_count' => $accuracyMetrics['suspicious_calculations']],
                'timestamp' => $timestamp->toISOString()
            ];
        }
        
        // Cache alerts for dashboard
        if (!empty($alerts)) {
            Cache::put(
                self::ALERT_PREFIX . 'current',
                $alerts,
                now()->addHours(1)
            );
        }
        
        return $alerts;
    }
    
    /**
     * Get comprehensive dashboard data
     */
    public function getDashboardData(int $hours = 24): array
    {
        return [
            'overview' => [
                'period_hours' => $hours,
                'generated_at' => now()->toISOString(),
                'system_status' => $this->getSystemStatus()
            ],
            'performance' => $this->getPerformanceMetrics($hours),
            'accuracy' => $this->getAccuracyMetrics($hours),
            'errors' => $this->getErrorMetrics($hours),
            'alerts' => $this->generateAlerts(),
            'pricing_configuration_usage' => $this->getPricingConfigurationUsage($hours)
        ];
    }
    
    /**
     * Get pricing configuration usage statistics
     */
    public function getPricingConfigurationUsage(int $hours = 24): array
    {
        $usage = [
            'total_pricing_type' => 0,
            'monthly_pricing_type' => 0,
            'fallback_usage' => 0,
            'configuration_changes' => 0,
            'usage_by_apartment_type' => []
        ];
        
        for ($i = 0; $i < $hours; $i++) {
            $hour = now()->subHours($i)->format('Y-m-d-H');
            $hourMetrics = Cache::get(self::METRICS_PREFIX . 'pricing_usage_' . $hour, []);
            
            if (!empty($hourMetrics)) {
                $usage['total_pricing_type'] += $hourMetrics['total_pricing_type'] ?? 0;
                $usage['monthly_pricing_type'] += $hourMetrics['monthly_pricing_type'] ?? 0;
                $usage['fallback_usage'] += $hourMetrics['fallback_usage'] ?? 0;
                $usage['configuration_changes'] += $hourMetrics['configuration_changes'] ?? 0;
            }
        }
        
        return $usage;
    }
    
    // Private helper methods
    
    private function updateHourlyPerformanceMetrics(string $hour, array $performanceData): void
    {
        $key = self::METRICS_PREFIX . 'hourly_' . $hour;
        $existing = Cache::get($key, [
            'hour' => $hour,
            'total_calculations' => 0,
            'successful_calculations' => 0,
            'failed_calculations' => 0,
            'total_execution_time_ms' => 0,
            'slow_calculations' => 0,
            'pricing_types' => []
        ]);
        
        $existing['total_calculations']++;
        $existing['total_execution_time_ms'] += $performanceData['execution_time_ms'];
        
        if ($performanceData['success']) {
            $existing['successful_calculations']++;
        } else {
            $existing['failed_calculations']++;
        }
        
        if ($performanceData['execution_time_ms'] > self::SLOW_CALCULATION_THRESHOLD) {
            $existing['slow_calculations']++;
        }
        
        $pricingType = $performanceData['pricing_type'];
        $existing['pricing_types'][$pricingType] = ($existing['pricing_types'][$pricingType] ?? 0) + 1;
        
        Cache::put($key, $existing, now()->addDays(7));
    }
    
    private function updateHourlyAccuracyMetrics(string $hour, array $accuracyData): void
    {
        $key = self::ACCURACY_PREFIX . 'hourly_' . $hour;
        $existing = Cache::get($key, [
            'hour' => $hour,
            'verified_calculations' => 0,
            'accurate_calculations' => 0,
            'total_deviation' => 0,
            'fallback_usage' => 0,
            'high_value_calculations' => 0,
            'suspicious_calculations' => 0
        ]);
        
        if ($accuracyData['accuracy_verified']) {
            $existing['verified_calculations']++;
            
            if ($accuracyData['deviation'] !== null && $accuracyData['deviation'] < 0.01) {
                $existing['accurate_calculations']++;
            }
            
            if ($accuracyData['deviation'] !== null) {
                $existing['total_deviation'] += $accuracyData['deviation'];
            }
        }
        
        if ($accuracyData['fallback_used']) {
            $existing['fallback_usage']++;
        }
        
        if ($accuracyData['calculated_amount'] > self::HIGH_VALUE_THRESHOLD) {
            $existing['high_value_calculations']++;
        }
        
        Cache::put($key, $existing, now()->addDays(7));
    }
    
    private function updateHourlyErrorMetrics(string $hour, array $errorData): void
    {
        $key = self::ERROR_PREFIX . 'hourly_' . $hour;
        $existing = Cache::get($key, [
            'hour' => $hour,
            'total_errors' => 0,
            'critical_errors' => 0,
            'total_calculations' => 0,
            'error_types' => [],
            'error_messages' => []
        ]);
        
        $existing['total_errors']++;
        $existing['total_calculations']++;
        
        if ($this->isCriticalError($errorData['error_type'])) {
            $existing['critical_errors']++;
        }
        
        $errorType = $errorData['error_type'];
        $existing['error_types'][$errorType] = ($existing['error_types'][$errorType] ?? 0) + 1;
        
        $errorMessage = substr($errorData['error_message'], 0, 100); // Truncate for storage
        $existing['error_messages'][$errorMessage] = ($existing['error_messages'][$errorMessage] ?? 0) + 1;
        
        Cache::put($key, $existing, now()->addDays(30));
    }
    
    private function checkPerformanceAlerts(string $calculationId, float $executionTimeMs, PaymentCalculationResult $result, array $inputs): void
    {
        if ($executionTimeMs > self::SLOW_CALCULATION_THRESHOLD * 3) {
            Log::warning('Very slow payment calculation detected', [
                'calculation_id' => $calculationId,
                'execution_time_ms' => $executionTimeMs,
                'threshold' => self::SLOW_CALCULATION_THRESHOLD * 3,
                'inputs' => $inputs
            ]);
        }
    }
    
    private function checkAccuracyAlerts(string $calculationId, float $calculatedAmount, float $expectedAmount, array $inputs): void
    {
        $deviation = abs($calculatedAmount - $expectedAmount);
        $percentageDeviation = $expectedAmount > 0 ? ($deviation / $expectedAmount) * 100 : 0;
        
        if ($percentageDeviation > 1) { // 1% deviation threshold
            Log::warning('Payment calculation accuracy deviation detected', [
                'calculation_id' => $calculationId,
                'calculated_amount' => $calculatedAmount,
                'expected_amount' => $expectedAmount,
                'deviation' => $deviation,
                'percentage_deviation' => $percentageDeviation,
                'inputs' => $inputs
            ]);
        }
    }
    
    private function checkErrorPatternAlerts(string $errorType, array $inputs): void
    {
        $hour = now()->format('Y-m-d-H');
        $errorCount = Cache::increment("error_pattern_{$errorType}_{$hour}");
        Cache::put("error_pattern_{$errorType}_{$hour}", $errorCount, now()->addHours(2));
        
        if ($errorCount > 10) { // Threshold for error pattern alert
            Log::critical('Payment calculation error pattern detected', [
                'error_type' => $errorType,
                'error_count' => $errorCount,
                'hour' => $hour,
                'sample_inputs' => $inputs
            ]);
        }
    }
    
    private function logSlowCalculation(string $calculationId, float $executionTimeMs, array $inputs, PaymentCalculationResult $result): void
    {
        Log::info('Slow payment calculation detected', [
            'calculation_id' => $calculationId,
            'execution_time_ms' => $executionTimeMs,
            'threshold' => self::SLOW_CALCULATION_THRESHOLD,
            'success' => $result->isValid,
            'inputs' => $inputs,
            'result_amount' => $result->totalAmount ?? 0
        ]);
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
    
    private function isCriticalError(string $errorType): bool
    {
        $criticalErrors = [
            'arithmetic_error',
            'overflow_exception',
            'division_by_zero',
            'bounds_validation_failed'
        ];
        
        return in_array($errorType, $criticalErrors);
    }
    
    private function getSystemStatus(): string
    {
        $recentMetrics = $this->getErrorMetrics(1);
        $performanceMetrics = $this->getPerformanceMetrics(1);
        
        if ($recentMetrics['error_rate'] > (self::ERROR_RATE_THRESHOLD * 100)) {
            return 'critical';
        }
        
        if ($performanceMetrics['avg_execution_time_ms'] > self::SLOW_CALCULATION_THRESHOLD * 2) {
            return 'degraded';
        }
        
        if ($recentMetrics['total_errors'] > 0 || $performanceMetrics['slow_calculations'] > 5) {
            return 'warning';
        }
        
        return 'healthy';
    }
}