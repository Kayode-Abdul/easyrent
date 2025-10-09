<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissionRate;
use App\Models\Role;
use App\Services\Commission\RegionalRateManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RegionalCommissionController extends Controller
{
    protected $regionalRateManager;

    public function __construct(RegionalRateManager $regionalRateManager)
    {
        $this->middleware('auth');
        // Temporarily disable role middleware for testing
        // $this->middleware('role:super_admin');
        $this->regionalRateManager = $regionalRateManager;
    }

    /**
     * Display a listing of commission rates
     */
    public function index(Request $request)
    {
        $query = CommissionRate::with(['role', 'creator'])
            ->orderBy('region')
            ->orderBy('role_id')
            ->orderBy('effective_from', 'desc');

        // Filter by region if provided
        if ($request->filled('region')) {
            $query->where('region', $request->region);
        }

        // Filter by role if provided
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $rates = $query->paginate(20);
        $roles = Role::all();
        $regions = CommissionRate::distinct()->pluck('region')->sort();

        return view('admin.commission-rates.index', compact('rates', 'roles', 'regions'));
    }

    /**
     * Show the form for creating a new commission rate
     */
    public function create()
    {
        $roles = Role::all();
        $regions = $this->getAvailableRegions();
        
        return view('admin.commission-rates.create', compact('roles', 'regions'));
    }

    /**
     * Store a newly created commission rate
     */
    public function store(Request $request)
    {
        $validator = $this->validateCommissionRate($request);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Validate rate configuration
            $validationResult = $this->regionalRateManager->validateRateConfiguration([
                $request->region => [
                    $request->role_id => $request->commission_percentage
                ]
            ]);

            if (!empty($validationResult['errors'])) {
                throw new \Exception(implode(', ', $validationResult['errors']));
            }

            // Deactivate existing rate if setting new one
            if ($request->filled('replace_existing')) {
                CommissionRate::where('region', $request->region)
                    ->where('role_id', $request->role_id)
                    ->where('is_active', true)
                    ->update(['is_active' => false, 'effective_until' => now()]);
            }

            // Create new rate
            $rate = CommissionRate::create([
                'region' => $request->region,
                'role_id' => $request->role_id,
                'commission_percentage' => $request->commission_percentage,
                'effective_from' => $request->effective_from ?? now(),
                'effective_until' => $request->effective_until,
                'created_by' => auth()->id(),
                'is_active' => true
            ]);

            DB::commit();

            Log::info('Commission rate created', [
                'rate_id' => $rate->id,
                'region' => $rate->region,
                'role_id' => $rate->role_id,
                'percentage' => $rate->commission_percentage,
                'created_by' => auth()->id()
            ]);

            return redirect()->route('admin.commission-rates.index')
                ->with('success', 'Commission rate created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create commission rate', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to create commission rate: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified commission rate
     */
    public function show(CommissionRate $commissionRate)
    {
        $commissionRate->load(['role', 'creator']);
        $history = CommissionRate::where('region', $commissionRate->region)
            ->where('role_id', $commissionRate->role_id)
            ->orderBy('effective_from', 'desc')
            ->get();

        return view('admin.commission-rates.show', compact('commissionRate', 'history'));
    }

    /**
     * Show the form for editing the specified commission rate
     */
    public function edit(CommissionRate $commissionRate)
    {
        $roles = Role::all();
        $regions = $this->getAvailableRegions();
        
        return view('admin.commission-rates.edit', compact('commissionRate', 'roles', 'regions'));
    }

    /**
     * Update the specified commission rate
     */
    public function update(Request $request, CommissionRate $commissionRate)
    {
        $validator = $this->validateCommissionRate($request, $commissionRate->id);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Validate rate configuration
            $validationResult = $this->regionalRateManager->validateRateConfiguration([
                $request->region => [
                    $request->role_id => $request->commission_percentage
                ]
            ]);

            if (!empty($validationResult['errors'])) {
                throw new \Exception(implode(', ', $validationResult['errors']));
            }

            $originalData = $commissionRate->toArray();

            $commissionRate->update([
                'region' => $request->region,
                'role_id' => $request->role_id,
                'commission_percentage' => $request->commission_percentage,
                'effective_from' => $request->effective_from,
                'effective_until' => $request->effective_until,
                'is_active' => $request->boolean('is_active', true)
            ]);

            DB::commit();

            Log::info('Commission rate updated', [
                'rate_id' => $commissionRate->id,
                'original_data' => $originalData,
                'updated_data' => $commissionRate->fresh()->toArray(),
                'updated_by' => auth()->id()
            ]);

            return redirect()->route('admin.commission-rates.index')
                ->with('success', 'Commission rate updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update commission rate', [
                'rate_id' => $commissionRate->id,
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to update commission rate: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified commission rate
     */
    public function destroy(CommissionRate $commissionRate)
    {
        try {
            DB::beginTransaction();

            // Instead of deleting, deactivate the rate
            $commissionRate->update([
                'is_active' => false,
                'effective_until' => now()
            ]);

            DB::commit();

            Log::info('Commission rate deactivated', [
                'rate_id' => $commissionRate->id,
                'deactivated_by' => auth()->id()
            ]);

            return redirect()->route('admin.commission-rates.index')
                ->with('success', 'Commission rate deactivated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to deactivate commission rate', [
                'rate_id' => $commissionRate->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to deactivate commission rate: ' . $e->getMessage()]);
        }
    }

    /**
     * Show bulk update form
     */
    public function bulkUpdateForm()
    {
        $roles = Role::all();
        $regions = $this->getAvailableRegions();
        $currentRates = CommissionRate::active()
            ->with('role')
            ->get()
            ->groupBy('region');

        return view('admin.commission-rates.bulk-update', compact('roles', 'regions', 'currentRates'));
    }

    /**
     * Process bulk update of commission rates
     */
    public function bulkUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rates' => 'required|array',
            'rates.*.region' => 'required|string|max:100',
            'rates.*.role_id' => 'required|exists:roles,id',
            'rates.*.commission_percentage' => 'required|numeric|min:0|max:100',
            'rates.*.effective_from' => 'nullable|date',
            'effective_from_global' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $rates = $request->rates;
            $globalEffectiveFrom = $request->effective_from_global;
            $updatedCount = 0;
            $errors = [];

            // Group rates by region for validation
            $ratesByRegion = [];
            foreach ($rates as $rate) {
                $region = $rate['region'];
                $roleId = $rate['role_id'];
                $percentage = $rate['commission_percentage'];
                
                if (!isset($ratesByRegion[$region])) {
                    $ratesByRegion[$region] = [];
                }
                $ratesByRegion[$region][$roleId] = $percentage;
            }

            // Validate all rate configurations
            $validationResult = $this->regionalRateManager->validateRateConfiguration($ratesByRegion);
            
            if (!empty($validationResult['errors'])) {
                throw new \Exception('Validation failed: ' . implode(', ', $validationResult['errors']));
            }

            // Process each rate update
            foreach ($rates as $rateData) {
                try {
                    $effectiveFrom = $rateData['effective_from'] ?? $globalEffectiveFrom ?? now();

                    // Deactivate existing rate
                    CommissionRate::where('region', $rateData['region'])
                        ->where('role_id', $rateData['role_id'])
                        ->where('is_active', true)
                        ->update([
                            'is_active' => false,
                            'effective_until' => $effectiveFrom
                        ]);

                    // Create new rate
                    CommissionRate::create([
                        'region' => $rateData['region'],
                        'role_id' => $rateData['role_id'],
                        'commission_percentage' => $rateData['commission_percentage'],
                        'effective_from' => $effectiveFrom,
                        'created_by' => auth()->id(),
                        'is_active' => true
                    ]);

                    $updatedCount++;

                } catch (\Exception $e) {
                    $errors[] = "Failed to update rate for {$rateData['region']}, role {$rateData['role_id']}: " . $e->getMessage();
                }
            }

            if (!empty($errors)) {
                throw new \Exception(implode('; ', $errors));
            }

            DB::commit();

            Log::info('Bulk commission rates update completed', [
                'updated_count' => $updatedCount,
                'updated_by' => auth()->id(),
                'rates_data' => $rates
            ]);

            return redirect()->route('admin.commission-rates.index')
                ->with('success', "Successfully updated {$updatedCount} commission rates.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk commission rates update failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Bulk update failed: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Get rate history for a specific region and role
     */
    public function history(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'region' => 'required|string',
            'role_id' => 'required|exists:roles,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid parameters'], 400);
        }

        $history = CommissionRate::where('region', $request->region)
            ->where('role_id', $request->role_id)
            ->with(['role', 'creator'])
            ->orderBy('effective_from', 'desc')
            ->get();

        return response()->json($history);
    }

    /**
     * Validate commission rate request
     */
    private function validateCommissionRate(Request $request, $excludeId = null)
    {
        $rules = [
            'region' => 'required|string|max:100',
            'role_id' => 'required|exists:roles,id',
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'effective_from' => 'nullable|date',
            'effective_until' => 'nullable|date|after:effective_from',
            'replace_existing' => 'boolean'
        ];

        // Add unique validation for active rates
        $uniqueRule = 'unique:commission_rates,region,NULL,id,role_id,' . $request->role_id . ',is_active,1';
        if ($excludeId) {
            $uniqueRule = 'unique:commission_rates,region,' . $excludeId . ',id,role_id,' . $request->role_id . ',is_active,1';
        }
        
        if (!$request->boolean('replace_existing')) {
            $rules['region'] = [$rules['region'], $uniqueRule];
        }

        return Validator::make($request->all(), $rules, [
            'region.unique' => 'An active commission rate already exists for this region and role combination.',
            'commission_percentage.max' => 'Commission percentage cannot exceed 100%.',
            'effective_until.after' => 'End date must be after the start date.'
        ]);
    }

    /**
     * Get available regions
     */
    private function getAvailableRegions()
    {
        // This could be expanded to pull from a regions table or configuration
        return [
            'Lagos', 'Abuja', 'Port Harcourt', 'Kano', 'Ibadan', 'Kaduna',
            'Jos', 'Ilorin', 'Aba', 'Onitsha', 'Warri', 'Calabar',
            'Benin City', 'Akure', 'Abeokuta', 'Osogbo', 'Ado-Ekiti',
            'Lokoja', 'Makurdi', 'Bauchi', 'Gombe', 'Yola', 'Minna',
            'Sokoto', 'Katsina', 'Dutse', 'Damaturu', 'Maiduguri',
            'Jalingo', 'Lafia', 'Asaba', 'Awka', 'Owerri', 'Umuahia',
            'Abakaliki', 'Enugu', 'Uyo', 'Yenagoa'
        ];
    }
}