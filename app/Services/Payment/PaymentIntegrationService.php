<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Models\ApartmentInvitation;
use App\Models\Apartment;
use App\Models\User;
use App\Services\Session\SessionManagerInterface;
use App\Services\Marketer\MarketerQualificationService;
use App\Mail\ApartmentAssignedMail;
use App\Mail\TenantApplicationMail;
use App\Mail\WelcomeToEasyRentMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PaymentIntegrationService
{
    protected $sessionManager;
    protected $marketerQualificationService;

    public function __construct(
        SessionManagerInterface $sessionManager,
        MarketerQualificationService $marketerQualificationService
    ) {
        $this->sessionManager = $sessionManager;
        $this->marketerQualificationService = $marketerQualificationService;
    }

    /**
     * Process payment for invitation-based applications
     */
    public function processInvitationPayment(Payment $payment, array $paymentDetails): array
    {
        try {
            DB::beginTransaction();

            Log::info('Processing invitation-based payment', [
                'payment_id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'amount' => $payment->amount
            ]);

            // Find the related invitation
            $invitation = $this->findRelatedInvitation($payment);
            if (!$invitation) {
                throw new \Exception('Related apartment invitation not found for payment');
            }

            // Update payment status
            $payment->update([
                'status' => Payment::STATUS_COMPLETED,
                'paid_at' => now(),
                'payment_meta' => array_merge($payment->payment_meta ?? [], [
                    'invitation_token' => $invitation->invitation_token,
                    'processed_via' => 'invitation_flow',
                    'payment_details' => $paymentDetails
                ])
            ]);

            // If guest payment (no tenant yet), do not assign apartment now
            if (!$payment->tenant_id) {
                $this->sessionManager->storeApplicationData($invitation->invitation_token, [
                    'payment_completed' => true,
                    'payment_id' => $payment->id,
                    'amount' => $payment->amount,
                    'duration' => $payment->duration,
                    'move_in_date' => $payment->payment_meta['move_in_date'] ?? null,
                    'completed_at' => now()->toISOString(),
                    'next_step' => 'registration'
                ]);
                $this->sessionManager->extendSessionExpiration($invitation->invitation_token, 48);

                Log::info('Guest payment completed; awaiting registration to finalize', [
                    'payment_id' => $payment->id,
                    'invitation_id' => $invitation->id
                ]);

                DB::commit();

                return [
                    'success' => true,
                    'payment' => $payment,
                    'invitation' => $invitation,
                    'apartment_assigned' => false,
                    'message' => 'Payment completed. Please register to finalize your apartment assignment.',
                    'next_step' => 'registration'
                ];
            }

            // Assign apartment to tenant
            $this->assignApartmentToTenant($invitation, $payment);

            // Mark invitation as completed
            $invitation->markPaymentCompleted();

            // Clean up session data
            $this->cleanupSessionData($invitation);

            // Send email notifications
            $this->sendPaymentCompletionEmails($invitation, $payment);

            // Check for marketer qualification
            $this->evaluateMarketerQualification($payment);

            DB::commit();

            Log::info('Invitation payment processed successfully', [
                'payment_id' => $payment->id,
                'invitation_id' => $invitation->id,
                'apartment_id' => $invitation->apartment_id,
                'tenant_id' => $payment->tenant_id
            ]);

            return [
                'success' => true,
                'payment' => $payment,
                'invitation' => $invitation,
                'apartment_assigned' => true,
                'message' => 'Payment processed successfully and apartment assigned'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to process invitation payment', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Handle payment failure with state preservation
            $this->handlePaymentFailure($payment, $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'payment' => $payment,
                'state_preserved' => true
            ];
        }
    }

    /**
     * Find the related apartment invitation for a payment
     */
    protected function findRelatedInvitation(Payment $payment): ?ApartmentInvitation
    {
        // Try to find by payment reference first
        if (!empty($payment->payment_reference) && Str::contains($payment->payment_reference, 'easyrent_')) {
            $token = str_replace('easyrent_', '', $payment->payment_reference);
            $invitation = ApartmentInvitation::where('invitation_token', $token)->first();
            if ($invitation) {
                return $invitation;
            }
        }

        // Try to find by apartment and tenant
        $invitation = ApartmentInvitation::where('apartment_id', $payment->apartment_id)
            ->where('tenant_user_id', $payment->tenant_id)
            ->where('status', '!=', ApartmentInvitation::STATUS_USED)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($invitation) {
            return $invitation;
        }

        // Try to find by payment metadata (support string or array)
        $meta = $payment->payment_meta;
        if (is_string($meta)) {
            $decoded = json_decode($meta, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $meta = $decoded;
            } else {
                $meta = null;
            }
        }
        if (is_array($meta) && isset($meta['invitation_token'])) {
            return ApartmentInvitation::where('invitation_token', $meta['invitation_token'])->first();
        }

        return null;
    }

    /**
     * Assign apartment to tenant after successful payment
     */
    protected function assignApartmentToTenant(ApartmentInvitation $invitation, Payment $payment): void
    {
        $apartment = Apartment::where('apartment_id', $invitation->apartment_id)->first();
        
        if (!$apartment) {
            throw new \Exception('Apartment not found for assignment');
        }

        // Calculate lease dates
        $moveInDate = $invitation->move_in_date ? 
            Carbon::parse($invitation->move_in_date) : 
            now()->addDays(7);
        
        $leaseEndDate = $moveInDate->copy()->addMonths($invitation->lease_duration ?? $payment->duration);

        // Update apartment with tenant assignment
        $apartment->update([
            'tenant_id' => $payment->tenant_id,
            'occupied' => true,
            'range_start' => $moveInDate,
            'range_end' => $leaseEndDate
        ]);

        Log::info('Apartment assigned to tenant', [
            'apartment_id' => $apartment->apartment_id,
            'tenant_id' => $payment->tenant_id,
            'move_in_date' => $moveInDate->toDateString(),
            'lease_end_date' => $leaseEndDate->toDateString()
        ]);
    }

    /**
     * Clean up session data after successful payment
     */
    protected function cleanupSessionData(ApartmentInvitation $invitation): void
    {
        try {
            // Clear invitation session data
            $invitation->clearSessionData();

            // Clear session manager data
            $this->sessionManager->clearInvitationContext($invitation->invitation_token);

            // Clear Laravel session data
            session()->forget([
                'easyrent_invitation_token',
                'easyrent_redirect_url',
                'easyrent_invitation_context',
                'easyrent_application_data',
                'application_attempt_data',
                'authenticated_invitation_context'
            ]);

            Log::info('Session data cleaned up after payment completion', [
                'invitation_id' => $invitation->id,
                'invitation_token' => substr($invitation->invitation_token, 0, 8) . '...'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to cleanup session data', [
                'invitation_id' => $invitation->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send email notifications after payment completion
     */
    protected function sendPaymentCompletionEmails(ApartmentInvitation $invitation, Payment $payment): void
    {
        try {
            $tenant = User::where('user_id', $payment->tenant_id)->first();
            $landlord = User::where('user_id', $payment->landlord_id)->first();

            if (!$tenant || !$landlord) {
                throw new \Exception('Tenant or landlord not found for email notifications');
            }

            // Send apartment assignment confirmation to tenant
            Mail::to($tenant->email)->send(
                new ApartmentAssignedMail($invitation, $payment, 'tenant')
            );

            // Send apartment assignment confirmation to landlord
            Mail::to($landlord->email)->send(
                new ApartmentAssignedMail($invitation, $payment, 'landlord')
            );

            // If this was a new user registration via invitation, send welcome email
            if ($tenant->registration_source === 'easyrent_invitation') {
                Mail::to($tenant->email)->send(
                    new WelcomeToEasyRentMail($tenant, $invitation)
                );
            }

            Log::info('Payment completion emails sent', [
                'invitation_id' => $invitation->id,
                'tenant_email' => $tenant->email,
                'landlord_email' => $landlord->email,
                'welcome_email_sent' => $tenant->registration_source === 'easyrent_invitation'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send payment completion emails', [
                'invitation_id' => $invitation->id,
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Evaluate marketer qualification after successful payment
     */
    protected function evaluateMarketerQualification(Payment $payment): void
    {
        try {
            $results = $this->marketerQualificationService->evaluateQualificationAfterPayment($payment);
            
            Log::info('Marketer qualification evaluation completed via service', [
                'payment_id' => $payment->id,
                'payment_amount' => $payment->amount,
                'evaluations_count' => count($results),
                'promotions_count' => collect($results)->where('promoted', true)->count(),
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to evaluate marketer qualification via service', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle payment failures with state preservation
     */
    protected function handlePaymentFailure(Payment $payment, string $errorMessage): void
    {
        try {
            // Update payment status to failed
            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'payment_meta' => array_merge($payment->payment_meta ?? [], [
                    'failure_reason' => $errorMessage,
                    'failed_at' => now()->toISOString(),
                    'state_preserved' => true
                ])
            ]);

            // Find related invitation and preserve application state
            $invitation = $this->findRelatedInvitation($payment);
            if ($invitation) {
                // Preserve application data in session for retry
                $applicationData = [
                    'payment_id' => $payment->id,
                    'duration' => $invitation->lease_duration,
                    'move_in_date' => $invitation->move_in_date,
                    'total_amount' => $invitation->total_amount,
                    'failure_reason' => $errorMessage,
                    'retry_available' => true,
                    'preserved_at' => now()->toISOString()
                ];

                // Store in session manager for retry
                $this->sessionManager->storeApplicationData(
                    $invitation->invitation_token, 
                    $applicationData
                );

                // Extend session expiration for retry
                $this->sessionManager->extendSessionExpiration(
                    $invitation->invitation_token, 
                    48 // 48 hours for retry
                );

                Log::info('Payment failure handled with state preservation', [
                    'payment_id' => $payment->id,
                    'invitation_id' => $invitation->id,
                    'error' => $errorMessage
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to handle payment failure', [
                'payment_id' => $payment->id,
                'original_error' => $errorMessage,
                'handling_error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Create payment record for invitation-based application
     */
    public function createInvitationPayment(ApartmentInvitation $invitation, User $tenant, array $applicationData): Payment
    {
        $apartment = $invitation->apartment;
        $duration = $applicationData['duration'] ?? 12;
        $totalAmount = $apartment->amount * $duration;

        $payment = Payment::create([
            'transaction_id' => $this->generateTransactionId($invitation, $tenant),
            'tenant_id' => $tenant->user_id,
            'landlord_id' => $invitation->landlord_id,
            'apartment_id' => $apartment->apartment_id,
            'amount' => $totalAmount,
            'duration' => $duration,
            'status' => Payment::STATUS_PENDING,
            'payment_method' => 'card',
            'payment_reference' => 'easyrent_' . $invitation->invitation_token,
            'payment_meta' => [
                'invitation_token' => $invitation->invitation_token,
                'application_data' => $applicationData,
                'created_via' => 'invitation_flow',
                'move_in_date' => $applicationData['move_in_date'] ?? null
            ]
        ]);

        Log::info('Invitation payment record created', [
            'payment_id' => $payment->id,
            'invitation_id' => $invitation->id,
            'tenant_id' => $tenant->user_id,
            'amount' => $totalAmount
        ]);

        return $payment;
    }

    /**
     * Create payment record for guest (unauthenticated) invitation-based application
     */
    public function createGuestInvitationPayment(ApartmentInvitation $invitation, array $applicationData): Payment
    {
        $apartment = $invitation->apartment;
        $duration = $applicationData['duration'] ?? 12;
        $totalAmount = $apartment->amount * $duration;

        $payment = Payment::create([
            'transaction_id' => $this->generateTransactionIdForGuest($invitation),
            'tenant_id' => null,
            'landlord_id' => $invitation->landlord_id,
            'apartment_id' => $apartment->apartment_id,
            'amount' => $totalAmount,
            'duration' => $duration,
            'status' => Payment::STATUS_PENDING,
            'payment_method' => 'card',
            'payment_reference' => 'easyrent_' . $invitation->invitation_token,
            'payment_meta' => [
                'invitation_token' => $invitation->invitation_token,
                'application_data' => $applicationData,
                'created_via' => 'invitation_guest_flow',
                'move_in_date' => $applicationData['move_in_date'] ?? null
            ]
        ]);

        Log::info('Guest invitation payment record created', [
            'payment_id' => $payment->id,
            'invitation_id' => $invitation->id,
            'amount' => $totalAmount
        ]);

        return $payment;
    }

    /**
     * Finalize guest payment and assignment after registration
     */
    public function finalizeAfterRegistration(ApartmentInvitation $invitation, Payment $payment, User $user): array
    {
        try {
            DB::beginTransaction();

            // Link payment to the newly registered tenant
            $payment->update(['tenant_id' => $user->user_id]);

            // Assign apartment now that tenant exists
            $this->assignApartmentToTenant($invitation, $payment);

            // Mark invitation as completed/used
            $invitation->markPaymentCompleted();

            // Clean session state
            $this->cleanupSessionData($invitation);

            // Send emails and evaluate marketer qualification
            $this->sendPaymentCompletionEmails($invitation, $payment);
            $this->evaluateMarketerQualification($payment);

            DB::commit();

            Log::info('Finalized apartment assignment post-registration', [
                'invitation_id' => $invitation->id,
                'payment_id' => $payment->id,
                'tenant_id' => $user->user_id
            ]);

            return [
                'success' => true,
                'apartment_assigned' => true
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to finalize after registration', [
                'invitation_id' => $invitation->id,
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate transaction ID for guest payments
     */
    protected function generateTransactionIdForGuest(ApartmentInvitation $invitation): string
    {
        $prefix = 'ER-INV-GUEST-';
        $timestamp = now()->format('YmdHis');
        $hash = substr(md5($invitation->invitation_token . $timestamp), 0, 8);
        return strtoupper($prefix . $timestamp . '-' . $hash);
    }

    /**
     * Generate unique transaction ID for authenticated invitation payments
     */
    protected function generateTransactionId(ApartmentInvitation $invitation, User $tenant): string
    {
        $prefix = 'ER-INV-';
        $timestamp = now()->format('YmdHis');
        $hash = substr(md5($invitation->invitation_token . $tenant->user_id . $timestamp), 0, 8);
        return strtoupper($prefix . $timestamp . '-' . $hash);
    }

    /**
     * Retry failed payment with preserved state
     */
    public function retryFailedPayment(Payment $failedPayment): array
    {
        try {
            // Find related invitation
            $invitation = $this->findRelatedInvitation($failedPayment);
            if (!$invitation) {
                throw new \Exception('Related invitation not found for retry');
            }

            // Get preserved application data
            $applicationData = $this->sessionManager->retrieveApplicationData($invitation->invitation_token);
            if (!$applicationData) {
                throw new \Exception('No preserved application data found for retry');
            }

            // Create new payment record for retry
            $tenant = User::where('user_id', $failedPayment->tenant_id)->first();
            $newPayment = $this->createInvitationPayment($invitation, $tenant, $applicationData);

            // Mark old payment as superseded
            $failedPayment->update([
                'payment_meta' => array_merge($failedPayment->payment_meta ?? [], [
                    'superseded_by' => $newPayment->id,
                    'retry_initiated_at' => now()->toISOString()
                ])
            ]);

            Log::info('Payment retry initiated', [
                'original_payment_id' => $failedPayment->id,
                'new_payment_id' => $newPayment->id,
                'invitation_id' => $invitation->id
            ]);

            return [
                'success' => true,
                'new_payment' => $newPayment,
                'invitation' => $invitation,
                'message' => 'Payment retry initiated successfully'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to retry payment', [
                'failed_payment_id' => $failedPayment->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get payment statistics for invitation-based payments
     */
    public function getInvitationPaymentStats(): array
    {
        return [
            'total_invitation_payments' => Payment::whereNotNull('payment_meta->invitation_token')->count(),
            'completed_invitation_payments' => Payment::whereNotNull('payment_meta->invitation_token')
                ->where('status', Payment::STATUS_COMPLETED)->count(),
            'failed_invitation_payments' => Payment::whereNotNull('payment_meta->invitation_token')
                ->where('status', Payment::STATUS_FAILED)->count(),
            'pending_invitation_payments' => Payment::whereNotNull('payment_meta->invitation_token')
                ->where('status', Payment::STATUS_PENDING)->count(),
            'total_invitation_revenue' => Payment::whereNotNull('payment_meta->invitation_token')
                ->where('status', Payment::STATUS_COMPLETED)->sum('amount'),
            'average_invitation_payment' => Payment::whereNotNull('payment_meta->invitation_token')
                ->where('status', Payment::STATUS_COMPLETED)->avg('amount')
        ];
    }
}