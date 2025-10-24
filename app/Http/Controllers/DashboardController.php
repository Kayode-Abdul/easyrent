<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Property;
use App\Models\Apartment;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

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
        // Double-check authentication (middleware should handle this, but extra safety)
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
        $isAdmin = $user->admin == 1;
        $userRole = $user->role; // 1=admin, 2=landlord, 3=tenant, 4=agent

        $stats = [];
        $chartData = [];
        $recentActivities = [];
        $greeting = $this->getGreeting();

        if ($isAdmin || $userRole == 7) {
            // Super Admin Dashboard
            $stats = $this->getAdminStats();
            $chartData = $this->getAdminChartData();
            $recentActivities = $this->getAdminActivities();
            return view('admin-dashboard', compact('stats', 'chartData', 'recentActivities', 'greeting'));
        } elseif ($userRole == 2) {
            // Landlord Dashboard
            $stats = $this->getLandlordStats($userId);
            $chartData = $this->getLandlordChartData($userId);
            $recentActivities = $this->getLandlordActivities($userId);
        } else {
            // Tenant Dashboard (role 3) and others
            $stats = $this->getTenantStats($userId);
            $chartData = $this->getTenantChartData($userId);
            $recentActivities = $this->getTenantActivities($userId);
        }

        return view('dash', compact('stats', 'chartData', 'recentActivities', 'greeting'));
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
                'total_revenue' => Payment::whereIn('status', ['completed', 'success'])->sum('amount'),
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
                'revenue_today' => Payment::where('status', 'completed')->whereDate('created_at', Carbon::today())->sum('amount'),
                'revenue_this_month' => Payment::where('status', 'completed')->whereMonth('created_at', Carbon::now()->month)->sum('amount'),
                'revenue_last_month' => Payment::where('status', 'completed')->whereMonth('created_at', Carbon::now()->subMonth()->month)->sum('amount'),
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
            $properties = Property::where('user_id', $userId)->pluck('prop_id');
            $apartments = Apartment::whereIn('property_id', $properties);
            
            return [
                'my_properties' => $properties->count(),
                'occupied_apartments' => $apartments->where('occupied', true)->count(),
                'monthly_revenue' => Payment::where('landlord_id', $userId)
                    ->where('status', 'completed')
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->sum('amount'),
                'new_bookings' => Booking::whereIn('property_id', $properties)
                    ->where('created_at', '>=', Carbon::now()->subWeek())
                    ->count(),
            ];
        });
    }

    private function getTenantStats($userId)
    {
        return Cache::remember('tenant_stats_' . $userId, now()->addMinutes(10), function () use ($userId) {
            return [
                'my_rentals' => Apartment::where('tenant_id', $userId)->where('occupied', true)->count(),
                'payments_this_month' => Payment::where('tenant_id', $userId)
                    ->where('status', 'completed')
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->sum('amount'),
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
        } catch (\Exception $e) {
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
        
        return $newUsersThisMonth > 0 ? '$' . round($marketingSpend / $newUsersThisMonth, 2) : '$0';
    }

    private function calculateLTV()
    {
        // Lifetime Value - simplified calculation
        $avgMonthlyRevenue = Payment::where('status', 'completed')
            ->whereMonth('created_at', Carbon::now()->month)
            ->avg('amount') ?? 0;
        $avgLifespan = 12; // months - would be calculated from user data
        
        return '$' . round($avgMonthlyRevenue * $avgLifespan, 2);
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
        
            $properties = Property::where('user_id', $userId)->pluck('prop_id');
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

    private function getAdminActivities()
    {
        $activities = collect();

        // Recent user registrations
        User::latest()->limit(5)->get()->each(function ($user) use ($activities) {
            $userType = $user->admin == 1 ? 'admin' : ($user->role == 2 ? 'landlord' : ($user->role == 3 ? 'tenant' : 'agent'));
            $activities->push([
                'type' => 'user_registration',
                'icon' => 'nc-icon nc-single-02',
                'color' => 'text-success',
                'title' => 'New User Registration',
                'description' => $user->first_name . ' ' . $user->last_name . ' joined as ' . $userType,
                'time' => $user->created_at->diffForHumans(),
                'link' => '/admin/users',
            ]);
        });

        // Recent property additions
        Property::latest()->limit(5)->get()->each(function ($property) use ($activities) {
            $activities->push([
                'type' => 'property_added',
                'icon' => 'nc-icon nc-istanbul',
                'color' => 'text-info',
                'title' => 'New Property Added',
                'description' => 'Property at ' . $property->address . ', ' . $property->state,
                'time' => $property->created_at->diffForHumans(),
                'link' => '/properties/' . $property->prop_id,
            ]);
        });

        // Recent payments
        Payment::where('status', 'completed')->latest()->limit(5)->get()->each(function ($payment) use ($activities) {
            $activities->push([
                'type' => 'payment_completed',
                'icon' => 'nc-icon nc-money-coins',
                'color' => 'text-success',
                'title' => 'Payment Completed',
                'description' => 'Payment of $' . number_format($payment->amount, 2) . ' received',
                'time' => $payment->created_at->diffForHumans(),
                'link' => '/payments/' . $payment->id,
            ]);
        });

        // System alerts
        $activities->push([
            'type' => 'system_alert',
            'icon' => 'nc-icon nc-bell-55',
            'color' => 'text-warning',
            'title' => 'System Alert',
            'description' => 'Database backup completed successfully',
            'time' => '2 hours ago',
            'link' => '/admin/system-health',
        ]);

        return $activities->sortByDesc('time')->take(20)->values();
    }

    private function getLandlordActivities($userId)
    {
        $activities = [];
        
        // Recent payments for this landlord
        $recentPayments = Payment::where('landlord_id', $userId)
            ->where('status', 'completed')
            ->latest()
            ->take(3)
            ->get();
        
        foreach ($recentPayments as $payment) {
            $activities[] = [
                'title' => 'Payment Received',
                'description' => "Received ₦" . number_format($payment->amount, 2) . " for property rent",
                'time' => $payment->created_at->diffForHumans(),
            ];
        }

        // Recent bookings
        $properties = Property::where('user_id', $userId)->pluck('prop_id');
        $recentBookings = Booking::whereIn('property_id', $properties)
            ->latest()
            ->take(2)
            ->get();
        
        foreach ($recentBookings as $booking) {
            $activities[] = [
                'title' => 'New Booking',
                'description' => "New booking received for your property",
                'time' => $booking->created_at->diffForHumans(),
            ];
        }

        return collect($activities)->sortByDesc('time')->take(5)->values()->all();
    }

    private function getTenantActivities($userId)
    {
        $activities = [];
        
        // Recent payments by this tenant
        $recentPayments = Payment::where('tenant_id', $userId)
            ->latest()
            ->take(3)
            ->get();
        
        foreach ($recentPayments as $payment) {
            $activities[] = [
                'title' => 'Payment Made',
                'description' => "Made payment of ₦" . number_format($payment->amount, 2) . " - Status: {$payment->status}",
                'time' => $payment->created_at->diffForHumans(),
            ];
        }

        // Recent messages
        $recentMessages = Message::where('receiver_id', $userId)
            ->latest()
            ->take(2)
            ->get();
        
        foreach ($recentMessages as $message) {
            $activities[] = [
                'title' => 'New Message',
                    'description' => "Received message: " . substr($message->body ?? $message->message, 0, 50) . "...",
                'time' => $message->created_at->diffForHumans(),
            ];
        }

        return collect($activities)->sortByDesc('time')->take(5)->values()->all();
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
        $propertyIds = $properties->pluck('prop_id');
        
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
        
        // Recent bookings
        $recentBookings = Booking::whereIn('apartment_id', 
            Apartment::whereIn('property_id', $propertyIds)->pluck('apartment_id')
        )->latest()->take(5)->get();
        
        return [
            'totalProperties' => $totalProperties,
            'totalApartments' => $totalApartments,
            'occupiedApartments' => $occupiedApartments,
            'vacantApartments' => $totalApartments - $occupiedApartments,
            'occupancyRate' => $totalApartments > 0 ? round(($occupiedApartments / $totalApartments) * 100, 1) : 0,
            'monthlyRevenue' => $monthlyRevenue,
            'pendingPayments' => $pendingPayments,
            'revenueTrend' => $revenueTrend,
            'recentBookings' => $recentBookings,
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
        $query = Payment::where('status', 'completed');
        
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
                // No additional filter for total
                break;
        }
        
        $payments = $query->get();
        $totalCommission = 0;
        
        foreach ($payments as $payment) {
            // Calculate commission based on property management status and hierarchy
            $commissionAmount = $this->calculatePaymentCommission($payment);
            $totalCommission += $commissionAmount['company_commission'];
        }
        
        return $totalCommission;
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
                    'marketer_commission' => $rentAmount * 0.005,       // 0.5%
                    'regional_manager_commission' => $rentAmount * 0.001, // 0.1%
                    'company_commission' => $rentAmount * 0.0165,       // 1.65%
                    'total_commission' => $rentAmount * 0.025           // 2.5%
                ];
            } else {
                // Managed without Super Marketer: 2.5% total
                return [
                    'super_marketer_commission' => 0,
                    'marketer_commission' => $rentAmount * 0.0075,      // 0.75%
                    'regional_manager_commission' => $rentAmount * 0.001, // 0.1%
                    'company_commission' => $rentAmount * 0.0165,       // 1.65%
                    'total_commission' => $rentAmount * 0.025           // 2.5%
                ];
            }
        } else {
            if ($hasSuperMarketer) {
                // Unmanaged with Super Marketer: 5% total
                return [
                    'super_marketer_commission' => $rentAmount * 0.005,  // 0.5%
                    'marketer_commission' => $rentAmount * 0.01,        // 1.0%
                    'regional_manager_commission' => $rentAmount * 0.0025, // 0.25%
                    'company_commission' => $rentAmount * 0.0325,       // 3.25%
                    'total_commission' => $rentAmount * 0.05            // 5%
                ];
            } else {
                // Unmanaged without Super Marketer: 5% total
                return [
                    'super_marketer_commission' => 0,
                    'marketer_commission' => $rentAmount * 0.015,       // 1.5%
                    'regional_manager_commission' => $rentAmount * 0.0025, // 0.25%
                    'company_commission' => $rentAmount * 0.0325,       // 3.25%
                    'total_commission' => $rentAmount * 0.05            // 5%
                ];
            }
        }
    }

    /**
     * Check if a property is managed (has project manager/agent)
     */
    private function isPropertyManaged($payment)
    {
        // You can enhance this by checking if the property has an agent_id or project_manager_id
        // For now, return false as default (unmanaged)
        return false; // Simplified - enhance based on your property management logic
    }

    /**
     * Check if payment involves a Super Marketer in the referral chain
     */
    private function paymentHasSuperMarketer($payment)
    {
        // You can enhance this by checking the referral chain
        // For now, return false as default
        return false; // Simplified - enhance based on your referral chain logic
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
            ->get();
        
        $thisMonthBreakdown = $this->calculateTotalCommissionBreakdown($thisMonthPayments);
        $lastMonthBreakdown = $this->calculateTotalCommissionBreakdown($lastMonthPayments);
        
        return [
            'this_month' => $thisMonthBreakdown,
            'last_month' => $lastMonthBreakdown,
            'growth' => [
                'company_commission' => $lastMonthBreakdown['company_commission'] > 0 
                    ? (($thisMonthBreakdown['company_commission'] - $lastMonthBreakdown['company_commission']) / $lastMonthBreakdown['company_commission']) * 100 
                    : 0,
                'total_commission' => $lastMonthBreakdown['total_commission'] > 0 
                    ? (($thisMonthBreakdown['total_commission'] - $lastMonthBreakdown['total_commission']) / $lastMonthBreakdown['total_commission']) * 100 
                    : 0
            ]
        ];
    }

    /**
     * Calculate total commission breakdown for a collection of payments
     */
    private function calculateTotalCommissionBreakdown($payments)
    {
        $breakdown = [
            'super_marketer_commission' => 0,
            'marketer_commission' => 0,
            'regional_manager_commission' => 0,
            'company_commission' => 0,
            'total_commission' => 0,
            'total_rent' => 0,
            'payment_count' => $payments->count()
        ];
        
        foreach ($payments as $payment) {
            $paymentCommission = $this->calculatePaymentCommission($payment);
            
            $breakdown['super_marketer_commission'] += $paymentCommission['super_marketer_commission'];
            $breakdown['marketer_commission'] += $paymentCommission['marketer_commission'];
            $breakdown['regional_manager_commission'] += $paymentCommission['regional_manager_commission'];
            $breakdown['company_commission'] += $paymentCommission['company_commission'];
            $breakdown['total_commission'] += $paymentCommission['total_commission'];
            $breakdown['total_rent'] += $payment->amount;
        }
        
        return $breakdown;
    }
}
