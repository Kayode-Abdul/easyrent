<?php

namespace App\Services\Logging;

use Illuminate\Http\Request;
use App\Models\ApartmentInvitation;
use App\Models\User;
use App\Models\Payment;

interface EasyRentLoggerInterface
{
    /**
     * Log invitation access with timestamps and user information
     */
    public function logInvitationAccess(ApartmentInvitation $invitation, Request $request, ?User $user = null): void;

    /**
     * Log invitation creation
     */
    public function logInvitationCreation(ApartmentInvitation $invitation, User $landlord): void;

    /**
     * Log authentication events
     */
    public function logAuthenticationEvent(string $event, Request $request, ?User $user = null, array $context = []): void;

    /**
     * Log user login attempts
     */
    public function logLoginAttempt(Request $request, string $email, bool $successful, ?User $user = null): void;

    /**
     * Log user registration
     */
    public function logRegistration(Request $request, User $user, bool $viaInvitation = false): void;

    /**
     * Log session transfers during authentication
     */
    public function logSessionTransfer(Request $request, User $user, array $sessionData): void;

    /**
     * Log detailed payment transaction events
     */
    public function logPaymentTransaction(string $event, Payment $payment, Request $request, array $context = []): void;

    /**
     * Log payment initiation
     */
    public function logPaymentInitiation(Payment $payment, Request $request, ApartmentInvitation $invitation): void;

    /**
     * Log payment completion
     */
    public function logPaymentCompletion(Payment $payment, Request $request): void;

    /**
     * Log payment failure
     */
    public function logPaymentFailure(Payment $payment, Request $request, string $reason): void;

    /**
     * Log apartment assignment
     */
    public function logApartmentAssignment(Payment $payment, ApartmentInvitation $invitation, User $tenant): void;

    /**
     * Log errors with debugging context
     */
    public function logError(string $message, \Throwable $exception, Request $request, array $context = []): void;

    /**
     * Log performance metrics
     */
    public function logPerformanceMetric(string $operation, float $executionTime, Request $request, array $context = []): void;

    /**
     * Log session management events
     */
    public function logSessionEvent(string $event, Request $request, array $context = []): void;

    /**
     * Log session cleanup
     */
    public function logSessionCleanup(string $sessionId, array $cleanedData): void;

    /**
     * Log security events
     */
    public function logSecurityEvent(string $event, Request $request, array $context = []): void;

    /**
     * Log rate limiting events
     */
    public function logRateLimitExceeded(Request $request, string $limitType): void;

    /**
     * Log suspicious activity
     */
    public function logSuspiciousActivity(Request $request, string $reason, array $details = []): void;

    /**
     * Log email events
     */
    public function logEmailEvent(string $event, string $emailType, array $recipients, array $context = []): void;

    /**
     * Log email delivery success
     */
    public function logEmailDeliverySuccess(string $emailType, array $recipients, array $context = []): void;

    /**
     * Log email delivery failure
     */
    public function logEmailDeliveryFailure(string $emailType, array $recipients, string $reason, array $context = []): void;

    /**
     * Log marketer qualification events
     */
    public function logMarketerQualification(User $user, bool $qualified, array $qualificationData): void;

    /**
     * Log marketer promotion
     */
    public function logMarketerPromotion(User $user, array $promotionData): void;

    /**
     * Log security breach events
     */
    public function logSecurityBreach(array $breachData, string $severity): void;

    /**
     * Log authentication errors
     */
    public function logAuthenticationError(\Throwable $exception, Request $request, array $context = []): void;

    /**
     * Log payment errors
     */
    public function logPaymentError(\Throwable $exception, Request $request, array $context = []): void;

    /**
     * Log session errors
     */
    public function logSessionError(\Throwable $exception, Request $request, array $context = []): void;

    /**
     * Log email errors
     */
    public function logEmailError(\Throwable $exception, Request $request, array $context = []): void;

    /**
     * Log system errors
     */
    public function logSystemError(\Throwable $exception, Request $request, array $context = []): void;

    /**
     * Log security errors
     */
    public function logSecurityError(\Throwable $exception, Request $request, array $context = []): void;
}