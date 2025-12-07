<?php

namespace App\Services\Email;

use App\Models\ApartmentInvitation;
use App\Models\Payment;
use App\Models\User;
use App\Mail\TenantApplicationMail;
use App\Mail\ApartmentAssignedMail;
use App\Mail\WelcomeToEasyRentMail;
use App\Mail\PaymentReceiptMail;
use App\Mail\PaymentConfirmationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class EmailNotificationService implements EmailNotificationInterface
{
    private array $deliveryStats = [
        'sent' => 0,
        'failed' => 0,
        'retries' => 0
    ];

    private EmailDeliveryTracker $tracker;

    public function __construct(EmailDeliveryTracker $tracker)
    {
        $this->tracker = $tracker;
    }

    /**
     * Send application notification to both landlord and tenant
     */
    public function sendApplicationNotification(ApartmentInvitation $invitation, Payment $payment): bool
    {
        $landlordSuccess = $this->sendWithRetry(
            TenantApplicationMail::class,
            ['invitation' => $invitation, 'payment' => $payment, 'recipient' => 'landlord'],
            $invitation->landlord->email
        );

        $tenantSuccess = $this->sendWithRetry(
            TenantApplicationMail::class,
            ['invitation' => $invitation, 'payment' => $payment, 'recipient' => 'tenant'],
            $invitation->prospect_email
        );

        $this->logNotificationEvent('application', $invitation, $landlordSuccess && $tenantSuccess);

        return $landlordSuccess && $tenantSuccess;
    }

    /**
     * Send payment confirmation to both parties
     */
    public function sendPaymentConfirmation(ApartmentInvitation $invitation, Payment $payment): bool
    {
        // Send payment confirmation to tenant
        $tenantSuccess = $this->sendWithRetry(
            PaymentConfirmationMail::class,
            ['invitation' => $invitation, 'payment' => $payment, 'recipient' => 'tenant'],
            $invitation->prospect_email
        );

        // Send payment confirmation to landlord
        $landlordSuccess = $this->sendWithRetry(
            PaymentConfirmationMail::class,
            ['invitation' => $invitation, 'payment' => $payment, 'recipient' => 'landlord'],
            $invitation->landlord->email
        );

        $this->logNotificationEvent('payment_confirmation', $invitation, $tenantSuccess && $landlordSuccess);

        return $tenantSuccess && $landlordSuccess;
    }

    /**
     * Send welcome email to new users registered via invitation
     */
    public function sendWelcomeEmail(User $user, ApartmentInvitation $invitation): bool
    {
        // Enhanced welcome email for invitation-based registrations
        $success = $this->sendWithRetry(
            WelcomeToEasyRentMail::class,
            ['user' => $user, 'invitation' => $invitation],
            $user->email
        );

        $this->logNotificationEvent('welcome', $invitation, $success, $user->id);

        return $success;
    }

    /**
     * Send apartment assignment confirmation to both parties
     */
    public function sendAssignmentConfirmation(ApartmentInvitation $invitation, Payment $payment): bool
    {
        $landlordSuccess = $this->sendWithRetry(
            ApartmentAssignedMail::class,
            ['invitation' => $invitation, 'payment' => $payment, 'recipient' => 'landlord'],
            $invitation->landlord->email
        );

        $tenantSuccess = $this->sendWithRetry(
            ApartmentAssignedMail::class,
            ['invitation' => $invitation, 'payment' => $payment, 'recipient' => 'tenant'],
            $invitation->tenant->email ?? $invitation->prospect_email
        );

        $this->logNotificationEvent('assignment', $invitation, $landlordSuccess && $tenantSuccess);

        return $landlordSuccess && $tenantSuccess;
    }

    /**
     * Send email with retry logic for delivery failures
     */
    public function sendWithRetry(string $mailClass, array $data, string $to, int $maxRetries = 3): bool
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $maxRetries) {
            try {
                $attempt++;
                
                // Create mail instance based on class and data
                $mail = $this->createMailInstance($mailClass, $data);
                
                // Send the email
                Mail::to($to)->send($mail);
                
                $this->deliveryStats['sent']++;
                
                // Track successful delivery
                $emailType = $this->getEmailType($mailClass);
                $this->tracker->trackSuccess($emailType, $to);
                
                if ($attempt > 1) {
                    $this->deliveryStats['retries']++;
                    $this->tracker->trackRetry($emailType, $to, $attempt);
                    Log::info("Email sent successfully after {$attempt} attempts", [
                        'mail_class' => $mailClass,
                        'recipient' => $to,
                        'attempt' => $attempt
                    ]);
                }
                
                return true;
                
            } catch (Exception $e) {
                $lastException = $e;
                $this->deliveryStats['retries']++;
                
                // Track retry attempt
                $emailType = $this->getEmailType($mailClass);
                $this->tracker->trackRetry($emailType, $to, $attempt);
                
                Log::warning("Email delivery attempt {$attempt} failed", [
                    'mail_class' => $mailClass,
                    'recipient' => $to,
                    'attempt' => $attempt,
                    'error' => $e->getMessage()
                ]);
                
                // Exponential backoff: wait 2^attempt seconds
                if ($attempt < $maxRetries) {
                    sleep(pow(2, $attempt));
                }
            }
        }

        // All attempts failed
        $this->deliveryStats['failed']++;
        
        // Track final failure
        $emailType = $this->getEmailType($mailClass);
        $this->tracker->trackFailure($emailType, $to, $lastException ? $lastException->getMessage() : 'Unknown error');
        
        Log::error("Email delivery failed after {$maxRetries} attempts", [
            'mail_class' => $mailClass,
            'recipient' => $to,
            'final_error' => $lastException ? $lastException->getMessage() : 'Unknown error'
        ]);

        return false;
    }

    /**
     * Create mail instance based on class name and data
     */
    private function createMailInstance(string $mailClass, array $data)
    {
        switch ($mailClass) {
            case TenantApplicationMail::class:
                return new TenantApplicationMail(
                    $data['invitation'],
                    $data['payment'],
                    $data['recipient']
                );
                
            case ApartmentAssignedMail::class:
                return new ApartmentAssignedMail(
                    $data['invitation'],
                    $data['payment'],
                    $data['recipient']
                );
                
            case WelcomeToEasyRentMail::class:
                return new WelcomeToEasyRentMail($data['user']);
                
            case PaymentReceiptMail::class:
                return new PaymentReceiptMail($data['payment']);
                
            case PaymentConfirmationMail::class:
                return new PaymentConfirmationMail(
                    $data['invitation'],
                    $data['payment'],
                    $data['recipient']
                );
                
            case 'App\Mail\LandlordPaymentNotification':
                return new \App\Mail\LandlordPaymentNotification($data['payment']);
                
            default:
                throw new Exception("Unsupported mail class: {$mailClass}");
        }
    }

    /**
     * Log notification events for tracking and debugging
     */
    private function logNotificationEvent(string $type, ApartmentInvitation $invitation, bool $success, ?int $userId = null): void
    {
        Log::info("Email notification event", [
            'type' => $type,
            'invitation_id' => $invitation->id,
            'apartment_id' => $invitation->apartment_id,
            'landlord_id' => $invitation->landlord_id,
            'user_id' => $userId,
            'success' => $success,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Get email delivery statistics
     */
    public function getDeliveryStats(): array
    {
        return $this->deliveryStats;
    }

    /**
     * Reset delivery statistics
     */
    public function resetDeliveryStats(): void
    {
        $this->deliveryStats = [
            'sent' => 0,
            'failed' => 0,
            'retries' => 0
        ];
    }

    /**
     * Get email type from mail class name for tracking
     */
    private function getEmailType(string $mailClass): string
    {
        switch ($mailClass) {
            case TenantApplicationMail::class:
                return 'application';
            case PaymentConfirmationMail::class:
                return 'payment_confirmation';
            case WelcomeToEasyRentMail::class:
                return 'welcome';
            case ApartmentAssignedMail::class:
                return 'assignment';
            default:
                return 'other';
        }
    }
}