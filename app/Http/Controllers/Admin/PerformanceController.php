<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PerformanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Performance monitoring dashboard
     */
    public function index()
    {
        $metrics = [
            'overview' => $this->getOverviewMetrics(),
            'slow_requests' => $this->getSlowRequests(),
            'error_analysis' => $this->getErrorAnalysis(),
            'resource_usage' => $this->getResourceUsage(),
            'trends' => $this->getPerformanceTrends(),
            'top_endpoints' => $this->getTopEndpoints()
        ];

        return view('admin.performance.index', compact('metrics'));
    }

    /**
     * Get overview performance metrics
     */
    private function getOverviewMetrics()
    {
        // Ensure performance_logs table exists
        if (!DB::getSchemaBuilder()->hasTable('performance_logs')) {
            return [
                'avg_response_time' => 0,
                'total_requests' => 0,
                'error_rate' => 0,
                'slow_requests' => 0
            ];
        }

        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $todayMetrics = DB::table('performance_logs')
            ->whereDate('created_at', $today)
            ->selectRaw('
                AVG(execution_time) as avg_response_time,
                COUNT(*) as total_requests,
                SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as error_count,
                SUM(CASE WHEN execution_time > 1000 THEN 1 ELSE 0 END) as slow_requests
            ')
            ->first();

        $yesterdayMetrics = DB::table('performance_logs')
            ->whereDate('created_at', $yesterday)
            ->selectRaw('
                AVG(execution_time) as avg_response_time,
                COUNT(*) as total_requests
            ')
            ->first();

        $errorRate = $todayMetrics->total_requests > 0 
            ? ($todayMetrics->error_count / $todayMetrics->total_requests) * 100 
            : 0;

        $responseTimeChange = $yesterdayMetrics->avg_response_time > 0
            ? (($todayMetrics->avg_response_time - $yesterdayMetrics->avg_response_time) / $yesterdayMetrics->avg_response_time) * 100
            : 0;

        return [
            'avg_response_time' => round($todayMetrics->avg_response_time ?? 0, 2),
            'total_requests' => $todayMetrics->total_requests ?? 0,
            'error_rate' => round($errorRate, 2),
            'slow_requests' => $todayMetrics->slow_requests ?? 0,
            'response_time_change' => round($responseTimeChange, 2),
            'requests_change' => $this->calculateChange(
                $yesterdayMetrics->total_requests ?? 0,
                $todayMetrics->total_requests ?? 0
            )
        ];
    }

    /**
     * Get slowest requests
     */
    private function getSlowRequests()
    {
        if (!DB::getSchemaBuilder()->hasTable('performance_logs')) {
            return [];
        }

        return DB::table('performance_logs')
            ->select([
                'url', 'method', 'execution_time', 'memory_usage', 
                'query_count', 'status_code', 'created_at'
            ])
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->orderBy('execution_time', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($log) {
                return [
                    'url' => $log->url,
                    'method' => $log->method,
                    'execution_time' => round($log->execution_time, 2) . 'ms',
                    'memory_usage' => $this->formatBytes($log->memory_usage),
                    'query_count' => $log->query_count,
                    'status_code' => $log->status_code,
                    'created_at' => Carbon::parse($log->created_at)->diffForHumans()
                ];
            });
    }

    /**
     * Get error analysis
     */
    private function getErrorAnalysis()
    {
        if (!DB::getSchemaBuilder()->hasTable('performance_logs')) {
            return [
                'by_status_code' => [],
                'by_endpoint' => [],
                'recent_errors' => []
            ];
        }

        // Errors by status code
        $errorsByStatus = DB::table('performance_logs')
            ->select('status_code', DB::raw('COUNT(*) as count'))
            ->where('status_code', '>=', 400)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('status_code')
            ->orderBy('count', 'desc')
            ->get();

        // Errors by endpoint
        $errorsByEndpoint = DB::table('performance_logs')
            ->select('url', 'method', DB::raw('COUNT(*) as error_count'))
            ->where('status_code', '>=', 400)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('url', 'method')
            ->orderBy('error_count', 'desc')
            ->limit(10)
            ->get();

        // Recent errors
        $recentErrors = DB::table('performance_logs')
            ->select(['url', 'method', 'status_code', 'execution_time', 'created_at'])
            ->where('status_code', '>=', 400)
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        return [
            'by_status_code' => $errorsByStatus,
            'by_endpoint' => $errorsByEndpoint,
            'recent_errors' => $recentErrors
        ];
    }

    /**
     * Get resource usage metrics
     */
    private function getResourceUsage()
    {
        if (!DB::getSchemaBuilder()->hasTable('performance_logs')) {
            return [
                'avg_memory_usage' => 0,
                'avg_query_count' => 0,
                'peak_memory' => 0,
                'max_queries' => 0
            ];
        }

        $usage = DB::table('performance_logs')
            ->whereDate('created_at', Carbon::today())
            ->selectRaw('
                AVG(memory_usage) as avg_memory_usage,
                AVG(query_count) as avg_query_count,
                MAX(memory_usage) as peak_memory,
                MAX(query_count) as max_queries
            ')
            ->first();

        return [
            'avg_memory_usage' => $this->formatBytes($usage->avg_memory_usage ?? 0),
            'avg_query_count' => round($usage->avg_query_count ?? 0, 1),
            'peak_memory' => $this->formatBytes($usage->peak_memory ?? 0),
            'max_queries' => $usage->max_queries ?? 0
        ];
    }

    /**
     * Get performance trends over time
     */
    private function getPerformanceTrends()
    {
        if (!DB::getSchemaBuilder()->hasTable('performance_logs')) {
            return [
                'hourly_response_times' => [],
                'daily_request_counts' => []
            ];
        }

        // Hourly response times for today
        $hourlyTrends = DB::table('performance_logs')
            ->selectRaw('
                HOUR(created_at) as hour,
                AVG(execution_time) as avg_response_time,
                COUNT(*) as request_count
            ')
            ->whereDate('created_at', Carbon::today())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        // Fill missing hours with zeros
        $hourlyData = [];
        for ($i = 0; $i < 24; $i++) {
            $hourlyData[] = [
                'hour' => $i,
                'avg_response_time' => isset($hourlyTrends[$i]) 
                    ? round($hourlyTrends[$i]->avg_response_time, 2) 
                    : 0,
                'request_count' => isset($hourlyTrends[$i]) 
                    ? $hourlyTrends[$i]->request_count 
                    : 0
            ];
        }

        // Daily request counts for the last 7 days
        $dailyTrends = DB::table('performance_logs')
            ->selectRaw('
                DATE(created_at) as date,
                COUNT(*) as request_count,
                AVG(execution_time) as avg_response_time
            ')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'hourly_response_times' => $hourlyData,
            'daily_request_counts' => $dailyTrends
        ];
    }

    /**
     * Get top endpoints by request count
     */
    private function getTopEndpoints()
    {
        if (!DB::getSchemaBuilder()->hasTable('performance_logs')) {
            return [];
        }

        return DB::table('performance_logs')
            ->selectRaw('
                url,
                method,
                COUNT(*) as request_count,
                AVG(execution_time) as avg_response_time,
                MAX(execution_time) as max_response_time,
                SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as error_count
            ')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('url', 'method')
            ->orderBy('request_count', 'desc')
            ->limit(15)
            ->get()
            ->map(function ($endpoint) {
                $errorRate = $endpoint->request_count > 0 
                    ? ($endpoint->error_count / $endpoint->request_count) * 100 
                    : 0;

                return [
                    'url' => $endpoint->url,
                    'method' => $endpoint->method,
                    'request_count' => $endpoint->request_count,
                    'avg_response_time' => round($endpoint->avg_response_time, 2),
                    'max_response_time' => round($endpoint->max_response_time, 2),
                    'error_rate' => round($errorRate, 2)
                ];
            });
    }

    /**
     * Get detailed performance report
     */
    public function getReport(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(7)->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());

        if (!DB::getSchemaBuilder()->hasTable('performance_logs')) {
            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => [],
                    'trends' => [],
                    'top_endpoints' => []
                ]
            ]);
        }

        $summary = DB::table('performance_logs')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total_requests,
                AVG(execution_time) as avg_response_time,
                MAX(execution_time) as max_response_time,
                MIN(execution_time) as min_response_time,
                AVG(memory_usage) as avg_memory_usage,
                AVG(query_count) as avg_query_count,
                SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as error_count,
                SUM(CASE WHEN execution_time > 1000 THEN 1 ELSE 0 END) as slow_requests
            ')
            ->first();

        $trends = DB::table('performance_logs')
            ->selectRaw('
                DATE(created_at) as date,
                COUNT(*) as requests,
                AVG(execution_time) as avg_response_time,
                SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as errors
            ')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topEndpoints = DB::table('performance_logs')
            ->selectRaw('
                url,
                method,
                COUNT(*) as requests,
                AVG(execution_time) as avg_response_time
            ')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('url', 'method')
            ->orderBy('requests', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'trends' => $trends,
                'top_endpoints' => $topEndpoints
            ]
        ]);
    }

    /**
     * Clear old performance logs
     */
    public function clearOldLogs(Request $request)
    {
        $days = $request->get('days', 30);

        try {
            if (!DB::getSchemaBuilder()->hasTable('performance_logs')) {
                return response()->json([
                    'success' => true,
                    'message' => 'No performance logs to clear'
                ]);
            }

            $deleted = DB::table('performance_logs')
                ->where('created_at', '<', Carbon::now()->subDays($days))
                ->delete();

            return response()->json([
                'success' => true,
                'message' => "Deleted {$deleted} old performance log entries"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear logs: ' . $e->getMessage()
            ], 500);
        }
    }

    // Helper methods
    private function calculateChange($old, $new)
    {
        if ($old == 0) return $new > 0 ? 100 : 0;
        return round((($new - $old) / $old) * 100, 2);
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}