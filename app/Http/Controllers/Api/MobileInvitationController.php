<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApartmentInvitation;
use App\Models\Apartment;
use App\Models\Property;
use App\Services\Session\SessionManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class MobileInvitationController extends Controller
{
    protected $sessionManager;
    protected $paymentCalculationService;

    public function __construct(
        SessionManagerInterface $sessionManager,
        \App\Services\Payment\PaymentCalculationServiceInterface $paymentCalculationService
    ) {
        $this->sessionManager = $sessionManager;
        $this->paymentCalculationService = $paymentCalculationService;
    }

    /**
     * Get apartment invitation details by token
     */
    public function show(Request $request, string $token): JsonResponse
    {
        try {
            $invitation = ApartmentInvitation::where('invitation_token', $token)
                ->where('expires_at', '>', now())
                ->with(['apartment.property.owner'])
                ->first();

            if (!$invitation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invitation not found or expired',
                    'error_code' => 'INVITATION_NOT_FOUND'
                ], 404);
            }

            // Track access
            $invitation->increment('access_count');
            $invitation->update(['last_accessed_at' => now()]);

            // Store session data for unauthenticated users
            $sessionData = [
                'invitation_token' => $token,
                'apartment_id' => $invitation->apartment_id,
                'access_timestamp' => now()->toISOString(),
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip()
            ];

            $this->sessionManager->storeInvitationContext($token, $sessionData);

            return response()->json([
                'success' => true,
                'data' => [
                    'invitation' => [
                        'token' => $invitation->invitation_token,
                        'expires_at' => $invitation->expires_at,
                        'access_count' => $invitation->access_count,
                        'created_at' => $invitation->created_at
                    ],
                    'apartment' => [
                        'id' => $invitation->apartment->apartment_id,
                        'rent' => $invitation->apartment->amount,
                        'duration' => $invitation->apartment->duration,
                        'apartment_type' => $invitation->apartment->apartment_type,
                        'bedrooms' => $invitation->apartment->bedrooms,
                        'bathrooms' => $invitation->apartment->bathrooms,
                        'size' => $invitation->apartment->size,
                        'description' => $invitation->apartment->description,
                        'photos' => $invitation->apartment->photos ? json_decode($invitation->apartment->photos) : [],
                        'amenities' => $invitation->apartment->amenities,
                        'available' => $invitation->apartment->available,
                        'pricing_type' => $invitation->apartment->getPricingType()
                    ],
                    'property' => [
                        'id' => $invitation->apartment->property->property_id,
                        'address' => $invitation->apartment->property->address,
                        'state' => $invitation->apartment->property->state,
                        'lga' => $invitation->apartment->property->lga,
                        'prop_type' => $invitation->apartment->property->prop_type
                    ],
                    'landlord' => [
                        'name' => $invitation->apartment->property->owner->first_name . ' ' . $invitation->apartment->property->owner->last_name,
                        'email' => $invitation->apartment->property->owner->email,
                        'phone' => $invitation->apartment->property->owner->phone
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve invitation: ' . $e->getMessage(),
                'error_code' => 'INVITATION_RETRIEVAL_FAILED'
            ], 500);
        }
    }

    /**
     * Apply for apartment via mobile
     */
    public function apply(Request $request, string $token): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'duration' => 'required|integer|min:1|max:24',
            'move_in_date' => 'required|date|after:today',
            'additional_notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $invitation = ApartmentInvitation::where('invitation_token', $token)
                ->where('expires_at', '>', now())
                ->with(['apartment.property'])
                ->first();

            if (!$invitation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invitation not found or expired',
                    'error_code' => 'INVITATION_NOT_FOUND'
                ], 404);
            }

            // Check if apartment is still available
            if (!$invitation->apartment->available) {
                return response()->json([
                    'success' => false,
                    'message' => 'Apartment is no longer available',
                    'error_code' => 'APARTMENT_UNAVAILABLE'
                ], 409);
            }

            // Check if user is authenticated
            if (!$request->user()) {
                // Store application data in session for later use
                $sessionData = $this->sessionManager->retrieveInvitationContext($token) ?? [];
                $sessionData['application_data'] = [
                    'duration' => $request->duration,
                    'move_in_date' => $request->move_in_date,
                    'additional_notes' => $request->additional_notes
                ];
                
                $this->sessionManager->storeInvitationContext($token, $sessionData);

                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required to complete application',
                    'error_code' => 'AUTHENTICATION_REQUIRED',
                    'data' => [
                        'redirect_to' => 'login',
                        'invitation_token' => $token
                    ]
                ], 401);
            }

            // User is authenticated, process the application
            $user = $request->user();
            
            // Calculate total amount using centralized service
            $apartmentPrice = (float) $invitation->apartment->amount;
            $pricingType = $invitation->apartment->getPricingType();
            $rentalDuration = $request->duration;

            $calculationResult = $this->paymentCalculationService->calculatePaymentTotal(
                $apartmentPrice,
                $rentalDuration,
                $pricingType
            );

            if (!$calculationResult->isValid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment calculation failed: ' . $calculationResult->errorMessage,
                    'error_code' => 'CALCULATION_FAILED'
                ], 400);
            }

            $totalAmount = $calculationResult->totalAmount;

            // Create payment record
            $payment = new \App\Models\Payment();
            $payment->transaction_id = 'inv_' . $token . '_' . time();
            $payment->payment_reference = 'mobile_' . uniqid();
            $payment->amount = $totalAmount;
            $payment->tenant_id = $user->user_id;
            $payment->landlord_id = $invitation->apartment->property->user_id;
            $payment->apartment_id = $invitation->apartment->apartment_id;
            $payment->status = 'pending';
            $payment->payment_method = 'mobile_app';
            $payment->duration = $request->duration;
            $payment->move_in_date = $request->move_in_date;
            $payment->additional_notes = $request->additional_notes;
            $payment->save();

            return response()->json([
                'success' => true,
                'message' => 'Application submitted successfully',
                'data' => [
                    'payment' => [
                        'id' => $payment->id,
                        'reference' => $payment->payment_reference,
                        'amount' => $payment->amount,
                        'duration' => $payment->duration,
                        'status' => $payment->status
                    ],
                    'next_step' => 'payment',
                    'payment_url' => route('apartment.invite.payment', ['token' => $token, 'payment' => $payment->id])
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Application failed: ' . $e->getMessage(),
                'error_code' => 'APPLICATION_FAILED'
            ], 500);
        }
    }

    /**
     * Get session data for invitation
     */
    public function getSession(Request $request, string $token): JsonResponse
    {
        try {
            $sessionData = $this->sessionManager->retrieveInvitationContext($token);

            if (!$sessionData) {
                return response()->json([
                    'success' => false,
                    'message' => 'No session data found',
                    'error_code' => 'NO_SESSION_DATA'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'session_data' => $sessionData,
                    'has_application_data' => isset($sessionData['application_data']),
                    'has_registration_data' => isset($sessionData['registration_data'])
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve session: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store session data for invitation
     */
    public function storeSession(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'invitation_token' => 'required|string',
            'session_data' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $this->sessionManager->storeInvitationContext(
                $request->invitation_token,
                $request->session_data
            );

            return response()->json([
                'success' => true,
                'message' => 'Session data stored successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to store session: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear session data for invitation
     */
    public function clearSession(Request $request, string $token): JsonResponse
    {
        try {
            $this->sessionManager->clearInvitationContext($token);

            return response()->json([
                'success' => true,
                'message' => 'Session data cleared successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear session: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate invitation link (for landlords)
     */
    public function generateLink(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'apartment_id' => 'required|integer|exists:apartments,apartment_id',
            'expires_in_hours' => 'nullable|integer|min:1|max:168' // Max 1 week
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $apartment = Apartment::with('property')->find($request->apartment_id);

            // Verify user owns this apartment
            if ($apartment->property->user_id !== $user->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to create invitation for this apartment',
                    'error_code' => 'UNAUTHORIZED'
                ], 403);
            }

            // Generate unique token
            do {
                $token = bin2hex(random_bytes(32));
            } while (ApartmentInvitation::where('invitation_token', $token)->exists());

            $expiresAt = now()->addHours($request->expires_in_hours ?? 72); // Default 3 days

            $invitation = ApartmentInvitation::create([
                'apartment_id' => $apartment->apartment_id,
                'invitation_token' => $token,
                'expires_at' => $expiresAt,
                'landlord_id' => $user->user_id,
                'access_count' => 0,
                'status' => 'active'
            ]);

            $invitationUrl = url("/apartment/invite/{$token}");

            return response()->json([
                'success' => true,
                'message' => 'Invitation link generated successfully',
                'data' => [
                    'invitation' => [
                        'id' => $invitation->id,
                        'token' => $invitation->invitation_token,
                        'url' => $invitationUrl,
                        'expires_at' => $invitation->expires_at,
                        'created_at' => $invitation->created_at
                    ],
                    'apartment' => [
                        'id' => $apartment->apartment_id,
                        'rent' => $apartment->rent,
                        'apartment_type' => $apartment->apartment_type
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate invitation: ' . $e->getMessage(),
                'error_code' => 'INVITATION_GENERATION_FAILED'
            ], 500);
        }
    }

    /**
     * Get payment calculation details for invitation
     * Enhanced with comprehensive pricing structure information and mobile-optimized features
     */
    public function getPaymentCalculation(Request $request, string $token): JsonResponse
    {
        $validator = Validator::make($request->all(), [
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
                        'Ensure rental duration is between 1 and 120 months',
                        'Verify additional charges are positive numbers',
                        'Check that all required fields are provided'
                    ]
                ]
            ], 422);
        }

        try {
            $invitation = ApartmentInvitation::where('invitation_token', $token)
                ->where('expires_at', '>', now())
                ->with(['apartment.property'])
                ->first();

            if (!$invitation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invitation not found or expired',
                    'error_code' => 'INVITATION_NOT_FOUND',
                    'mobile_error_handling' => [
                        'user_friendly_message' => 'This invitation link is no longer valid',
                        'suggested_actions' => [
                            'Check if the link is correct',
                            'Request a new invitation from the landlord',
                            'Contact support if you believe this is an error'
                        ],
                        'expiration_info' => [
                            'message' => 'Invitation links expire for security reasons',
                            'typical_validity' => '72 hours from creation'
                        ]
                    ]
                ], 404);
            }

            if (!$invitation->apartment->available) {
                return response()->json([
                    'success' => false,
                    'message' => 'Apartment is not available',
                    'error_code' => 'APARTMENT_UNAVAILABLE',
                    'mobile_error_handling' => [
                        'user_friendly_message' => 'This apartment is no longer available for rent',
                        'suggested_actions' => [
                            'Browse other available apartments',
                            'Contact the landlord for updates',
                            'Set up alerts for similar properties'
                        ]
                    ]
                ], 409);
            }

            $rentalDuration = (int) $request->rental_duration;
            $additionalCharges = $request->additional_charges ?? [];
            $includePricingDetails = $request->boolean('include_pricing_details', true);
            $includeCalculationAudit = $request->boolean('include_calculation_audit', false);

            // Get apartment pricing configuration
            $apartmentPrice = (float) $invitation->apartment->amount;
            $pricingType = $invitation->apartment->getPricingType();

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
                    'invitation_context' => [
                        'invitation_token' => $token,
                        'apartment_id' => $invitation->apartment->apartment_id,
                        'pricing_type' => $pricingType
                    ],
                    'mobile_error_handling' => [
                        'user_friendly_message' => 'Unable to calculate payment for this apartment',
                        'technical_details' => $result->errorMessage,
                        'suggested_actions' => [
                            'Try a different rental duration',
                            'Remove additional charges and try again',
                            'Contact the landlord for clarification'
                        ]
                    ]
                ], 400);
            }

            // Build comprehensive mobile-optimized response
            $responseData = [
                'invitation_token' => $token,
                'payment_calculation' => [
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
                    'id' => $invitation->apartment->apartment_id,
                    'rent' => $invitation->apartment->amount,
                    'apartment_type' => $invitation->apartment->apartment_type,
                    'address' => $invitation->apartment->property->address ?? 'N/A',
                    'pricing_type' => $pricingType,
                    'available' => $invitation->apartment->available
                ],
                'invitation_details' => [
                    'expires_at' => $invitation->expires_at,
                    'time_remaining' => $this->getTimeRemaining($invitation->expires_at),
                    'access_count' => $invitation->access_count,
                    'landlord_info' => [
                        'name' => $invitation->apartment->property->owner->first_name . ' ' . $invitation->apartment->property->owner->last_name,
                        'contact_available' => true
                    ]
                ],
                'mobile_features' => [
                    'formatted_display_amounts' => [
                        'total_amount' => format_money($result->totalAmount, $invitation->apartment->currency)->getSymbol() . number_format($result->totalAmount, 2),
                        'base_rent' => format_money($apartmentPrice, $invitation->apartment->currency)->getSymbol() . number_format($apartmentPrice, 2),
                        'monthly_equivalent' => $pricingType === 'total' && $rentalDuration > 0 
                            ? format_money($result->totalAmount / $rentalDuration, $invitation->apartment->currency)->getSymbol() . number_format($result->totalAmount / $rentalDuration, 2) . '/month'
                            : null,
                        'additional_charges_total' => !empty($additionalCharges) 
                            ? format_money(array_sum($additionalCharges), $invitation->apartment->currency)->getSymbol() . number_format(array_sum($additionalCharges), 2)
                            : null
                    ],
                    'calculation_explanation' => [
                        'method_description' => $this->getCalculationMethodDescription($result->calculationMethod),
                        'pricing_explanation' => $this->getPricingTypeExplanation($pricingType),
                        'steps_count' => count($result->calculationSteps),
                        'has_additional_charges' => !empty($additionalCharges),
                        'calculation_transparency' => [
                            'base_calculation' => $pricingType === 'total' 
                                ? format_money($apartmentPrice, $invitation->apartment->currency)->getSymbol() . "{$apartmentPrice} (total for {$rentalDuration} months)"
                                : format_money($apartmentPrice, $invitation->apartment->currency)->getSymbol() . "{$apartmentPrice} × {$rentalDuration} months = " . format_money($apartmentPrice * $rentalDuration, $invitation->apartment->currency)->getSymbol() . number_format($apartmentPrice * $rentalDuration, 2),
                            'additional_charges_breakdown' => $this->formatAdditionalChargesForMobile($additionalCharges)
                        ]
                    ],
                    'invitation_guidance' => [
                        'next_steps' => [
                            'Review the calculation details',
                            'Proceed with the application if satisfied',
                            'Contact landlord for any questions'
                        ],
                        'urgency_indicator' => $this->getUrgencyIndicator($invitation->expires_at),
                        'application_tips' => [
                            'Have your identification ready',
                            'Prepare payment method information',
                            'Review all terms before proceeding'
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
                        'price_configuration' => $invitation->apartment->price_configuration ?? null
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
                'invitation_based_calculation' => true,
                'pricing_structure_transparency' => true
            ];

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'meta' => $metaData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment calculation failed: ' . $e->getMessage(),
                'error_code' => 'CALCULATION_EXCEPTION',
                'mobile_error_handling' => [
                    'user_friendly_message' => 'Something went wrong while calculating your payment',
                    'technical_details' => $e->getMessage(),
                    'suggested_actions' => [
                        'Try refreshing the app',
                        'Check your internet connection',
                        'Contact support if the problem continues'
                    ],
                    'support_info' => [
                        'error_reference' => uniqid('mobile_invitation_error_'),
                        'timestamp' => now()->toISOString(),
                        'invitation_token' => $token
                    ]
                ]
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
                'formatted_amount' => format_money($charge, $invitation->apartment->currency)->getSymbol() . number_format($charge, 2),
                'description' => "Additional charge " . ($index + 1)
            ];
        }

        return $breakdown;
    }

    /**
     * Get time remaining for invitation expiration
     */
    protected function getTimeRemaining($expiresAt): array
    {
        $now = now();
        $expiration = \Carbon\Carbon::parse($expiresAt);
        
        if ($expiration->isPast()) {
            return [
                'expired' => true,
                'message' => 'Invitation has expired',
                'time_remaining' => null
            ];
        }

        $diff = $now->diff($expiration);
        
        return [
            'expired' => false,
            'hours_remaining' => $diff->h + ($diff->days * 24),
            'minutes_remaining' => $diff->i,
            'formatted_time' => $this->formatTimeRemaining($diff),
            'urgency_level' => $this->getUrgencyLevel($diff)
        ];
    }

    /**
     * Format time remaining in human-readable format
     */
    protected function formatTimeRemaining(\DateInterval $diff): string
    {
        if ($diff->days > 0) {
            return $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' remaining';
        } elseif ($diff->h > 0) {
            return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' remaining';
        } else {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' remaining';
        }
    }

    /**
     * Get urgency level based on time remaining
     */
    protected function getUrgencyLevel(\DateInterval $diff): string
    {
        $totalHours = $diff->h + ($diff->days * 24);
        
        if ($totalHours <= 1) {
            return 'critical';
        } elseif ($totalHours <= 6) {
            return 'high';
        } elseif ($totalHours <= 24) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Get urgency indicator for mobile display
     */
    protected function getUrgencyIndicator($expiresAt): array
    {
        $timeRemaining = $this->getTimeRemaining($expiresAt);
        
        if ($timeRemaining['expired']) {
            return [
                'level' => 'expired',
                'message' => 'This invitation has expired',
                'color' => 'red',
                'action_required' => 'Request new invitation'
            ];
        }

        $urgencyLevel = $timeRemaining['urgency_level'];
        
        $indicators = [
            'critical' => [
                'level' => 'critical',
                'message' => 'Expires very soon! Act now.',
                'color' => 'red',
                'action_required' => 'Complete application immediately'
            ],
            'high' => [
                'level' => 'high',
                'message' => 'Expires soon. Please complete your application.',
                'color' => 'orange',
                'action_required' => 'Complete application today'
            ],
            'medium' => [
                'level' => 'medium',
                'message' => 'You have some time, but don\'t delay.',
                'color' => 'yellow',
                'action_required' => 'Complete application within 24 hours'
            ],
            'low' => [
                'level' => 'low',
                'message' => 'You have plenty of time to review.',
                'color' => 'green',
                'action_required' => 'Review details and apply when ready'
            ]
        ];

        return $indicators[$urgencyLevel] ?? $indicators['low'];
    }
}