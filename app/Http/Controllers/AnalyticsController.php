<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Property;
use App\Models\Apartment;
use App\Models\Payment;
use App\Models\Referral;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Advanced Analytics Dashboard
     */
    public function index()
    {
        $analytics = [
            'overview' => $this->getOverviewMetrics(),
            'revenue' => $this->getRevenueAnalytics(),
            'users' => $this->getUserAnalytics(),
            'properties' => $this->getPropertyAnalytics(),
            'geographic' => $this->getGeographicAnalytics(),
            'trends' => $this->getTrendAnalytics(),
            'predictions' => $this->getPredictiveAnalytics()
        ];

        return view('admin.analytics.index', compact('analytics'));
    }

    /**
     * Get overview metrics
     */
    private function getOverviewMetrics()
    {
        $currentMonth = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        return [
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            'monthly_revenue' => Payment::where('status', 'completed')
                ->whereMonth('created_at', $currentMonth->month)
                ->whereYear('created_at', $currentMonth->year)
                ->sum('amount'),
            'revenue_growth' => $this->calculateGrowthRate(
                Payment::where('status', 'completed')
                    ->whereMonth('created_at', $lastMonth->month)
                    ->whereYear('created_at', $lastMonth->year)
                    ->sum('amount'),
                Payment::where('status', 'completed')
                    ->whereMonth('created_at', $currentMonth->month)
                    ->whereYear('created_at', $currentMonth->year)
                    ->sum('amount')
            ),
            'total_users' => User::count(),
            'active_users' => User::where('updated_at', '>=', Carbon::now()->subDays(30))->count(),
            'user_growth' => $this->calculateGrowthRate(
                User::whereMonth('created_at', $lastMonth->month)
                    ->whereYear('created_at', $lastMonth->year)
                    ->count(),
                User::whereMonth('created_at', $currentMonth->month)
                    ->whereYear('created_at', $currentMonth->year)
                    ->count()
            ),
            'total_properties' => Property::count(),
            'occupied_rate' => $this->calculateOccupancyRate(),
            'avg_property_value' => Apartment::avg('amount') ?? 0
        ];
    }

    /**
     * Get revenue analytics
     */
    private function getRevenueAnalytics()
    {
        // Monthly revenue for the last 12 months
        $monthlyRevenue = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthlyRevenue[] = [
                'month' => $date->format('M Y'),
                'revenue' => Payment::where('status', 'completed')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('amount'),
                'transactions' => Payment::where('status', 'completed')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count()
            ];
        }

        // Revenue by payment method
        $revenueByMethod = Payment::where('status', 'completed')
            ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        // Top performing landlords
        $topLandlords = Payment::where('status', 'completed')
            ->select('landlord_id', DB::raw('SUM(amount) as total_revenue'), DB::raw('COUNT(*) as transactions'))
            ->groupBy('landlord_id')
            ->orderBy('total_revenue', 'desc')
            ->limit(10)
            ->with('landlord:user_id,first_name,last_name,email')
            ->get();

        return [
            'monthly_revenue' => $monthlyRevenue,
            'revenue_by_method' => $revenueByMethod,
            'top_landlords' => $topLandlords,
            'avg_transaction_value' => Payment::where('status', 'completed')->avg('amount') ?? 0,
            'total_transactions' => Payment::where('status', 'completed')->count(),
            'failed_transactions' => Payment::where('status', 'failed')->count(),
            'pending_transactions' => Payment::where('status', 'pending')->count()
        ];
    }

    /**
     * Get user analytics
     */
    private function getUserAnalytics()
    {
        // User registration trends
        $userRegistrations = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $userRegistrations[] = [
                'month' => $date->format('M Y'),
                'registrations' => User::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count()
            ];
        }

        // User distribution by role
        $usersByRole = [
            'landlords' => User::where('role', 2)->count(),
            'tenants' => User::where('role', 3)->count(),
            'agents' => User::where('role', 4)->count(),
            'marketers' => User::where('role', 5)->count(),
            'regional_managers' => User::where('role', 6)->count(),
            'admins' => User::where('admin', 1)->count()
        ];

        // User activity metrics
        $activityMetrics = [
            'daily_active' => User::where('updated_at', '>=', Carbon::now()->subDay())->count(),
            'weekly_active' => User::where('updated_at', '>=', Carbon::now()->subWeek())->count(),
            'monthly_active' => User::where('updated_at', '>=', Carbon::now()->subMonth())->count(),
            'retention_rate' => $this->calculateRetentionRate()
        ];

        return [
            'registrations' => $userRegistrations,
            'distribution' => $usersByRole,
            'activity' => $activityMetrics,
            'total_users' => User::count(),
            'verified_users' => User::whereNotNull('email_verified_at')->count()
        ];
    }

    /**
     * Get property analytics
     */
    private function getPropertyAnalytics()
    {
        // Properties by type
        $propertiesByType = Property::select('prop_type', DB::raw('COUNT(*) as count'))
            ->groupBy('prop_type')
            ->get()
            ->mapWithKeys(function ($item) {
                $types = [1 => 'Mansion', 2 => 'Duplex', 3 => 'Flat', 4 => 'Terrace'];
                return [$types[$item->prop_type] ?? 'Unknown' => $item->count];
            });

        // Properties by state (top 10)
        $propertiesByState = Property::select('state', DB::raw('COUNT(*) as count'))
            ->groupBy('state')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // Occupancy trends
        $occupancyTrends = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $totalApartments = Apartment::whereMonth('created_at', '<=', $date->month)
                ->whereYear('created_at', '<=', $date->year)
                ->count();
            $occupiedApartments = Apartment::where('occupied', true)
                ->whereMonth('created_at', '<=', $date->month)
                ->whereYear('created_at', '<=', $date->year)
                ->count();
            
            $occupancyTrends[] = [
                'month' => $date->format('M Y'),
                'occupancy_rate' => $totalApartments > 0 ? round(($occupiedApartments / $totalApartments) * 100, 2) : 0
            ];
        }

        return [
            'by_type' => $propertiesByType,
            'by_state' => $propertiesByState,
            'occupancy_trends' => $occupancyTrends,
            'total_properties' => Property::count(),
            'total_apartments' => Apartment::count(),
            'occupied_apartments' => Apartment::where('occupied', true)->count(),
            'avg_apartments_per_property' => Property::avg('no_of_apartment') ?? 0
        ];
    }

    /**
     * Get geographic analytics
     */
    private function getGeographicAnalytics()
    {
        // Revenue by state
        $revenueByState = DB::table('payments')
            ->join('apartments', 'payments.apartment_id', '=', 'apartments.apartment_id')
            ->join('properties', 'apartments.property_id', '=', 'properties.prop_id')
            ->where('payments.status', 'completed')
            ->select('properties.state', DB::raw('SUM(payments.amount) as total_revenue'))
            ->groupBy('properties.state')
            ->orderBy('total_revenue', 'desc')
            ->get();

        // User distribution by state
        $usersByState = User::select('state', DB::raw('COUNT(*) as count'))
            ->whereNotNull('state')
            ->groupBy('state')
            ->orderBy('count', 'desc')
            ->limit(15)
            ->get();

        return [
            'revenue_by_state' => $revenueByState,
            'users_by_state' => $usersByState,
            'top_performing_states' => $revenueByState->take(5)
        ];
    }

    /**
     * Get trend analytics
     */
    private function getTrendAnalytics()
    {
        return [
            'seasonal_trends' => $this->getSeasonalTrends(),
            'growth_patterns' => $this->getGrowthPatterns(),
            'market_indicators' => $this->getMarketIndicators()
        ];
    }

    /**
     * Get predictive analytics
     */
    private function getPredictiveAnalytics()
    {
        return [
            'revenue_forecast' => $this->forecastRevenue(),
            'user_growth_forecast' => $this->forecastUserGrowth(),
            'market_opportunities' => $this->identifyMarketOpportunities()
        ];
    }

    // Helper methods
    private function calculateGrowthRate($previous, $current)
    {
        if ($previous == 0) return $current > 0 ? 100 : 0;
        return round((($current - $previous) / $previous) * 100, 2);
    }

    private function calculateOccupancyRate()
    {
        $total = Apartment::count();
        $occupied = Apartment::where('occupied', true)->count();
        return $total > 0 ? round(($occupied / $total) * 100, 2) : 0;
    }

    private function calculateRetentionRate()
    {
        $totalUsers = User::count();
        $activeUsers = User::where('updated_at', '>=', Carbon::now()->subMonth())->count();
        return $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 2) : 0;
    }

    private function getSeasonalTrends()
    {
        // Analyze seasonal patterns in revenue and user activity
        $quarters = [];
        for ($i = 3; $i >= 0; $i--) {
            $startDate = Carbon::now()->subQuarters($i)->startOfQuarter();
            $endDate = Carbon::now()->subQuarters($i)->endOfQuarter();
            
            $quarters[] = [
                'quarter' => 'Q' . $startDate->quarter . ' ' . $startDate->year,
                'revenue' => Payment::where('status', 'completed')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('amount'),
                'new_users' => User::whereBetween('created_at', [$startDate, $endDate])->count(),
                'new_properties' => Property::whereBetween('created_at', [$startDate, $endDate])->count()
            ];
        }
        
        return $quarters;
    }

    private function getGrowthPatterns()
    {
        // Calculate various growth metrics
        return [
            'user_growth_rate' => $this->calculateMonthlyGrowthRate('users'),
            'revenue_growth_rate' => $this->calculateMonthlyGrowthRate('revenue'),
            'property_growth_rate' => $this->calculateMonthlyGrowthRate('properties')
        ];
    }

    private function calculateMonthlyGrowthRate($type)
    {
        $currentMonth = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();
        
        switch ($type) {
            case 'users':
                $current = User::whereMonth('created_at', $currentMonth->month)->count();
                $previous = User::whereMonth('created_at', $lastMonth->month)->count();
                break;
            case 'revenue':
                $current = Payment::where('status', 'completed')
                    ->whereMonth('created_at', $currentMonth->month)->sum('amount');
                $previous = Payment::where('status', 'completed')
                    ->whereMonth('created_at', $lastMonth->month)->sum('amount');
                break;
            case 'properties':
                $current = Property::whereMonth('created_at', $currentMonth->month)->count();
                $previous = Property::whereMonth('created_at', $lastMonth->month)->count();
                break;
            default:
                return 0;
        }
        
        return $this->calculateGrowthRate($previous, $current);
    }

    private function getMarketIndicators()
    {
        return [
            'market_penetration' => $this->calculateMarketPenetration(),
            'customer_satisfaction' => $this->calculateCustomerSatisfaction(),
            'platform_health' => $this->calculatePlatformHealth()
        ];
    }

    private function calculateMarketPenetration()
    {
        // Simplified market penetration calculation
        $totalStates = Property::distinct('state')->count();
        $totalLGAs = Property::distinct('lga')->count();
        
        return [
            'states_covered' => $totalStates,
            'lgas_covered' => $totalLGAs,
            'market_coverage' => min(100, ($totalStates / 36) * 100) // Nigeria has 36 states
        ];
    }

    private function calculateCustomerSatisfaction()
    {
        // Based on successful transactions and user retention
        $successfulTransactions = Payment::where('status', 'completed')->count();
        $totalTransactions = Payment::count();
        
        return [
            'transaction_success_rate' => $totalTransactions > 0 ? 
                round(($successfulTransactions / $totalTransactions) * 100, 2) : 0,
            'user_retention_rate' => $this->calculateRetentionRate()
        ];
    }

    private function calculatePlatformHealth()
    {
        return [
            'active_listings' => Property::count(),
            'successful_matches' => Apartment::where('occupied', true)->count(),
            'revenue_stability' => $this->calculateRevenueStability()
        ];
    }

    private function calculateRevenueStability()
    {
        // Calculate coefficient of variation for monthly revenue
        $monthlyRevenues = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthlyRevenues[] = Payment::where('status', 'completed')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('amount');
        }
        
        $mean = array_sum($monthlyRevenues) / count($monthlyRevenues);
        if ($mean == 0) return 100; // Perfect stability if no revenue
        
        $variance = array_sum(array_map(function($x) use ($mean) { 
            return pow($x - $mean, 2); 
        }, $monthlyRevenues)) / count($monthlyRevenues);
        
        $stdDev = sqrt($variance);
        $coefficientOfVariation = ($stdDev / $mean) * 100;
        
        return max(0, 100 - $coefficientOfVariation); // Higher is more stable
    }

    private function forecastRevenue()
    {
        // Simple linear regression forecast for next 6 months
        $monthlyData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthlyData[] = Payment::where('status', 'completed')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('amount');
        }
        
        // Calculate trend
        $n = count($monthlyData);
        $sumX = array_sum(range(1, $n));
        $sumY = array_sum($monthlyData);
        $sumXY = 0;
        $sumX2 = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $sumXY += ($i + 1) * $monthlyData[$i];
            $sumX2 += pow($i + 1, 2);
        }
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - pow($sumX, 2));
        $intercept = ($sumY - $slope * $sumX) / $n;
        
        // Forecast next 6 months
        $forecast = [];
        for ($i = 1; $i <= 6; $i++) {
            $forecast[] = [
                'month' => Carbon::now()->addMonths($i)->format('M Y'),
                'predicted_revenue' => max(0, $intercept + $slope * ($n + $i))
            ];
        }
        
        return $forecast;
    }

    private function forecastUserGrowth()
    {
        // Similar forecasting for user growth
        $monthlyUsers = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthlyUsers[] = User::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
        }
        
        // Simple moving average for user growth forecast
        $recentGrowth = array_slice($monthlyUsers, -3); // Last 3 months
        $avgGrowth = array_sum($recentGrowth) / count($recentGrowth);
        
        $forecast = [];
        for ($i = 1; $i <= 6; $i++) {
            $forecast[] = [
                'month' => Carbon::now()->addMonths($i)->format('M Y'),
                'predicted_users' => max(0, round($avgGrowth * (1 + 0.05 * $i))) // 5% growth factor
            ];
        }
        
        return $forecast;
    }

    private function identifyMarketOpportunities()
    {
        // Identify underserved markets and growth opportunities
        $statePerformance = DB::table('properties')
            ->select('state', DB::raw('COUNT(*) as property_count'))
            ->groupBy('state')
            ->orderBy('property_count', 'asc')
            ->limit(5)
            ->get();
        
        return [
            'underserved_states' => $statePerformance,
            'growth_potential' => $this->calculateGrowthPotential(),
            'market_gaps' => $this->identifyMarketGaps()
        ];
    }

    private function calculateGrowthPotential()
    {
        $totalUsers = User::count();
        $activeUsers = User::where('updated_at', '>=', Carbon::now()->subMonth())->count();
        $conversionRate = $totalUsers > 0 ? ($activeUsers / $totalUsers) * 100 : 0;
        
        return [
            'user_activation_potential' => max(0, 80 - $conversionRate), // Target 80% activation
            'market_expansion_score' => min(100, Property::distinct('state')->count() * 2.78) // 36 states max
        ];
    }

    private function identifyMarketGaps()
    {
        return [
            'low_supply_areas' => Property::select('state', DB::raw('COUNT(*) as count'))
                ->groupBy('state')
                ->having('count', '<', 5)
                ->get(),
            'high_demand_indicators' => User::select('state', DB::raw('COUNT(*) as user_count'))
                ->whereNotNull('state')
                ->groupBy('state')
                ->orderBy('user_count', 'desc')
                ->limit(10)
                ->get()
        ];
    }
}