<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Monitoring\CommissionHealthMonitor;
use Illuminate\Support\Facades\Log;

class MonitorCommissionHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commission:monitor-health 
                            {--alert-threshold=warning : Minimum severity level to create alerts (warning, error, critical)}
                            {--send-notifications : Send notifications for critical issues}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor commission system health and create alerts for issues';

    protected CommissionHealthMonitor $healthMonitor;

    /**
     * Create a new command instance.
     */
    public function __construct(CommissionHealthMonitor $healthMonitor)
    {
        parent::__construct();
        $this->healthMonitor = $healthMonitor;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting commission system health monitoring...');

        try {
            // Get system health metrics
            $metrics = $this->healthMonitor->getSystemHealthMetrics();
            
            $this->displayHealthSummary($metrics);
            
            // Check for issues and create alerts
            $alertsCreated = $this->checkAndCreateAlerts($metrics);
            
            if ($alertsCreated > 0) {
                $this->warn("Created {$alertsCreated} new alerts");
            } else {
                $this->info('No new alerts created - system is healthy');
            }

            // Display active alerts
            $activeAlerts = $this->healthMonitor->getActiveAlerts();
            if (!empty($activeAlerts)) {
                $this->displayActiveAlerts($activeAlerts);
            }

            $this->info('Commission health monitoring completed successfully');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Health monitoring failed: ' . $e->getMessage());
            Log::error('Commission health monitoring failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Display health summary
     *
     * @param array $metrics
     * @return void
     */
    private function displayHealthSummary(array $metrics): void
    {
        $this->info('=== Commission System Health Summary ===');
        
        // Calculation Health
        $calcHealth = $metrics['calculation_health'];
        $this->displayHealthStatus('Calculation Health', $calcHealth['health_status']);
        $this->line("  Success Rate: {$calcHealth['success_rate']}%");
        $this->line("  Total Calculations: {$calcHealth['total_calculations']}");
        $this->line("  Failed Calculations: {$calcHealth['failed_calculations']}");
        
        // Payment Processing
        $paymentHealth = $metrics['payment_processing'];
        $this->displayHealthStatus('Payment Processing', $paymentHealth['health_status']);
        $this->line("  Success Rate: {$paymentHealth['success_rate']}%");
        $this->line("  Total Payments: {$paymentHealth['total_payments']}");
        $this->line("  Failed Payments: {$paymentHealth['failed_payments']}");
        
        // Fraud Detection
        $fraudHealth = $metrics['fraud_detection'];
        $this->displayHealthStatus('Fraud Detection', $fraudHealth['health_status']);
        $this->line("  Flagged Accounts: {$fraudHealth['flagged_accounts']}");
        $this->line("  Suspicious Referrals: {$fraudHealth['suspicious_referrals']}");
        $this->line("  Active Alerts: {$fraudHealth['active_alerts']}");
        
        // System Performance
        $perfHealth = $metrics['system_performance'];
        $this->displayHealthStatus('System Performance', $perfHealth['health_status']);
        $this->line("  Avg Response Time: {$perfHealth['average_response_time_ms']}ms");
        $this->line("  Slow Requests: {$perfHealth['slow_requests']}");
        
        // Error Rates
        $errorHealth = $metrics['error_rates'];
        $this->displayHealthStatus('Error Rates', $errorHealth['health_status']);
        $this->line("  HTTP Error Rate: {$errorHealth['http_error_rate']}%");
        $this->line("  Critical Errors: {$errorHealth['critical_errors']}");
        
        $this->line('');
    }

    /**
     * Display health status with color coding
     *
     * @param string $component
     * @param string $status
     * @return void
     */
    private function displayHealthStatus(string $component, string $status): void
    {
        $statusText = ucfirst($status);
        
        switch ($status) {
            case 'healthy':
                $this->line("<info>{$component}: {$statusText}</info>");
                break;
            case 'warning':
                $this->line("<comment>{$component}: {$statusText}</comment>");
                break;
            case 'critical':
                $this->line("<error>{$component}: {$statusText}</error>");
                break;
            default:
                $this->line("{$component}: {$statusText}");
        }
    }

    /**
     * Check metrics and create alerts for issues
     *
     * @param array $metrics
     * @return int Number of alerts created
     */
    private function checkAndCreateAlerts(array $metrics): int
    {
        $alertsCreated = 0;
        $alertThreshold = $this->option('alert-threshold');
        
        // Check calculation health
        $calcHealth = $metrics['calculation_health'];
        if ($this->shouldCreateAlert($calcHealth['health_status'], $alertThreshold)) {
            $this->healthMonitor->createAlert(
                'calculation_health_degraded',
                [
                    'success_rate' => $calcHealth['success_rate'],
                    'failed_calculations' => $calcHealth['failed_calculations'],
                    'anomalies' => $calcHealth['anomalies']
                ],
                $calcHealth['health_status']
            );
            $alertsCreated++;
        }

        // Check payment processing
        $paymentHealth = $metrics['payment_processing'];
        if ($this->shouldCreateAlert($paymentHealth['health_status'], $alertThreshold)) {
            $this->healthMonitor->createAlert(
                'payment_processing_issues',
                [
                    'success_rate' => $paymentHealth['success_rate'],
                    'failed_payments' => $paymentHealth['failed_payments'],
                    'pending_payments' => $paymentHealth['pending_payments'],
                    'bottlenecks' => $paymentHealth['bottlenecks']
                ],
                $paymentHealth['health_status']
            );
            $alertsCreated++;
        }

        // Check fraud detection
        $fraudHealth = $metrics['fraud_detection'];
        if ($this->shouldCreateAlert($fraudHealth['health_status'], $alertThreshold)) {
            $this->healthMonitor->createAlert(
                'fraud_detection_alerts',
                [
                    'flagged_accounts' => $fraudHealth['flagged_accounts'],
                    'suspicious_referrals' => $fraudHealth['suspicious_referrals'],
                    'detection_rate' => $fraudHealth['detection_rate'],
                    'alerts' => $fraudHealth['alerts']
                ],
                $fraudHealth['health_status']
            );
            $alertsCreated++;
        }

        // Check system performance
        $perfHealth = $metrics['system_performance'];
        if ($this->shouldCreateAlert($perfHealth['health_status'], $alertThreshold)) {
            $this->healthMonitor->createAlert(
                'system_performance_degraded',
                [
                    'average_response_time' => $perfHealth['average_response_time_ms'],
                    'max_response_time' => $perfHealth['max_response_time_ms'],
                    'slow_requests' => $perfHealth['slow_requests'],
                    'database_performance' => $perfHealth['database_performance']
                ],
                $perfHealth['health_status']
            );
            $alertsCreated++;
        }

        // Check error rates
        $errorHealth = $metrics['error_rates'];
        if ($this->shouldCreateAlert($errorHealth['health_status'], $alertThreshold)) {
            $this->healthMonitor->createAlert(
                'high_error_rates',
                [
                    'http_error_rate' => $errorHealth['http_error_rate'],
                    'critical_errors' => $errorHealth['critical_errors'],
                    'commission_errors' => $errorHealth['commission_errors']
                ],
                $errorHealth['health_status']
            );
            $alertsCreated++;
        }

        return $alertsCreated;
    }

    /**
     * Determine if an alert should be created based on status and threshold
     *
     * @param string $status
     * @param string $threshold
     * @return bool
     */
    private function shouldCreateAlert(string $status, string $threshold): bool
    {
        $severityLevels = [
            'healthy' => 0,
            'warning' => 1,
            'error' => 2,
            'critical' => 3
        ];

        $statusLevel = $severityLevels[$status] ?? 0;
        $thresholdLevel = $severityLevels[$threshold] ?? 1;

        return $statusLevel >= $thresholdLevel;
    }

    /**
     * Display active alerts
     *
     * @param array $alerts
     * @return void
     */
    private function displayActiveAlerts(array $alerts): void
    {
        $this->warn('=== Active Alerts ===');
        
        foreach ($alerts as $alert) {
            $severity = strtoupper($alert['severity']);
            $type = $alert['type'];
            $createdAt = $alert['created_at'];
            
            switch ($alert['severity']) {
                case 'critical':
                    $this->line("<error>[{$severity}] {$type} - {$createdAt}</error>");
                    break;
                case 'error':
                    $this->line("<comment>[{$severity}] {$type} - {$createdAt}</comment>");
                    break;
                default:
                    $this->line("<info>[{$severity}] {$type} - {$createdAt}</info>");
            }
            
            // Display alert details
            if (isset($alert['data']['description'])) {
                $this->line("  Description: {$alert['data']['description']}");
            }
        }
        
        $this->line('');
    }
}