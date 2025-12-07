<?php

namespace App\Services\Logging;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\ApartmentInvitation;
use App\Models\User;
use App\Models\Payment;
use Carbon\Carbon;

class EasyRentLogger
{
    /**
     * Log invitation access with timestamps and user information
     */
    public function logInvitationAccess(ApartmentInvitation $invitation, Request $request, ?User $user = null): void
    {
        $logData = [
            'event' => 'invitation_access',
            'invitation_id' => $invitation->id,
            'invitation_token' => $invitation->token,
            'apartment_id' => $invitation->apartment_id,
            'user_id' => $user?->user_id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => Carbon::now()->toISOString(),
            'session_id' => $request->session()->getId(),
            'referer' => $request->header('referer'),
            'is_authenticated' => $user !== null,
        ];

        Log::channel('easyrent_invitations')->info('Invitation accessed', $logData);
    }

    /**
     * Log invitation creation
     */
    public function logInvitationCreation(ApartmentInvitation $invitation, User $landlord): void
    {
        $logData = [
            'event' => 'invitation_created',
            'invitation_id' => $invitation->id,
            'invitation_token' => $invitation->token,
            'apartment_id' => $invitation->apartment_id,
            'landlord_id' => $landlord->user_id,
            'expires_at' => $invitation->expires_at?->toISOString(),
            'timestamp' => Carbon::now()->toISOString(),
        ];

        Log::channel('easyrent_invitations')->info('Invitation created', $logData);
    }

    /**
     * Log authentication events
     */
    public function logAuthenticationEvent(string $event, Request $request, ?User $user = null, array $context = []): void
    {
        $logData = array_merge([
            'event' => $event,
            'user_id' => $user?->user_id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => Carbon::now()->toISOString(),
            'session_id' => $request->session()->getId(),
        ], $context);

        Log::channel('easyrent_auth')->info("Authentication event: {$event}", $logData);
    }

    /**
     * Log user login attempts
     */
    public function logLoginAttempt(Request $request, string $email, bool $successful, ?User $user = null): void
    {
        $this->logAuthenticationEvent('login_attempt', $request, $user, [
            'email' => $email,
            'successful' => $successful,
            'has_invitation_context' => $request->session()->has('invitation_context'),
        ]);
    }

    /**
     * Log user registration
     */
    public function logRegistration(Request $request, User $user, bool $viaInvitation = false): void
    {
        $this->logAuthenticationEvent('user_registration', $request, $user, [
            'registration_source' => $user->registration_source ?? 'direct',
            'via_invitation' => $viaInvitation,
            'referred_by' => $user->referred_by,
        ]);
    }

    /**
     * Log session transfers during authentication
     */
    public function logSessionTransfer(Request $request, User $user, array $sessionData): void
    {
        $this->logAuthenticationEvent('session_transfer', $request, $user, [
            'session_data_keys' => array_keys($sessionData),
            'has_invitation_token' => isset($sessionData['invitation_token']),
        ]);
    }

    /**
     * Log detailed payment transaction events
     */
    public function logPaymentTransaction(string $event, Payment $payment, Request $request, array $context = []): void
    {
        $logData = array_merge([
            'event' => $event,
            'payment_id' => $payment->id,
            'user_id' => $payment->user_id,
            'apartment_id' => $payment->apartment_id,
            'amount' => $payment->amount,
            'status' => $payment->status,
            'payment_method' => $payment->payment_method ?? 'unknown',
            'transaction_reference' => $payment->transaction_reference,
            'ip_address' => $request->ip(),
            'timestamp' => Carbon::now()->toISOString(),
            'session_id' => $request->session()->getId(),
        ], $context);

        Log::channel('easyrent_payments')->info("Payment transaction: {$event}", $logData);
    }

    /**
     * Log payment initiation
     */
    public function logPaymentInitiation(Payment $payment, Request $request, ApartmentInvitation $invitation): void
    {
        $this->logPaymentTransaction('payment_initiated', $payment, $request, [
            'invitation_id' => $invitation->id,
            'invitation_token' => $invitation->token,
        ]);
    }

