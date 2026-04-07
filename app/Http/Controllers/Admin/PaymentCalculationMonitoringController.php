<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Monitoring\PaymentCalculationMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PaymentCalculationMonitoringController extends Controller
{
    protected $monitoringService;

    public function __construct(PaymentCalculationMonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
        
        // Ensure only authorized users can access monitoring
        $this->middleware(['auth', \App\Http\Middleware\PaymentMonitoringAccessMiddleware::class]);
    }

    /**
     * Display the monitoring dashboard
     */
    public function dashboard(Request $request): View
    {
        $hours = $request->get('hours', 24);
        $dashboardData = $this->monitoringService->getDashboardData($hours);
        
        return view('admin.payment-monitoring.dashboard', [
            'dashboardData' => $dashboardData,
            'selectedHours' => $hours,
            'availablePeriods' => [
                1 => 'Last Hour',
                6 => 'Last 6 Hours', 
                24 => 'Last 24 Hours',
                72 => 'Last 3 Days',
                168 => 'Last Week'
            ]
        ]);
    }

    /**
     * Get performance metrics API endpoint
     */
    public function performanceMetrics(Request $request): JsonResponse
    {
        $hours = $request->get('hours', 24);
        $metrics = $this->monitoringService->getPerformanceMetrics($hours);
        
        return response()->json([
            'success' => true,
            'data' => $metrics
        ]);
    }

    /**
     * Get accuracy metrics API endpoint
     */
    public function accuracyMetrics(Request $request): JsonResponse
    {
        $hours = $request->get('hours', 24);
        $metrics = $this->monitoringService->getAccuracyMetrics($hours);
        
        return response()->json([
            'success' => true,
            'data' => $metrics
        ]);
    }

    /**
     * Get error metrics API endpoint
     */
    public function errorMetrics(Request $request): JsonResponse
    {
        $hours = $request->get('hours', 24);
        $metrics = $this->monitoringService->getErrorMetrics($hours);
        
        return response()->json([
            'success' => true,
            'data' => $metrics
        ]);
    }

    /**
     * Get current alerts API endpoint
     */
    public function alerts(): JsonResponse
    {
        $alerts = $this->monitoringService->generateAlerts();
        
        return response()->json([
            'success' => true,
            'data' => $alerts,
            'count' => count($alerts)
        ]);
    }

    /**
     * Get pricing configuration usage API endpoint
     */
    public function pricingUsage(Request $request): JsonResponse
    {
        $hours = $request->get('hours', 24);
        $usage = $this->monitoringService->getPricingConfigurationUsage($hours);
        
        return response()->json([
            'success' => true,
            'data' => $usage
        ]);
    }

    /**
     * Get comprehensive dashboard data API endpoint
     */
    public function dashboardData(Request $request): JsonResponse
    {
        $hours = $request->get('hours', 24);
        $dashboardData = $this->monitoringService->getDashboardData($hours);
        
        return response()->json([
            'success' => true,
            'data' => $dashboardData
        ]);
    }

    /**
     * Export monitoring data
     */
    public function export(Request $request)
    {
        $hours = $request->get('hours', 24);
        $format = $request->get('format', 'json');
        
        $dashboardData = $this->monitoringService->getDashboardData($hours);
        
        $exportData = [
            'export_info' => [
                'generated_at' => now()->toISOString(),
                'period_hours' => $hours,
                'exported_by' => auth()->user()->email ?? 'system'
            ],
            'dashboard_data' => $dashboardData
        ];
        
        $filename = 'payment-calculation-monitoring-' . now()->format('Y-m-d-H-i-s');
        
        switch ($format) {
            case 'csv':
                return $this->exportToCsv($exportData, $filename);
            case 'excel':
                return $this->exportToExcel($exportData, $filename);
            case 'json':
            default:
                return response()->json($exportData)
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '.json"');
        }
    }

    /**
     * Real-time monitoring endpoint for live updates
     */
    public function realTimeData(): JsonResponse
    {
        // Get last 15 minutes of data for real-time monitoring
        $recentData = [
            'performance' => $this->monitoringService->getPerformanceMetrics(0.25), // 15 minutes
            'errors' => $this->monitoringService->getErrorMetrics(0.25),
            'alerts' => $this->monitoringService->generateAlerts(),
            'timestamp' => now()->toISOString()
        ];
        
        return response()->json([
            'success' => true,
            'data' => $recentData
        ]);
    }

    /**
     * System health check endpoint
     */
    public function healthCheck(): JsonResponse
    {
        $performanceMetrics = $this->monitoringService->getPerformanceMetrics(1);
        $errorMetrics = $this->monitoringService->getErrorMetrics(1);
        $alerts = $this->monitoringService->generateAlerts();
        
        $status = 'healthy';
        $issues = [];
        
        // Check error rate
        if ($errorMetrics['error_rate'] > 5) {
            $status = 'critical';
            $issues[] = 'High error rate: ' . $errorMetrics['error_rate'] . '%';
        }
        
        // Check performance
        if ($performanceMetrics['avg_execution_time_ms'] > 200) {
            $status = $status === 'healthy' ? 'degraded' : $status;
            $issues[] = 'Slow performance: ' . $performanceMetrics['avg_execution_time_ms'] . 'ms avg';
        }
        
        // Check for critical alerts
        $criticalAlerts = array_filter($alerts, fn($alert) => $alert['severity'] === 'critical');
        if (!empty($criticalAlerts)) {
            $status = 'critical';
            $issues[] = count($criticalAlerts) . ' critical alert(s) active';
        }
        
        return response()->json([
            'status' => $status,
            'issues' => $issues,
            'metrics' => [
                'calculations_last_hour' => $performanceMetrics['total_calculations'],
                'success_rate' => $performanceMetrics['success_rate'],
                'error_rate' => $errorMetrics['error_rate'],
                'avg_execution_time' => $performanceMetrics['avg_execution_time_ms']
            ],
            'alerts_count' => count($alerts),
            'checked_at' => now()->toISOString()
        ]);
    }

    /**
     * Export data to CSV format
     */
    protected function exportToCsv(array $data, string $filename)
    {
        $csvData = [];
        
        // Performance metrics
        $performance = $data['dashboard_data']['performance'];
        $csvData[] = ['Section', 'Metric', 'Value'];
        $csvData[] = ['Performance', 'Total Calculations', $performance['total_calculations']];
        $csvData[] = ['Performance', 'Success Rate', $performance['success_rate'] . '%'];
        $csvData[] = ['Performance', 'Avg Execution Time', $performance['avg_execution_time_ms'] . 'ms'];
        $csvData[] = ['Performance', 'Slow Calculations', $performance['slow_calculations']];
        
        // Accuracy metrics
        $accuracy = $data['dashboard_data']['accuracy'];
        $csvData[] = ['Accuracy', 'Verified Calculations', $accuracy['total_verified_calculations']];
        $csvData[] = ['Accuracy', 'Accuracy Rate', $accuracy['accuracy_rate'] . '%'];
        $csvData[] = ['Accuracy', 'Avg Deviation', '$' . $accuracy['avg_deviation']];
        $csvData[] = ['Accuracy', 'Fallback Usage Rate', $accuracy['fallback_usage_rate'] . '%'];
        
        // Error metrics
        $errors = $data['dashboard_data']['errors'];
        $csvData[] = ['Errors', 'Total Errors', $errors['total_errors']];
        $csvData[] = ['Errors', 'Error Rate', $errors['error_rate'] . '%'];
        $csvData[] = ['Errors', 'Critical Errors', $errors['critical_errors']];
        
        $output = fopen('php://temp', 'r+');
        foreach ($csvData as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);
        
        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '.csv"');
    }

    /**
     * Export data to Excel format (simplified CSV for now)
     */
    protected function exportToExcel(array $data, string $filename)
    {
        // For now, return CSV format with Excel extension
        // In a full implementation, you would use a library like PhpSpreadsheet
        return $this->exportToCsv($data, $filename)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '.xls"');
    }
}