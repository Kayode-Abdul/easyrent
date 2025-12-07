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

    public function __construct(
        PaymentIntegrationService $paymentService,
        SessionManagerInterface $sessionManager
    ) {
        $this->paymentService = $paymentService;
        $this->sessionManager = $sessionManager;
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
}