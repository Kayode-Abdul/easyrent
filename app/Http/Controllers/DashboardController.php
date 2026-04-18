<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Property;
use App\Models\Apartment;
use App\Models\Payment;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the dashboard view for authenticated users.
     */
    public function index()
    {
        // Double-check authentication
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access the dashboard.');
        }

        $user = Auth::user();

        // Ensure user object exists and has required properties
        if (!$user || !isset($user->user_id)) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Invalid user session. Please login again.');
        }

        $userId = $user->user_id;
        $isAdmin = ($user->admin == 1 || $user->role == 7);
        $userRole = $user->role;

        // Mode-based redirects/views
        // 1. Admin Mode wins if active
        // Activity Pagination Setup
        $activityPage = request()->get('page', 1);

        // 1. Admin Mode wins if active
        if ($isAdmin && session('admin_dashboard_mode') === 'admin') {
            $stats = $this->getAdminStats();
            $chartData = $this->getAdminChartData();
            $recentActivities = $this->getAdminActivities($activityPage);
            return view('admin-dashboard', compact('stats', 'chartData', 'recentActivities', 'user'));
        }

        // 2. Artisan Mode wins if active and user is an artisan
        if ($user->isArtisan() && session('dashboard_mode') === 'artisan') {
            return redirect()->route('artisan.dashboard');
        }

        // 3. Marketer Mode wins if active
        if ($user->isMarketer() && session('dashboard_mode') === 'marketer') {
            return redirect()->route('marketer.dashboard');
        }

        // 4. Property Manager Mode wins if active


        // Fallback: Personal Dashboard (Landlord/Tenant view)
        $stats = [];
        $chartData = [];
        $recentActivities = [];
        $greeting = $this->getGreeting();

        if ($user->isLandlord()) {
            $stats = $this->getLandlordStats($userId);
            $chartData = $this->getLandlordChartData($userId);
            $recentActivities = $this->getLandlordActivities($userId, $activityPage);
        }
        else {
            $stats = $this->getTenantStats($userId);
            $chartData = $this->getTenantChartData($userId);
            $recentActivities = $this->getTenantActivities($userId, $activityPage);
        }

        // Referral stats for the widget
        $referralData = $this->getReferralDashboardData($user);
        $hasReferrals = $referralData['has_referrals'];

        return view('dash', compact('stats', 'chartData', 'recentActivities', 'greeting', 'hasReferrals', 'referralData'));
    }

    /**
     * Get basic referral data for the dashboard widget
     */
    private function getReferralDashboardData($user)
    {
        return Cache::remember('user_referral_data_' . $user->user_id, now()->addMinutes(10), function () use ($user) {
            $referralsCount = $user->referrals()->count();
            
            return [
                'has_referrals' => $referralsCount > 0,
                'total_referrals' => $referralsCount,
                'pending_commissions' => $user->referralRewards()->where('status', 'approved')->sum('amount'),
                'total_earned' => $user->referralRewards()->where('status', 'paid')->sum('amount'),
                'referral_link' => $user->getReferralLink(),
                'referral_code' => $user->referral_code
            ];
        });
    }

    private function getGreeting()
    {
        $hour = Carbon::now()->hour;
        $timeGreeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
        return $timeGreeting . '! Here\'s your dashboard overview.';
    }

    private function getAdminStats()
    {
        return Cache::remember('admin_stats', now()->addMinutes(10), function () {
            return [
                // Basic Stats
                'total_users' => User::count(),
                'total_properties' => Property::count(),
                'total_revenue_by_currency' => Payment::whereIn('status', ['completed', 'success'])
                    ->select('currency_id', DB::raw('SUM(amount) as total'))
                    ->groupBy('currency_id')
                    ->with('currency')
                    ->get()
                    ->mapWithKeys(fn($item) => [$item->currency->code ?? 'NGN' => ['amount' => $item->total, 'symbol' => $item->currency->symbol ?? '₦']])
                    ->toArray(),
                'pending_payments' => Payment::where('status', 'pending')->count(),

                // User Management Stats
                'active_users' => User::count(), // Assuming all users are active for now
                'new_users_today' => User::whereDate('created_at', Carbon::today())->count(),
                'new_users_this_week' => User::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count(),
                'inactive_users' => 0, // No status column found
                'users_by_type' => [
                    'landlords' => User::where('role', 2)->count(),
                    'tenants' => User::where('role', 3)->count(),
                    'admins' => User::where('admin', 1)->count(),
                    'agents' => User::where('role', 4)->count(),
                ],

                // Property Management Stats
                'occupied_apartments' => Apartment::where('occupied', true)->count(),
                'vacant_apartments' => Apartment::where('occupied', false)->count(),
                'properties_added_this_month' => Property::whereMonth('created_at', Carbon::now()->month)->count(),
                'total_apartments' => Apartment::count(),

                // Financial Overview
                'revenue_today_by_currency' => Payment::where('status', 'completed')
                    ->whereDate('created_at', Carbon::today())
                    ->select('currency_id', DB::raw('SUM(amount) as total'))
                    ->groupBy('currency_id')
                    ->with('currency')
                    ->get()
                    ->mapWithKeys(fn($item) => [$item->currency->code ?? 'NGN' => ['amount' => $item->total, 'symbol' => $item->currency->symbol ?? '₦']])
                    ->toArray(),
                'revenue_this_month_by_currency' => Payment::where('status', 'completed')
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->select('currency_id', DB::raw('SUM(amount) as total'))
                    ->groupBy('currency_id')
                    ->with('currency')
                    ->get()
                    ->mapWithKeys(fn($item) => [$item->currency->code ?? 'NGN' => ['amount' => $item->total, 'symbol' => $item->currency->symbol ?? '₦']])
                    ->toArray(),
                'revenue_last_month_by_currency' => Payment::where('status', 'completed')
                    ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                    ->select('currency_id', DB::raw('SUM(amount) as total'))
                    ->groupBy('currency_id')
                    ->with('currency')
                    ->get()
                    ->mapWithKeys(fn($item) => [$item->currency->code ?? 'NGN' => ['amount' => $item->total, 'symbol' => $item->currency->symbol ?? '₦']])
                    ->toArray(),
                'failed_payments' => Payment::where('status', 'failed')->count(),
                'average_transaction_value' => Payment::where('status', 'completed')->avg('amount'),

                // Company Commission Revenue
                'company_commission_today' => $this->getCompanyCommissionRevenue('today'),
                'company_commission_this_month' => $this->getCompanyCommissionRevenue('this_month'),
                'company_commission_last_month' => $this->getCompanyCommissionRevenue('last_month'),
                'company_commission_total' => $this->getCompanyCommissionRevenue('total'),
                'commission_breakdown' => $this->getCommissionBreakdown(),

                // System Health
                'platform_uptime' => '99.9%', // This would be calculated from monitoring system
                'database_size' => $this->getDatabaseSize(),
                'storage_used' => $this->getStorageUsage(),
                'active_sessions' => $this->getActiveSessions(),

                // Business Intelligence
                'conversion_rate' => $this->calculateConversionRate(),
                'churn_rate' => $this->calculateChurnRate(),
                'customer_acquisition_cost' => $this->calculateCAC(),
                'lifetime_value' => $this->calculateLTV(),
            ];
        });
    }

    private function getLandlordStats($userId)
    {
        return Cache::remember('landlord_stats_' . $userId, now()->addMinutes(10), function () use ($userId) {
            $properties = Property::where('user_id', $userId)->pluck('property_id');
            $apartments = Apartment::whereIn('property_id', $properties);

            return [
                'my_properties' => $properties->count(),
                'occupied_apartments' => $apartments->where('occupied', true)->count(),
                'monthly_revenue_by_currency' => Payment::where('landlord_id', $userId)
                    ->where('status', 'completed')
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->select('currency_id', DB::raw('SUM(amount) as total'))
                    ->groupBy('currency_id')
                    ->with('currency')
                    ->get()
                    ->mapWithKeys(fn($item) => [$item->currency->code ?? 'NGN' => ['amount' => $item->total, 'symbol' => $item->currency->symbol ?? '₦']])
                    ->toArray(),
            ];
        });
    }

    private function getTenantStats($userId)
    {
        return Cache::remember('tenant_stats_' . $userId, now()->addMinutes(10), function () use ($userId) {
            return [
                'my_rentals' => Apartment::where('tenant_id', $userId)->where('occupied', true)->count(),
                'payments_this_month_by_currency' => Payment::where('tenant_id', $userId)
                    ->where('status', 'completed')
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->select('currency_id', DB::raw('SUM(amount) as total'))
                    ->groupBy('currency_id')
                    ->with('currency')
                    ->get()
                    ->mapWithKeys(fn($item) => [$item->currency->code ?? 'NGN' => ['amount' => $item->total, 'symbol' => $item->currency->symbol ?? '₦']])
                    ->toArray(),
                'my_pending_payments' => Payment::where('tenant_id', $userId)
                ->where('status', 'pending')
                ->count(),
                'unread_messages' => Message::where('receiver_id', $userId)
                ->where('is_read', false)
                ->count(),
            ];
        });
    }

    // Helper methods for advanced metrics
    private function getDatabaseSize()
    {
        try {
            $size = DB::select("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS 'db_size' FROM information_schema.tables WHERE table_schema=DATABASE()")[0]->db_size;
            return $size . ' MB';
        }
        catch (\Exception $e) {
            return 'Unknown';
        }
    }

    private function getStorageUsage()
    {
        // This would integrate with your file storage system
        // For now, return a placeholder
        return '2.3 GB';
    }

    private function getActiveSessions()
    {
        // Count active sessions from the last hour
        return User::where('updated_at', '>=', Carbon::now()->subHour())->count();
    }

    private function calculateConversionRate()
    {
        $totalSignups = User::count();
        $activeTenants = User::where('role', 3)->count(); // Role 3 = tenant

        return $totalSignups > 0 ? round(($activeTenants / $totalSignups) * 100, 2) . '%' : '0%';
    }

    private function calculateChurnRate()
    {
        $totalUsers = User::count();
        $inactiveUsers = 0; // No status column, so assume 0 for now

        return $totalUsers > 0 ? round(($inactiveUsers / $totalUsers) * 100, 2) . '%' : '0%';
    }

    private function calculateCAC()
    {
        // Customer Acquisition Cost - simplified calculation
        // This would integrate with marketing spend data
        $newUsersThisMonth = User::whereMonth('created_at', Carbon::now()->month)->count();
        $marketingSpend = 5000; // Placeholder - would come from marketing data

        return format_money($marketingSpend / ($newUsersThisMonth ?: 1));
    }

    private function calculateLTV()
    {
        // Lifetime Value - simplified calculation
        $avgMonthlyRevenue = Payment::where('status', 'completed')
            ->whereMonth('created_at', Carbon::now()->month)
            ->avg('amount') ?? 0;
        $avgLifespan = 12; // months - would be calculated from user data

        return format_money($avgMonthlyRevenue * $avgLifespan);
    }

    private function getAdminChartData()
    {
        return Cache::remember('admin_chart_data', now()->addMinutes(10), function () {
            // Optimized revenue trend
            $startDate = Carbon::now()->subMonths(11)->startOfMonth();
            $monthlyRevenues = Payment::select(
                DB::raw("DATE_FORMAT(created_at, '%b %Y') as month"),
                DB::raw('SUM(amount) as total')
            )
                ->where('status', 'completed')
                ->where('created_at', '>=', $startDate)
                ->groupBy(DB::raw("DATE_FORMAT(created_at, '%b %Y')"))
                ->orderBy(DB::raw("DATE_FORMAT(created_at, '%b %Y')"))
                ->pluck('total', 'month')
                ->toArray();

            $labels = [];
            $data = [];
            $currentMonth = $startDate->copy();
            for ($i = 0; $i < 12; $i++) {
                $monthKey = $currentMonth->format('M Y');
                $labels[] = $monthKey;
                $data[] = $monthlyRevenues[$monthKey] ?? 0;
                $currentMonth->addMonth();
            }

            $revenueTrend = [
                'labels' => $labels,
                'data' => $data,
            ];

            // Optimized user growth (cumulative)
            $baseDate = Carbon::now()->subMonths(12)->endOfMonth();
            $baseCount = User::where('created_at', '<=', $baseDate)->count();

            $monthlyUsers = User::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as count')
            )
                ->where('created_at', '>', $baseDate)
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('count', 'month')
                ->toArray();

            $cumulative = $baseCount;
            $userGrowthLabels = [];
            $userGrowthData = [];
            $currentMonth = $baseDate->addDay()->startOfMonth(); // Start from next month
            for ($i = 0; $i < 12; $i++) {
                $monthKey = $currentMonth->format('Y-m');
                $userGrowthLabels[] = $currentMonth->format('M Y');
                $cumulative += $monthlyUsers[$monthKey] ?? 0;
                $userGrowthData[] = $cumulative;
                $currentMonth->addMonth();
            }

            $userGrowth = [
                'labels' => $userGrowthLabels,
                'data' => $userGrowthData,
            ];

            // Other charts remain the same but will benefit from caching
            $paymentStatus = [
                'labels' => ['Completed', 'Pending', 'Failed'],
                'data' => [
                    Payment::where('status', 'completed')->count(),
                    Payment::where('status', 'pending')->count(),
                    Payment::where('status', 'failed')->count(),
                ],
            ];

            $propertyTypes = [
                'labels' => Property::select('prop_type')->distinct()->pluck('prop_type'),
                'data' => Property::select('prop_type', DB::raw('count(*) as count'))
                ->groupBy('prop_type')
                ->pluck('count'),
            ];

            $geographicData = [
                'labels' => Property::select('state')->distinct()->limit(10)->pluck('state'),
                'data' => Property::select('state', DB::raw('count(*) as count'))
                ->groupBy('state')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->pluck('count'),
            ];

            return [
                'revenue_trend' => $revenueTrend,
                'user_growth' => $userGrowth,
                'payment_status' => $paymentStatus,
                'property_types' => $propertyTypes,
                'geographic_data' => $geographicData,
            ];
        });
    }

    private function getLandlordChartData($userId)
    {
        return Cache::remember('landlord_chart_data_' . $userId, now()->addMinutes(10), function () use ($userId) {
            // Optimized revenue trend (last 6 months)
            $startDate = Carbon::now()->subMonths(5)->startOfMonth();
            $monthlyRevenues = Payment::select(
                DB::raw("DATE_FORMAT(created_at, '%b %Y') as month"),
                DB::raw('SUM(amount) as total')
            )
                ->where('landlord_id', $userId)
                ->where('status', 'completed')
                ->where('created_at', '>=', $startDate)
                ->groupBy(DB::raw("DATE_FORMAT(created_at, '%b %Y')"))
                ->orderBy(DB::raw("DATE_FORMAT(created_at, '%b %Y')"))
                ->pluck('total', 'month')
                ->toArray();

            $labels = [];
            $data = [];
            $currentMonth = $startDate->copy();
            for ($i = 0; $i < 6; $i++) {
                $monthKey = $currentMonth->format('M Y');
                $labels[] = $monthKey;
                $data[] = $monthlyRevenues[$monthKey] ?? 0;
                $currentMonth->addMonth();
            }

            $revenueTrend = [
                'labels' => $labels,
                'data' => $data,
            ];

            $properties = Property::where('user_id', $userId)->pluck('property_id');
            $apartments = Apartment::whereIn('property_id', $properties);

            $propertyDistribution = [
                $apartments->where('occupied', true)->count(),
                $apartments->where('occupied', false)->count(),
                0, // maintenance (placeholder)
            ];

            $distribution = [
                'labels' => ['Occupied', 'Vacant', 'Maintenance'],
                'data' => $propertyDistribution,
            ];

            return [
                'revenue_trend' => $revenueTrend,
                'property_distribution' => $distribution,
            ];
        });
    }

    private function getTenantChartData($userId)
    {
        return Cache::remember('tenant_chart_data_' . $userId, now()->addMinutes(10), function () use ($userId) {
            // Optimized payment trend (last 6 months)
            $startDate = Carbon::now()->subMonths(5)->startOfMonth();
            $monthlyPayments = Payment::select(
                DB::raw("DATE_FORMAT(created_at, '%b %Y') as month"),
                DB::raw('SUM(amount) as total')
            )
                ->where('tenant_id', $userId)
                ->where('status', 'completed')
                ->where('created_at', '>=', $startDate)
                ->groupBy(DB::raw("DATE_FORMAT(created_at, '%b %Y')"))
                ->orderBy(DB::raw("DATE_FORMAT(created_at, '%b %Y')"))
                ->pluck('total', 'month')
                ->toArray();

            $labels = [];
            $data = [];
            $currentMonth = $startDate->copy();
            for ($i = 0; $i < 6; $i++) {
                $monthKey = $currentMonth->format('M Y');
                $labels[] = $monthKey;
                $data[] = $monthlyPayments[$monthKey] ?? 0;
                $currentMonth->addMonth();
            }

            $paymentTrend = [
                'labels' => $labels,
                'data' => $data,
            ];

            $paymentDistribution = [
                Payment::where('tenant_id', $userId)->where('status', 'completed')->count(),
                Payment::where('tenant_id', $userId)->where('status', 'pending')->count(),
                Payment::where('tenant_id', $userId)->where('status', 'failed')->count(),
            ];

            $distribution = [
                'labels' => ['Completed', 'Pending', 'Failed'],
                'data' => $paymentDistribution,
            ];

            return [
                'payment_trend' => $paymentTrend,
                'payment_distribution' => $distribution,
            ];
        });
    }

    private function getAdminActivities($page = 1, $perPage = 10)
    {
        $activities = collect();

        // Recent user registrations
        User::latest()->take(50)->get()->each(function ($user) use ($activities) {
            $userType = $user->admin == 1 ? 'admin' : ($user->role == 2 ? 'landlord' : ($user->role == 3 ? 'tenant' : 'agent'));
            $activities->push([
                'type' => 'user_registration',
                'icon' => 'nc-icon nc-single-02',
                'color' => 'text-success',
                'title' => 'New User Registration',
                'description' => $user->first_name . ' ' . $user->last_name . ' joined as ' . $userType,
                'time' => $user->created_at,
                'time_for_humans' => $user->created_at->diffForHumans(),
                'link' => '/admin/users',
            ]);
        });

        // Recent property additions
        Property::latest()->take(50)->get()->each(function ($property) use ($activities) {
            $activities->push([
                'type' => 'property_added',
                'icon' => 'nc-icon nc-istanbul',
                'color' => 'text-info',
                'title' => 'New Property Added',
                'description' => 'Property at ' . $property->address . ', ' . $property->state,
                'time' => $property->created_at,
                'time_for_humans' => $property->created_at->diffForHumans(),
                'link' => '/properties/' . $property->property_id,
            ]);
        });

        // Recent payments
        Payment::where('status', 'completed')->with('currency')->latest()->take(50)->get()->each(function ($payment) use ($activities) {
            $currencySymbol = $payment->currency->symbol ?? '₦';
            $activities->push([
                'type' => 'payment_completed',
                'icon' => 'nc-icon nc-money-coins',
                'color' => 'text-success',
                'title' => 'Payment Completed',
                'description' => 'Payment of ' . $currencySymbol . number_format($payment->amount, 2) . ' received',
                'time' => $payment->created_at,
                'time_for_humans' => $payment->created_at->diffForHumans(),
                'link' => '/payments/' . $payment->id,
            ]);
        });

        $sortedActivities = $activities->sortByDesc('time')->values();
        
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $sortedActivities->forPage($page, $perPage),
            $sortedActivities->count(),
            $perPage,
            $page,
            ['path' => url()->current(), 'query' => request()->query()]
        );
    }

    private function getLandlordActivities($userId, $page = 1, $perPage = 5)
    {
        $activities = collect();

        // Recent payments for this landlord
        Payment::where('landlord_id', $userId)
            ->where('status', 'completed')
            ->latest()
            ->take(20)
            ->get()
            ->each(function ($payment) use ($activities) {
                $currencySymbol = $payment->currency->symbol ?? '₦';
                $activities->push([
                    'title' => 'Payment Received',
                    'description' => "Received {$currencySymbol}" . number_format($payment->amount, 2) . " for property rent",
                    'time' => $payment->created_at,
                    'time_for_humans' => $payment->created_at->diffForHumans(),
                    'link' => '/payments/' . $payment->id,
                ]);
            });

        $sortedActivities = $activities->sortByDesc('time')->values();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $sortedActivities->forPage($page, $perPage),
            $sortedActivities->count(),
            $perPage,
            $page,
            ['path' => url()->current(), 'query' => request()->query()]
        );
    }

    private function getTenantActivities($userId, $page = 1, $perPage = 5)
    {
        $activities = collect();

        // Recent payments by this tenant
        Payment::where('tenant_id', $userId)
            ->latest()
            ->take(20)
            ->get()
            ->each(function ($payment) use ($activities) {
                $currencySymbol = $payment->currency->symbol ?? '₦';
                $activities->push([
                    'title' => 'Payment Made',
                    'description' => "Made payment of {$currencySymbol}" . number_format($payment->amount, 2) . " - Status: {$payment->status}",
                    'time' => $payment->created_at,
                    'time_for_humans' => $payment->created_at->diffForHumans(),
                    'link' => '/payments/' . $payment->id,
                ]);
            });

        // Recent messages
        Message::where('receiver_id', $userId)
            ->latest()
            ->take(10)
            ->get()
            ->each(function ($message) use ($activities) {
                $activities->push([
                    'title' => 'New Message',
                    'description' => "Received message: " . substr($message->body ?? $message->message, 0, 50) . "...",
                    'time' => $message->created_at,
                    'time_for_humans' => $message->created_at->diffForHumans(),
                    'link' => '/messages',
                ]);
            });

        $sortedActivities = $activities->sortByDesc('time')->values();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $sortedActivities->forPage($page, $perPage),
            $sortedActivities->count(),
            $perPage,
            $page,
            ['path' => url()->current(), 'query' => request()->query()]
        );
    }

    /**
     * Admin dashboard data
     */
    private function getAdminDashboardData()
    {
        $totalUsers = User::count();
        $totalProperties = Property::count();
        $totalApartments = Apartment::count();
        $occupiedApartments = Apartment::whereNotNull('tenant_id')->count();

        $monthlyRevenue = Payment::where('status', 'completed')
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('amount');

        $pendingPayments = Payment::where('status', 'pending')->count();

        // User growth data (last 6 months)
        $userGrowth = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $userGrowth[] = [
                'month' => $month->format('M Y'),
                'count' => User::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count()
            ];
        }

        // Recent activities
        $recentUsers = User::latest()->take(5)->get();
        $recentProperties = Property::latest()->take(5)->get();

        return [
            'totalUsers' => $totalUsers,
            'totalProperties' => $totalProperties,
            'totalApartments' => $totalApartments,
            'occupiedApartments' => $occupiedApartments,
            'occupancyRate' => $totalApartments > 0 ? round(($occupiedApartments / $totalApartments) * 100, 1) : 0,
            'monthlyRevenue' => $monthlyRevenue,
            'pendingPayments' => $pendingPayments,
            'userGrowth' => $userGrowth,
            'recentUsers' => $recentUsers,
            'recentProperties' => $recentProperties,
        ];
    }

    /**
     * Landlord dashboard data
     */
    private function getLandlordDashboardData($user)
    {
        $properties = Property::where('user_id', $user->user_id)->get();
        $propertyIds = $properties->pluck('property_id');

        $totalProperties = $properties->count();
        $totalApartments = Apartment::whereIn('property_id', $propertyIds)->count();
        $occupiedApartments = Apartment::whereIn('property_id', $propertyIds)
            ->whereNotNull('tenant_id')->count();

        $monthlyRevenue = Payment::where('landlord_id', $user->user_id)
            ->where('status', 'completed')
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('amount');

        $pendingPayments = Payment::where('landlord_id', $user->user_id)
            ->where('status', 'pending')->count();

        // Revenue trend (last 6 months)
        $revenueTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $revenueTrend[] = [
                'month' => $month->format('M'),
                'revenue' => Payment::where('landlord_id', $user->user_id)
                ->where('status', 'completed')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount')
            ];
        }

        return [
            'totalProperties' => $totalProperties,
            'totalApartments' => $totalApartments,
            'occupiedApartments' => $occupiedApartments,
            'vacantApartments' => $totalApartments - $occupiedApartments,
            'occupancyRate' => $totalApartments > 0 ? round(($occupiedApartments / $totalApartments) * 100, 1) : 0,
            'monthlyRevenue' => $monthlyRevenue,
            'pendingPayments' => $pendingPayments,
            'revenueTrend' => $revenueTrend,
            'properties' => $properties->take(5), // Latest 5 properties for quick access
        ];
    }

    /**
     * Tenant dashboard data
     */
    private function getTenantDashboardData($user)
    {
        $rentedApartments = Apartment::where('tenant_id', $user->user_id)->get();
        $apartmentIds = $rentedApartments->pluck('apartment_id');

        $totalRentals = $rentedApartments->count();
        $totalPaid = Payment::where('tenant_id', $user->user_id)
            ->where('status', 'completed')
            ->sum('amount');

        $pendingPayments = Payment::where('tenant_id', $user->user_id)
            ->where('status', 'pending')->count();

        $nextPaymentDue = Payment::where('tenant_id', $user->user_id)
            ->where('status', 'pending')
            ->orderBy('due_date')
            ->first();

        // Payment history (last 6 months)
        $paymentHistory = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $paymentHistory[] = [
                'month' => $month->format('M'),
                'amount' => Payment::where('tenant_id', $user->user_id)
                ->where('status', 'completed')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount')
            ];
        }

        return [
            'totalRentals' => $totalRentals,
            'totalPaid' => $totalPaid,
            'pendingPayments' => $pendingPayments,
            'nextPaymentDue' => $nextPaymentDue,
            'paymentHistory' => $paymentHistory,
            'rentedApartments' => $rentedApartments,
        ];
    }

    /**
     * Default dashboard data for other roles
     */
    private function getDefaultDashboardData($user)
    {
        return [
            'totalProperties' => Property::count(),
            'totalApartments' => Apartment::count(),
            'availableApartments' => Apartment::whereNull('tenant_id')->count(),
        ];
    }

    /**
     * Switch dashboard mode for property managers
     */
    public function switchPropertyManagerMode(Request $request)
    {
        $user = Auth::user();

        // Verify user is a property manager
        if (!in_array($user->role, [6, 8])) {
            return response()->json(['success' => false, 'message' => 'Access denied']);
        }

        $mode = $request->input('mode', 'property_manager');

        // Validate mode
        if (!in_array($mode, ['property_manager', 'personal'])) {
            return response()->json(['success' => false, 'message' => 'Invalid mode']);
        }
        
        // When switching to property manager mode, ensure we exit admin mode
        if ($mode === 'property_manager') {
            session(['admin_dashboard_mode' => 'personal']);
        } else {
            // When switching FROM pm mode back to personal, ensure all specific modes are cleared
            session(['admin_dashboard_mode' => 'personal']);
        }
        
        // Store mode in session
        session(['dashboard_mode' => $mode]);

        return response()->json(['success' => true, 'mode' => $mode]);
    }

    /**
     * Switch dashboard mode for admin users
     */
    public function switchAdminMode(Request $request)
    {
        $user = Auth::user();

        // Verify user is an admin
        if (!($user->admin == 1 || $user->role == 7)) {
            return response()->json(['success' => false, 'message' => 'Access denied']);
        }

        $mode = $request->input('mode', 'admin');

        // Validate mode
        if (!in_array($mode, ['admin', 'personal'])) {
            return response()->json(['success' => false, 'message' => 'Invalid mode']);
        }

        // Store mode in session
        if ($mode === 'admin') {
            // When switching TO admin mode, ensure we exit other specific modes
            session(['dashboard_mode' => 'personal']);
        } else {
            // When switching FROM admin mode back to personal, ensure ALL specific modes are cleared
            session(['dashboard_mode' => 'personal']);
        }
        
        session(['admin_dashboard_mode' => $mode]);

        return response()->json(['success' => true, 'mode' => $mode]);
    }

    /**
     * Common dashboard data for all users
     */
    private function getCommonDashboardData($user)
    {
        $unreadMessages = Message::where('receiver_id', $user->user_id)
            ->where('is_read', false)->count();

        $recentMessages = Message::where('receiver_id', $user->user_id)
            ->latest()->take(5)->get();

        return [
            'unreadMessages' => $unreadMessages,
            'recentMessages' => $recentMessages,
        ];
    }

    /**
     * Calculate company commission revenue based on the new commission structure
     */
    private function getCompanyCommissionRevenue($period = 'total')
    {
        $query = Payment::where('status', 'completed')->with('currency');

        switch ($period) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'this_month':
                $query->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
                break;
            case 'last_month':
                $query->whereMonth('created_at', Carbon::now()->subMonth()->month)
                    ->whereYear('created_at', Carbon::now()->subMonth()->year);
                break;
            case 'total':
            default:
                break;
        }

        $payments = $query->get();
        $commissionsByCurrency = [];

        foreach ($payments as $payment) {
            $currencyCode = $payment->currency->code ?? 'NGN';
            $currencySymbol = $payment->currency->symbol ?? '₦';
            
            if (!isset($commissionsByCurrency[$currencyCode])) {
                $commissionsByCurrency[$currencyCode] = [
                    'amount' => 0,
                    'symbol' => $currencySymbol
                ];
            }

            $commissionAmount = $this->calculatePaymentCommission($payment);
            $commissionsByCurrency[$currencyCode]['amount'] += $commissionAmount['company_commission'];
        }

        return $commissionsByCurrency;
    }

    /**
     * Calculate commission for a specific payment
     */
    private function calculatePaymentCommission($payment)
    {
        // Default commission structure (you can enhance this based on actual property data)
        $rentAmount = $payment->amount;

        // Determine if property is managed (simplified - you can enhance this)
        $isManaged = $this->isPropertyManaged($payment);
        $hasSuperMarketer = $this->paymentHasSuperMarketer($payment);

        // Get commission rates based on scenario
        if ($isManaged) {
            if ($hasSuperMarketer) {
                // Managed with Super Marketer: 2.5% total
                return [
                    'super_marketer_commission' => $rentAmount * 0.0025, // 0.25%
                    'marketer_commission' => $rentAmount * 0.005, // 0.5%
                    'regional_manager_commission' => $rentAmount * 0.001, // 0.1%
                    'company_commission' => $rentAmount * 0.0165, // 1.65%
                    'total_commission' => $rentAmount * 0.025 // 2.5%
                ];
            }
            else {
                // Managed without Super Marketer: 2.5% total
                return [
                    'super_marketer_commission' => 0,
                    'marketer_commission' => $rentAmount * 0.0075, // 0.75%
                    'regional_manager_commission' => $rentAmount * 0.001, // 0.1%
                    'company_commission' => $rentAmount * 0.0165, // 1.65%
                    'total_commission' => $rentAmount * 0.025 // 2.5%
                ];
            }
        }
        else {
            if ($hasSuperMarketer) {
                // Unmanaged with Super Marketer: 5% total
                return [
                    'super_marketer_commission' => $rentAmount * 0.005, // 0.5%
                    'marketer_commission' => $rentAmount * 0.01, // 1.0%
                    'regional_manager_commission' => $rentAmount * 0.0025, // 0.25%
                    'company_commission' => $rentAmount * 0.0325, // 3.25%
                    'total_commission' => $rentAmount * 0.05 // 5%
                ];
            }
            else {
                // Unmanaged without Super Marketer: 5% total
                return [
                    'super_marketer_commission' => 0,
                    'marketer_commission' => $rentAmount * 0.015, // 1.5%
                    'regional_manager_commission' => $rentAmount * 0.0025, // 0.25%
                    'company_commission' => $rentAmount * 0.0325, // 3.25%
                    'total_commission' => $rentAmount * 0.05 // 5%
                ];
            }
        }
    }

    /**
     * Check if a property is managed (has project manager/agent)
     */
    private function isPropertyManaged($payment)
    {
        // Check if the related apartment has an assigned agent/manager
        $apartment = $payment->apartment;
        if ($apartment && $apartment->property && $apartment->property->agent_id) {
            return true;
        }
        return false;
    }

    /**
     * Check if payment involves a Super Marketer in the referral chain
     */
    private function paymentHasSuperMarketer($payment)
    {
        // Check if the landlord involved in this payment was referred via a chain involving a Super Marketer
        $landlord = $payment->landlord;
        if ($landlord && $landlord->referringSuperMarketer()) {
            return true;
        }
        return false;
    }

    /**
     * Get detailed commission breakdown for admin dashboard
     */
    private function getCommissionBreakdown()
    {
        $thisMonth = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        $thisMonthPayments = Payment::where('status', 'completed')
            ->whereMonth('created_at', $thisMonth->month)
            ->whereYear('created_at', $thisMonth->year)
            ->get();

        $lastMonthPayments = Payment::where('status', 'completed')
            ->whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->with('currency')
            ->get();

        $thisMonthBreakdown = $this->calculateTotalCommissionBreakdown($thisMonthPayments);
        $lastMonthBreakdown = $this->calculateTotalCommissionBreakdown($lastMonthPayments);

        return [
            'this_month' => $thisMonthBreakdown,
            'last_month' => $lastMonthBreakdown,
            // Growth is trickier now with currencies, skipping for now or should be per currency
        ];
    }

    /**
     * Calculate total commission breakdown for a collection of payments
     */
    private function calculateTotalCommissionBreakdown($payments)
    {
        $breakdownByCurrency = [];

        foreach ($payments as $payment) {
            $currencyCode = $payment->currency->code ?? 'NGN';
            $currencySymbol = $payment->currency->symbol ?? '₦';

            if (!isset($breakdownByCurrency[$currencyCode])) {
                $breakdownByCurrency[$currencyCode] = [
                    'super_marketer_commission' => 0,
                    'marketer_commission' => 0,
                    'regional_manager_commission' => 0,
                    'company_commission' => 0,
                    'total_commission' => 0,
                    'total_rent' => 0,
                    'payment_count' => 0,
                    'symbol' => $currencySymbol
                ];
            }

            $paymentCommission = $this->calculatePaymentCommission($payment);

            $breakdownByCurrency[$currencyCode]['super_marketer_commission'] += $paymentCommission['super_marketer_commission'];
            $breakdownByCurrency[$currencyCode]['marketer_commission'] += $paymentCommission['marketer_commission'];
            $breakdownByCurrency[$currencyCode]['regional_manager_commission'] += $paymentCommission['regional_manager_commission'];
            $breakdownByCurrency[$currencyCode]['company_commission'] += $paymentCommission['company_commission'];
            $breakdownByCurrency[$currencyCode]['total_commission'] += $paymentCommission['total_commission'];
            $breakdownByCurrency[$currencyCode]['total_rent'] += $payment->amount;
            $breakdownByCurrency[$currencyCode]['payment_count']++;
        }

        return $breakdownByCurrency;
    }

    /**
     * Switch dashboard mode for artisan users
     */
    public function switchArtisanMode(Request $request)
    {
        $user = Auth::user();

        // Verify user is an artisan
        if (!$user->isArtisan()) {
            return response()->json(['success' => false, 'message' => 'Access denied']);
        }

        $mode = $request->input('mode', 'artisan');

        // Validate mode
        if (!in_array($mode, ['artisan', 'personal'])) {
            return response()->json(['success' => false, 'message' => 'Invalid mode']);
        }

        // When switching to artisan mode, ensure we exit admin mode
        if ($mode === 'artisan') {
            session(['admin_dashboard_mode' => 'personal']);
        } else {
            // When switching FROM artisan mode back to personal, ensure ALL specific modes are cleared
            session(['admin_dashboard_mode' => 'personal']);
        }
        
        session(['dashboard_mode' => $mode]);

        return response()->json(['success' => true, 'mode' => $mode]);
    }
}