    /**
     * Log payment completion
     */
    public function logPaymentCompletion(Payment $payment, Request $request): void
    {
        $this->logPaymentTransaction('payment_completed', $payment, $request, [
            'processing_time_seconds' => $payment->updated_at->diffInSeconds($payment->created_at),
        ]);
    }

    /**
     * Log payment failure
     */
    public function logPaymentFailure(Payment $payment, Request $request, string $reason): void
    {
        $this->logPaymentTransaction('payment_failed', $payment, $request, [
            'failure_reason' => $reason,
        ]);
    }

    /**
     * Log apartment assignment
     */
    public function logApartmentAssignment(Payment $payment, ApartmentInvitation $invitation, User $tenant): void
    {
        $logData = [
            'event' => 'apartment_assigned',
            'payment_id' => $payment->id,
            'invitation_id' => $invitation->id,
            'apartment_id' => $invitation->apartment_id,
            'tenant_id' => $tenant->user_id,
            'landlord_id' => $invitation->apartment->property->user_id ?? null,
            'timestamp' => Carbon::now()->toISOString(),
        ];

        Log::channel('easyrent_assignments')->info('Apartment assigned to tenant', $logData);
    }

    /**
     * Log errors with debugging context
     */
    public function logError(string $message, \Throwable $exception, Request $request, array $context = []): void
    {
        $logData = array_merge([
            'error_message' => $message,
            'exception_class' => get_class($exception),
            'exception_message' => $exception->getMessage(),
            'exception_code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'stack_trace' => $exception->getTraceAsString(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'timestamp' => Carbon::now()->toISOString(),
            'session_id' => $request->session()->getId(),
            'user_id' => auth()->id(),
        ], $context);

        Log::channel('easyrent_errors')->error($message, $logData);
    }

    /**
     * Log performance metrics
     */
    public function logPerformanceMetric(string $operation, float $executionTime, Request $request, array $context = []): void
    {
        $logData = array_merge([
            'operation' => $operation,
            'execution_time_ms' => round($executionTime * 1000, 2),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip_address' => $request->ip(),
            'timestamp' => Carbon::now()->toISOString(),
            'user_id' => auth()->id(),
        ], $context);

        Log::channel('easyrent_performance')->info("Performance metric: {$operation}", $logData);
    }

    /**
     * Log session management events
     */
    public function logSessionEvent(string $event, Request $request, array $context = []): void
    {
        $logData = array_merge([
            'event' => $event,
            'session_id' => $request->session()->getId(),
            'ip_address' => $request->ip(),
            'timestamp' => Carbon::now()->toISOString(),
            'user_id' => auth()->id(),
        ], $context);

        Log::channel('easyrent_sessions')->info("Session event: {$event}", $logData);
    }

    /**
     * Log session cleanup
     */
    public function logSessionCleanup(string $sessionId, array $cleanedData): void
    {
        $this->logSessionEvent('session_cleanup', request(), [
            'cleaned_session_id' => $sessionId,
            'cleaned_data_keys' => array_keys($cleanedData),
        ]);
    }

    /**
     * Log security events
     */
    public function logSecurityEvent(string $event, Request $request, array $context = []): void
    {
        $logData = array_merge([
            'event' => $event,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'timestamp' => Carbon::now()->toISOString(),
            'session_id' => $request->session()->getId(),
            'user_id' => auth()->id(),
        ], $context);

        Log::channel('easyrent_security')->warning("Security event: {$event}", $logData);
    }

    /**
     * Log rate limiting events
     */
    public function logRateLimitExceeded(Request $request, string $limitType): void
    {
        $this->logSecurityEvent('rate_limit_exceeded', $request, [
            'limit_type' => $limitType,
        ]);
    }

