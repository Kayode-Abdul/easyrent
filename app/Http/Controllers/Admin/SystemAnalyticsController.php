<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Monitoring\CommissionHealthMonitor;
use App\Services\Monitoring\PaymentSuccessTracker;
use App\Services\Monitoring\FraudAlertSystem;
use App\Services\Commission\MultiTierCommissionCalculator;
use App\Services\Commission\ReferralChainService;
use App\Models\CommissionPayment;
use App\Models\ReferralChain;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SystemAnalyticsController extends Controller
{
    protected CommissionHealthMonitor $healthMonitor;
    protected PaymentSuccessTracker $paymentTracker;
    protected FraudAlertSystem $fraudAlerts;
    protected MultiTierCommissionCalculator $commissionCalculator;
    protected ReferralChainService $chainService;

    public function __construct(
        CommissionHealthMonitor $healthMonitor,
        PaymentSuccessTracker $paymentTracker,
        FraudAlertSystem $fraudAlerts,
        MultiTierCommissionCalculator $commissionCalculator,
        ReferralChainService $chainService
    ) {
        $this->middleware(['auth', 'role:admin']);
        $this->healthMonitor = $healthMonitor;
        $this->paymentTracker = $paymentTracker;
        $this->fraudAlerts = $fraudAlerts;
        $this->commissionCalculator = $commissionCalculator;
        $this->chainService = $chainService;
    }

    /**
     * Display system performance analytics dashboard
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get date range from request or default to last 30 days
        $endDate = $request->get('end_date') ? 
            Carbon::parse($request->get('end_date')) : Carbon::now();
        $startDate = $request->get('start_date') ? 
            Carbon::parse($request->get('start_date')) : $endDate->copy()->subDays(30);

        // Get system health metrics
        $healthMetrics = $this->healthMonitor->getSystemHealthMetrics();
        
        // Get commission performance metrics
        $commissionMetrics = $this->getCommissionPerformanceMetrics($startDate, $endDate);
        
        // Get referral chain effectiveness
        $chainEffectiveness = $this->getReferralChainEffectiveness($startDate, $endDate);
        
        // Get regional performance comparison
        $regionalPerformance = $this->getRegionalPerformanceComparison($startDate, $endDate);
        
        // Get payment processing trends
        $paymentTrends = $this->paymentTracker->getProcessingTrends(30);
        
        // Get fraud statistics
        $fraudStats = $this->fraudAlerts->getFraudStatistics($startDate, $endDate);
        
        // Get active alerts
        $activeAlerts = $this->healthMonitor->getActiveAlerts();

        return view('admin.system-analytics.dashboard', compact(
            'healthMetrics',
            'commissionMetrics',
            'chainEffectiveness',
            'regionalPerformance',
            'paymentTrends',
            'fraudStats',
            'activeAlerts',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get commission performance metrics
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getCommissionPerformanceMetrics(Carbon $startDate, Carbon $endDate): array
    {
        // Total commission volume
        $totalCommissions = CommissionPayment::whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');

        // Commission by tier
        $commissionByTier = CommissionPayment::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('commission_tier, COUNT(*) as count, SUM(total_amount) as total_amount')
            ->groupBy('commission_tier')
            ->get();

        // Daily commission trends
        $dailyTrends = CommissionPayment::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total_amount) as total_amount')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Average commission per payment
        $avgCommission = CommissionPayment::whereBetween('created_at', [$startDate, $endDate])
            ->avg('total_amount');

        // Commission success rate
        $successfulCommissions = CommissionPayment::whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'completed')
            ->count();
        $totalCommissionAttempts = CommissionPayment::whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $successRate = $totalCommissionAttempts > 0 ? 
            round(($successfulCommissions / $totalCommissionAttempts) * 100, 2) : 100;

        return [
            'total_commissions' => $totalCommissions,
            'commission_by_tier' => $commissionByTier,
            'daily_trends' => $dailyTrends,
            'average_commission' => round($avgCommission ?? 0, 2),
            'success_rate' => $successRate,
            'successful_commissions' => $successfulCommissions,
            'total_attempts' => $totalCommissionAttempts
        ];
    }

    /**
     * Get referral chain effectiveness analysis
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getReferralChainEffectiveness(Carbon $startDate, Carbon $endDate): array
    {
        // Total referral chains
        $totalChains = ReferralChain::whereBetween('created_at', [$startDate, $endDate])->count();

        // Active chains (with successful commissions)
        $activeChains = ReferralChain::whereBetween('created_at', [$startDate, $endDate])
            ->whereHas('commissionPayments', function($query) {
                $query->where('payment_status', 'completed');
            })
            ->count();

        // Chain conversion rate
        $conversionRate = $totalChains > 0 ? 
            round(($activeChains / $totalChains) * 100, 2) : 0;

        // Average commission per chain
        $avgCommissionPerChain = ReferralChain::whereBetween('created_at', [$startDate, $endDate])
            ->withSum('commissionPayments', 'amount')
            ->get()
            ->avg('commission_payments_sum_amount');

        // Chain performance by tier
        $chainsByTier = DB::table('referral_chains')
            ->leftJoin('commission_payments', 'referral_chains.id', '=', 'commission_payments.referral_chain_id')
            ->whereBetween('referral_chains.created_at', [$startDate, $endDate])
            ->selectRaw('
                COUNT(DISTINCT referral_chains.id) as total_chains,
                COUNT(DISTINCT CASE WHEN commission_payments.commission_tier = "super_marketer" THEN referral_chains.id END) as super_marketer_chains,
                COUNT(DISTINCT CASE WHEN commission_payments.commission_tier = "marketer" THEN referral_chains.id END) as marketer_chains,
                SUM(CASE WHEN commission_payments.commission_tier = "super_marketer" THEN commission_payments.total_amount ELSE 0 END) as super_marketer_total,
                SUM(CASE WHEN commission_payments.commission_tier = "marketer" THEN commission_payments.total_amount ELSE 0 END) as marketer_total
            ')
            ->first();

        // Top performing chains
        $topChains = ReferralChain::whereBetween('created_at', [$startDate, $endDate])
            ->withSum('commissionPayments', 'amount')
            ->with(['superMarketer', 'marketer', 'landlord'])
            ->orderBy('commission_payments_sum_amount', 'desc')
            ->limit(10)
            ->get();

        return [
            'total_chains' => $totalChains,
            'active_chains' => $activeChains,
            'conversion_rate' => $conversionRate,
            'average_commission_per_chain' => round($avgCommissionPerChain ?? 0, 2),
            'chains_by_tier' => $chainsByTier,
            'top_performing_chains' => $topChains
        ];
    }

    /**
     * Get regional performance comparison
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getRegionalPerformanceComparison(Carbon $startDate, Carbon $endDate): array
    {
        // Commission performance by region
        $regionalCommissions = DB::table('commission_payments')
            ->join('users', 'commission_payments.marketer_id', '=', 'users.user_id')
            ->whereBetween('commission_payments.created_at', [$startDate, $endDate])
            ->selectRaw('
                users.state as region,
                COUNT(*) as total_payments,
                SUM(commission_payments.total_amount) as total_amount,
                AVG(commission_payments.total_amount) as avg_amount,
                SUM(CASE WHEN commission_payments.payment_status = "completed" THEN 1 ELSE 0 END) as successful_payments
            ')
            ->groupBy('users.state')
            ->orderBy('total_amount', 'desc')
            ->get();

        // Calculate success rates for each region
        foreach ($regionalCommissions as $region) {
            $region->success_rate = $region->total_payments > 0 ? 
                round(($region->successful_payments / $region->total_payments) * 100, 2) : 0;
        }

        // Referral chain performance by region
        $regionalChains = DB::table('referral_chains')
            ->join('users as marketers', 'referral_chains.marketer_id', '=', 'marketers.user_id')
            ->whereBetween('referral_chains.created_at', [$startDate, $endDate])
            ->selectRaw('
                marketers.state as region,
                COUNT(*) as total_chains,
                COUNT(CASE WHEN referral_chains.status = "active" THEN 1 END) as active_chains
            ')
            ->groupBy('marketers.state')
            ->get();

        // Top performing regions
        $topRegions = $regionalCommissions->take(5);

        return [
            'regional_commissions' => $regionalCommissions,
            'regional_chains' => $regionalChains,
            'top_regions' => $topRegions
        ];
    }

    /**
     * Get system health status API endpoint
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function healthStatus()
    {
        $healthMetrics = $this->healthMonitor->getSystemHealthMetrics();
        
        return response()->json([
            'status' => 'success',
            'data' => $healthMetrics
        ]);
    }

    /**
     * Get real-time metrics API endpoint
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function realTimeMetrics()
    {
        $metrics = [
            'payment_metrics' => $this->paymentTracker->getRealTimeMetrics(),
            'fraud_risk' => $this->fraudAlerts->getSystemFraudRiskScore(),
            'active_alerts' => $this->healthMonitor->getActiveAlerts(),
            'processing_bottlenecks' => $this->paymentTracker->getProcessingBottlenecks()
        ];

        return response()->json([
            'status' => 'success',
            'data' => $metrics
        ]);
    }

    /**
     * Export analytics data
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportAnalytics(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'required|in:csv,json'
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $format = $request->format;

        $data = [
            'commission_metrics' => $this->getCommissionPerformanceMetrics($startDate, $endDate),
            'chain_effectiveness' => $this->getReferralChainEffectiveness($startDate, $endDate),
            'regional_performance' => $this->getRegionalPerformanceComparison($startDate, $endDate),
            'fraud_statistics' => $this->fraudAlerts->getFraudStatistics($startDate, $endDate),
            'export_info' => [
                'generated_at' => now()->toISOString(),
                'period' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString()
                ]
            ]
        ];

        if ($format === 'json') {
            return response()->json($data)
                ->header('Content-Disposition', 'attachment; filename="system_analytics_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.json"');
        }

        // For CSV format, flatten the data
        $csvData = $this->flattenAnalyticsData($data);
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="system_analytics_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.csv"'
        ];

        return response()->stream(function() use ($csvData) {
            $handle = fopen('php://output', 'w');
            
            // Write headers
            if (!empty($csvData)) {
                fputcsv($handle, array_keys($csvData[0]));
                
                // Write data
                foreach ($csvData as $row) {
                    fputcsv($handle, $row);
                }
            }
            
            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Flatten analytics data for CSV export
     *
     * @param array $data
     * @return array
     */
    private function flattenAnalyticsData(array $data): array
    {
        $flattened = [];
        
        // Commission metrics summary
        $commissionMetrics = $data['commission_metrics'];
        $flattened[] = [
            'metric_type' => 'commission_summary',
            'metric_name' => 'total_commissions',
            'value' => $commissionMetrics['total_commissions'],
            'period_start' => $data['export_info']['period']['start'],
            'period_end' => $data['export_info']['period']['end']
        ];
        
        $flattened[] = [
            'metric_type' => 'commission_summary',
            'metric_name' => 'success_rate',
            'value' => $commissionMetrics['success_rate'],
            'period_start' => $data['export_info']['period']['start'],
            'period_end' => $data['export_info']['period']['end']
        ];

        // Regional performance
        foreach ($data['regional_performance']['regional_commissions'] as $region) {
            $flattened[] = [
                'metric_type' => 'regional_performance',
                'metric_name' => 'total_amount',
                'region' => $region->region,
                'value' => $region->total_amount,
                'success_rate' => $region->success_rate,
                'period_start' => $data['export_info']['period']['start'],
                'period_end' => $data['export_info']['period']['end']
            ];
        }

        return $flattened;
    }

    /**
     * Get commission trends chart data
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function commissionTrends(Request $request)
    {
        $days = $request->get('days', 30);
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays($days);

        $trends = CommissionPayment::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $trends
        ]);
    }

    /**
     * Get referral chain performance chart data
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function chainPerformance(Request $request)
    {
        $days = $request->get('days', 30);
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays($days);

        $performance = ReferralChain::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total_chains')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Add successful chains data
        foreach ($performance as $day) {
            $successfulChains = ReferralChain::whereDate('created_at', $day->date)
                ->whereHas('commissionPayments', function($query) {
                    $query->where('payment_status', 'completed');
                })
                ->count();
            
            $day->successful_chains = $successfulChains;
            $day->success_rate = $day->total_chains > 0 ? 
                round(($successfulChains / $day->total_chains) * 100, 2) : 0;
        }

        return response()->json([
            'status' => 'success',
            'data' => $performance
        ]);
    }
}