<?php

namespace App\Services\Logging;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\ApartmentInvitation;
use App\Models\User;
use App\Models\Payment;
use Carbon\Carbon;

class EasyRentLinkLogger
{
    /**
     * Log invitation access with timestamps and user information
     */
    public function logInvitationAccess(ApartmentInvitation $invitation, Request $request, ?User $user = null): void
    {
        $logData = [
            'event_type' => 'invitation_access',
            'invitation_id' => $invitation->id,
            'invitation_token' => $invitation->token,
            'apartment_id' => $invitation->apartment_id,
            'user_id' => $user?->user_id,
            'user_authenticated' => $user !== null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
            'access_timestamp' => Carbon::now()->toISOString(),
            'session_id' => $request->session()->getId(),
            'request_method' => $request->method(),
            'request_url' => $request->fullUrl(),
        ];

        Log::channel('easyrent_invitations')->info('Invitation accessed', $logData);
        
        // Also log to activity log for database tracking
        if ($user) {
            \App\Models\ActivityLog::create([
                'user_id' => $user->user_id,
                'action' => 'invitation_access',
                'description' => "Accessed apartment invitation for apartment ID: {$invitation->apartment_id}",
                'ip_address' => $request->ip(),
            ]);
        }
    }

    /**
     * Log authentication events during invitation flow
     */
    public function logAuthenticationEvent(string $eventType, Request $request, ?User $user = null, array $additionalData = []): void
    {
        $logData = array_merge([
            'event_type' => 'authentication_event',
            'auth_event' => $eventType,
            'user_id' => $user?->user_id,
            'user_email' => $user?->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => $request->session()->getId(),
            'timestamp' => Carbon::now()->toISOString(),
            'has_invitation_context' => $request->session()->has('invitation_context'),
        ], $additionalData);

        Log::channel('easyrent_auth')->info("Authentication event: {$eventType}", $logData);

        // Log to activity log for important auth events
        if ($user && in_array($eventType, ['login_success', 'registration_success', 'login_failed'])) {
            \App\Models\ActivityLog::create([
                'user_id' => $user->user_id,
                'action' => $eventType,
                'description' => "Authentication event: {$eventType} via invitation flow",
                'ip_address' => $request->ip(),
            ]);
        }
    }

    /**
     * Log detailed payment transaction information
     */
    public function logPaymentTransaction(string $eventType, Payment $payment, Request $request, array $additionalData = []): void
    {
        $logData = array_merge([
            'event_type' => 'payment_transaction',
            'payment_event' => $eventType,
            'payment_id' => $payment->id,
            'payment_reference' => $payment->reference,
            'amount' => $payment->amount,
            'currency' => $payment->currency ?? 'NGN',
            'status' => $payment->status,
            'user_id' => $payment->user_id,
            'apartment_id' => $payment->apartment_id,
            'payment_method' => $payment->payment_method,
            'gateway_response' => $payment->gateway_response,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => $request->session()->getId(),
            'timestamp' => Carbon::now()->toISOString(),
            'processing_time_ms' => $additionalData['processing_time_ms'] ?? null,
        ], $additionalData);

        Log::channel('easyrent_payments')->info("Payment transaction: {$eventType}", $logData);

        // Log to activity log for payment events
        if ($payment->user_id) {
            \App\Models\ActivityLog::create([
                'user_id' => $payment->user_id,
                'action' => "payment_{$eventType}",
                'description' => "Payment {$eventType}: {$payment->reference} - Amount: {$payment->amount}",
                'ip_address' => $request->ip(),
            ]);
        }
    }

    /**
     * Log errors with comprehensive debugging context
     */
    public function logError(\Throwable $exception, Request $request, array $context = []): void
    {
        $logData = array_merge([
            'event_type' => 'error',
            'error_class' => get_class($exception),
            'error_message' => $exception->getMessage(),
            'error_code' => $exception->getCode(),
            'error_file' => $exception->getFile(),
            'error_line' => $exception->getLine(),
            'stack_trace' => $exception->getTraceAsString(),
            'request_method' => $request->method(),
            'request_url' => $request->fullUrl(),
            'request_data' => $request->except(['password', 'password_confirmation', '_token']),
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => $request->session()->getId(),
            'session_data' => $request->session()->all(),
            'timestamp' => Carbon::now()->toISOString(),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
        ], $context);

        Log::channel('easyrent_errors')->error('EasyRent Link Error', $logData);
    }

    /**
     * Log performance metrics and monitoring data
     */
    public function logPerformanceMetrics(string $operation, float $executionTime, array $metrics = []): void
    {
        $logData = array_merge([
            'event_type' => 'performance_metrics',
            'operation' => $operation,
            'execution_time_ms' => round($executionTime * 1000, 2),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'timestamp' => Carbon::now()->toISOString(),
        ], $metrics);

        Log::channel('easyrent_performance')->info("Performance: {$operation}", $logData);
    }

    /**
     * Log session lifecycle events
     */
    public function logSessionEvent(string $eventType, Request $request, array $sessionData = []): void
    {
        $logData = [
            'event_type' => 'session_lifecycle',
            'session_event' => $eventType,
            'session_id' => $request->session()->getId(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => auth()->id(),
            'session_data_keys' => array_keys($sessionData),
            'timestamp' => Carbon::now()->toISOString(),
        ];

        Log::channel('easyrent_sessions')->info("Session event: {$eventType}", $logData);
    }

    /**
     * Log security events and suspicious activities
     */
    public function logSecurityEvent(string $eventType, Request $request, array $securityData = []): void
    {
        $logData = array_merge([
            'event_type' => 'security_event',
            'security_event' => $eventType,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => auth()->id(),
            'session_id' => $request->session()->getId(),
            'request_url' => $request->fullUrl(),
            'timestamp' => Carbon::now()->toISOString(),
            'severity' => $securityData['severity'] ?? 'medium',
        ], $securityData);

        Log::channel('easyrent_security')->warning("Security event: {$eventType}", $logData);
    }

    /**
     * Log email notification events
     */
    public function logEmailEvent(string $eventType, string $recipient, string $emailType, array $emailData = []): void
    {
        $logData = array_merge([
            'event_type' => 'email_notification',
            'email_event' => $eventType,
            'recipient' => $recipient,
            'email_type' => $emailType,
            'timestamp' => Carbon::now()->toISOString(),
        ], $emailData);

        Log::channel('easyrent_emails')->info("Email event: {$eventType}", $logData);
    }

    /**
     * Log apartment assignment events
     */
    public function logApartmentAssignment(User $user, ApartmentInvitation $invitation, Payment $payment, Request $request): void
    {
        $logData = [
            'event_type' => 'apartment_assignment',
            'user_id' => $user->user_id,
            'user_email' => $user->email,
            'apartment_id' => $invitation->apartment_id,
            'invitation_id' => $invitation->id,
            'payment_id' => $payment->id,
            'payment_reference' => $payment->reference,
            'amount_paid' => $payment->amount,
            'ip_address' => $request->ip(),
            'timestamp' => Carbon::now()->toISOString(),
        ];

        Log::channel('easyrent_assignments')->info('Apartment assigned to user', $logData);

        // Log to activity log
        \App\Models\ActivityLog::create([
            'user_id' => $user->user_id,
            'action' => 'apartment_assignment',
            'description' => "Assigned to apartment ID: {$invitation->apartment_id} via payment: {$payment->reference}",
            'ip_address' => $request->ip(),
        ]);
    }
}