    /**
     * Log suspicious activity
     */
    public function logSuspiciousActivity(Request $request, string $reason, array $details = []): void
    {
        $this->logSecurityEvent('suspicious_activity', $request, array_merge([
            'reason' => $reason,
        ], $details));
    }

    /**
     * Log email events
     */
    public function logEmailEvent(string $event, string $emailType, array $recipients, array $context = []): void
    {
        $logData = array_merge([
            'event' => $event,
            'email_type' => $emailType,
            'recipients' => $recipients,
            'timestamp' => Carbon::now()->toISOString(),
        ], $context);

        Log::channel('easyrent_emails')->info("Email event: {$event}", $logData);
    }

    /**
     * Log email delivery success
     */
    public function logEmailDeliverySuccess(string $emailType, array $recipients, array $context = []): void
    {
        $this->logEmailEvent('email_delivered', $emailType, $recipients, $context);
    }

    /**
     * Log email delivery failure
     */
    public function logEmailDeliveryFailure(string $emailType, array $recipients, string $reason, array $context = []): void
    {
        $this->logEmailEvent('email_delivery_failed', $emailType, $recipients, array_merge([
            'failure_reason' => $reason,
        ], $context));
    }

    /**
     * Log marketer qualification events
     */
    public function logMarketerQualification(User $user, bool $qualified, array $qualificationData): void
    {
        $logData = [
            'event' => 'marketer_qualification_check',
            'user_id' => $user->user_id,
            'qualified' => $qualified,
            'qualification_data' => $qualificationData,
            'timestamp' => Carbon::now()->toISOString(),
        ];

        Log::channel('easyrent_auth')->info('Marketer qualification evaluated', $logData);
    }

    /**
     * Log marketer promotion
     */
    public function logMarketerPromotion(User $user, array $promotionData): void
    {
        $logData = [
            'event' => 'marketer_promotion',
            'user_id' => $user->user_id,
            'promotion_data' => $promotionData,
            'timestamp' => Carbon::now()->toISOString(),
        ];

        Log::channel('easyrent_auth')->info('User promoted to marketer', $logData);
    }

    /**
     * Log security breach events
     */
    public function logSecurityBreach(array $breachData, string $severity): void
    {
        $logData = array_merge([
            'event' => 'security_breach',
            'severity' => $severity,
            'timestamp' => Carbon::now()->toISOString(),
        ], $breachData);

        Log::channel('easyrent_security')->critical("Security breach detected: {$severity}", $logData);
    }

    /**
     * Log authentication errors
     */
    public function logAuthenticationError(\Throwable $exception, Request $request, array $context = []): void
    {
        $this->logError('Authentication error occurred', $exception, $request, array_merge([
            'error_category' => 'authentication',
        ], $context));
    }

    /**
     * Log payment errors
     */
    public function logPaymentError(\Throwable $exception, Request $request, array $context = []): void
    {
        $this->logError('Payment error occurred', $exception, $request, array_merge([
            'error_category' => 'payment',
        ], $context));
    }

    /**
     * Log session errors
     */
    public function logSessionError(\Throwable $exception, Request $request, array $context = []): void
    {
        $this->logError('Session error occurred', $exception, $request, array_merge([
            'error_category' => 'session',
        ], $context));
    }

    /**
     * Log email errors
     */
    public function logEmailError(\Throwable $exception, Request $request, array $context = []): void
    {
        $this->logError('Email error occurred', $exception, $request, array_merge([
            'error_category' => 'email',
        ], $context));
    }

    /**
     * Log system errors
     */
    public function logSystemError(\Throwable $exception, Request $request, array $context = []): void
    {
        $this->logError('System error occurred', $exception, $request, array_merge([
            'error_category' => 'system',
        ], $context));
    }

    /**
     * Log security errors
     */
    public function logSecurityError(\Throwable $exception, Request $request, array $context = []): void
    {
        $this->logError('Security error occurred', $exception, $request, array_merge([
            'error_category' => 'security',
        ], $context));
    }
}