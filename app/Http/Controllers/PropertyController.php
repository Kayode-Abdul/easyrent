<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Apartment;
use App\Models\User;
use App\Http\Requests\PropertyRequest;
use App\Http\Requests\ApartmentRequest;
use App\Http\Requests\SingleApartmentRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function properties(Request $request): \Illuminate\View\View
    {
        $perPage = 10; // Number of properties per page
        $all_properties = Property::with('owner')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        return view('properties', compact('all_properties'));
    }

    public function add(PropertyRequest $request): \Illuminate\View\View|JsonResponse
    {
        if (!$request->isMethod('post')) {
            $countries = json_decode(File::get(resource_path('/countries.json')), true);
            // Keep $location for backward compatibility with the listing blade view
            $location = $countries[0]['states'] ?? [];
            return view('listing', compact('countries', 'location'));
        }
        try {
            // Check if user is logged in
            if (!auth()->check()) {
                return response()->json([
                    'success' => false,
                    'messages' => 'Please log in to add a property.'
                ], 401);
            }
            $userId = auth()->user()->user_id;
            
            // Create the property with basic fields
            $property = Property::create([
                'user_id' => $userId,
                'property_id' => $this->generateUniquePropertyId(),
                'prop_type' => $request->propertyType,
                'address' => $request->address,
                'country' => $request->country ?? 'Nigeria',
                'state' => $request->state,
                'lga' => $request->city,
                'no_of_apartment' => $request->noOfApartment ?? null,
                'size_value' => $request->size_value ?? null,
                'size_unit' => $request->size_unit ?? null,
                'created_at' => now()
            ]);

            // Save property-specific attributes based on property type
            $propType = (int)$request->propertyType;
            
            // Warehouse attributes (Type 5)
            if ($propType === 5) {
                if ($request->filled('height_clearance')) {
                    $property->setPropertyAttribute('height_clearance', $request->height_clearance);
                }
                if ($request->filled('loading_docks')) {
                    $property->setPropertyAttribute('loading_docks', $request->loading_docks);
                }
                if ($request->filled('storage_type')) {
                    $property->setPropertyAttribute('storage_type', $request->storage_type);
                }
            }
            
            // Land/Farm attributes (Type 6 or 7)
            elseif ($propType === 6 || $propType === 7) {
                if ($request->filled('land_type')) {
                    $property->setPropertyAttribute('land_type', $request->land_type);
                }
                if ($request->filled('soil_type')) {
                    $property->setPropertyAttribute('soil_type', $request->soil_type);
                }
                if ($request->filled('water_access')) {
                    $property->setPropertyAttribute('water_access', $request->water_access);
                }
                if ($request->filled('water_source')) {
                    $property->setPropertyAttribute('water_source', $request->water_source);
                }
                if ($request->filled('topography')) {
                    $property->setPropertyAttribute('topography', $request->topography);
                }
            }
            
            // Store/Shop attributes (Type 8 or 9)
            elseif ($propType === 8 || $propType === 9) {
                if ($request->filled('frontage_width')) {
                    $property->setPropertyAttribute('frontage_width', $request->frontage_width);
                }
                if ($request->filled('store_type')) {
                    $property->setPropertyAttribute('store_type', $request->store_type);
                }
                if ($request->filled('foot_traffic')) {
                    $property->setPropertyAttribute('foot_traffic', $request->foot_traffic);
                }
                if ($request->filled('parking_spaces')) {
                    $property->setPropertyAttribute('parking_spaces', $request->parking_spaces);
                }
            }

            Log::info('Property created successfully with type: ' . $property->getPropertyTypeName(), [
                'property_id' => $property->property_id,
                'prop_type' => $propType
            ]);

            // Handle Image Uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $originalName = $image->getClientOriginalName();
                    $extension = $image->getClientOriginalExtension();
                    $fileName = 'prop_' . Str::random(10) . '_' . time() . '.' . $extension;
                    $filePath = 'properties/' . $property->property_id . '/images';
                    
                    $path = $image->storeAs('public/' . $filePath, $fileName);
                    $storagePath = str_replace('public/', '', $path);

                    PropertyImage::create([
                        'property_id' => $property->id, // Use primary ID for FK
                        'uploaded_by' => $userId,
                        'file_name' => $fileName,
                        'file_path' => $storagePath,
                        'original_name' => $originalName,
                        'file_size' => $image->getSize(),
                        'mime_type' => $image->getMimeType(),
                        'is_main' => ($index === 0), // Set first image as main by default
                        'order' => $index
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'messages' => [
                    'message' => 'Property Listed Successfully!',
                    'more' => true,
                    'propId' => $property->property_id
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Property creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'messages' => 'Server Error! Cannot create Property. ' . $e->getMessage()
            ], 500);
        }
    }

    public function addApartment(ApartmentRequest $request): JsonResponse
    {
        try {
            Log::info('Apartment creation request data:', $request->all());
            $property = Property::where('property_id', $request->propertyId)->firstOrFail();
            
            $createdApartments = [];
            
            // Detect if request has singular or array fields
            // Singular fields: tenantId, fromRange, toRange, amount, rentalType
            // Array fields: tenantId[], fromRange[], toRange[], amount[], rentalType[]
            $isSingular = !is_array($request->amount);
            
            if ($isSingular) {
                // Single apartment creation (from property show page)
                // Wrap singular values in arrays for uniform processing
                $tenantIds = [$request->tenantId ?? null];
                $fromRanges = [$request->fromRange ?? null];
                $toRanges = [$request->toRange ?? null];
                $amounts = [$request->amount];
                $rentalTypes = [$request->rentalType ?? 'monthly'];
                $durations = [$request->duration ?? null];
            } else {
                // Bulk apartment creation (from listing page if still used)
                $tenantIds = $request->tenantId ?? [];
                $fromRanges = $request->fromRange ?? [];
                $toRanges = $request->toRange ?? [];
                $amounts = $request->amount ?? [];
                $rentalTypes = $request->rentalType ?? [];
                $durations = $request->duration ?? [];
            }
            
            // Ensure we have at least one apartment to create
            if (empty($amounts)) {
                throw new \Exception('At least one apartment must be specified');
            }
            
            // Create apartments based on the arrays
            for ($i = 0; $i < count($amounts); $i++) {
                $tenantId = !empty($tenantIds[$i]) ? $tenantIds[$i] : null;
                $fromRange = !empty($fromRanges[$i]) ? $fromRanges[$i] : null;
                $toRange = !empty($toRanges[$i]) ? $toRanges[$i] : null;
                $amount = $amounts[$i];
                $rentalType = $rentalTypes[$i] ?? 'monthly';
                $selectedDuration = !empty($durations[$i]) ? (float) $durations[$i] : null;
                
                $startDate = $fromRange ? Carbon::parse($fromRange) : null;
                $endDate = $toRange ? Carbon::parse($toRange) : null;
                $isOccupied = ($tenantId && $startDate && $endDate) ? 1 : 0;
                $transactionId = (int)mt_rand(1000000, 9999999);
                
                // Store selected duration (decimal months) if provided.
                // Do not derive via diffInMonths() because weekly/daily would become 0.
                $duration = $selectedDuration;
                
                // Set up rental configuration based on rental type
                $rentalConfig = $this->setupRentalConfiguration($rentalType, $amount);
                
                $apartment = Apartment::create([
                    'apartment_id' => $transactionId,
                    'property_id' => $property->property_id,
                    'apartment_type' => $property->property_type ?? 'Standard',
                    'tenant_id' => $tenantId,
                    'user_id' => auth()->user()->user_id,
                    'duration' => $duration,
                    'range_start' => $startDate,
                    'range_end' => $endDate,
                    'amount' => $amount,
                    'occupied' => $isOccupied,
                    'created_at' => now(),
                    // Rental duration fields
                    'supported_rental_types' => $rentalConfig['supported_types'],
                    'default_rental_type' => $rentalType,
                    'hourly_rate' => $rentalConfig['hourly_rate'],
                    'daily_rate' => $rentalConfig['daily_rate'],
                    'weekly_rate' => $rentalConfig['weekly_rate'],
                    'monthly_rate' => $rentalConfig['monthly_rate'],
                    'yearly_rate' => $rentalConfig['yearly_rate'],
                ]);
                
                // Create proforma receipt if tenant is assigned
                if ($apartment->tenant_id) {
                    \App\Models\ProfomaReceipt::create([
                        'user_id' => $property->user_id,
                        'tenant_id' => $apartment->tenant_id,
                        'status' => 3,
                        'transaction_id' => $transactionId,
                        'apartment_id' => $transactionId,
                        'duration' => $apartment->duration,
                    ]);
                }
                
                $createdApartments[] = $apartment;
            }
            
            Log::info('Apartments created successfully:', ['count' => count($createdApartments)]);
            return response()->json([
                'success' => true,
                'messages' => [
                    'message' => count($createdApartments) . ' Apartment(s) Listed Successfully!',
                    'location' => 'listing'
                ],
                'data' => $createdApartments
            ]);
        } catch (\Exception $e) {
            Log::error('Apartment creation failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'messages' => 'Server Error! Cannot create Apartment. Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Add a single apartment to a property (for dashboard modal)
     */
    public function addSingleApartment(SingleApartmentRequest $request): JsonResponse
    {
        try {
            Log::info('Single apartment creation request data:', $request->all());
            $property = Property::where('property_id', $request->propertyId)->firstOrFail();
            
            // Generate unique apartment ID
            do {
                $apartmentId = mt_rand(1000000, 9999999);
            } while (Apartment::where('apartment_id', $apartmentId)->exists());
            
            // Parse dates
            $startDate = Carbon::parse($request->fromRange);
            $endDate = Carbon::parse($request->toRange);
            $isOccupied = ($request->tenantId && $startDate && $endDate) ? 1 : 0;
            
            // Store selected duration (decimal months) from the form.
            // Using diffInMonths() would return 0 for weekly/daily leases.
            $duration = (float) $request->duration;
            
            // Use rental type from request
            $rentalType = $request->rentalType ?? 'monthly';
            
            // Set up rental configuration
            $rentalConfig = $this->setupRentalConfiguration($rentalType, $request->amount);
            
            // Get apartment type from property if not provided
            $apartmentType = $request->apartmentType ?? $property->property_type ?? 'Standard';
            
            $apartment = Apartment::create([
                'apartment_id' => $apartmentId,
                'property_id' => $property->property_id,
                'apartment_type' => $apartmentType,
                'tenant_id' => $request->tenantId,
                'user_id' => auth()->user()->user_id,
                'duration' => $duration,
                'range_start' => $startDate,
                'range_end' => $endDate,
                'amount' => $request->amount,
                'occupied' => $isOccupied,
                'created_at' => now(),
                // Rental duration fields
                'supported_rental_types' => $rentalConfig['supported_types'],
                'default_rental_type' => $rentalType,
                'hourly_rate' => $rentalConfig['hourly_rate'],
                'daily_rate' => $rentalConfig['daily_rate'],
                'weekly_rate' => $rentalConfig['weekly_rate'],
                'monthly_rate' => $rentalConfig['monthly_rate'],
                'yearly_rate' => $rentalConfig['yearly_rate'],
            ]);
            
            // Create proforma receipt if tenant is assigned
            if ($apartment->tenant_id) {
                \App\Models\ProfomaReceipt::create([
                    'user_id' => $property->user_id,
                    'tenant_id' => $apartment->tenant_id,
                    'status' => 3,
                    'transaction_id' => $apartmentId,
                    'apartment_id' => $apartmentId,
                    'duration' => $apartment->duration,
                ]);
            }
            
            Log::info('Single apartment created successfully:', ['apartment_id' => $apartmentId]);
            return response()->json([
                'success' => true,
                'message' => 'Apartment created successfully!',
                'data' => $apartment
            ]);
            
        } catch (\Exception $e) {
            Log::error('Single apartment creation failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Server Error! Cannot create Apartment. Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Determine rental type based on duration value
     */
    private function determineRentalType(float $duration): string
    {
        return match(true) {
            $duration <= 0.03 => 'daily',
            $duration <= 0.04 => 'hourly',
            $duration <= 0.25 => 'weekly',
            $duration <= 1 => 'monthly',
            $duration <= 3 => 'quarterly',
            $duration <= 6 => 'semi_annually',
            $duration <= 12 => 'yearly',
            $duration <= 24 => 'bi_annually',
            default => 'monthly'
        };
    }
    
    /**
     * Set up rental configuration based on rental type and amount
     */
    private function setupRentalConfiguration(string $rentalType, float $amount): array
    {
        $config = [
            'supported_types' => [$rentalType],
            'hourly_rate' => null,
            'daily_rate' => null,
            'weekly_rate' => null,
            'monthly_rate' => null,
            'yearly_rate' => null,
        ];
        
        // Set the specific rate based on rental type
        switch ($rentalType) {
            case 'hourly':
                $config['hourly_rate'] = $amount;
                $config['supported_types'][] = 'daily'; // Also support daily (24 * hourly)
                $config['daily_rate'] = $amount * 24;
                break;
                
            case 'daily':
                $config['daily_rate'] = $amount;
                $config['supported_types'][] = 'weekly'; // Also support weekly (7 * daily)
                $config['weekly_rate'] = $amount * 7;
                break;
                
            case 'weekly':
                $config['weekly_rate'] = $amount;
                $config['supported_types'][] = 'monthly'; // Also support monthly (4.33 * weekly)
                $config['monthly_rate'] = $amount * 4.33;
                break;
                
            case 'monthly':
                $config['monthly_rate'] = $amount;
                // Add all converted types for monthly
                $config['supported_types'] = ['monthly', 'quarterly', 'semi_annually', 'yearly', 'bi_annually'];
                break;
                
            case 'yearly':
                $config['yearly_rate'] = $amount;
                $config['supported_types'][] = 'monthly'; // Also support monthly (yearly / 12)
                $config['monthly_rate'] = $amount / 12;
                break;
                
            case 'quarterly':
            case 'semi_annually':
            case 'bi_annually':
                // These are calculated from monthly, so set monthly rate
                $monthlyRate = match($rentalType) {
                    'quarterly' => $amount / 3,
                    'semi_annually' => $amount / 6,
                    'bi_annually' => $amount / 24,
                };
                $config['monthly_rate'] = $monthlyRate;
                $config['supported_types'] = ['monthly', 'quarterly', 'semi_annually', 'yearly', 'bi_annually'];
                break;
        }
        
        return $config;
    }

    public function switchDashboardMode(Request $request): \Illuminate\Http\JsonResponse
    {
        $mode = $request->input('mode');
        if (!in_array($mode, ['landlord', 'tenant'])) {
            return response()->json(['success' => false, 'message' => 'Invalid mode'], 400);
        }
        session(['dashboard_mode' => $mode]);
        return response()->json(['success' => true, 'message' => 'Dashboard mode switched', 'mode' => $mode]);
    }

    public function userProperty(Request $request)
    {
        if (!auth()->check()) {
            return view('auth.login');
        }
        $userId = auth()->user()->user_id;
        $user = auth()->user();
        $hasProperties = Property::where('user_id', $userId)->exists();
        
        // Handle mode for property managers and regular users
        if (in_array($user->role, [6, 8])) {
            // Property manager - check their dashboard mode preference
            $dashboardMode = session('dashboard_mode', 'landlord');
            
            // If they have explicitly set landlord/tenant mode, use that
            if (in_array($dashboardMode, ['landlord', 'tenant'])) {
                $mode = $dashboardMode;
            } else {
                // Default to landlord mode for their personal properties
                $mode = 'landlord';
            }
        } else {
            // Regular user mode handling
            $mode = session('dashboard_mode');
            if (!$mode) {
                $mode = $hasProperties ? 'landlord' : 'tenant';
                session(['dashboard_mode' => $mode]);
            }
        }
        $myProperties = collect();
        $myApartment = collect();
        if ($mode === 'landlord') {
            $myProperties = Property::where('user_id', $userId)
                ->with(['apartments', 'mainImage'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            // Flatten all apartments from all properties into one collection for statistics
            $myApartment = $myProperties->pluck('apartments')->flatten(1);
        }
        if ($mode === 'tenant') {
            $query = Apartment::where('tenant_id', $userId)
                ->with(['property', 'tenant'])
                ->orderBy('created_at', 'desc');

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('apartment_id', 'like', "%{$search}%")
                      ->orWhere('apartment_type', 'like', "%{$search}%")
                      ->orWhereHas('property', function($q2) use ($search) {
                          $q2->where('address', 'like', "%{$search}%")
                             ->orWhere('state', 'like', "%{$search}%")
                             ->orWhere('lga', 'like', "%{$search}%");
                      });
                });
            }

            $myApartment = $query->paginate(10)->withQueryString();
        }
        $countries = json_decode(File::get(resource_path('/countries.json')), true);
        $locations = $countries[0]['states'] ?? [];
        
        // Get commission transparency data for landlord mode
        $commissionData = [];
        if ($mode === 'landlord') {
            $commissionData = $this->getCommissionTransparencyData($userId);
        }
        
        return view('myProperty', compact('myProperties', 'myApartment', 'locations', 'countries', 'mode', 'hasProperties', 'commissionData'));
    }

    private function generateUniquePropertyId(): int
    {
        do {
            $id = mt_rand(1000000, 9999999);
        } while (Property::where('property_id', $id)->exists());

        return $id;
    }

    /**
     * Get commission transparency data for landlord
     */
    private function getCommissionTransparencyData($userId): array
    {
        // Get landlord's properties
        $propertyIds = Property::where('user_id', $userId)->pluck('property_id');
        
        // Get payments for these properties via apartment -> property_id
        $payments = \App\Models\Payment::whereHas('apartment', function($q) use ($propertyIds) {
                $q->whereIn('property_id', $propertyIds);
            })
            ->where('status', \App\Models\Payment::STATUS_SUCCESS)
            ->with(['apartment.property'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Get commission payments related to these properties through referrals
        // First, find referrals for these properties
        $referralIds = \App\Models\Referral::whereIn('property_id', $propertyIds)->pluck('id');
        
        // Then find commission payments that might be related to these referrals
        // Since we don't have a direct relationship, we'll get payments for users who made referrals for these properties
        $referrerIds = \App\Models\Referral::whereIn('property_id', $propertyIds)->pluck('referrer_id');
        
        $commissionPayments = \App\Models\CommissionPayment::whereIn('user_id', $referrerIds)
            ->with(['marketer'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Calculate commission breakdown for recent payments
        $commissionBreakdown = [];
        foreach ($payments as $payment) {
            $breakdown = $this->calculateCommissionBreakdown($payment);
            if (!empty($breakdown)) {
                $commissionBreakdown[$payment->id] = $breakdown;
            }
        }

        // Get current commission rates for landlord's region
        $landlordRegion = Property::where('user_id', $userId)->first()?->state ?? 'Default';
        $currentRates = \App\Models\CommissionRate::where('region', $landlordRegion)
            ->where('is_active', true)
            ->get()
            ->keyBy('role_id');

        return [
            'recent_payments' => $payments,
            'commission_payments' => $commissionPayments,
            'commission_breakdown' => $commissionBreakdown,
            'current_rates' => $currentRates,
            'landlord_region' => $landlordRegion,
            'transparency_enabled' => true // This could be a user setting
        ];
    }

    /**
     * Calculate commission breakdown for a payment
     */
    private function calculateCommissionBreakdown($payment): array
    {
        // Find referrals related to this payment's property
        $paymentPropertyId = $payment->apartment?->property_id;
        if (!$paymentPropertyId) {
            return [];
        }
        $referrals = \App\Models\Referral::where('property_id', $paymentPropertyId)
            ->with(['referrer', 'referred'])
            ->get();

        if ($referrals->isEmpty()) {
            return [];
        }

        $breakdown = [];
        $totalCommission = 0;

    // Get commission rate for the property's region
    $region = $payment->apartment?->property?->state ?? 'Default';
        $commissionCalculator = app(\App\Services\Commission\MultiTierCommissionCalculator::class);

        foreach ($referrals as $referral) {
            try {
                // Build referral chain
                $referralChain = app(\App\Services\Commission\ReferralChainService::class)
                    ->getReferralHierarchy($referral->id);

                // Calculate commission split
                $commissionSplit = $commissionCalculator->calculateCommissionSplit(
                    $payment->amount * 0.025, // Assuming 2.5% total commission
                    $referralChain,
                    $region
                );

                foreach ($commissionSplit as $tier => $amount) {
                    $breakdown[] = [
                        'tier' => $tier,
                        'amount' => $amount,
                        'percentage' => ($amount / $payment->amount) * 100,
                        'recipient' => $this->getTierRecipient($tier, $referralChain)
                    ];
                    $totalCommission += $amount;
                }
            } catch (\Exception $e) {
                Log::error('Commission breakdown calculation failed: ' . $e->getMessage());
            }
        }

        return [
            'breakdown' => $breakdown,
            'total_commission' => $totalCommission,
            'net_amount' => $payment->amount - $totalCommission,
            'commission_percentage' => ($totalCommission / $payment->amount) * 100
        ];
    }

    /**
     * Get recipient information for a commission tier
     */
    private function getTierRecipient($tier, $referralChain): ?array
    {
        switch ($tier) {
            case 'super_marketer':
                $user = $referralChain['super_marketer'] ?? null;
                break;
            case 'marketer':
                $user = $referralChain['marketer'] ?? null;
                break;
            case 'regional_manager':
                $user = $referralChain['regional_manager'] ?? null;
                break;
            default:
                return null;
        }

        if (!$user) {
            return null;
        }

        return [
            'id' => $user->user_id,
            'name' => $user->first_name . ' ' . $user->last_name,
            'email' => $user->email
        ];
    }

    /**
     * Get commission rate change notifications for landlord
     */
    public function getCommissionRateNotifications(Request $request): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = auth()->user()->user_id;
        
        // Get landlord's region
        $landlordRegion = Property::where('user_id', $userId)->first()?->state ?? 'Default';
        
        // Get recent rate changes in landlord's region (last 30 days)
        $rateChanges = \App\Models\CommissionRate::where('region', $landlordRegion)
            ->where('created_at', '>=', now()->subDays(30))
            ->with('createdBy')
            ->orderBy('created_at', 'desc')
            ->get();

        $notifications = $rateChanges->map(function ($rate) {
            return [
                'id' => $rate->id,
                'message' => "Commission rate updated for {$rate->region} region - Role ID {$rate->role_id}: {$rate->commission_percentage}%",
                'effective_from' => $rate->effective_from->format('Y-m-d H:i:s'),
                'created_at' => $rate->created_at->format('Y-m-d H:i:s'),
                'created_by' => $rate->createdBy ? $rate->createdBy->first_name . ' ' . $rate->createdBy->last_name : 'System'
            ];
        });

        return response()->json([
            'success' => true,
            'notifications' => $notifications
        ]);
    }



    /**
     * Get detailed commission breakdown for a specific payment
     */
    public function getPaymentCommissionDetails(Request $request, $paymentId): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = auth()->user()->user_id;
        
        // Verify the payment belongs to this landlord
        $payment = \App\Models\Payment::where('id', $paymentId)
            ->where('landlord_id', $userId)
            ->with(['apartment.property', 'tenant'])
            ->first();

        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        $commissionBreakdown = $this->calculateCommissionBreakdown($payment);

        return response()->json([
            'success' => true,
            'payment' => [
                'id' => $payment->id,
                'amount' => $payment->amount,
                'property_address' => $payment->property?->address,
                'apartment_type' => $payment->apartment?->apartment_type,
                'tenant_name' => $payment->tenant ? $payment->tenant->first_name . ' ' . $payment->tenant->last_name : 'N/A',
                'payment_date' => $payment->created_at->format('Y-m-d H:i:s')
            ],
            'commission_breakdown' => $commissionBreakdown
        ]);
    }

    /**
     * Show commission transparency dashboard
     */
    public function commissionTransparency(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userId = auth()->user()->user_id;
        
        // Get landlord's properties
        $properties = Property::where('user_id', $userId)->get();
        $propertyIds = $properties->pluck('property_id');

        // Apply filters
        $dateFrom = $request->input('date_from', now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        $propertyId = $request->input('property_id');

        // Normalize date range to full-day boundaries
        $start = Carbon::parse($dateFrom)->startOfDay();
        $end = Carbon::parse($dateTo)->endOfDay();

        // Build payments query via apartment's property filter
        $paymentsQuery = \App\Models\Payment::whereHas('apartment', function($q) use ($propertyIds, $propertyId) {
                $q->whereIn('property_id', $propertyIds);
                if ($propertyId) {
                    $q->where('property_id', $propertyId);
                }
            })
            ->where('status', \App\Models\Payment::STATUS_SUCCESS)
            ->whereBetween('created_at', [$start, $end])
            ->with(['apartment.property', 'tenant']);

        $payments = $paymentsQuery->orderBy('created_at', 'desc')->paginate(25);

        // Calculate commission breakdowns for all payments
        $commissionBreakdowns = [];
        $totalRevenue = 0;
        $totalCommission = 0;

        foreach ($payments as $payment) {
            $breakdown = $this->calculateCommissionBreakdown($payment);
            $commissionBreakdowns[$payment->id] = $breakdown;
            
            $totalRevenue += $payment->amount;
            $totalCommission += $breakdown['total_commission'] ?? 0;
        }

        $netIncome = $totalRevenue - $totalCommission;
        $avgCommissionPercentage = $totalRevenue > 0 ? ($totalCommission / $totalRevenue) * 100 : 0;

        $summary = [
            'total_revenue' => $totalRevenue,
            'total_commission' => $totalCommission,
            'net_income' => $netIncome,
            'avg_commission_percentage' => $avgCommissionPercentage
        ];

        return view('landlord.commission-transparency', compact(
            'payments',
            'properties',
            'commissionBreakdowns',
            'summary'
        ));
    }

    /**
     * Export commission transparency report
     */
    public function exportCommissionReport(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userId = auth()->user()->user_id;
        $format = $request->input('format', 'csv');
        
        // Get filtered data
        $propertyIds = Property::where('user_id', $userId)->pluck('property_id');
        $dateFrom = $request->input('date_from', now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        $propertyId = $request->input('property_id');

        $start = Carbon::parse($dateFrom)->startOfDay();
        $end = Carbon::parse($dateTo)->endOfDay();

        $paymentsQuery = \App\Models\Payment::whereHas('apartment', function($q) use ($propertyIds, $propertyId) {
                $q->whereIn('property_id', $propertyIds);
                if ($propertyId) {
                    $q->where('property_id', $propertyId);
                }
            })
            ->where('status', \App\Models\Payment::STATUS_SUCCESS)
            ->whereBetween('created_at', [$start, $end])
            ->with(['apartment.property', 'tenant']);

        $payments = $paymentsQuery->orderBy('created_at', 'desc')->get();

        // Generate CSV
        $filename = 'commission_report_' . $dateFrom . '_to_' . $dateTo . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Date',
                'Property Address',
                'Apartment Type',
                'Tenant Name',
                'Gross Amount',
                'Total Commission',
                'Commission Percentage',
                'Net Amount',
                'Super Marketer Commission',
                'Marketer Commission',
                'Regional Manager Commission'
            ]);

            foreach ($payments as $payment) {
                $breakdown = $this->calculateCommissionBreakdown($payment);
                $totalCommission = $breakdown['total_commission'] ?? 0;
                $netAmount = $payment->amount - $totalCommission;
                $commissionPercentage = $payment->amount > 0 ? ($totalCommission / $payment->amount) * 100 : 0;

                // Extract individual tier amounts
                $superMarketerAmount = 0;
                $marketerAmount = 0;
                $regionalManagerAmount = 0;

                if (isset($breakdown['breakdown'])) {
                    foreach ($breakdown['breakdown'] as $tier) {
                        switch ($tier['tier']) {
                            case 'super_marketer':
                                $superMarketerAmount = $tier['amount'];
                                break;
                            case 'marketer':
                                $marketerAmount = $tier['amount'];
                                break;
                            case 'regional_manager':
                                $regionalManagerAmount = $tier['amount'];
                                break;
                        }
                    }
                }

                fputcsv($file, [
                    $payment->created_at->format('Y-m-d'),
                    $payment->property?->address ?? 'N/A',
                    $payment->apartment?->apartment_type ?? 'N/A',
                    $payment->tenant ? $payment->tenant->first_name . ' ' . $payment->tenant->last_name : 'N/A',
                    number_format($payment->amount, 2),
                    number_format($totalCommission, 2),
                    number_format($commissionPercentage, 2) . '%',
                    number_format($netAmount, 2),
                    number_format($superMarketerAmount, 2),
                    number_format($marketerAmount, 2),
                    number_format($regionalManagerAmount, 2)
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get commission rate history for landlord's region
     */
    public function getCommissionRateHistory(Request $request): JsonResponse
    {
        try {
            if (!auth()->check()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $userId = auth()->user()->user_id;
            
            // Get landlord's region
            $landlordRegion = Property::where('user_id', $userId)->first()?->state ?? 'Default';
        
        // Get rate history for the region
        $history = \App\Models\CommissionRate::where('region', $landlordRegion)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
            
        // If no records found for specific region, try default region
        if ($history->isEmpty() && $landlordRegion !== 'Default') {
            $history = \App\Models\CommissionRate::where('region', 'Default')
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();
        }

        $formattedHistory = $history->map(function ($rate) {
            $roleNames = [
                5 => 'Marketer',
                6 => 'Regional Manager',
                8 => 'Property Manager',
                9 => 'Super Marketer'
            ];

            return [
                'id' => $rate->id,
                'created_at' => $rate->created_at->format('Y-m-d H:i:s'),
                'role_name' => $roleNames[$rate->role_id] ?? "Role {$rate->role_id}",
                'commission_percentage' => $rate->commission_percentage,
                'effective_from' => $rate->effective_from ? $rate->effective_from->format('Y-m-d H:i:s') : $rate->created_at->format('Y-m-d H:i:s'),
                'created_by' => 'System', // Simplified since createdBy relationship doesn't exist
                'region' => $rate->region
            ];
        });

        return response()->json([
            'success' => true,
            'history' => $formattedHistory->toArray(), // Convert Collection to array
            'region' => $landlordRegion,
            'total_records' => $formattedHistory->count()
        ]);
        
        } catch (\Exception $e) {
           Log::error('Commission Rate History Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to load commission rate history',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    // New helper methods
    public function show(string $propId): \Illuminate\View\View
    {
        if (!auth()->check()) {
            // Redirect, but cast to View to satisfy return type
            return view('auth.login');
        }
        $property = Property::where('property_id', $propId)
            ->with(['apartments.tenant', 'apartments.apartmentType', 'owner', 'agent', 'images'])
            ->firstOrFail();
        $userId = auth()->check() ? auth()->user()->user_id : null;
        
        // Load durations for the form
        $durations = \App\Models\Duration::getActiveOrdered();
        $durationOptions = \App\Models\Duration::getForDropdown();
        
        // Get all properties for this user that have an agent assigned
        $previousAgentIds = Property::where('user_id', $userId)
            ->whereNotNull('agent_id')
            ->pluck('agent_id')
            ->unique()
            ->toArray();
        // Get agent users for those IDs
        $previousAgents = User::whereIn('user_id', $previousAgentIds)
            ->withRole(['property_manager','agent'])
            ->get();
        $apartments = $property->apartments;

        $countries = json_decode(File::get(resource_path('/countries.json')), true);
        $locations = $countries[0]['states'] ?? [];
        return view('property.show', compact('property', 'apartments', 'durations', 'durationOptions', 'previousAgents', 'userId', 'locations', 'countries'));
    }

    public function assignAgent(Request $request, string $propId): JsonResponse
    {
        try {
            // Accept agent_id directly (AJAX), or fallback to manual_agent_id/previous_agent_id (form)
            $agentId = $request->input('agent_id') ?: $request->input('manual_agent_id') ?: $request->input('previous_agent_id');

            if (!$agentId || !is_numeric($agentId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid agent ID provided'
                ], 422);
            }

            // Validate agent exists and is allowed (legacy numeric or modern many-to-many roles)
            $agent = User::where('user_id', $agentId)->first();
            if (!$agent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Agent not found'
                ], 404);
            }

            $agentRoleId = method_exists(User::class, 'getRoleId') ? User::getRoleId('agent') : null;
            $pmRoleId = method_exists(User::class, 'getRoleId') ? User::getRoleId('property_manager') : null;
            $projMgrRoleId = method_exists(User::class, 'getRoleId') ? User::getRoleId('project_manager') : null;
            $legacyRoleNum = is_numeric($agent->role) ? (int) $agent->role : null;
            $legacyRoleStr = strtolower(trim((string) $agent->role));
            $isLegacyAgentNum = in_array($legacyRoleNum, array_filter([$agentRoleId, $pmRoleId, $projMgrRoleId, 4]), true);
            $isLegacyAgentStr = in_array($legacyRoleStr, ['agent', 'property_manager', 'project_manager', 'property_agent', '4'], true);
            $isModernAgent = (method_exists($agent, 'hasRole') && ($agent->hasRole('agent') || $agent->hasRole('property_manager') || $agent->hasRole('project_manager') || $agent->hasRole('property_agent')));
            $wasPreviouslyAssigned = \App\Models\Property::where('agent_id', $agent->user_id)->exists();
            $notAdmin = (int) ($agent->admin ?? 0) === 0;

            if (!($notAdmin && ($isLegacyAgentNum || $isLegacyAgentStr || $isModernAgent || $wasPreviouslyAssigned))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected user is not eligible to be assigned as property manager'
                ], 422);
            }

            $property = Property::where('property_id', $propId)->firstOrFail();
            $property->agent_id = $agent->user_id;
            $property->save();

            return response()->json([
                'success' => true,
                'message' => 'Property manager assigned successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to assign agent:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'messages' => 'Property not found'
            ], 404);
        }
    }

    public function getApartmentDetails(int $apartmentId): JsonResponse
    {
        try {
            $apartment = Apartment::where('apartment_id', $apartmentId)
                ->with(['property', 'tenant'])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => [
                    'apartment' => $apartment,
                    'status' => $apartment->getStatus(),
                    'remaining_days' => $apartment->getRemainingDays(),
                    'formatted_amount' => $apartment->getFormattedAmount()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'messages' => 'Apartment not found'
            ], 404);
        }
    }

    public function showApartment(int $apartmentId): \Illuminate\View\View
    {
        if(!auth()->check()){
            redirect()->to("/login")->send();
        } 
        $apartment = Apartment::where('apartment_id', $apartmentId)
            ->with(['property', 'tenant'])
            ->firstOrFail();
        return view('apartment.show', compact('apartment'));
    }

    public function editApartment(int $apartmentId): \Illuminate\View\View
    {
        $apartment = Apartment::where('apartment_id', $apartmentId)
            ->with('property')
            ->firstOrFail();
            
        // Load durations for the form
        $durations = \App\Models\Duration::getActiveOrdered();
        $durationOptions = \App\Models\Duration::getForDropdown();
        $rentalTypes = \App\Models\Duration::getRentalTypes();
        
        return view('apartment.edit', compact('apartment', 'durations', 'durationOptions', 'rentalTypes'));
    }

    public function updateApartment(Request $request, int $apartmentId): JsonResponse
    {
        try {
            $apartment = Apartment::where('apartment_id', $apartmentId)->firstOrFail();
            
            // Basic apartment fields
            $updateData = [
                'tenant_id' => $request->tenantId,
                'duration' => $request->has('duration') ? (float) $request->duration : $apartment->duration,
                'range_start' => Carbon::parse($request->fromRange),
                'range_end' => Carbon::parse($request->toRange),
                'amount' => $request->amount,
                'occupied' => $request->occupied ? 1 : 0
            ];
            
            // Handle rental duration types if provided
            if ($request->has('rental_types') && is_array($request->rental_types)) {
                $supportedTypes = $request->rental_types;
                $updateData['supported_rental_types'] = $supportedTypes;
                
                // Update individual rates
                foreach (['hourly', 'daily', 'weekly', 'monthly', 'yearly'] as $type) {
                    $rateField = $type . '_rate';
                    if (in_array($type, $supportedTypes) && $request->has($rateField)) {
                        $updateData[$rateField] = $request->$rateField;
                    } else {
                        $updateData[$rateField] = null; // Clear rate if type not supported
                    }
                }
                
                // Set default rental type
                if ($request->has('default_rental_type') && in_array($request->default_rental_type, $supportedTypes)) {
                    $updateData['default_rental_type'] = $request->default_rental_type;
                } else {
                    // Set first supported type as default
                    $updateData['default_rental_type'] = $supportedTypes[0] ?? 'monthly';
                }
            }
            
            $apartment->update($updateData);
            
            return response()->json([
                'success' => true,
                'messages' => 'Apartment updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'messages' => 'Failed to update apartment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyApartment(int $apartmentId): JsonResponse
    {
        try {
            $apartment = Apartment::where('apartment_id', $apartmentId)->firstOrFail();
            $apartment->delete();
            return response()->json([
                'success' => true,
                'messages' => 'Apartment deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'messages' => 'Failed to delete apartment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove agent from property (set agent_id to null)
     */
    public function removeAgent(Request $request, string $propId): JsonResponse
    {
        try {
            $property = Property::where('property_id', $propId)->firstOrFail();
            $property->agent_id = null;
            $property->save();
            return response()->json([
                'success' => true,
                'message' => 'Agent removed successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove agent: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resend a profoma receipt: set status to 2 (new/not viewed) and update transaction_id.
     * Only allowed if previously rejected (status 0).
     */
    public function resendProfoma(Request $request, $id): JsonResponse
    {
        $profoma = \App\Models\ProfomaReceipt::findOrFail($id);
        if ($profoma->status !== \App\Models\ProfomaReceipt::STATUS_REJECTED) {
            return response()->json([
                'success' => false,
                'message' => 'Only rejected profoma receipts can be resent.'
            ], 400);
        }
        $profoma->status = \App\Models\ProfomaReceipt::STATUS_NEW; // 2 = new/not viewed
        $profoma->transaction_id = mt_rand(100000000, 999999999); // new transaction id
        // Ensure duration is set (copy from apartment if missing)
        if (!$profoma->duration) {
            $apartment = \App\Models\Apartment::where('apartment_id', $profoma->apartment_id)->first();
            if ($apartment && $apartment->duration) {
                $profoma->duration = $apartment->duration;
            }
        }
        $profoma->save();
        return response()->json([
            'success' => true,
            'message' => 'Profoma receipt resent successfully.',
            'profoma' => $profoma
        ]);
    }

    public function edit(string $propId): \Illuminate\View\View
    {
        if (!auth()->check()) {
            return view('auth.login');
        }
        $property = Property::where('property_id', $propId)->firstOrFail();
        $countries = json_decode(File::get(resource_path('/countries.json')), true);
        $locations = $countries[0]['states'] ?? [];
        return view('property.edit', compact('property', 'locations', 'countries'));
    }

    public function update(Request $request, string $propId): JsonResponse
    {
        try {
            $property = Property::where('property_id', $propId)->firstOrFail();
            $property->update([
                'prop_type' => $request->propertyType,
                'address' => $request->address,
                'country' => $request->country ?? $property->country ?? 'Nigeria',
                'state' => $request->state,
                'lga' => $request->city,
                'no_of_apartment' => $request->noOfApartment
            ]);
            return response()->json([
                'success' => true,
                'messages' => 'Property updated successfully!',
                'redirect' => url('/dashboard/myproperty')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'messages' => 'Failed to update property: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $propId): JsonResponse
    {
        try {
            $property = Property::where('property_id', $propId)->firstOrFail();
            $property->delete();
            return response()->json([
                'success' => true,
                'messages' => 'Property deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'messages' => 'Failed to delete property: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set profoma receipt status to 2 (sent and not viewed by tenant) for a given apartment.
     */
    public function sendProfomaForApartment(Request $request, $apartmentId): JsonResponse
    {
        try {
            // Resolve apartment by either numeric PK id or public apartment_id
            $apartment = Apartment::find($apartmentId);
            if (!$apartment) {
                $apartment = Apartment::where('apartment_id', $apartmentId)->first();
            }
            if (!$apartment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Apartment not found.'
                ], 404);
            }
            
            $profoma = \App\Models\ProfomaReceipt::where('apartment_id', $apartment->id)->first();
            if (!$profoma) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profoma receipt not found for this apartment.'
                ], 404);
            }
            $profoma->status = 2; // 2 = sent and not viewed by tenant
            $profoma->save();
            return response()->json([
                'success' => true,
                'message' => 'Profoma receipt sent successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send profoma: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notification counts for the current user (profoma, messages, etc.).
     */
    public function getNotificationCounts(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'count' => 0]);
        }
        // Profoma notifications: status 2, 1, or 0 (for this user as tenant or owner)
        $profomaCount = \App\Models\ProfomaReceipt::where(function($q) use ($user) {
            $q->where('tenant_id', $user->user_id)
              ->orWhere('user_id', $user->user_id);
        })->whereIn('status', [0, 1, 2])->count();
        // Placeholder for messages and other notifications
        $messageCount = 0; // TODO: implement message notification count
        $total = $profomaCount + $messageCount;
        return response()->json([
            'success' => true,
            'profoma' => $profomaCount,
            'messages' => $messageCount,
            'total' => $total
        ]);
    }

    /**
     * Mark notifications as seen (set profoma status 2/1/0 to 1 for this user as tenant or owner).
     */
    public function markNotificationsSeen(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false]);
        }
        // Mark all profoma receipts for this user (tenant or owner) with status 2 or 0 as viewed (status 1)
        \App\Models\ProfomaReceipt::where(function($q) use ($user) {
            $q->where('tenant_id', $user->user_id)
              ->orWhere('user_id', $user->user_id);
        })->whereIn('status', [0,2])
          ->update(['status' => 1]);
        // You can add similar logic for messages in the future
        return response()->json(['success' => true]);
    }

    /**
     * AJAXDestroy method
     * TODO: Implement this method
     */
    public function ajaxDestroy(Request $request)
    {
        // TODO: Implement ajaxDestroy functionality
        return response()->json([
            'success' => false,
            'message' => 'Method not implemented yet'
        ]);
    }

    /**
     * API endpoint to get location data (states/cities) for a given country.
     */
    public function getLocationData(Request $request): JsonResponse
    {
        $countryName = $request->input('country', 'Nigeria');
        $countries = json_decode(File::get(resource_path('/countries.json')), true);

        $country = collect($countries)->firstWhere('name', $countryName);

        if (!$country) {
            return response()->json(['states' => []]);
        }

        return response()->json([
            'states' => $country['states'] ?? []
        ]);
    }
}