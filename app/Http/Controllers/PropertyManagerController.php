<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\Apartment;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PropertyManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the property manager dashboard
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        
        // Verify user is a property manager
        if (!$this->isPropertyManager($user)) {
            return redirect()->route('dashboard')->with('error', 'Access denied. You are not a property manager.');
        }

        // Get properties assigned to this property manager
        $managedProperties = Property::where('agent_id', $user->user_id)
            ->with(['owner', 'apartments'])
            ->paginate(10);

        // Get statistics
        $stats = $this->getPropertyManagerStats($user->user_id);

        // Get recent activities
        $recentActivities = $this->getRecentActivities($user->user_id);

        // Get current dashboard mode
        $mode = session('dashboard_mode', 'property_manager');

        return view('property_manager.dashboard', compact(
            'managedProperties',
            'stats',
            'recentActivities',
            'mode'
        ));
    }

    /**
     * Display managed properties with filtering and search
     */
    public function managedProperties(Request $request)
    {
        $user = Auth::user();
        
        if (!$this->isPropertyManager($user)) {
            return redirect()->route('dashboard')->with('error', 'Access denied.');
        }

        $query = Property::where('agent_id', $user->user_id)
            ->with(['owner', 'apartments']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('address', 'LIKE', "%{$search}%")
                  ->orWhere('prop_id', 'LIKE', "%{$search}%")
                  ->orWhere('state', 'LIKE', "%{$search}%")
                  ->orWhere('lga', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('property_type')) {
            $query->where('prop_type', $request->get('property_type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('state')) {
            $query->where('state', $request->get('state'));
        }

        $properties = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get filter options
        $states = Property::where('agent_id', $user->user_id)
            ->distinct()
            ->pluck('state')
            ->filter()
            ->sort();

        return view('property_manager.managed_properties', compact(
            'properties',
            'states'
        ));
    }

    /**
     * Display property details for managed property
     */
    public function propertyDetails($propertyId)
    {
        $user = Auth::user();
        
        if (!$this->isPropertyManager($user)) {
            return redirect()->route('dashboard')->with('error', 'Access denied.');
        }

        $property = Property::where('prop_id', $propertyId)
            ->where('agent_id', $user->user_id)
            ->with(['owner', 'apartments.tenant'])
            ->firstOrFail();

        // Get property statistics
        $propertyStats = $this->getPropertyStats($propertyId);

        // Get recent payments for this property
        $recentPayments = Payment::whereHas('apartment', function($query) use ($propertyId) {
                $query->where('property_id', $propertyId);
            })
            ->with(['apartment', 'tenant'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('property_manager.property_details', compact(
            'property',
            'propertyStats',
            'recentPayments'
        ));
    }

    /**
     * Display apartments for a managed property
     */
    public function propertyApartments($propertyId)
    {
        $user = Auth::user();
        
        if (!$this->isPropertyManager($user)) {
            return redirect()->route('dashboard')->with('error', 'Access denied.');
        }

        $property = Property::where('prop_id', $propertyId)
            ->where('agent_id', $user->user_id)
            ->with('owner')
            ->firstOrFail();

        $apartments = Apartment::where('property_id', $propertyId)
            ->with(['tenant', 'payments'])
            ->paginate(20);

        return view('property_manager.property_apartments', compact(
            'property',
            'apartments'
        ));
    }

    /**
     * Display payments for managed properties
     */
    public function payments(Request $request)
    {
        $user = Auth::user();
        
        if (!$this->isPropertyManager($user)) {
            return redirect()->route('dashboard')->with('error', 'Access denied.');
        }

        // Get property IDs managed by this user
        $managedPropertyIds = Property::where('agent_id', $user->user_id)
            ->pluck('prop_id');

        $query = Payment::whereHas('apartment', function($q) use ($managedPropertyIds) {
                $q->whereIn('property_id', $managedPropertyIds);
            })
            ->with(['apartment.property', 'tenant']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('property_id')) {
            $query->whereHas('apartment', function($q) use ($request) {
                $q->where('property_id', $request->get('property_id'));
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get managed properties for filter dropdown
        $managedProperties = Property::where('agent_id', $user->user_id)
            ->select('prop_id', 'address')
            ->get();

        return view('property_manager.payments', compact(
            'payments',
            'managedProperties'
        ));
    }

    /**
     * Display analytics for managed properties
     */
    public function analytics(Request $request)
    {
        $user = Auth::user();
        
        if (!$this->isPropertyManager($user)) {
            return redirect()->route('dashboard')->with('error', 'Access denied.');
        }

        $startDate = $request->get('start_date', now()->subMonths(6)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Get managed property IDs
        $managedPropertyIds = Property::where('agent_id', $user->user_id)
            ->pluck('prop_id');

        // Get analytics data
        $analytics = $this->getAnalyticsData($managedPropertyIds, $startDate, $endDate);

        return view('property_manager.analytics', compact(
            'analytics',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Check if user is a property manager
     */
    private function isPropertyManager($user)
    {
        return in_array($user->role, [6, 8]); // Property manager roles (6 = property_manager, 8 = Verified_Property_Manager)
    }

    /**
     * Get property manager statistics
     */
    private function getPropertyManagerStats($userId)
    {
        $managedPropertyIds = Property::where('agent_id', $userId)->pluck('prop_id');
        
        $totalProperties = $managedPropertyIds->count();
        
        $totalApartments = Apartment::whereIn('property_id', $managedPropertyIds)->count();
        
        $occupiedApartments = Apartment::whereIn('property_id', $managedPropertyIds)
            ->where('occupied', true)
            ->count();
        
        $monthlyRevenue = Payment::whereHas('apartment', function($query) use ($managedPropertyIds) {
                $query->whereIn('property_id', $managedPropertyIds);
            })
            ->where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        $pendingPayments = Payment::whereHas('apartment', function($query) use ($managedPropertyIds) {
                $query->whereIn('property_id', $managedPropertyIds);
            })
            ->where('status', 'pending')
            ->count();

        return [
            'total_properties' => $totalProperties,
            'total_apartments' => $totalApartments,
            'occupied_apartments' => $occupiedApartments,
            'vacant_apartments' => $totalApartments - $occupiedApartments,
            'occupancy_rate' => $totalApartments > 0 ? round(($occupiedApartments / $totalApartments) * 100, 1) : 0,
            'monthly_revenue' => $monthlyRevenue,
            'pending_payments' => $pendingPayments,
        ];
    }

    /**
     * Get recent activities for property manager
     */
    private function getRecentActivities($userId)
    {
        $activities = collect();
        
        $managedPropertyIds = Property::where('agent_id', $userId)->pluck('prop_id');

        // Recent payments
        $recentPayments = Payment::whereHas('apartment', function($query) use ($managedPropertyIds) {
                $query->whereIn('property_id', $managedPropertyIds);
            })
            ->with(['apartment.property', 'tenant'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentPayments as $payment) {
            $activities->push([
                'type' => 'payment',
                'title' => 'Payment ' . ucfirst($payment->status),
                'description' => "₦" . number_format($payment->amount, 2) . " for " . ($payment->apartment->property->address ?? 'Property'),
                'time' => $payment->created_at ? $payment->created_at->diffForHumans() : 'Unknown time',
                'icon' => 'nc-icon nc-money-coins',
                'color' => $payment->status === 'completed' ? 'text-success' : 'text-warning'
            ]);
        }

        // Recent apartment assignments
        $recentApartments = Apartment::whereIn('property_id', $managedPropertyIds)
            ->whereNotNull('tenant_id')
            ->with(['property', 'tenant'])
            ->orderBy('updated_at', 'desc')
            ->limit(3)
            ->get();

        foreach ($recentApartments as $apartment) {
            $activities->push([
                'type' => 'assignment',
                'title' => 'New Tenant Assignment',
                'description' => "Apartment {$apartment->apartment_id} assigned to tenant",
                'time' => $apartment->updated_at ? $apartment->updated_at->diffForHumans() : 'Unknown time',
                'icon' => 'nc-icon nc-single-02',
                'color' => 'text-info'
            ]);
        }

        return $activities->sortByDesc('time')->take(10)->values();
    }

    /**
     * Get statistics for a specific property
     */
    private function getPropertyStats($propertyId)
    {
        $totalApartments = Apartment::where('property_id', $propertyId)->count();
        
        $occupiedApartments = Apartment::where('property_id', $propertyId)
            ->where('occupied', true)
            ->count();
        
        $monthlyRevenue = Payment::whereHas('apartment', function($query) use ($propertyId) {
                $query->where('property_id', $propertyId);
            })
            ->where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        $totalRevenue = Payment::whereHas('apartment', function($query) use ($propertyId) {
                $query->where('property_id', $propertyId);
            })
            ->where('status', 'completed')
            ->sum('amount');

        return [
            'total_apartments' => $totalApartments,
            'occupied_apartments' => $occupiedApartments,
            'vacant_apartments' => $totalApartments - $occupiedApartments,
            'occupancy_rate' => $totalApartments > 0 ? round(($occupiedApartments / $totalApartments) * 100, 1) : 0,
            'monthly_revenue' => $monthlyRevenue,
            'total_revenue' => $totalRevenue,
        ];
    }

    /**
     * Get analytics data for managed properties
     */
    private function getAnalyticsData($propertyIds, $startDate, $endDate)
    {
        // Revenue trend
        $revenueTrend = [];
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        while ($start <= $end) {
            $monthRevenue = Payment::whereHas('apartment', function($query) use ($propertyIds) {
                    $query->whereIn('property_id', $propertyIds);
                })
                ->where('status', 'completed')
                ->whereYear('created_at', $start->year)
                ->whereMonth('created_at', $start->month)
                ->sum('amount');

            $revenueTrend[] = [
                'month' => $start->format('M Y'),
                'revenue' => $monthRevenue
            ];

            $start->addMonth();
        }

        // Occupancy trend
        $occupancyData = [];
        foreach ($propertyIds as $propertyId) {
            $property = Property::find($propertyId);
            if ($property) {
                $totalApartments = Apartment::where('property_id', $propertyId)->count();
                $occupiedApartments = Apartment::where('property_id', $propertyId)
                    ->where('occupied', true)
                    ->count();
                
                $occupancyData[] = [
                    'property' => $property->address,
                    'occupancy_rate' => $totalApartments > 0 ? round(($occupiedApartments / $totalApartments) * 100, 1) : 0
                ];
            }
        }

        return [
            'revenue_trend' => $revenueTrend,
            'occupancy_data' => $occupancyData,
            'total_properties' => count($propertyIds),
            'total_revenue' => Payment::whereHas('apartment', function($query) use ($propertyIds) {
                    $query->whereIn('property_id', $propertyIds);
                })
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completed')
                ->sum('amount')
        ];
    }
}