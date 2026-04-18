<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentCalculationServiceInterface;
use App\Models\Apartment;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentApiController extends Controller
{
    protected $paymentCalculationService;

    public function __construct(PaymentCalculationServiceInterface $paymentCalculationService)
    {
        $this->paymentCalculationService = $paymentCalculationService;
    }

    public function index()
    {
        $payments = DB::table('payments')->orderByDesc('created_at')->limit(50)->get();
        return response()->json($payments);
    }

    public function show($id)
    {
        $payment = DB::table('payments')->where('id', $id)->first();
        abort_unless($payment, 404);
        return response()->json($payment);
    }

    public function store(Request $request)
    {
        $data = $request->only(['tenant_id','landlord_id','apartment_id','amount','status','reference']);
        $data['created_at'] = now();
        $data['updated_at'] = now();
        $id = DB::table('payments')->insertGetId($data);
        return response()->json(['id' => $id], 201);
    }

    /**
     * Calculate payment total using centralized calculation service
     * Enhanced for mobile integration with detailed calculation information
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function calculate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'apartment_price' => 'required|numeric|min:0',
            'rental_duration' => 'required|integer|min:1|max:120',
            'pricing_type' => 'nullable|string|in:total,monthly',
            'additional_charges' => 'nullable|array',
            'additional_charges.*' => 'numeric|min:0',
            'mobile_client' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'error_code' => 'VALIDATION_FAILED'
            ], 422);
        }

        try {
            $apartmentPrice = (float) $request->apartment_price;
            $rentalDuration = (int) $request->rental_duration;
            $pricingType = $request->pricing_type ?? 'total';
            $additionalCharges = $request->additional_charges ?? [];
            $isMobileClient = $request->boolean('mobile_client', false);

            // Use centralized calculation service
            if (!empty($additionalCharges)) {
                $result = $this->paymentCalculationService->calculatePaymentTotalWithCharges(
                    $apartmentPrice,
                    $rentalDuration,
                    $pricingType,
                    $additionalCharges
                );
            } else {
                $result = $this->paymentCalculationService->calculatePaymentTotal(
                    $apartmentPrice,
                    $rentalDuration,
                    $pricingType
                );
            }

            if (!$result->isValid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Calculation failed',
                    'error' => $result->errorMessage,
                    'error_code' => 'CALCULATION_FAILED',
                    'pricing_structure' => [
                        'apartment_price' => $apartmentPrice,
                        'rental_duration' => $rentalDuration,
                        'pricing_type' => $pricingType,
                        'additional_charges' => $additionalCharges
                    ]
                ], 400);
            }

            // Enhanced response with mobile optimization
            $responseData = [
                'calculation' => [
                    'total_amount' => $result->totalAmount,
                    'formatted_total' => $result->getFormattedTotal(),
                    'calculation_method' => $result->calculationMethod,
                    'pricing_structure' => [
                        'apartment_price' => $apartmentPrice,
                        'rental_duration' => $rentalDuration,
                        'pricing_type' => $pricingType,
                        'additional_charges' => $additionalCharges
                    ],
                    'calculation_details' => $result->calculationSteps,
                    'is_valid' => $result->isValid
                ],
                'pricing_information' => [
                    'supported_pricing_types' => $this->paymentCalculationService->getSupportedPricingTypes(),
                    'validation_limits' => $this->paymentCalculationService->getValidationLimits(),
                    'pricing_explanation' => [
                        'total' => 'Apartment price represents the complete rental amount (no multiplication by duration)',
                        'monthly' => 'Apartment price represents monthly rent (multiplied by rental duration)'
                    ]
                ]
            ];

            $metaData = [
                'calculation_timestamp' => now()->toISOString(),
                'service_version' => '1.0.0',
                'mobile_optimized' => $isMobileClient,
                'api_version' => 'v1'
            ];

            // Add mobile-specific enhancements
            if ($isMobileClient) {
                $responseData['mobile_features'] = [
                    'formatted_display_amounts' => [
                        'total_amount' => format_money($result->totalAmount)->getSymbol() . number_format($result->totalAmount, 2),
                        'base_rent' => format_money($apartmentPrice)->getSymbol() . number_format($apartmentPrice, 2)
                    ],
                    'calculation_summary' => [
                        'method_description' => $this->getCalculationMethodDescription($result->calculationMethod),
                        'steps_count' => count($result->calculationSteps),
                        'has_additional_charges' => !empty($additionalCharges)
                    ]
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'meta' => $metaData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment calculation failed',
                'error' => $e->getMessage(),
                'error_code' => 'CALCULATION_EXCEPTION',
                'debug_info' => [
                    'apartment_price' => $apartmentPrice ?? null,
                    'rental_duration' => $rentalDuration ?? null,
                    'pricing_type' => $pricingType ?? null
                ]
            ], 500);
        }
    }

    /**
     * Calculate proforma payment total with apartment context
     * Enhanced for mobile integration with comprehensive apartment and pricing information
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function calculateProforma(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'apartment_id' => 'required|integer|exists:apartments,apartment_id',
            'rental_duration' => 'required|integer|min:1|max:120',
            'additional_charges' => 'nullable|array',
            'additional_charges.*' => 'numeric|min:0',
            'mobile_client' => 'nullable|boolean',
            'include_apartment_details' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'error_code' => 'VALIDATION_FAILED'
            ], 422);
        }

        try {
            $apartment = Apartment::with(['property', 'apartmentType'])->find($request->apartment_id);
            
            if (!$apartment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Apartment not found',
                    'error_code' => 'APARTMENT_NOT_FOUND'
                ], 404);
            }

            $rentalDuration = (int) $request->rental_duration;
            $additionalCharges = $request->additional_charges ?? [];
            $isMobileClient = $request->boolean('mobile_client', false);
            $includeApartmentDetails = $request->boolean('include_apartment_details', true);

            // Get apartment pricing configuration
            $apartmentPrice = (float) $apartment->amount;
            $pricingType = $apartment->getPricingType();

            // Use centralized calculation service
            if (!empty($additionalCharges)) {
                $result = $this->paymentCalculationService->calculatePaymentTotalWithCharges(
                    $apartmentPrice,
                    $rentalDuration,
                    $pricingType,
                    $additionalCharges
                );
            } else {
                $result = $this->paymentCalculationService->calculatePaymentTotal(
                    $apartmentPrice,
                    $rentalDuration,
                    $pricingType
                );
            }

            if (!$result->isValid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proforma calculation failed',
                    'error' => $result->errorMessage,
                    'error_code' => 'PROFORMA_CALCULATION_FAILED',
                    'apartment_context' => [
                        'apartment_id' => $apartment->apartment_id,
                        'pricing_type' => $pricingType,
                        'base_price' => $apartmentPrice
                    ]
                ], 400);
            }

            // Build comprehensive response
            $responseData = [
                'proforma' => [
                    'total_amount' => $result->totalAmount,
                    'formatted_total' => $result->getFormattedTotal(),
                    'calculation_method' => $result->calculationMethod,
                    'calculation_details' => $result->calculationSteps,
                    'is_valid' => $result->isValid,
                    'calculation_summary' => $result->getCalculationSummary()
                ],
                'pricing_structure' => [
                    'base_price' => $apartmentPrice,
                    'rental_duration' => $rentalDuration,
                    'pricing_type' => $pricingType,
                    'additional_charges' => $additionalCharges,
                    'pricing_explanation' => $this->getPricingTypeExplanation($pricingType)
                ]
            ];

            // Include apartment details if requested
            if ($includeApartmentDetails) {
                $responseData['apartment'] = [
                    'id' => $apartment->apartment_id,
                    'rent' => $apartment->amount,
                    'pricing_type' => $pricingType,
                    'apartment_type' => $apartment->apartment_type,
                    'bedrooms' => $apartment->bedrooms,
                    'bathrooms' => $apartment->bathrooms,
                    'size' => $apartment->size,
                    'available' => $apartment->available,
                    'address' => $apartment->property->address ?? 'N/A',
                    'property_id' => $apartment->property->property_id ?? null
                ];

                // Add amenities if available
                if ($apartment->amenities) {
                    $responseData['apartment']['amenities'] = $apartment->amenities;
                }
            }

            $metaData = [
                'calculation_timestamp' => now()->toISOString(),
                'service_version' => '1.0.0',
                'mobile_optimized' => $isMobileClient,
                'api_version' => 'v1',
                'proforma_type' => 'apartment_rental'
            ];

            // Add mobile-specific enhancements
            if ($isMobileClient) {
                $responseData['mobile_features'] = [
                    'formatted_display_amounts' => [
                        'total_amount' => format_money($result->totalAmount, $apartment->currency)->getSymbol() . number_format($result->totalAmount, 2),
                        'base_rent' => format_money($apartmentPrice, $apartment->currency)->getSymbol() . number_format($apartmentPrice, 2),
                        'monthly_equivalent' => $pricingType === 'total' && $rentalDuration > 0 
                            ? format_money($result->totalAmount / $rentalDuration, $apartment->currency)->getSymbol() . number_format($result->totalAmount / $rentalDuration, 2) . '/month'
                            : null
                    ],
                    'calculation_breakdown' => [
                        'method_description' => $this->getCalculationMethodDescription($result->calculationMethod),
                        'steps_count' => count($result->calculationSteps),
                        'has_additional_charges' => !empty($additionalCharges),
                        'charges_total' => !empty($additionalCharges) ? array_sum($additionalCharges) : 0
                    ],
                    'apartment_summary' => [
                        'type_display' => $apartment->apartment_type,
                        'location_display' => $apartment->property->address ?? 'Location not specified',
                        'availability_status' => $apartment->available ? 'Available' : 'Not Available'
                    ]
                ];

                // Add pricing recommendations for mobile
                $responseData['mobile_features']['pricing_recommendations'] = [
                    'is_reasonable_duration' => $rentalDuration >= 6 && $rentalDuration <= 24,
                    'duration_suggestion' => $rentalDuration < 6 ? 'Consider longer rental for better rates' : null,
                    'pricing_transparency' => [
                        'base_calculation' => $pricingType === 'total' 
                            ? 'This is a total price for the entire rental period'
                            : 'This is a monthly rate multiplied by ' . $rentalDuration . ' months'
                    ]
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'meta' => $metaData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Proforma calculation failed',
                'error' => $e->getMessage(),
                'error_code' => 'PROFORMA_CALCULATION_EXCEPTION',
                'debug_info' => [
                    'apartment_id' => $request->apartment_id,
                    'rental_duration' => $request->rental_duration
                ]
            ], 500);
        }
    }

    /**
     * Get human-readable description of calculation method
     */
    protected function getCalculationMethodDescription(string $method): string
    {
        $descriptions = [
            'total_price_no_multiplication' => 'Total rental amount (no duration multiplication)',
            'monthly_price_with_duration_multiplication' => 'Monthly rent multiplied by rental duration',
            'total_price_no_multiplication_with_additional_charges' => 'Total rental amount plus additional charges',
            'monthly_price_with_duration_multiplication_with_additional_charges' => 'Monthly rent multiplied by duration plus additional charges'
        ];

        return $descriptions[$method] ?? 'Standard calculation method';
    }

    /**
     * Get explanation for pricing type
     */
    protected function getPricingTypeExplanation(string $pricingType): string
    {
        $explanations = [
            'total' => 'The apartment price represents the complete rental amount for the entire period',
            'monthly' => 'The apartment price represents the monthly rent that will be multiplied by the rental duration'
        ];

        return $explanations[$pricingType] ?? 'Standard pricing calculation';
    }

    /**
     * Mobile-optimized payment calculation endpoint
     * Specifically designed for mobile clients with enhanced features
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function calculateMobile(Request $request): JsonResponse
    {
        // Force mobile client flag to true for this endpoint
        $request->merge(['mobile_client' => true]);
        
        return $this->calculate($request);
    }

    /**
     * Mobile-optimized proforma calculation endpoint
     * Specifically designed for mobile clients with enhanced apartment details
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function calculateProformaMobile(Request $request): JsonResponse
    {
        // Force mobile client and apartment details flags to true for this endpoint
        $request->merge([
            'mobile_client' => true,
            'include_apartment_details' => true
        ]);
        
        return $this->calculateProforma($request);
    }
}
