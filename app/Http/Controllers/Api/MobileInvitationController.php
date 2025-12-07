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

    public function __construct(SessionManagerInterface $sessionManager)
    {
        $this->sessionManager = $sessionManager;
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
                        'rent' => $invitation->apartment->rent,
                        'duration' => $invitation->apartment->duration,
                        'apartment_type' => $invitation->apartment->apartment_type,
                        'bedrooms' => $invitation->apartment->bedrooms,
                        'bathrooms' => $invitation->apartment->bathrooms,
                        'size' => $invitation->apartment->size,
                        'description' => $invitation->apartment->description,
                        'photos' => $invitation->apartment->photos ? json_decode($invitation->apartment->photos) : [],
                        'amenities' => $invitation->apartment->amenities,
                        'available' => $invitation->apartment->available
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
            
            // Calculate total amount
            $totalAmount = $invitation->apartment->rent * $request->duration;

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
}