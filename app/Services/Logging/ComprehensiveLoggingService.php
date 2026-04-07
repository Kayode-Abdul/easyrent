<?php

namespace App\Services\Logging;

use App\Models\ApartmentInvitation;
use App\Models\User;
use App\Models\Payment;
use App\Models\ActivityLog;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ComprehensiveLoggingService
{
    protected EasyRentLinkLogger $easyRentLogger;

    public function __construct(EasyRentLinkLogger $easyRentLogger)
    {
        $this->easyRentLogger = $easyRentLogger;
    }

    /**
     * Log comprehensive invitation access with all required details
     */
    public function logInvitationAccess(
        ApartmentInvitation $invitation, 
        Request $request, 
        ?User $user = null,
        array $additionalContext = []
    ): void {
        // Use the existing EasyRent logger
        $this->easyRentLogger->logInvitationAccess($invitation, $request, $user);

        // Add comprehensive audit logging
        $this->createAuditLog([
            'action' => 'invitation_access',
            'model_type' => ApartmentInvitation::class,
            'model_id' => $invitation->id,
            'user_id' => $user?->user_id,
            'description' => "Apartment invitation accessed for apartment ID: {$invitation->apartment_id}",
            'old_values' => null,
            'new_values' => [
                'access_timestamp' => now()->toISOString(),
                'user_authenticated' => $user !== null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_id' => $request->session()->getId(),
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'performed_at' => now(),
        ]);

        // Log security-related access patterns
        $this->logSecurityMetrics($invitation, $request, $user, $additionalContext);
    }

    /**
     * Log authentication events with comprehensive context
     */
    public function logAuthenticationEvent(
        string $eventType, 
        Request $request, 
        ?User $user = null, 
        array $additionalData = []
    ): void {
        // Use existing EasyRent logger
        $this->easyRentLogger->logAuthenticationEvent($eventType, $request, $user, $additionalData);

        // Create comprehensive audit log
        $this->createAuditLog([
            'action' => "auth_{$eventType}",
            'model_type' => User::class,
            'model_id' => $user?->user_id,
            'user_id' => $user?->user_id,
            'description' => "Authentication event: {$eventType}" . ($user ? " for user {$user->email}" : ""),
            'old_values' => null,
            'new_values' => array_merge([
                'event_type' => $eventType,
                'timestamp' => now()->toISOString(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_id' => $request->session()->getId(),
                'has_invitation_context' => $request->session()->has('invitation_context'),
            ], $additionalData),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'performed_at' => now(),
        ]);

        // Log failed authentication attempts for security monitoring
        if (str_contains($eventType, 'failed') || str_contains($eventType, 'blocked')) {
            $this->logSecurityEvent('authentication_failure', $request, [
                'event_type' => $eventType,
                'user_email' => $user?->email,
                'severity' => 'medium',
                'additional_data' => $additionalData,
            ]);
        }
    }

    /**
     * Log payment transactions with comprehensive details
     */
    public function logPaymentTransaction(
        string $eventType, 
        Payment $payment, 
        Request $request, 
        array $additionalData = []
    ): void {
        $startTime = microtime(true);

        // Use existing EasyRent logger
        $this->easyRentLogger->logPaymentTransaction($eventType, $payment, $request, $additionalData);

        $processingTime = (microtime(true) - $startTime) * 1000;

        // Create comprehensive audit log
        $this->createAuditLog([
            'action' => "payment_{$eventType}",
            'model_type' => Payment::class,
            'model_id' => $payment->id,
            'user_id' => $payment->tenant_id,
            'description' => "Payment {$eventType}: {$payment->payment_reference} - Amount: {$payment->amount}",
            'old_values' => $payment->getOriginal(),
            'new_values' => array_merge($payment->getAttributes(), [
                'processing_time_ms' => $processingTime,
                'event_timestamp' => now()->toISOString(),
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'performed_at' => now(),
        ]);

        // Log payment performance metrics
        $this->easyRentLogger->logPerformanceMetrics("payment_{$eventType}", $processingTime / 1000, [
            'payment_id' => $payment->id,
            'payment_amount' => $payment->amount,
            'payment_method' => $payment->payment_method,
            'gateway_response_time' => $additionalData['gateway_response_time'] ?? null,
        ]);

        // Log suspicious payment patterns
        if ($eventType === 'failed' || $payment->amount > 1000000) { // Large amounts
            $this->logSecurityEvent('payment_monitoring', $request, [
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'event_type' => $eventType,
                'severity' => $payment->amount > 1000000 ? 'high' : 'medium',
            ]);
        }
    }

    /**
     * Log errors with comprehensive debugging context
     */
    public function logError(
        \Throwable $exception, 
        Request $request, 
        array $context = []
    ): void {
        // Use existing EasyRent logger
        $this->easyRentLogger->logError($exception, $request, $context);

        // Create audit log for critical errors
        if ($this->isCriticalError($exception)) {
            $this->createAuditLog([
                'action' => 'critical_error',
                'model_type' => 'System',
                'model_id' => null,
                'user_id' => auth()->id(),
                'description' => "Critical error: {$exception->getMessage()}",
                'old_values' => null,
                'new_values' => [
                    'error_class' => get_class($exception),
                    'error_message' => $exception->getMessage(),
                    'error_code' => $exception->getCode(),
                    'error_file' => $exception->getFile(),
                    'error_line' => $exception->getLine(),
                    'request_url' => $request->fullUrl(),
                    'context' => $context,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'performed_at' => now(),
            ]);
        }

        // Log error patterns for monitoring
        $this->logErrorPattern($exception, $request, $context);
    }

    /**
     * Log performance metrics with enhanced context
     */
    public function logPerformanceMetrics(
        string $operation, 
        float $executionTime, 
        array $metrics = []
    ): void {
        // Use existing EasyRent logger
        $this->easyRentLogger->logPerformanceMetrics($operation, $executionTime, $metrics);

        // Log performance issues to audit log
        if ($executionTime > 2.0) { // Slow operations
            $this->createAuditLog([
                'action' => 'performance_issue',
                'model_type' => 'System',
                'model_id' => null,
                'user_id' => auth()->id(),
                'description' => "Slow operation detected: {$operation}",
                'old_values' => null,
                'new_values' => array_merge([
                    'operation' => $operation,
                    'execution_time_seconds' => $executionTime,
                    'performance_threshold_exceeded' => true,
                ], $metrics),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'performed_at' => now(),
            ]);
        }
    }

    /**
     * Log session lifecycle events with comprehensive tracking
     */
    public function logSessionEvent(
        string $eventType, 
        Request $request, 
        array $sessionData = []
    ): void {
        // Use existing EasyRent logger
        $this->easyRentLogger->logSessionEvent($eventType, $request, $sessionData);

        // Create audit log for important session events
        if (in_array($eventType, ['session_created', 'session_destroyed', 'session_hijack_detected'])) {
            $this->createAuditLog([
                'action' => "session_{$eventType}",
                'model_type' => 'Session',
                'model_id' => $request->session()->getId(),
                'user_id' => auth()->id(),
                'description' => "Session event: {$eventType}",
                'old_values' => null,
                'new_values' => [
                    'session_id' => $request->session()->getId(),
                    'event_type' => $eventType,
                    'session_data_keys' => array_keys($sessionData),
                    'timestamp' => now()->toISOString(),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'performed_at' => now(),
            ]);
        }
    }

    /**
     * Log security events with threat assessment
     */
    public function logSecurityEvent(
        string $eventType, 
        Request $request, 
        array $securityData = []
    ): void {
        // Use existing EasyRent logger
        $this->easyRentLogger->logSecurityEvent($eventType, $request, $securityData);

        // Create audit log for all security events
        $this->createAuditLog([
            'action' => "security_{$eventType}",
            'model_type' => 'Security',
            'model_id' => null,
            'user_id' => auth()->id(),
            'description' => "Security event: {$eventType}",
            'old_values' => null,
            'new_values' => array_merge([
                'event_type' => $eventType,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toISOString(),
                'threat_level' => $this->assessThreatLevel($eventType, $securityData),
            ], $securityData),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'performed_at' => now(),
        ]);
    }

    /**
     * Log email notification events
     */
    public function logEmailEvent(
        string $eventType, 
        string $recipient, 
        string $emailType, 
        array $emailData = []
    ): void {
        // Use existing EasyRent logger
        $this->easyRentLogger->logEmailEvent($eventType, $recipient, $emailType, $emailData);

        // Create audit log for email delivery tracking
        $this->createAuditLog([
            'action' => "email_{$eventType}",
            'model_type' => 'Email',
            'model_id' => null,
            'user_id' => auth()->id(),
            'description' => "Email {$eventType}: {$emailType} to {$recipient}",
            'old_values' => null,
            'new_values' => array_merge([
                'event_type' => $eventType,
                'recipient' => $recipient,
                'email_type' => $emailType,
                'timestamp' => now()->toISOString(),
            ], $emailData),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now(),
        ]);
    }

    /**
     * Create audit log entry
     */
    protected function createAuditLog(array $data): void
    {
        try {
            AuditLog::create($data);
        } catch (\Exception $e) {
            // Fallback to regular logging if audit log creation fails
            Log::error('Failed to create audit log entry', [
                'error' => $e->getMessage(),
                'audit_data' => $data,
            ]);
        }
    }

    /**
     * Log security metrics and patterns
     */
    protected function logSecurityMetrics(
        ApartmentInvitation $invitation, 
        Request $request, 
        ?User $user, 
        array $context
    ): void {
        // Track access patterns
        $accessPattern = [
            'invitation_id' => $invitation->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
            'user_authenticated' => $user !== null,
            'session_id' => $request->session()->getId(),
        ];

        // Check for suspicious patterns
        $suspiciousIndicators = [];
        
        // Multiple rapid accesses from same IP
        $recentAccesses = ActivityLog::where('ip_address', $request->ip())
            ->where('action', 'invitation_access')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();
            
        if ($recentAccesses > 10) {
            $suspiciousIndicators[] = 'rapid_access_pattern';
        }

        // Access from different countries in short time
        // (This would require GeoIP lookup - simplified for now)
        
        if (!empty($suspiciousIndicators)) {
            $this->logSecurityEvent('suspicious_access_pattern', $request, [
                'invitation_id' => $invitation->id,
                'indicators' => $suspiciousIndicators,
                'access_count' => $recentAccesses,
                'severity' => 'medium',
            ]);
        }
    }

    /**
     * Log error patterns for monitoring
     */
    protected function logErrorPattern(\Throwable $exception, Request $request, array $context): void
    {
        $errorSignature = md5(get_class($exception) . $exception->getFile() . $exception->getLine());
        
        // This could be enhanced to track error frequency and patterns
        Log::channel('easyrent_errors')->info('Error pattern tracking', [
            'error_signature' => $errorSignature,
            'error_class' => get_class($exception),
            'error_message' => $exception->getMessage(),
            'occurrence_timestamp' => now()->toISOString(),
            'request_url' => $request->fullUrl(),
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Determine if an error is critical
     */
    protected function isCriticalError(\Throwable $exception): bool
    {
        $criticalErrors = [
            'PDOException',
            'QueryException',
            'FatalErrorException',
            'OutOfMemoryError',
        ];

        return in_array(get_class($exception), $criticalErrors) ||
               str_contains($exception->getMessage(), 'database') ||
               str_contains($exception->getMessage(), 'payment') ||
               str_contains($exception->getMessage(), 'security');
    }

    /**
     * Assess threat level for security events
     */
    protected function assessThreatLevel(string $eventType, array $securityData): string
    {
        $highThreatEvents = [
            'sql_injection_attempt',
            'xss_attempt',
            'brute_force_attack',
            'session_hijack_detected',
        ];

        $mediumThreatEvents = [
            'rate_limit_exceeded',
            'suspicious_access_pattern',
            'authentication_failure',
            'invalid_token_access',
        ];

        if (in_array($eventType, $highThreatEvents)) {
            return 'high';
        }

        if (in_array($eventType, $mediumThreatEvents)) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Get comprehensive logging statistics
     */
    public function getLoggingStatistics(): array
    {
        $last24Hours = now()->subHours(24);

        return [
            'invitation_accesses' => ActivityLog::where('action', 'invitation_access')
                ->where('created_at', '>=', $last24Hours)->count(),
            'authentication_events' => ActivityLog::where('action', 'LIKE', 'auth_%')
                ->where('created_at', '>=', $last24Hours)->count(),
            'payment_transactions' => ActivityLog::where('action', 'LIKE', 'payment_%')
                ->where('created_at', '>=', $last24Hours)->count(),
            'security_events' => AuditLog::where('action', 'LIKE', 'security_%')
                ->where('performed_at', '>=', $last24Hours)->count(),
            'error_count' => AuditLog::where('action', 'critical_error')
                ->where('performed_at', '>=', $last24Hours)->count(),
            'performance_issues' => AuditLog::where('action', 'performance_issue')
                ->where('performed_at', '>=', $last24Hours)->count(),
        ];
    }
}