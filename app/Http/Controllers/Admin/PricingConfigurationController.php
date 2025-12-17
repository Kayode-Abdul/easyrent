<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Apartment;
use App\Models\Property;
use App\Models\AuditLog;
use App\Services\Payment\PaymentCalculationServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PricingConfigurationController extends Controller
{
    protected $paymentCalculationService;

    public function __construct(PaymentCalculationServiceInterface $paymentCalculationService)
    {
        $this->paymentCalculationService = $paymentCalculationService;
    }

    /**
     * Display apartment pricing configurations
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $pricingType = $request->get('pricing_type');
        $propertyId = $request->get('property_id');
        
        // Build query for apartments with pricing configuration
        $query = Apartment::with(['property', 'apartmentType'])
            ->select('apartments.*');
        
        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('apartment_id', 'LIKE', "%{$search}%")
                  ->orWhereHas('property', function($pq) use ($search) {
                      $pq->where('property_name', 'LIKE', "%{$search}%");
                  });
            });
        }
        
        // Apply pricing type filter
        if ($pricingType) {
            $query->where('pricing_type', $pricingType);
        }
        
        // Apply property filter
        if ($propertyId) {
            $query->where('property_id', $propertyId);
        }
        
        $apartments = $query->orderBy('property_id')
            ->orderBy('apartment_id')
            ->paginate(20);
        
        // Get filter options
        $properties = Property::select('property_id', 'address as property_name')
            ->orderBy('address')
            ->get();
        
        $pricingTypes = ['total', 'monthly'];
        
        // Get statistics
        $stats = [
            'total_apartments' => Apartment::count(),
            'total_pricing' => Apartment::where('pricing_type', 'total')->count(),
            'monthly_pricing' => Apartment::where('pricing_type', 'monthly')->count(),
            'configured_apartments' => Apartment::whereNotNull('price_configuration')->count(),
        ];
        
        return view('admin.pricing-configuration.index', compact(
            'apartments', 
            'properties', 
            'pricingTypes',
            'stats',
            'search', 
            'pricingType',
            'propertyId'
        ));
    }

    /**
     * Show form to edit pricing configuration for an apartment
     */
    public function edit(Apartment $apartment)
    {
        $apartment->load(['property', 'apartmentType']);
        
        // Get current configuration or set defaults
        $currentConfig = $apartment->price_configuration ?? [];
        
        return view('admin.pricing-configuration.edit', compact('apartment', 'currentConfig'));
    }

    /**
     * Update pricing configuration for an apartment
     */
    public function update(Request $request, Apartment $apartment)
    {
        $request->validate([
            'pricing_type' => 'required|in:total,monthly',
            'amount' => 'required|numeric|min:0',
            'price_configuration' => 'nullable|array',
            'price_configuration.base_amount' => 'nullable|numeric|min:0',
            'price_configuration.multiplier' => 'nullable|numeric|min:0',
            'price_configuration.description' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();
            
            // Store original values for audit
            $originalData = [
                'pricing_type' => $apartment->pricing_type,
                'amount' => $apartment->amount,
                'price_configuration' => $apartment->price_configuration,
            ];
            
            // Update apartment pricing configuration
            $apartment->pricing_type = $request->pricing_type;
            $apartment->amount = $request->amount;
            
            // Handle price configuration
            $priceConfig = $request->price_configuration ?? [];
            if (!empty($priceConfig)) {
                // Clean up empty values
                $priceConfig = array_filter($priceConfig, function($value) {
                    return $value !== null && $value !== '';
                });
            }
            $apartment->price_configuration = !empty($priceConfig) ? $priceConfig : null;
            
            // Validate the configuration
            if (!$apartment->validatePricingConfiguration()) {
                throw new \Exception('Invalid pricing configuration provided');
            }
            
            $apartment->save();
            
            // Create audit log entry
            $this->logPricingConfigurationChange($apartment, $originalData, [
                'pricing_type' => $apartment->pricing_type,
                'amount' => $apartment->amount,
                'price_configuration' => $apartment->price_configuration,
            ]);
            
            DB::commit();
            
            Log::info('Pricing configuration updated', [
                'apartment_id' => $apartment->apartment_id,
                'property_id' => $apartment->property_id,
                'updated_by' => Auth::id(),
                'changes' => [
                    'pricing_type' => ['from' => $originalData['pricing_type'], 'to' => $apartment->pricing_type],
                    'amount' => ['from' => $originalData['amount'], 'to' => $apartment->amount],
                ]
            ]);
            
            return redirect()
                ->route('admin.pricing-configuration.index')
                ->with('success', 'Pricing configuration updated successfully for Apartment ' . $apartment->apartment_id);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update pricing configuration', [
                'error' => $e->getMessage(),
                'apartment_id' => $apartment->apartment_id,
                'user_id' => Auth::id()
            ]);
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update pricing configuration: ' . $e->getMessage());
        }
    }

    /**
     * Show pricing configuration preview
     */
    public function preview(Request $request)
    {
        $request->validate([
            'apartment_id' => 'required|exists:apartments,apartment_id',
            'pricing_type' => 'required|in:total,monthly',
            'amount' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1|max:60',
        ]);

        try {
            $apartment = Apartment::where('apartment_id', $request->apartment_id)->firstOrFail();
            
            // Calculate payment total using the service
            $result = $this->paymentCalculationService->calculatePaymentTotal(
                $request->amount,
                $request->duration,
                $request->pricing_type
            );
            
            return response()->json([
                'success' => true,
                'calculation' => [
                    'base_amount' => $request->amount,
                    'duration' => $request->duration,
                    'pricing_type' => $request->pricing_type,
                    'total_amount' => $result->totalAmount,
                    'calculation_method' => $result->calculationMethod,
                    'calculation_steps' => $result->calculationSteps,
                ],
                'apartment' => [
                    'id' => $apartment->apartment_id,
                    'property_name' => $apartment->property->property_name ?? 'Unknown Property',
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate preview: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Bulk update pricing configurations
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'apartment_ids' => 'required|array|min:1',
            'apartment_ids.*' => 'exists:apartments,apartment_id',
            'pricing_type' => 'required|in:total,monthly',
            'update_amount' => 'boolean',
            'amount' => 'required_if:update_amount,true|nullable|numeric|min:0',
            'price_configuration' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();
            
            $apartmentIds = $request->apartment_ids;
            $updatedCount = 0;
            $errors = [];
            
            foreach ($apartmentIds as $apartmentId) {
                try {
                    $apartment = Apartment::where('apartment_id', $apartmentId)->firstOrFail();
                    
                    // Store original data for audit
                    $originalData = [
                        'pricing_type' => $apartment->pricing_type,
                        'amount' => $apartment->amount,
                        'price_configuration' => $apartment->price_configuration,
                    ];
                    
                    // Update pricing type
                    $apartment->pricing_type = $request->pricing_type;
                    
                    // Update amount if requested
                    if ($request->update_amount && $request->amount !== null) {
                        $apartment->amount = $request->amount;
                    }
                    
                    // Update price configuration if provided
                    if ($request->price_configuration) {
                        $priceConfig = array_filter($request->price_configuration, function($value) {
                            return $value !== null && $value !== '';
                        });
                        $apartment->price_configuration = !empty($priceConfig) ? $priceConfig : null;
                    }
                    
                    // Validate and save
                    if (!$apartment->validatePricingConfiguration()) {
                        $errors[] = "Invalid configuration for apartment {$apartmentId}";
                        continue;
                    }
                    
                    $apartment->save();
                    
                    // Log the change
                    $this->logPricingConfigurationChange($apartment, $originalData, [
                        'pricing_type' => $apartment->pricing_type,
                        'amount' => $apartment->amount,
                        'price_configuration' => $apartment->price_configuration,
                    ]);
                    
                    $updatedCount++;
                    
                } catch (\Exception $e) {
                    $errors[] = "Failed to update apartment {$apartmentId}: " . $e->getMessage();
                }
            }
            
            DB::commit();
            
            Log::info('Bulk pricing configuration update completed', [
                'updated_count' => $updatedCount,
                'total_requested' => count($apartmentIds),
                'errors_count' => count($errors),
                'updated_by' => Auth::id()
            ]);
            
            $message = "Successfully updated {$updatedCount} apartments";
            if (!empty($errors)) {
                $message .= ". " . count($errors) . " errors occurred.";
            }
            
            return redirect()
                ->back()
                ->with('success', $message)
                ->with('bulk_errors', $errors);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk pricing configuration update failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return redirect()
                ->back()
                ->with('error', 'Bulk update failed: ' . $e->getMessage());
        }
    }

    /**
     * Show audit trail for pricing configuration changes
     */
    public function auditTrail(Request $request)
    {
        $apartmentId = $request->get('apartment_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        
        $query = AuditLog::where('action', 'pricing_configuration_updated')
            ->with('user')
            ->orderBy('created_at', 'desc');
        
        if ($apartmentId) {
            $query->where('auditable_id', $apartmentId);
        }
        
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        
        $auditLogs = $query->paginate(20);
        
        // Get apartments for filter
        $apartments = Apartment::with('property')
            ->select('apartment_id', 'property_id')
            ->orderBy('property_id')
            ->orderBy('apartment_id')
            ->get();
        
        return view('admin.pricing-configuration.audit-trail', compact(
            'auditLogs',
            'apartments',
            'apartmentId',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Log pricing configuration changes for audit trail
     */
    private function logPricingConfigurationChange(Apartment $apartment, array $originalData, array $newData)
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'pricing_configuration_updated',
            'auditable_type' => Apartment::class,
            'auditable_id' => $apartment->apartment_id,
            'old_values' => $originalData,
            'new_values' => $newData,
            'url' => request()->fullUrl(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Get apartment data for AJAX requests
     */
    public function getApartmentData(Request $request)
    {
        $apartmentId = $request->get('apartment_id');
        
        if (!$apartmentId) {
            return response()->json(['error' => 'Apartment ID required'], 400);
        }
        
        try {
            $apartment = Apartment::with(['property', 'apartmentType'])
                ->where('apartment_id', $apartmentId)
                ->firstOrFail();
            
            return response()->json([
                'success' => true,
                'apartment' => [
                    'id' => $apartment->apartment_id,
                    'property_name' => $apartment->property->property_name ?? 'Unknown Property',
                    'apartment_type' => $apartment->apartment_type,
                    'amount' => $apartment->amount,
                    'pricing_type' => $apartment->getPricingType(),
                    'price_configuration' => $apartment->price_configuration,
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Apartment not found'
            ], 404);
        }
    }
}