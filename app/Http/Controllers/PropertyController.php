<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Apartment;
use App\Models\User;
use App\Http\Requests\PropertyRequest;
use App\Http\Requests\ApartmentRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
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
            $locations = File::get(resource_path('/states-and-cities.json'));
            return view('listing', compact('locations'));
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
            $property = Property::create([
                'user_id' => $userId,
                'prop_id' => $this->generateUniquePropertyId(),
                'prop_type' => $request->propertyType,
                'address' => $request->address,
                'state' => $request->state,
                'lga' => $request->city,
                'no_of_apartment' => $request->noOfApartment,
                'created_at' => now() // Removed, use created_at
            ]);
            return response()->json([
                'success' => true,
                'messages' => [
                    'message' => 'Property Listed Successfully!',
                    'more' => true,
                    'propId' => $property->prop_id
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
        // if (!auth()->check()) {
        //     return redirect('/login');
        // }
        try {
            Log::info('Apartment creation request data:', $request->all());
            $property = Property::where('prop_id', $request->propertyId)->firstOrFail();
            $startDate = $request->tenantId ? Carbon::parse($request->fromRange) : null;
            $endDate = null;
            if ($startDate && $request->duration) {
                $endDate = $startDate->copy()->addMonths((int)$request->duration);
            }
            $isOccupied = ($request->tenantId && $request->duration) ? 1 : 0;
            $transactionId = (int)mt_rand(1000000, 9999999);
            $apartment = Apartment::create([
                'apartment_id' => $transactionId, // Generate a unique ID for the apartment  
                'property_id' => $property->prop_id, // Use prop_id instead of id
                'apartment_type' => $request->apartmentType,
                'tenant_id' => $request->tenantId ?: null,
                'user_id' => auth()->user()->user_id,
                'duration' => $request->duration ?: null,
                'range_start' => $startDate,
                'range_end' => $endDate,
                'amount' => $request->price ?: null,
                'occupied' => $isOccupied,
                'created_at' => now()
            ]);
            // Create profoma receipt if tenant is assigned
            if ($apartment->tenant_id) {
                \App\Models\ProfomaReceipt::create([
                    'user_id' => $property->user_id, // property owner
                    'tenant_id' => $apartment->tenant_id,
                    'status' => 3, // 3 = new and not sent by landlord| 2= sent and not viewed by tenant | 1 = viewed by tenant | 0 = rejected by tenant
                    'transaction_id' => $transactionId,
                    'apartment_id' => $transactionId, // Use the same transaction ID
                    'duration' => $apartment->duration, // Set duration from apartment
                ]);
            }
            Log::info('Apartments created successfully:', ['apartments' => $apartment]);
            return response()->json([
                'success' => true,
                'messages' => [
                    'message' => 'Apartment Listed Successfully!',
                    'location' => 'listing'
                ],
                'data' => $apartment
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

    public function switchDashboardMode(Request $request): \Illuminate\Http\JsonResponse
    {
        $mode = $request->input('mode');
        if (!in_array($mode, ['landlord', 'tenant'])) {
            return response()->json(['success' => false, 'message' => 'Invalid mode'], 400);
        }
        session(['dashboard_mode' => $mode]);
        return response()->json(['success' => true, 'message' => 'Dashboard mode switched', 'mode' => $mode]);
    }

    public function userProperty(): \Illuminate\View\View
    {
        if (!auth()->check()) {
            return view('auth.login');
        }
        $userId = auth()->user()->user_id;
        $hasProperties = Property::where('user_id', $userId)->exists();
        $mode = session('dashboard_mode');
        if (!$mode) {
            $mode = $hasProperties ? 'landlord' : 'tenant';
            session(['dashboard_mode' => $mode]);
        }
        $myProperties = collect();
        $myApartment = collect();
        if ($mode === 'landlord') {
            $myProperties = Property::where('user_id', $userId)
                ->with(['apartments'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            // Flatten all apartments from all properties into one collection for statistics
            $myApartment = $myProperties->pluck('apartments')->flatten(1);
        }
        if ($mode === 'tenant') {
            $myApartment = Apartment::where('tenant_id', $userId)
                ->with(['property', 'tenant'])
                ->orderBy('created_at', 'desc')
                ->get();
        }
        $locations = json_decode(File::get(resource_path('/states-and-cities.json')), true);
        
        // Get commission transparency data for landlord mode
        $commissionData = [];
        if ($mode === 'landlord') {
            $commissionData = $this->getCommissionTransparencyData($userId);
        }
        
        return view('myProperty', compact('myProperties', 'myApartment', 'locations', 'mode', 'hasProperties', 'commissionData'));
    }

    private function generateUniquePropertyId(): int
    {
        do {
            $id = mt_rand(1000000, 9999999);
        } while (Property::where('prop_id', $id)->exists());

        return $id;
    }

    /**
     * Get commission transparency data for landlord
     */
    private function getCommissionTransparencyData($userId): array
    {
        // Get landlord's properties
        $propertyIds = Property::where('user_id', $userId)->pluck('prop_id');
        
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
        $propertyIds = $properties->pluck('prop_id');

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
        $propertyIds = Property::where('user_id', $userId)->pluck('prop_id');
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
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = auth()->user()->user_id;
        
        // Get landlord's region
        $landlordRegion = Property::where('user_id', $userId)->first()?->state ?? 'Default';
        
        // Get rate history for the region
        $history = \App\Models\CommissionRate::where('region', $landlordRegion)
            ->with('createdBy')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $formattedHistory = $history->map(function ($rate) {
            $roleNames = [
                5 => 'Marketer',
                6 => 'Regional Manager',
                9 => 'Super Marketer'
            ];

            return [
                'id' => $rate->id,
                'created_at' => $rate->created_at->format('Y-m-d H:i:s'),
                'role_name' => $roleNames[$rate->role_id] ?? "Role {$rate->role_id}",
                'commission_percentage' => $rate->commission_percentage,
                'effective_from' => $rate->effective_from->format('Y-m-d H:i:s'),
                'created_by' => $rate->createdBy ? $rate->createdBy->first_name . ' ' . $rate->createdBy->last_name : 'System',
                'old_rate' => null // This would require tracking previous rates
            ];
        });

        return response()->json([
            'success' => true,
            'history' => $formattedHistory
        ]);
    }

    // New helper methods
    public function show(string $propId): \Illuminate\View\View
    {
        if (!auth()->check()) {
            // Redirect, but cast to View to satisfy return type
            return view('auth.login');
        }
        $property = Property::where('prop_id', $propId)
            ->with(['apartments.tenant', 'owner', 'agent'])
            ->firstOrFail();
        $userId = auth()->check() ? auth()->user()->user_id : null;
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

        $locations = json_decode(File::get(resource_path('/states-and-cities.json')), true);
        return view('property.show', compact('property', 'apartments', 'previousAgents', 'locations'));
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

            $property = Property::where('prop_id', $propId)->firstOrFail();
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
                'message' => 'Failed to assign agent'
            ], 500);
        }
    }

    public function getPropertyDetails(string $propId): JsonResponse
    {
        try {
            $property = Property::where('prop_id', $propId)
                ->with(['apartments.tenant', 'owner'])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => [
                    'property' => $property,
                    'type_name' => $property->getPropertyTypeName(),
                    'full_address' => $property->getFullAddress(),
                    'active_apartments' => $property->hasActiveApartments()
                ]
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
        return view('apartment.edit', compact('apartment'));
    }

    public function updateApartment(Request $request, int $apartmentId): JsonResponse
    {
        try {
            $apartment = Apartment::where('apartment_id', $apartmentId)->firstOrFail();
            $apartment->update([
                'tenant_id' => $request->tenantId,
                'range_start' => Carbon::parse($request->fromRange),
                'range_end' => Carbon::parse($request->toRange),
                'amount' => $request->amount,
                'occupied' => $request->occupied ? 1 : 0
            ]);
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
            $property = Property::where('prop_id', $propId)->firstOrFail();
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
        $property = Property::where('prop_id', $propId)->firstOrFail();
        $locations = json_decode(File::get(resource_path('/states-and-cities.json')), true);
        return view('property.edit', compact('property', 'locations'));
    }

    public function update(Request $request, string $propId): JsonResponse
    {
        try {
            $property = Property::where('prop_id', $propId)->firstOrFail();
            $property->update([
                'prop_type' => $request->propertyType,
                'address' => $request->address,
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
            $property = Property::where('prop_id', $propId)->firstOrFail();
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
            $profoma = \App\Models\ProfomaReceipt::where('apartment_id', $apartmentId)->first();
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
}
