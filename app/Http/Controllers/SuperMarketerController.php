<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ReferralChain;
use App\Models\CommissionPayment;
use App\Models\Referral;
use App\Models\ReferralReward;
use App\Models\Property;
use App\Services\Commission\MultiTierCommissionCalculator;
use App\Services\Commission\ReferralChainService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class SuperMarketerController extends Controller
{
    private MultiTierCommissionCalculator $commissionCalculator;
    private ReferralChainService $referralChainService;

    public function __construct(
        MultiTierCommissionCalculator $commissionCalculator,
        ReferralChainService $referralChainService
    ) {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user->isSuperMarketer()) {
                abort(403, 'Access denied. Super Marketer privileges required.');
            }
            return $next($request);
        });
        
        $this->commissionCalculator = $commissionCalculator;
        $this->referralChainService = $referralChainService;
    }

    /**
     * Super Marketer Dashboard
     * Implements dashboard data aggregation methods
     */
    public function dashboard()
    {
        $superMarketer = Auth::user();
        
        // Get dashboard statistics
        $stats = $this->getDashboardStats($superMarketer);
        
        // Get referred marketers with performance data
        $referredMarketers = $this->getReferredMarketersWithPerformance($superMarketer);
        
        // Get commission breakdown and analytics
        $commissionBreakdown = $this->getCommissionBreakdown($superMarketer);
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities($superMarketer);
        
        // Get performance data for charts
        $performanceData = $this->getPerformanceData($superMarketer);
        
        // Get top performing marketers
        $topPerformers = $this->getTopPerformingMarketers($superMarketer);

        return view('super-marketer.dashboard', compact(
            'superMarketer',
            'stats',
            'referredMarketers',
            'commissionBreakdown',
            'recentActivities',
            'performanceData',
            'topPerformers'
        ));
    }

    /**
     * Get referred marketer performance tracking
     * Implements referred marketer performance tracking
     */
    public function referredMarketers(Request $request)
    {
        $superMarketer = Auth::user();
        
        $query = $superMarketer->referredMarketers()
            ->with(['marketerProfile', 'referrals', 'commissionPayments']);
        
        // Apply filters
        if ($request->status) {
            $query->where('marketer_status', $request->status);
        }
        
        if ($request->region) {
            $query->where('state', $request->region);
        }
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        
        $marketers = $query->paginate(15);
        
        // Add performance metrics to each marketer
        $marketers->getCollection()->transform(function ($marketer) {
            $marketer->performance_metrics = $this->getMarketerPerformanceMetrics($marketer);
            return $marketer;
        });
        
        $summary = $this->getReferredMarketersSummary($superMarketer);
        
        return view('super-marketer.referred-marketers', compact('marketers', 'summary'));
    }

    /**
     * Show individual marketer performance details
     */
    public function showMarketerPerformance($marketerId)
    {
        $superMarketer = Auth::user();
        
        // Verify this marketer is referred by the current Super Marketer
        $marketer = $superMarketer->referredMarketers()
            ->with(['marketerProfile', 'referrals.referred', 'commissionPayments'])
            ->findOrFail($marketerId);
        
        $performanceMetrics = $this->getDetailedMarketerPerformance($marketer);
        $commissionHistory = $this->getMarketerCommissionHistory($marketer);
        $referralChains = $this->getMarketerReferralChains($marketer);
        
        return view('super-marketer.marketer-performance', compact(
            'marketer',
            'performanceMetrics',
            'commissionHistory',
            'referralChains'
        ));
    }

    /**
     * Get commission breakdown and analytics
     * Implements commission breakdown and analytics
     */
    public function commissionAnalytics(Request $request)
    {
        $superMarketer = Auth::user();
        
        $dateRange = $this->getDateRange($request);
        
        // Get commission breakdown by tier
        $commissionByTier = $this->getCommissionBreakdownByTier($superMarketer, $dateRange);
        
        // Get commission trends over time
        $commissionTrends = $this->getCommissionTrends($superMarketer, $dateRange);
        
        // Get regional commission breakdown
        $regionalBreakdown = $this->getRegionalCommissionBreakdown($superMarketer, $dateRange);
        
        // Get commission comparison with previous period
        $comparison = $this->getCommissionComparison($superMarketer, $dateRange);
        
        return view('super-marketer.commission-analytics', compact(
            'commissionByTier',
            'commissionTrends',
            'regionalBreakdown',
            'comparison',
            'dateRange'
        ));
    }

    /**
     * Get marketer details for modal display
     */
    public function getMarketerDetails($marketerId)
    {
        $superMarketer = Auth::user();
        
        // Verify this marketer is referred by the current Super Marketer
        $marketer = $superMarketer->referredMarketers()
            ->with(['marketerProfile'])
            ->findOrFail($marketerId);
        
        $performanceMetrics = $this->getMarketerPerformanceMetrics($marketer);
        
        return response()->json([
            'success' => true,
            'marketer' => [
                'user_id' => $marketer->user_id,
                'first_name' => $marketer->first_name,
                'last_name' => $marketer->last_name,
                'email' => $marketer->email,
                'phone' => $marketer->phone,
                'state' => $marketer->state,
                'photo' => $marketer->photo,
                'marketer_status' => $marketer->marketer_status,
                'created_at' => $marketer->created_at,
                'marketer_profile' => $marketer->marketerProfile,
                'performance' => $performanceMetrics
            ]
        ]);
    }

    /**
     * Generate referral links for marketers
     */
    public function generateReferralLink(Request $request)
    {
        $request->validate([
            'campaign_name' => 'nullable|string|max:255',
            'target_audience' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000'
        ]);
        
        $superMarketer = Auth::user();
        
        // Generate unique referral code for Super Marketer
        $referralCode = $this->generateUniqueReferralCode($superMarketer);
        
        $referralLink = url('/register?super_ref=' . $referralCode);
        
        if ($request->campaign_name) {
            $referralLink .= '&campaign=' . urlencode($request->campaign_name);
        }
        
        // Log referral link generation
        $this->logReferralLinkGeneration($superMarketer, $referralCode, $request->all());
        
        return response()->json([
            'success' => true,
            'referral_link' => $referralLink,
            'referral_code' => $referralCode,
            'qr_code_url' => $this->generateQRCode($referralLink)
        ]);
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats($superMarketer): array
    {
        $totalReferredMarketers = $superMarketer->referredMarketers()->count();
        $activeMarketers = $superMarketer->referredMarketers()
            ->where('marketer_status', 'active')->count();
        
        $totalCommissionEarned = $superMarketer->commissionPayments()
            ->where('commission_tier', 'super_marketer')
            ->where('payment_status', 'completed')
            ->sum('total_amount');
        
        $pendingCommission = $superMarketer->commissionPayments()
            ->where('commission_tier', 'super_marketer')
            ->where('payment_status', 'pending')
            ->sum('total_amount');
        
        $totalReferralChains = ReferralChain::where('super_marketer_id', $superMarketer->user_id)
            ->where('status', 'active')
            ->count();
        
        $thisMonthCommission = $superMarketer->commissionPayments()
            ->where('commission_tier', 'super_marketer')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');
        
        $lastMonthCommission = $superMarketer->commissionPayments()
            ->where('commission_tier', 'super_marketer')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('total_amount');
        
        $commissionGrowth = $lastMonthCommission > 0 
            ? (($thisMonthCommission - $lastMonthCommission) / $lastMonthCommission) * 100 
            : 0;
        
        return [
            'total_referred_marketers' => $totalReferredMarketers,
            'active_marketers' => $activeMarketers,
            'total_commission_earned' => $totalCommissionEarned,
            'pending_commission' => $pendingCommission,
            'total_referral_chains' => $totalReferralChains,
            'this_month_commission' => $thisMonthCommission,
            'commission_growth' => round($commissionGrowth, 2),
            'conversion_rate' => $this->calculateConversionRate($superMarketer)
        ];
    }

    /**
     * Get referred marketers with performance data
     */
    private function getReferredMarketersWithPerformance($superMarketer): Collection
    {
        return $superMarketer->referredMarketers()
            ->with(['marketerProfile'])
            ->limit(10)
            ->get()
            ->map(function ($marketer) {
                $marketer->performance = $this->getMarketerPerformanceMetrics($marketer);
                return $marketer;
            });
    }

    /**
     * Get marketer performance metrics
     */
    private function getMarketerPerformanceMetrics($marketer): array
    {
        $totalReferrals = $marketer->referrals()->count();
        $successfulReferrals = $marketer->referrals()
            ->whereHas('referred', function($q) {
                $q->where('role', 2); // Landlords
            })->count();
        
        $totalCommission = $marketer->referralRewards()
            ->where('status', 'paid')
            ->sum('amount');
        
        $thisMonthReferrals = $marketer->referrals()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        $conversionRate = $totalReferrals > 0 
            ? round(($successfulReferrals / $totalReferrals) * 100, 2) 
            : 0;
        
        return [
            'total_referrals' => $totalReferrals,
            'successful_referrals' => $successfulReferrals,
            'total_commission' => $totalCommission,
            'this_month_referrals' => $thisMonthReferrals,
            'conversion_rate' => $conversionRate,
            'status' => $marketer->marketer_status,
            'join_date' => $marketer->created_at
        ];
    }

    /**
     * Get commission breakdown
     */
    private function getCommissionBreakdown($superMarketer): array
    {
        $region = $superMarketer->state ?? 'default';
        
        // Get commission payments by tier for this Super Marketer
        $superMarketerCommissions = $superMarketer->commissionPayments()
            ->where('commission_tier', 'super_marketer')
            ->where('payment_status', 'completed')
            ->sum('total_amount');
        
        // Get total commissions generated by referred marketers
        $referredMarketerIds = $superMarketer->referredMarketers()->pluck('user_id');
        $marketerCommissions = CommissionPayment::whereIn('marketer_id', $referredMarketerIds)
            ->where('commission_tier', 'marketer')
            ->where('payment_status', 'completed')
            ->sum('total_amount');
        
        // Get commission breakdown by region
        $regionalBreakdown = $this->getRegionalCommissionBreakdown($superMarketer);
        
        return [
            'super_marketer_total' => $superMarketerCommissions,
            'marketer_total' => $marketerCommissions,
            'total_generated' => $superMarketerCommissions + $marketerCommissions,
            'regional_breakdown' => $regionalBreakdown,
            'commission_rate' => $superMarketer->getCommissionRate($region)
        ];
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities($superMarketer): Collection
    {
        $activities = collect();
        
        // Recent marketer referrals
        $recentMarketers = ReferralChain::where('super_marketer_id', $superMarketer->user_id)
            ->with(['marketer'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($chain) {
                return [
                    'type' => 'marketer_referral',
                    'message' => "New marketer {$chain->marketer->first_name} {$chain->marketer->last_name} joined your network",
                    'date' => $chain->created_at,
                    'data' => $chain
                ];
            });
        
        // Recent commission payments
        $recentPayments = $superMarketer->commissionPayments()
            ->where('commission_tier', 'super_marketer')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($payment) {
                return [
                    'type' => 'commission_payment',
                    'message' => "Commission payment of ₦" . number_format($payment->amount, 2) . " received",
                    'date' => $payment->created_at,
                    'data' => $payment
                ];
            });
        
        return $activities->merge($recentMarketers)
            ->merge($recentPayments)
            ->sortByDesc('date')
            ->take(10);
    }

    /**
     * Get performance data for charts
     */
    private function getPerformanceData($superMarketer): array
    {
        $months = [];
        $referrals = [];
        $commissions = [];
        $marketers = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            // Count new marketers referred this month
            $monthlyMarketers = ReferralChain::where('super_marketer_id', $superMarketer->user_id)
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
            $marketers[] = $monthlyMarketers;
            
            // Count total referrals from network this month
            $referredMarketerIds = $superMarketer->referredMarketers()->pluck('user_id');
            $monthlyReferrals = Referral::whereIn('referrer_id', $referredMarketerIds)
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
            $referrals[] = $monthlyReferrals;
            
            // Sum commissions for this month
            $monthlyCommissions = $superMarketer->commissionPayments()
                ->where('commission_tier', 'super_marketer')
                ->where('payment_status', 'completed')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('total_amount');
            $commissions[] = (float)$monthlyCommissions;
        }
        
        return [
            'months' => $months,
            'marketers' => $marketers,
            'referrals' => $referrals,
            'commissions' => $commissions
        ];
    }

    /**
     * Get top performing marketers
     */
    private function getTopPerformingMarketers($superMarketer): Collection
    {
        return $superMarketer->referredMarketers()
            ->with(['referrals', 'referralRewards'])
            ->get()
            ->map(function ($marketer) {
                $totalCommission = $marketer->referralRewards()
                    ->where('status', 'paid')
                    ->sum('amount');
                
                $marketer->total_commission = $totalCommission;
                $marketer->total_referrals = $marketer->referrals()->count();
                
                return $marketer;
            })
            ->sortByDesc('total_commission')
            ->take(5);
    }

    /**
     * Calculate conversion rate for Super Marketer
     */
    private function calculateConversionRate($superMarketer): float
    {
        $totalMarketersReferred = $superMarketer->referredMarketers()->count();
        $activeMarketers = $superMarketer->referredMarketers()
            ->where('marketer_status', 'active')
            ->count();
        
        return $totalMarketersReferred > 0 
            ? round(($activeMarketers / $totalMarketersReferred) * 100, 2) 
            : 0;
    }

    /**
     * Generate unique referral code
     */
    private function generateUniqueReferralCode($superMarketer): string
    {
        do {
            $code = 'SM-' . strtoupper(substr(md5(time() . $superMarketer->user_id), 0, 8));
        } while (User::where('referral_code', $code)->exists());
        
        return $code;
    }

    /**
     * Generate QR Code for referral link
     */
    private function generateQRCode($referralLink): string
    {
        // This would integrate with a QR code generation service
        // For now, return a placeholder URL
        return url('/api/qr-code?data=' . urlencode($referralLink));
    }

    /**
     * Log referral link generation
     */
    private function logReferralLinkGeneration($superMarketer, $referralCode, $data): void
    {
        // Log the referral link generation for tracking
        \Log::info('Super Marketer referral link generated', [
            'super_marketer_id' => $superMarketer->user_id,
            'referral_code' => $referralCode,
            'campaign_name' => $data['campaign_name'] ?? null,
            'target_audience' => $data['target_audience'] ?? null
        ]);
    }

    /**
     * Get date range from request
     */
    private function getDateRange($request): array
    {
        $startDate = $request->start_date 
            ? Carbon::parse($request->start_date) 
            : Carbon::now()->subMonths(3);
        
        $endDate = $request->end_date 
            ? Carbon::parse($request->end_date) 
            : Carbon::now();
        
        return [$startDate, $endDate];
    }

    /**
     * Get commission breakdown by tier
     */
    private function getCommissionBreakdownByTier($superMarketer, $dateRange): array
    {
        [$startDate, $endDate] = $dateRange;
        
        return [
            'super_marketer' => $superMarketer->commissionPayments()
                ->where('commission_tier', 'super_marketer')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('total_amount'),
            'marketer' => CommissionPayment::whereIn('marketer_id', 
                    $superMarketer->referredMarketers()->pluck('user_id')
                )
                ->where('commission_tier', 'marketer')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('total_amount')
        ];
    }

    /**
     * Get commission trends over time
     */
    private function getCommissionTrends($superMarketer, $dateRange): array
    {
        [$startDate, $endDate] = $dateRange;
        
        $trends = [];
        $current = $startDate->copy();
        
        while ($current <= $endDate) {
            $monthCommission = $superMarketer->commissionPayments()
                ->where('commission_tier', 'super_marketer')
                ->whereMonth('created_at', $current->month)
                ->whereYear('created_at', $current->year)
                ->sum('total_amount');
            
            $trends[] = [
                'month' => $current->format('M Y'),
                'amount' => (float)$monthCommission
            ];
            
            $current->addMonth();
        }
        
        return $trends;
    }

    /**
     * Get regional commission breakdown
     */
    private function getRegionalCommissionBreakdown($superMarketer, $dateRange = null): array
    {
        $query = $superMarketer->commissionPayments()
            ->where('commission_tier', 'super_marketer')
            ->select('region', DB::raw('SUM(amount) as total'))
            ->groupBy('region');
        
        if ($dateRange) {
            [$startDate, $endDate] = $dateRange;
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        return $query->get()->toArray();
    }

    /**
     * Get commission comparison with previous period
     */
    private function getCommissionComparison($superMarketer, $dateRange): array
    {
        [$startDate, $endDate] = $dateRange;
        $daysDiff = $startDate->diffInDays($endDate);
        
        $currentPeriod = $superMarketer->commissionPayments()
            ->where('commission_tier', 'super_marketer')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');
        
        $previousStart = $startDate->copy()->subDays($daysDiff);
        $previousEnd = $startDate->copy()->subDay();
        
        $previousPeriod = $superMarketer->commissionPayments()
            ->where('commission_tier', 'super_marketer')
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->sum('total_amount');
        
        $growth = $previousPeriod > 0 
            ? (($currentPeriod - $previousPeriod) / $previousPeriod) * 100 
            : 0;
        
        return [
            'current_period' => (float)$currentPeriod,
            'previous_period' => (float)$previousPeriod,
            'growth_percentage' => round($growth, 2)
        ];
    }

    /**
     * Get referred marketers summary
     */
    private function getReferredMarketersSummary($superMarketer): array
    {
        $total = $superMarketer->referredMarketers()->count();
        $active = $superMarketer->referredMarketers()->where('marketer_status', 'active')->count();
        $pending = $superMarketer->referredMarketers()->where('marketer_status', 'pending')->count();
        $inactive = $superMarketer->referredMarketers()->where('marketer_status', 'inactive')->count();
        
        return [
            'total' => $total,
            'active' => $active,
            'pending' => $pending,
            'inactive' => $inactive,
            'active_percentage' => $total > 0 ? round(($active / $total) * 100, 2) : 0
        ];
    }

    /**
     * Get detailed marketer performance
     */
    private function getDetailedMarketerPerformance($marketer): array
    {
        $metrics = $this->getMarketerPerformanceMetrics($marketer);
        
        // Add additional detailed metrics
        $metrics['properties_referred'] = Property::whereHas('user', function($q) use ($marketer) {
            $q->whereHas('referredUsers', function($q2) use ($marketer) {
                $q2->where('referrer_id', $marketer->user_id);
            });
        })->count();
        
        $metrics['average_monthly_referrals'] = $this->getAverageMonthlyReferrals($marketer);
        $metrics['best_month'] = $this->getBestPerformingMonth($marketer);
        
        return $metrics;
    }

    /**
     * Get marketer commission history
     */
    private function getMarketerCommissionHistory($marketer): Collection
    {
        return $marketer->commissionPayments()
            ->latest()
            ->limit(20)
            ->get();
    }

    /**
     * Get marketer referral chains
     */
    private function getMarketerReferralChains($marketer): Collection
    {
        return ReferralChain::where('marketer_id', $marketer->user_id)
            ->with(['landlord', 'superMarketer'])
            ->latest()
            ->limit(10)
            ->get();
    }

    /**
     * Get average monthly referrals for marketer
     */
    private function getAverageMonthlyReferrals($marketer): float
    {
        $monthsActive = max(1, $marketer->created_at->diffInMonths(now()));
        $totalReferrals = $marketer->referrals()->count();
        
        return round($totalReferrals / $monthsActive, 2);
    }

    /**
     * Get best performing month for marketer
     */
    private function getBestPerformingMonth($marketer): array
    {
        $bestMonth = $marketer->referrals()
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as referral_count')
            )
            ->groupBy('year', 'month')
            ->orderBy('referral_count', 'desc')
            ->first();
        
        if (!$bestMonth) {
            return ['month' => 'N/A', 'count' => 0];
        }
        
        return [
            'month' => Carbon::create($bestMonth->year, $bestMonth->month)->format('M Y'),
            'count' => $bestMonth->referral_count
        ];
    }
}