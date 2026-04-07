<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Monitoring\PaymentCalculationMonitoringService;
use Illuminate\Support\Facades\Log;

class MonitorPaymentCalculations extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'payment:monitor 
                            {--hours=1 : Number of hours to analyze}
                            {--alerts : Generate and display alerts}
                            {--dashboard : Show dashboard summary}
                            {--export= : Export data to file}';

    /**
     * The console command description.
     */
    protected $description = 'Monitor payment calculation performance, accuracy, and generate alerts';

    protected $monitoringService;

    public function __construct(PaymentCalculationMonitoringService $monitoringService)
    {
        parent::__construct();
        $this->monitoringService = $monitoringService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = (int) $this->option('hours');
        
        $this->info("Payment Calculation Monitoring Report");
        $this->info("Analysis Period: Last {$hours} hour(s)");
        $this->info("Generated at: " . now()->toDateTimeString());
        $this->line("");

        try {
            if ($this->option('dashboard')) {
                $this->showDashboard($hours);
            } elseif ($this->option('alerts')) {
                $this->showAlerts();
            } else {
                $this->showSummary($hours);
            }

            if ($this->option('export')) {
                $this->exportData($hours, $this->option('export'));
            }

        } catch (\Exception $e) {
            $this->error("Error generating monitoring report: " . $e->getMessage());
            Log::error('Payment calculation monitoring command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        return 0;
    }

    /**
     * Show comprehensive dashboard
     */
    protected function showDashboard(int $hours): void
    {
        $dashboardData = $this->monitoringService->getDashboardData($hours);
        
        $this->displaySystemOverview($dashboardData['overview']);
        $this->line("");
        
        $this->displayPerformanceMetrics($dashboardData['performance']);
        $this->line("");
        
        $this->displayAccuracyMetrics($dashboardData['accuracy']);
        $this->line("");
        
        $this->displayErrorMetrics($dashboardData['errors']);
        $this->line("");
        
        $this->displayPricingUsage($dashboardData['pricing_configuration_usage']);
        $this->line("");
        
        if (!empty($dashboardData['alerts'])) {
            $this->displayAlerts($dashboardData['alerts']);
        }
    }

    /**
     * Show summary report
     */
    protected function showSummary(int $hours): void
    {
        $performance = $this->monitoringService->getPerformanceMetrics($hours);
        $accuracy = $this->monitoringService->getAccuracyMetrics($hours);
        $errors = $this->monitoringService->getErrorMetrics($hours);

        $this->info("📊 PERFORMANCE SUMMARY");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Calculations', number_format($performance['total_calculations'])],
                ['Success Rate', $performance['success_rate'] . '%'],
                ['Avg Execution Time', $performance['avg_execution_time_ms'] . 'ms'],
                ['Slow Calculations', number_format($performance['slow_calculations'])],
            ]
        );

        $this->line("");
        $this->info("🎯 ACCURACY SUMMARY");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Verified Calculations', number_format($accuracy['total_verified_calculations'])],
                ['Accuracy Rate', $accuracy['accuracy_rate'] . '%'],
                ['Avg Deviation', '$' . number_format($accuracy['avg_deviation'], 2)],
                ['Fallback Usage Rate', $accuracy['fallback_usage_rate'] . '%'],
                ['High Value Calculations', number_format($accuracy['high_value_calculations'])],
            ]
        );

        $this->line("");
        $this->info("❌ ERROR SUMMARY");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Errors', number_format($errors['total_errors'])],
                ['Error Rate', $errors['error_rate'] . '%'],
                ['Critical Errors', number_format($errors['critical_errors'])],
            ]
        );

        if (!empty($errors['error_types'])) {
            $this->line("");
            $this->info("Top Error Types:");
            foreach (array_slice($errors['error_types'], 0, 5, true) as $type => $count) {
                $this->line("  • {$type}: {$count}");
            }
        }
    }

    /**
     * Show alerts only
     */
    protected function showAlerts(): void
    {
        $alerts = $this->monitoringService->generateAlerts();
        
        if (empty($alerts)) {
            $this->info("✅ No active alerts - system is operating normally");
            return;
        }

        $this->displayAlerts($alerts);
    }

    /**
     * Display system overview
     */
    protected function displaySystemOverview(array $overview): void
    {
        $status = $overview['system_status'];
        $statusIcon = $this->getStatusIcon($status);
        
        $this->info("🏥 SYSTEM OVERVIEW");
        $this->line("Status: {$statusIcon} " . strtoupper($status));
        $this->line("Period: {$overview['period_hours']} hours");
        $this->line("Generated: {$overview['generated_at']}");
    }

    /**
     * Display performance metrics
     */
    protected function displayPerformanceMetrics(array $performance): void
    {
        $this->info("⚡ PERFORMANCE METRICS");
        
        $this->table(
            ['Metric', 'Value', 'Status'],
            [
                [
                    'Total Calculations', 
                    number_format($performance['total_calculations']),
                    $performance['total_calculations'] > 0 ? '✅' : '⚠️'
                ],
                [
                    'Success Rate', 
                    $performance['success_rate'] . '%',
                    $performance['success_rate'] >= 95 ? '✅' : ($performance['success_rate'] >= 90 ? '⚠️' : '❌')
                ],
                [
                    'Avg Execution Time', 
                    $performance['avg_execution_time_ms'] . 'ms',
                    $performance['avg_execution_time_ms'] <= 100 ? '✅' : ($performance['avg_execution_time_ms'] <= 200 ? '⚠️' : '❌')
                ],
                [
                    'Slow Calculations', 
                    number_format($performance['slow_calculations']),
                    $performance['slow_calculations'] == 0 ? '✅' : '⚠️'
                ]
            ]
        );

        if (!empty($performance['pricing_type_breakdown'])) {
            $this->line("");
            $this->line("Pricing Type Usage:");
            foreach ($performance['pricing_type_breakdown'] as $type => $count) {
                $percentage = $performance['total_calculations'] > 0 ? 
                    round(($count / $performance['total_calculations']) * 100, 1) : 0;
                $this->line("  • {$type}: {$count} ({$percentage}%)");
            }
        }
    }

    /**
     * Display accuracy metrics
     */
    protected function displayAccuracyMetrics(array $accuracy): void
    {
        $this->info("🎯 ACCURACY METRICS");
        
        $this->table(
            ['Metric', 'Value', 'Status'],
            [
                [
                    'Verified Calculations', 
                    number_format($accuracy['total_verified_calculations']),
                    $accuracy['total_verified_calculations'] > 0 ? '✅' : '⚠️'
                ],
                [
                    'Accuracy Rate', 
                    $accuracy['accuracy_rate'] . '%',
                    $accuracy['accuracy_rate'] >= 98 ? '✅' : ($accuracy['accuracy_rate'] >= 95 ? '⚠️' : '❌')
                ],
                [
                    'Avg Deviation', 
                    '$' . number_format($accuracy['avg_deviation'], 2),
                    $accuracy['avg_deviation'] <= 0.01 ? '✅' : ($accuracy['avg_deviation'] <= 1.00 ? '⚠️' : '❌')
                ],
                [
                    'Fallback Usage Rate', 
                    $accuracy['fallback_usage_rate'] . '%',
                    $accuracy['fallback_usage_rate'] <= 5 ? '✅' : ($accuracy['fallback_usage_rate'] <= 15 ? '⚠️' : '❌')
                ],
                [
                    'High Value Calculations', 
                    number_format($accuracy['high_value_calculations']),
                    '📊'
                ],
                [
                    'Suspicious Calculations', 
                    number_format($accuracy['suspicious_calculations']),
                    $accuracy['suspicious_calculations'] == 0 ? '✅' : '⚠️'
                ]
            ]
        );
    }

    /**
     * Display error metrics
     */
    protected function displayErrorMetrics(array $errors): void
    {
        $this->info("❌ ERROR METRICS");
        
        $this->table(
            ['Metric', 'Value', 'Status'],
            [
                [
                    'Total Errors', 
                    number_format($errors['total_errors']),
                    $errors['total_errors'] == 0 ? '✅' : '⚠️'
                ],
                [
                    'Error Rate', 
                    $errors['error_rate'] . '%',
                    $errors['error_rate'] <= 1 ? '✅' : ($errors['error_rate'] <= 5 ? '⚠️' : '❌')
                ],
                [
                    'Critical Errors', 
                    number_format($errors['critical_errors']),
                    $errors['critical_errors'] == 0 ? '✅' : '❌'
                ]
            ]
        );

        if (!empty($errors['error_types'])) {
            $this->line("");
            $this->line("Error Types (Top 5):");
            foreach (array_slice($errors['error_types'], 0, 5, true) as $type => $count) {
                $this->line("  • {$type}: {$count}");
            }
        }

        if (!empty($errors['top_error_messages'])) {
            $this->line("");
            $this->line("Common Error Messages:");
            foreach (array_slice($errors['top_error_messages'], 0, 3, true) as $message => $count) {
                $this->line("  • " . substr($message, 0, 60) . "... ({$count}x)");
            }
        }
    }

    /**
     * Display pricing configuration usage
     */
    protected function displayPricingUsage(array $usage): void
    {
        $this->info("⚙️ PRICING CONFIGURATION USAGE");
        
        $total = $usage['total_pricing_type'] + $usage['monthly_pricing_type'];
        
        if ($total > 0) {
            $totalPercentage = round(($usage['total_pricing_type'] / $total) * 100, 1);
            $monthlyPercentage = round(($usage['monthly_pricing_type'] / $total) * 100, 1);
            
            $this->table(
                ['Configuration Type', 'Count', 'Percentage'],
                [
                    ['Total Pricing', number_format($usage['total_pricing_type']), $totalPercentage . '%'],
                    ['Monthly Pricing', number_format($usage['monthly_pricing_type']), $monthlyPercentage . '%'],
                    ['Fallback Usage', number_format($usage['fallback_usage']), ''],
                    ['Configuration Changes', number_format($usage['configuration_changes']), '']
                ]
            );
        } else {
            $this->line("No pricing configuration usage data available for this period.");
        }
    }

    /**
     * Display alerts
     */
    protected function displayAlerts(array $alerts): void
    {
        $this->info("🚨 ACTIVE ALERTS");
        
        if (empty($alerts)) {
            $this->line("No active alerts");
            return;
        }

        foreach ($alerts as $alert) {
            $severityIcon = $this->getSeverityIcon($alert['severity']);
            $this->line("");
            $this->line("{$severityIcon} {$alert['type']} ({$alert['severity']})");
            $this->line("   " . $alert['message']);
            $this->line("   Time: " . $alert['timestamp']);
        }
    }

    /**
     * Export data to file
     */
    protected function exportData(int $hours, string $filename): void
    {
        try {
            $dashboardData = $this->monitoringService->getDashboardData($hours);
            
            $exportData = [
                'export_info' => [
                    'generated_at' => now()->toISOString(),
                    'period_hours' => $hours,
                    'command' => $this->signature
                ],
                'dashboard_data' => $dashboardData
            ];
            
            $jsonData = json_encode($exportData, JSON_PRETTY_PRINT);
            
            if (file_put_contents($filename, $jsonData)) {
                $this->info("✅ Data exported to: {$filename}");
            } else {
                $this->error("❌ Failed to export data to: {$filename}");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Export failed: " . $e->getMessage());
        }
    }

    /**
     * Get status icon
     */
    protected function getStatusIcon(string $status): string
    {
        return match($status) {
            'healthy' => '✅',
            'warning' => '⚠️',
            'degraded' => '🟡',
            'critical' => '❌',
            default => '❓'
        };
    }

    /**
     * Get severity icon
     */
    protected function getSeverityIcon(string $severity): string
    {
        return match($severity) {
            'low' => '🔵',
            'medium' => '🟡',
            'warning' => '⚠️',
            'high' => '🟠',
            'critical' => '🔴',
            default => '❓'
        };
    }
}