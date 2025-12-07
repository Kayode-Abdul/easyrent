<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Monitoring\SystemHealthMonitor;
use App\Services\Monitoring\ErrorMonitoringService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MonitorSystemHealth extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'easyrent:monitor-health {--alert : Send alerts for critical issues}';

    /**
     * The console command description.
     */
    protected $description = 'Monitor EasyRent system health and send alerts for critical issues';

    protected $healthMonitor;
    protected $errorMonitoring;

    /**
     * Create a new command instance.
     */
    public function __construct(SystemHealthMonitor $healthMonitor, ErrorMonitoringService $errorMonitoring)
    {
        parent::__construct();
        $this->healthMonitor = $healthMonitor;
        $this->errorMonitoring = $errorMonitoring;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking EasyRent system health...');

        // Get current health status
        $healthStatus = $this->healthMonitor->getHealthStatus();
        $errorStats = $this->errorMonitoring->getErrorStatistics(24);

        // Display health status
        $this->displayHealthStatus($healthStatus);
        $this->displayErrorStatistics($errorStats);

        // Check if alerts should be sent
        if ($this->option('alert') && $this->shouldSendAlert($healthStatus)) {
            $this->sendHealthAlert($healthStatus, $errorStats);
        }

        // Log health check
        Log::info('System health check completed', [
            'health_status' => $healthStatus,
            'error_stats' => $errorStats
        ]);

        return 0;
    }

    /**
     * Display health status in console
     */
    private function displayHealthStatus(array $healthStatus): void
    {
        $this->line('');
        $this->line('<comment>System Health Status</comment>');
        $this->line('====================');

        $statusColor = $this->getStatusColor($healthStatus['status']);
        $this->line("Status: <{$statusColor}>{$healthStatus['status']}</{$statusColor}>");

        if (!empty($healthStatus['issues'])) {
            $this->line('');
            $this->line('<error>Issues Detected:</error>');
            foreach ($healthStatus['issues'] as $issue) {
                $this->line("  - {$issue}");
            }
        }

        $this->line('');
        $this->line('<info>Metrics:</info>');
        $this->line("  Recent Errors: {$healthStatus['metrics']['recent_errors']}");
        $this->line("  Critical Errors: {$healthStatus['metrics']['critical_errors']}");
        $this->line("  Error Rate: " . number_format($healthStatus['metrics']['error_rate'] * 100, 2) . "%");
        $this->line("  Last Checked: {$healthStatus['last_checked']}");
    }

    /**
     * Display error statistics in console
     */
    private function displayErrorStatistics(array $errorStats): void
    {
        $this->line('');
        $this->line('<comment>Error Statistics (Last 24 Hours)</comment>');
        $this->line('====================================');
        $this->line("Total Errors: {$errorStats['total_errors']}");
        $this->line("Critical Errors: " . count($errorStats['critical_errors']));
        $this->line("Recovery Success Rate: " . number_format($errorStats['recovery_success_rate'] * 100, 2) . "%");

        if (!empty($errorStats['error_types'])) {
            $this->line('');
            $this->line('<info>Error Types:</info>');
            foreach ($errorStats['error_types'] as $type => $count) {
                $this->line("  {$type}: {$count}");
            }
        }
    }

    /**
     * Get color for status display
     */
    private function getStatusColor(string $status): string
    {
        switch ($status) {
            case 'healthy':
                return 'info';
            case 'degraded':
                return 'comment';
            case 'critical':
                return 'error';
            default:
                return 'comment';
        }
    }

    /**
     * Determine if alert should be sent
     */
    private function shouldSendAlert(array $healthStatus): bool
    {
        return in_array($healthStatus['status'], ['critical', 'degraded']) && 
               !empty($healthStatus['issues']);
    }

    /**
     * Send health alert email
     */
    private function sendHealthAlert(array $healthStatus, array $errorStats): void
    {
        try {
            $alertData = [
                'health_status' => $healthStatus,
                'error_stats' => $errorStats,
                'timestamp' => now()->toISOString(),
                'server' => config('app.name'),
                'environment' => config('app.env')
            ];

            // In a real implementation, you would send this to administrators
            // For now, we'll just log it as a critical alert
            Log::critical('System health alert triggered', $alertData);

            $this->line('');
            $this->line('<error>Health alert logged for administrator review</error>');

        } catch (\Exception $e) {
            $this->error("Failed to send health alert: {$e->getMessage()}");
            Log::error('Failed to send health alert', ['error' => $e->getMessage()]);
        }
    }
}