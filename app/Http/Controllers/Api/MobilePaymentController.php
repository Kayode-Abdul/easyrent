<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\ApartmentInvitation;
use App\Services\Payment\PaymentIntegrationService;
use App\Services\Session\SessionManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class MobilePaymentController extends Controller
{
    protected $paymentService;
    protected $sessionManager;
    protected $paymentCalculationService;

    public function __construct(
        PaymentIntegrationService $paymentService,
        SessionManagerInterface $sessionManager,
        \App\Services\Payment\PaymentCalculationServiceInterface $paymentCalculationService
    ) {
        $this->paymentService = $paymentService;
        $this->sessionManager = $sessionManager;
        $this->paymentCalculationService = $paymentCalculationService;
    }

    /**
     * Get payment details
     */
    public function show(Request $request, int $paymentId): JsonResponse
    {
        try {
            $payment = Payment::with(['tenant', 'landlord', 'apartment.property'])
                ->find($paymentId);

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found',
                    'error_code' => 'PAYMENT_NOT_FOUND'
                ], 404);
            }

            // Check if user has access to this payment
            $user = $request->user();
            if ($user && $payment->tenant_id !== $user->user_id && $payment->landlord_id !== $user->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view this payment',
                    'error_code' => 'UNAUTHORIZED'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'payment' => [
                        'id' => $payment->id,
                        'reference' => $payment->payment_reference,
                        'transaction_id' => $payment->transaction_id,
                        'amount' => $payment->amount,
                        'status' => $payment->status,
                        'payment_method' => $payment->payment_method,
                        'duration' => $payment->duration,
                        'move_in_date' => $payment->move_in_date,
                        'additional_notes' => $payment->additional_notes,
                        'paid_at' => $payment->paid_at,
                        'created_at' => $payment->created_at
                    ],
                    'apartment' => [
                        'id' => $payment->apartment->apartment_id,
                        'rent' => $payment->apartment->rent,
                        'apartment_type' => $payment->apartment->apartment_type,
                        'address' => $payment->apartment->property->address
                    ],
                    'tenant' => $payment->tenant ? [
                        'name' => $payment->tenant->first_name . ' ' . $payment->tenant->last_name,
                        'email' => $payment->tenant->email
                    ] : null,
                    'landlord' => [
                        'name' => $payment->landlord->first_name . ' ' . $payment->landlord->last_name,
                        'email' => $payment->landlord->email
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Initialize payment for mobile
     */
    public function initializePayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|integer|exists:payments,id',
            'payment_method' => 'required|string|in:paystack,flutterwave,bank_transfer',
            'callback_url' => 'nullable|url'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $payment = Payment::find($request->payment_id);
            $user = $request->user();

            // Verify user can make this payment
            if ($payment->tenant_id !== $user->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to make this payment',
                    'error_code' => 'UNAUTHORIZED'
                ], 403);
            }

            // Check if payment is still pending
            if ($payment->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment is not in pending status',
                    'error_code' => 'INVALID_PAYMENT_STATUS'
                ], 409);
            }

            // Initialize payment with gateway
            $paymentData = [
                'amount' => $payment->amount,
                'email' => $user->email,
                'reference' => $payment->payment_reference,
                'callback_url' => $request->callback_url ?? config('app.url') . '/api/v1/mobile/payments/callback',
                'metadata' => [
                    'payment_id' => $payment->id,
                    'user_id' => $user->user_id,
                    'apartment_id' => $payment->apartment_id,
                    'source' => 'mobile_app'
                ]
            ];

            $gatewayResponse = $this->paymentService->initializePayment($paymentData, $request->payment_method);

            if (!$gatewayResponse['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment initialization failed',
                    'error_code' => 'PAYMENT_INIT_FAILED',
                    'gateway_error' => $gatewayResponse['message'] ?? 'Unknown error'
                ], 500);
            }

            // Update payment with gateway reference
            $payment->update([
                'payment_method' => $request->payment_method,
                'gateway_reference' => $gatewayResponse['data']['reference'] ?? null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment initialized successfully',
                'data' => [
                    'payment_id' => $payment->id,
                    'gateway_url' => $gatewayResponse['data']['authorization_url'] ?? null,
                    'reference' => $gatewayResponse['data']['reference'] ?? $payment->payment_reference,
                    'amount' => $payment->amount,
                    'payment_method' => $request->payment_method
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment initialization failed: ' . $e->getMessage(),
                'error_code' => 'PAYMENT_INIT_EXCEPTION'
            ], 500);
        }
    }

    /**
     * Handle payment callback from gateway
     */
    public function paymentCallback(Request $request): JsonResponse
    {
        try {
            $reference = $request->get('reference') ?? $request->get('trxref');
            
            if (!$reference) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment reference is required',
                    'error_code' => 'MISSING_REFERENCE'
                ], 400);
            }

            // Find payment by reference
            $payment = Payment::where('payment_reference', $reference)
                ->orWhere('gateway_reference', $reference)
                ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found',
                    'error_code' => 'PAYMENT_NOT_FOUND'
                ], 404);
            }

            // Verify payment with gateway
            $verificationResult = $this->paymentService->verifyPayment($reference, $payment->payment_method);

            if (!$verificationResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment verification failed',
                    'error_code' => 'VERIFICATION_FAILED',
                    'details' => $verificationResult['message'] ?? 'Unknown error'
                ], 400);
            }

            // Process successful payment
            if ($verificationResult['data']['status'] === 'success') {
                $result = $this->paymentService->processSuccessfulPayment($payment, $verificationResult['data']);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'data' => [
                        'payment' => [
                            'id' => $payment->id,
                            'reference' => $payment->payment_reference,
                            'amount' => $payment->amount,
                            'status' => $payment->fresh()->status,
                            'paid_at' => $payment->fresh()->paid_at
                        ],
                        'apartment_assigned' => $result['apartment_assigned'] ?? false,
                        'emails_sent' => $result['emails_sent'] ?? false
                    ]
                ]);
            } else {
                // Payment failed
                $payment->update([
                    'status' => 'failed',
                    'gateway_response' => json_encode($verificationResult['data'])
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Payment was not successful',
                    'error_code' => 'PAYMENT_FAILED',
                    'data' => [
                        'payment_id' => $payment->id,
                        'status' => 'failed'
                    ]
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment callback processing failed: ' . $e->getMessage(),
                'error_code' => 'CALLBACK_PROCESSING_FAILED'
            ], 500);
        }
    }

    /**
     * Get user's payment history
     */
    public function getUserPayments(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $perPage = min($request->get('per_page', 15), 100);

            $payments = Payment::with(['apartment.property'])
                ->where(function ($query) use ($user) {
                    $query->where('tenant_id', $user->user_id)
                          ->orWhere('landlord_id', $user->user_id);
                })
                ->when($request->get('status'), function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $payments->items(),
                'pagination' => [
                    'current_page' => $payments->currentPage(),
                    'per_page' => $payments->perPage(),
                    'total' => $payments->total(),
                    'last_page' => $payments->lastPage(),
                    'has_more' => $payments->hasMorePages()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel pending payment
     */
    public function cancelPayment(Request $request, int $paymentId): JsonResponse
    {
        try {
            $payment = Payment::find($paymentId);
            $user = $request->user();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found',
                    'error_code' => 'PAYMENT_NOT_FOUND'
                ], 404);
            }

            // Verify user can cancel this payment
            if ($payment->tenant_id !== $user->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to cancel this payment',
                    'error_code' => 'UNAUTHORIZED'
                ], 403);
            }

            // Check if payment can be cancelled
            if ($payment->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending payments can be cancelled',
                    'error_code' => 'INVALID_PAYMENT_STATUS'
                ], 409);
            }

            $payment->update(['status' => 'cancelled']);

            return response()->json([
                'success' => true,
                'message' => 'Payment cancelled successfully',
                'data' => [
                    'payment_id' => $payment->id,
                    'status' => 'cancelled'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate payment preview for mobile clients
     * Enhanced with comprehensive pricing structure information and mobile-optimized features
     */
    public function calculatePaymentPreview(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'apartment_id' => 'required|integer|exists:apartments,apartment_id',
            'rental_duration' => 'required|integer|min:1|max:120',
            'additional_charges' => 'nullable|array',
            'additional_charges.*' => 'numeric|min:0',
            'include_pricing_details' => 'nullable|boolean',
            'include_calculation_audit' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'error_code' => 'VALIDATION_FAILED',
                'mobile_error_handling' => [
                    'user_friendly_message' => 'Please check your input values and try again',
                    'field_errors' => $this->formatValidationErrorsForMobile($validator->errors()),
                    'retry_suggestions' => [
                        'Check that apartment ID is valid',
                        'Ensure rental duration is between 1 and 120 months',
                        'Verify additional charges are positive numbers'
                    ]
                ]
            ], 422);
        }

        try {
            $apartment = \App\Models\Apartment::with(['property', 'apartmentType'])->find($request->apartment_id);
            
            if (!$apartment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Apartment not found',
                    'error_code' => 'APARTMENT_NOT_FOUND',
                    'mobile_error_handling' => [
                        'user_friendly_message' => 'The apartment you\'re looking for is not available',
                        'suggested_actions' => [
                            'Check if the apartment ID is correct',
                            'Browse available apartments',
                            'Contact support if you believe this is an error'
                        ]
                    ]
                ], 404);
            }

            if (!$apartment->available) {
                return response()->json([
                    'success' => false,
                    'message' => 'Apartment is not available',
                    'error_code' => 'APARTMENT_UNAVAILABLE',
                    'mobile_error_handling' => [
                        'user_friendly_message' => 'This apartment is no longer available for rent',
                        'suggested_actions' => [
                            'Browse similar available apartments',
                            'Set up alerts for when this apartment becomes available',
                            'Contact the landlord for more information'
                        ]
                    ]
                ], 409);
            }

            $rentalDuration = (int) $request->rental_duration;
            $additionalCharges = $request->additional_charges ?? [];
            $includePricingDetails = $request->boolean('include_pricing_details', true);
            $includeCalculationAudit = $request->boolean('include_calculation_audit', false);

            // Get apartment pricing configuration
            $apartmentPrice = (float) $apartment->amount;
            $pricingType = $apartment->getPricingType();

            // Use centralized calculation service with enhanced error handling
            try {
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
            } catch (\Exception $calculationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment calculation service error',
                    'error' => $calculationException->getMessage(),
                    'error_code' => 'CALCULATION_SERVICE_ERROR',
                    'mobile_error_handling' => [
                        'user_friendly_message' => 'Unable to calculate payment at this time',
                        'suggested_actions' => [
                            'Try again in a few moments',
                            'Check your internet connection',
                            'Contact support if the problem persists'
                        ]
                    ]
                ], 500);
            }

            if (!$result->isValid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment calculation failed',
                    'error' => $result->errorMessage,
                    'error_code' => 'CALCULATION_FAILED',
                    'apartment_context' => [
                        'apartment_id' => $apartment->apartment_id,
                        'pricing_type' => $pricingType,
                        'base_price' => $apartmentPrice
                    ],
                    'mobile_error_handling' => [
                        'user_friendly_message' => 'Unable to calculate payment for this apartment',
                        'technical_details' => $result->errorMessage,
                        'suggested_actions' => [
                            'Try a different rental duration',
                            'Remove additional charges and try again',
                            'Contact support for assistance'
                        ]
                    ]
                ], 400);
            }

            // Build comprehensive mobile-optimized response
            $responseData = [
                'payment_preview' => [
                    'total_amount' => $result->totalAmount,
                    'formatted_total' => $result->getFormattedTotal(),
                    'calculation_method' => $result->calculationMethod,
                    'pricing_breakdown' => [
                        'base_rent' => $apartmentPrice,
                        'rental_duration' => $rentalDuration,
                        'pricing_type' => $pricingType,
                        'additional_charges' => $additionalCharges,
                        'additional_charges_total' => array_sum($additionalCharges),
                        'calculation_steps' => $includeCalculationAudit ? $result->calculationSteps : []
                    ],
                    'calculation_summary' => $result->getCalculationSummary()
                ],
                'apartment' => [
                    'id' => $apartment->apartment_id,
                    'rent' => $apartment->amount,
                    'apartment_type' => $apartment->apartment_type,
                    'bedrooms' => $apartment->bedrooms,
                    'bathrooms' => $apartment->bathrooms,
                    'size' => $apartment->size,
                    'address' => $apartment->property->address ?? 'N/A',
                    'pricing_type' => $pricingType,
                    'available' => $apartment->available
                ],
                'mobile_features' => [
                    'formatted_display_amounts' => [
                        'total_amount' => '₦' . number_format($result->totalAmount, 2),
                        'base_rent' => '₦' . number_format($apartmentPrice, 2),
                        'monthly_equivalent' => $pricingType === 'total' && $rentalDuration > 0 
                            ? '₦' . number_format($result->totalAmount / $rentalDuration, 2) . '/month'
                            : null,
                        'additional_charges_total' => !empty($additionalCharges) 
                            ? '₦' . number_format(array_sum($additionalCharges), 2)
                            : null
                    ],
                    'calculation_explanation' => [
                        'method_description' => $this->getCalculationMethodDescription($result->calculationMethod),
                        'pricing_explanation' => $this->getPricingTypeExplanation($pricingType),
                        'steps_count' => count($result->calculationSteps),
                        'has_additional_charges' => !empty($additionalCharges),
                        'calculation_transparency' => [
                            'base_calculation' => $pricingType === 'total' 
                                ? "₦{$apartmentPrice} (total for {$rentalDuration} months)"
                                : "₦{$apartmentPrice} × {$rentalDuration} months = ₦" . number_format($apartmentPrice * $rentalDuration, 2),
                            'additional_charges_breakdown' => $this->formatAdditionalChargesForMobile($additionalCharges)
                        ]
                    ],
                    'user_experience' => [
                        'payment_affordability' => [
                            'monthly_cost' => $pricingType === 'total' && $rentalDuration > 0 
                                ? round($result->totalAmount / $rentalDuration, 2)
                                : ($pricingType === 'monthly' ? $apartmentPrice : null),
                            'affordability_rating' => $this->calculateAffordabilityRating($result->totalAmount, $rentalDuration),
                            'cost_comparison' => [
                                'per_month' => $rentalDuration > 0 ? round($result->totalAmount / $rentalDuration, 2) : null,
                                'per_week' => $rentalDuration > 0 ? round($result->totalAmount / ($rentalDuration * 4.33), 2) : null,
                                'per_day' => $rentalDuration > 0 ? round($result->totalAmount / ($rentalDuration * 30), 2) : null
                            ]
                        ],
                        'rental_recommendations' => [
                            'optimal_duration' => $this->getOptimalRentalDuration($apartmentPrice, $pricingType),
                            'cost_savings_tips' => $this->getCostSavingsTips($pricingType, $rentalDuration, $additionalCharges)
                        ]
                    ]
                ]
            ];

            // Add detailed pricing structure information if requested
            if ($includePricingDetails) {
                $responseData['pricing_structure_details'] = [
                    'supported_pricing_types' => $this->paymentCalculationService->getSupportedPricingTypes(),
                    'validation_limits' => $this->paymentCalculationService->getValidationLimits(),
                    'current_apartment_configuration' => [
                        'pricing_type' => $pricingType,
                        'base_price' => $apartmentPrice,
                        'price_configuration' => $apartment->price_configuration ?? null
                    ],
                    'calculation_methodology' => [
                        'total_pricing' => 'The apartment price represents the complete rental amount for the entire period (no multiplication by duration)',
                        'monthly_pricing' => 'The apartment price represents the monthly rent that will be multiplied by the rental duration',
                        'additional_charges' => 'Any additional charges are added to the base calculation regardless of pricing type'
                    ]
                ];
            }

            $metaData = [
                'calculation_timestamp' => now()->toISOString(),
                'service_version' => '1.0.0',
                'mobile_optimized' => true,
                'api_version' => 'v1',
                'calculation_service_used' => 'PaymentCalculationService',
                'pricing_structure_transparency' => true,
                'mobile_error_handling_enabled' => true
            ];

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'meta' => $metaData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment preview calculation failed: ' . $e->getMessage(),
                'error_code' => 'PREVIEW_CALCULATION_EXCEPTION',
                'mobile_error_handling' => [
                    'user_friendly_message' => 'Something went wrong while calculating your payment',
                    'technical_details' => $e->getMessage(),
                    'suggested_actions' => [
                        'Try refreshing the app',
                        'Check your internet connection',
                        'Contact support if the problem continues'
                    ],
                    'support_info' => [
                        'error_reference' => uniqid('mobile_error_'),
                        'timestamp' => now()->toISOString()
                    ]
                ]
            ], 500);
        }
    }

    /**
     * Validate payment calculation before processing
     */
    public function validatePaymentCalculation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|integer|exists:payments,id',
            'expected_amount' => 'required|numeric|min:0'
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
            $payment = Payment::with(['apartment'])->find($request->payment_id);
            $user = $request->user();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found',
                    'error_code' => 'PAYMENT_NOT_FOUND'
                ], 404);
            }

            // Verify user can validate this payment
            if ($payment->tenant_id !== $user->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to validate this payment',
                    'error_code' => 'UNAUTHORIZED'
                ], 403);
            }

            $expectedAmount = (float) $request->expected_amount;

            // Recalculate using centralized service to verify
            $apartmentPrice = (float) $payment->apartment->amount;
            $pricingType = $payment->apartment->getPricingType();
            $rentalDuration = $payment->duration;

            $result = $this->paymentCalculationService->calculatePaymentTotal(
                $apartmentPrice,
                $rentalDuration,
                $pricingType
            );

            if (!$result->isValid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment validation calculation failed',
                    'error' => $result->errorMessage,
                    'error_code' => 'VALIDATION_CALCULATION_FAILED'
                ], 400);
            }

            $calculatedAmount = $result->totalAmount;
            $storedAmount = (float) $payment->amount;

            // Check for discrepancies
            $amountMatches = abs($calculatedAmount - $expectedAmount) < 0.01;
            $storedMatches = abs($storedAmount - $expectedAmount) < 0.01;
            $calculationMatches = abs($calculatedAmount - $storedAmount) < 0.01;

            return response()->json([
                'success' => true,
                'data' => [
                    'validation' => [
                        'payment_id' => $payment->id,
                        'expected_amount' => $expectedAmount,
                        'calculated_amount' => $calculatedAmount,
                        'stored_amount' => $storedAmount,
                        'amount_matches_expected' => $amountMatches,
                        'stored_matches_expected' => $storedMatches,
                        'calculation_matches_stored' => $calculationMatches,
                        'validation_passed' => $amountMatches && $storedMatches && $calculationMatches,
                        'calculation_method' => $result->calculationMethod
                    ],
                    'discrepancies' => [
                        'expected_vs_calculated' => $expectedAmount - $calculatedAmount,
                        'expected_vs_stored' => $expectedAmount - $storedAmount,
                        'calculated_vs_stored' => $calculatedAmount - $storedAmount
                    ]
                ],
                'meta' => [
                    'validation_timestamp' => now()->toISOString(),
                    'service_version' => '1.0.0'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment validation failed: ' . $e->getMessage(),
                'error_code' => 'VALIDATION_EXCEPTION'
            ], 500);
        }
    }

    /**
     * Get human-readable description of calculation method for mobile clients
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
     * Get explanation for pricing type for mobile clients
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
     * Format validation errors for mobile clients
     */
    protected function formatValidationErrorsForMobile($errors): array
    {
        $formattedErrors = [];
        
        foreach ($errors->toArray() as $field => $messages) {
            $formattedErrors[$field] = [
                'messages' => $messages,
                'user_friendly_message' => $this->getUserFriendlyValidationMessage($field, $messages[0] ?? '')
            ];
        }
        
        return $formattedErrors;
    }

    /**
     * Get user-friendly validation message for mobile clients
     */
    protected function getUserFriendlyValidationMessage(string $field, string $message): string
    {
        $friendlyMessages = [
            'apartment_id' => 'Please select a valid apartment',
            'rental_duration' => 'Rental duration must be between 1 and 120 months',
            'additional_charges' => 'Additional charges must be positive numbers',
            'additional_charges.*' => 'Each additional charge must be a positive number'
        ];

        return $friendlyMessages[$field] ?? $message;
    }

    /**
     * Format additional charges breakdown for mobile display
     */
    protected function formatAdditionalChargesForMobile(array $additionalCharges): array
    {
        if (empty($additionalCharges)) {
            return [];
        }

        $breakdown = [];
        foreach ($additionalCharges as $index => $charge) {
            $breakdown[] = [
                'index' => $index,
                'amount' => $charge,
                'formatted_amount' => '₦' . number_format($charge, 2),
                'description' => "Additional charge " . ($index + 1)
            ];
        }

        return $breakdown;
    }

    /**
     * Calculate affordability rating for mobile users
     */
    protected function calculateAffordabilityRating(float $totalAmount, int $rentalDuration): array
    {
        $monthlyEquivalent = $rentalDuration > 0 ? $totalAmount / $rentalDuration : $totalAmount;
        
        // Define affordability thresholds (in Naira)
        $thresholds = [
            'budget' => 50000,      // Under 50k/month
            'moderate' => 150000,   // 50k-150k/month
            'premium' => 500000,    // 150k-500k/month
            'luxury' => 1000000     // 500k-1M/month
        ];

        if ($monthlyEquivalent <= $thresholds['budget']) {
            $rating = 'budget_friendly';
            $description = 'Very affordable option';
        } elseif ($monthlyEquivalent <= $thresholds['moderate']) {
            $rating = 'moderate';
            $description = 'Reasonably priced';
        } elseif ($monthlyEquivalent <= $thresholds['premium']) {
            $rating = 'premium';
            $description = 'Premium pricing';
        } elseif ($monthlyEquivalent <= $thresholds['luxury']) {
            $rating = 'luxury';
            $description = 'Luxury pricing';
        } else {
            $rating = 'ultra_luxury';
            $description = 'Ultra-luxury pricing';
        }

        return [
            'rating' => $rating,
            'description' => $description,
            'monthly_equivalent' => $monthlyEquivalent,
            'formatted_monthly' => '₦' . number_format($monthlyEquivalent, 2) . '/month'
        ];
    }

    /**
     * Get optimal rental duration recommendation
     */
    protected function getOptimalRentalDuration(float $apartmentPrice, string $pricingType): array
    {
        if ($pricingType === 'total') {
            return [
                'recommendation' => 'Consider the full rental period as this is a total price',
                'optimal_range' => 'N/A - Total pricing',
                'reasoning' => 'This apartment uses total pricing, so the duration is already factored into the price'
            ];
        }

        // For monthly pricing, suggest optimal ranges
        $recommendations = [
            'short_term' => [
                'range' => '1-6 months',
                'description' => 'Good for temporary stays or trial periods'
            ],
            'medium_term' => [
                'range' => '6-12 months',
                'description' => 'Balanced option for most renters'
            ],
            'long_term' => [
                'range' => '12+ months',
                'description' => 'Best value for extended stays'
            ]
        ];

        return [
            'recommendation' => 'Consider 6-12 months for the best balance',
            'options' => $recommendations,
            'reasoning' => 'Longer rentals often provide better value and stability'
        ];
    }

    /**
     * Get cost savings tips for mobile users
     */
    protected function getCostSavingsTips(string $pricingType, int $rentalDuration, array $additionalCharges): array
    {
        $tips = [];

        if ($pricingType === 'monthly' && $rentalDuration < 12) {
            $tips[] = [
                'tip' => 'Consider a longer rental period',
                'description' => 'Longer rentals often come with better rates',
                'potential_savings' => 'Up to 10-15% savings'
            ];
        }

        if (!empty($additionalCharges)) {
            $tips[] = [
                'tip' => 'Review additional charges',
                'description' => 'Some additional charges might be negotiable',
                'potential_savings' => '₦' . number_format(array_sum($additionalCharges) * 0.1, 2) . ' or more'
            ];
        }

        if ($rentalDuration >= 12) {
            $tips[] = [
                'tip' => 'Ask about annual payment discounts',
                'description' => 'Many landlords offer discounts for upfront annual payments',
                'potential_savings' => '5-10% discount possible'
            ];
        }

        $tips[] = [
            'tip' => 'Compare similar properties',
            'description' => 'Check other apartments in the same area for better deals',
            'potential_savings' => 'Market rate comparison'
        ];

        return $tips;
    }
}