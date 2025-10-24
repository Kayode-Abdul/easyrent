<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissionRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CommissionManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display commission management dashboard
     */
    public function index()
    {
        $regions = CommissionRate::getAvailableRegions();
        $commissionRates = CommissionRate::with(['creator', 'updater'])
            ->orderBy('region')
            ->orderBy('property_management_status')
            ->orderBy('hierarchy_status')
            ->get()
            ->groupBy(['region', 'property_management_status', 'hierarchy_status']);

        return view('admin.commission-management.index', compact('regions', 'commissionRates'));
    }

    /**
     * Show commission rates for a specific region
     */
    public function showRegion(string $region)
    {
        $commissionRates = CommissionRate::where('region', $region)
            ->with(['creator', 'updater'])
            ->orderBy('property_management_status')
            ->orderBy('hierarchy_status')
            ->get()
            ->groupBy(['property_management_status', 'hierarchy_status']);

        return view('admin.commission-management.region', compact('region', 'commissionRates'));
    }

    /**
     * Show form to edit commission rates
     */
    public function edit(CommissionRate $commissionRate)
    {
        return view('admin.commission-management.edit', compact('commissionRate'));
    }

    /**
     * Update commission rates
     */
    public function update(Request $request, CommissionRate $commissionRate)
    {
        $request->validate([
            'super_marketer_rate' => 'nullable|numeric|min:0|max:100',
            'marketer_rate' => 'required|numeric|min:0|max:100',
            'regional_manager_rate' => 'nullable|numeric|min:0|max:100',
            'company_rate' => 'required|numeric|min:0|max:100',
            'total_commission_rate' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string|max:255'
        ]);

        // Validate that rates sum to total
        $sum = ($request->super_marketer_rate ?? 0) + 
               $request->marketer_rate + 
               ($request->regional_manager_rate ?? 0) + 
               $request->company_rate;

        if (abs($sum - $request->total_commission_rate) > 0.001) {
            return back()->withErrors([
                'rates_sum' => 'The sum of individual rates must equal the total commission rate.'
            ])->withInput();
        }

        DB::transaction(function () use ($request, $commissionRate) {
            $commissionRate->update([
                'super_marketer_rate' => $request->super_marketer_rate,
                'marketer_rate' => $request->marketer_rate,
                'regional_manager_rate' => $request->regional_manager_rate,
                'company_rate' => $request->company_rate,
                'total_commission_rate' => $request->total_commission_rate,
                'description' => $request->description,
                'updated_by' => Auth::id(),
                'last_updated_at' => Carbon::now()
            ]);
        });

        return redirect()->route('admin.commission-management.index')
            ->with('success', 'Commission rates updated successfully.');
    }

    /**
     * Show form to create new commission rate
     */
    public function create()
    {
        $regions = ['default', 'lagos', 'abuja', 'kano', 'port_harcourt', 'ibadan'];
        $propertyManagementStatuses = ['managed', 'unmanaged'];
        $hierarchyStatuses = ['with_super_marketer', 'without_super_marketer'];

        return view('admin.commission-management.create', compact(
            'regions', 
            'propertyManagementStatuses', 
            'hierarchyStatuses'
        ));
    }

    /**
     * Store new commission rate
     */
    public function store(Request $request)
    {
        $request->validate([
            'region' => 'required|string|max:100',
            'property_management_status' => 'required|in:managed,unmanaged',
            'hierarchy_status' => 'required|in:with_super_marketer,without_super_marketer',
            'super_marketer_rate' => 'nullable|numeric|min:0|max:100',
            'marketer_rate' => 'required|numeric|min:0|max:100',
            'regional_manager_rate' => 'nullable|numeric|min:0|max:100',
            'company_rate' => 'required|numeric|min:0|max:100',
            'total_commission_rate' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string|max:255'
        ]);

        // Check if combination already exists
        $exists = CommissionRate::where('region', $request->region)
            ->where('property_management_status', $request->property_management_status)
            ->where('hierarchy_status', $request->hierarchy_status)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'combination' => 'A commission rate for this combination already exists.'
            ])->withInput();
        }

        // Validate that rates sum to total
        $sum = ($request->super_marketer_rate ?? 0) + 
               $request->marketer_rate + 
               ($request->regional_manager_rate ?? 0) + 
               $request->company_rate;

        if (abs($sum - $request->total_commission_rate) > 0.001) {
            return back()->withErrors([
                'rates_sum' => 'The sum of individual rates must equal the total commission rate.'
            ])->withInput();
        }

        DB::transaction(function () use ($request) {
            CommissionRate::create([
                'region' => $request->region,
                'property_management_status' => $request->property_management_status,
                'hierarchy_status' => $request->hierarchy_status,
                'super_marketer_rate' => $request->super_marketer_rate,
                'marketer_rate' => $request->marketer_rate,
                'regional_manager_rate' => $request->regional_manager_rate,
                'company_rate' => $request->company_rate,
                'total_commission_rate' => $request->total_commission_rate,
                'description' => $request->description,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
                'last_updated_at' => Carbon::now(),
                'effective_from' => Carbon::now(),
                'is_active' => true
            ]);
        });

        return redirect()->route('admin.commission-management.index')
            ->with('success', 'Commission rate created successfully.');
    }

    /**
     * Bulk update commission rates for a region
     */
    public function bulkUpdate(Request $request, string $region)
    {
        $request->validate([
            'rates' => 'required|array',
            'rates.*.id' => 'required|exists:commission_rates,id',
            'rates.*.super_marketer_rate' => 'nullable|numeric|min:0|max:100',
            'rates.*.marketer_rate' => 'required|numeric|min:0|max:100',
            'rates.*.regional_manager_rate' => 'nullable|numeric|min:0|max:100',
            'rates.*.company_rate' => 'required|numeric|min:0|max:100',
            'rates.*.total_commission_rate' => 'required|numeric|min:0|max:100',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->rates as $rateData) {
                // Validate that rates sum to total
                $sum = ($rateData['super_marketer_rate'] ?? 0) + 
                       $rateData['marketer_rate'] + 
                       ($rateData['regional_manager_rate'] ?? 0) + 
                       $rateData['company_rate'];

                if (abs($sum - $rateData['total_commission_rate']) > 0.001) {
                    throw new \Exception('Invalid rate sum for rate ID: ' . $rateData['id']);
                }

                CommissionRate::where('id', $rateData['id'])->update([
                    'super_marketer_rate' => $rateData['super_marketer_rate'],
                    'marketer_rate' => $rateData['marketer_rate'],
                    'regional_manager_rate' => $rateData['regional_manager_rate'],
                    'company_rate' => $rateData['company_rate'],
                    'total_commission_rate' => $rateData['total_commission_rate'],
                    'updated_by' => Auth::id(),
                    'last_updated_at' => Carbon::now()
                ]);
            }
        });

        return redirect()->route('admin.commission-management.region', $region)
            ->with('success', 'Commission rates updated successfully.');
    }

    /**
     * Get commission breakdown preview
     */
    public function getCommissionBreakdown(Request $request)
    {
        $request->validate([
            'rent_amount' => 'required|numeric|min:0',
            'region' => 'required|string',
            'property_management_status' => 'required|in:managed,unmanaged',
            'hierarchy_status' => 'required|in:with_super_marketer,without_super_marketer'
        ]);

        $commissionRate = CommissionRate::getRateForScenario(
            $request->region,
            $request->property_management_status,
            $request->hierarchy_status
        );

        if (!$commissionRate) {
            return response()->json([
                'success' => false,
                'message' => 'No commission rate found for this scenario'
            ]);
        }

        $breakdown = $commissionRate->calculateCommissionBreakdown($request->rent_amount);

        return response()->json([
            'success' => true,
            'breakdown' => $breakdown,
            'commission_rate' => $commissionRate
        ]);
    }

    /**
     * Delete commission rate
     */
    public function destroy(CommissionRate $commissionRate)
    {
        $commissionRate->delete();

        return redirect()->route('admin.commission-management.index')
            ->with('success', 'Commission rate deleted successfully.');
    }

    /**
     * Show regional commission manager
     */
    public function regionalManager()
    {
        return view('admin.commission-management.regional-manager');
    }

    /**
     * Get commission rates for a specific region (AJAX)
     */
    public function getRegionRates(string $region)
    {
        $rates = CommissionRate::where('region', $region)
            ->get()
            ->groupBy(['property_management_status', 'hierarchy_status']);

        $formattedRates = [];
        
        foreach ($rates as $propertyStatus => $propertyRates) {
            foreach ($propertyRates as $hierarchyStatus => $rateCollection) {
                $rate = $rateCollection->first();
                $key = $propertyStatus . '_' . str_replace('_super_marketer', '', $hierarchyStatus);
                
                $formattedRates[$key] = [
                    'super_marketer_rate' => $rate->super_marketer_rate,
                    'marketer_rate' => $rate->marketer_rate,
                    'regional_manager_rate' => $rate->regional_manager_rate,
                    'company_rate' => $rate->company_rate,
                    'total_commission_rate' => $rate->total_commission_rate
                ];
            }
        }

        return response()->json([
            'success' => true,
            'rates' => $formattedRates
        ]);
    }

    /**
     * Bulk save commission rates for a region
     */
    public function bulkSaveRegion(Request $request)
    {
        $request->validate([
            'region' => 'required|string|max:100',
            'scenarios' => 'required|json'
        ]);

        $scenarios = json_decode($request->scenarios, true);
        
        DB::transaction(function () use ($request, $scenarios) {
            foreach ($scenarios as $scenario) {
                // Validate that rates sum to total
                $sum = ($scenario['super_marketer_rate'] ?? 0) + 
                       $scenario['marketer_rate'] + 
                       ($scenario['regional_manager_rate'] ?? 0) + 
                       $scenario['company_rate'];

                if (abs($sum - $scenario['total_commission_rate']) > 0.001) {
                    throw new \Exception('Invalid rate sum for scenario: ' . $scenario['property_management_status'] . ' + ' . $scenario['hierarchy_status']);
                }

                // Update or create the commission rate
                CommissionRate::updateOrCreate(
                    [
                        'region' => $request->region,
                        'property_management_status' => $scenario['property_management_status'],
                        'hierarchy_status' => $scenario['hierarchy_status']
                    ],
                    [
                        'super_marketer_rate' => $scenario['super_marketer_rate'],
                        'marketer_rate' => $scenario['marketer_rate'],
                        'regional_manager_rate' => $scenario['regional_manager_rate'],
                        'company_rate' => $scenario['company_rate'],
                        'total_commission_rate' => $scenario['total_commission_rate'],
                        'description' => ucfirst($scenario['property_management_status']) . ' properties ' . 
                                       str_replace('_', ' ', $scenario['hierarchy_status']),
                        'role_id' => 1, // Default role
                        'commission_percentage' => $scenario['total_commission_rate'],
                        'effective_from' => Carbon::now(),
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                        'last_updated_at' => Carbon::now(),
                        'is_active' => true
                    ]
                );
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Commission rates saved successfully for ' . $request->region
        ]);
    }
}