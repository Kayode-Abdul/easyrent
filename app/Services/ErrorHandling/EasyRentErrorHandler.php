<?php

namespace App\Services\ErrorHandling;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\Logging\EasyRentLogger;
use App\Services\Monitoring\SystemHealthMonitor;
use Carbon\Carbon;

class EasyRentErrorHandler implements ErrorHandlerInterface
{
    protected $logger;
    protected $healthMonitor;

    public function __construct(EasyRentLogger $logger, SystemHealthMonitor $healthMonitor = null)
    {
        $this->logger = $logger;
        $this->healthMonitor = $healthMonitor;
    }

    /**
     * Handle authentication flow errors
     */
    public function handleAuthenticationError(Throwable $exception, Request $request, array $context = []): array
    {
        $errorType = $this->classifyAuthenticationError($exception);
        
        // Log the error with context
        $this->logger->logAuthenticationError($exception, $request, array_merge($context, [
            'error_type' => $errorType,
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'session_id' => $request->session()->getId()
        ]));

        // Determine recovery strategy
        $recoveryOptions = $this->getAuthenticationRecoveryOptions($errorType, $context);
        
        // Check if session preservation is needed
        $preserveSession = in_array($errorType, [
            'session_timeout', 
            'registration_failure', 
            'login_failure'
        ]);

        return [
            'error_type' => $errorType,
            'user_message' => $this->getUserFriendlyMessage('authentication', $context),
            'recovery_options' => $recoveryOptions,
            'preserve_session' => $preserveSession,
            'redirect_url' => $this->getAuthenticationRedirectUrl($errorType, $request),
            'requires_attention' => $this->requiresImmediateAttention($exception),
            'retry_allowed' => $this->isRetryAllowed($errorType),
            'context_preserved' => $preserveSession
        ];
    }

    /**
     * Handle payment processing errors
     */
    public function handlePaymentError(Throwable $exception, Request $request, array $context = []): array
    {
        $errorType = $this->classifyPaymentError($exception);
        
        // Log payment error with transaction details
        $this->logger->logPaymentError($exception, $request, array_merge($context, [
            'error_type' => $errorType,
            'payment_reference' => $context['payment_reference'] ?? null,
            'amount' => $context['amount'] ?? null,
            'gateway_response' => $context['gateway_response'] ?? null
        ]));

        // Determine if payment state should be preserved
        $preserveState = in_array($errorType, [
            'gateway_timeout',
            'network_error',
            'temporary_failure',
            'insufficient_funds'
        ]);

        // Get retry configuration
        $retryConfig = $this->getPaymentRetryConfiguration($errorType);

        return [
            'error_type' => $errorType,
            'user_message' => $this->getUserFriendlyMessage('payment', array_merge($context, ['error_type' => $errorType])),
            'recovery_options' => $this->getPaymentRecoveryOptions($errorType, $context),
            'preserve_state' => $preserveState,
            'retry_config' => $retryConfig,
            'requires_attention' => $this->requiresImmediateAttention($exception),
            'fallback_available' => $this->hasFallbackPaymentMethod($context),
            'support_reference' => $this->generateSupportReference($context)
        ];
    }

    /**
     * Handle session management errors
     */
    public function handleSessionError(Throwable $exception, Request $request, array $context = []): array
    {
        $errorType = $this->classifySessionError($exception);
        
        // Log session error
        $this->logger->logSessionError($exception, $request, array_merge($context, [
            'error_type' => $errorType,
            'session_id' => $request->session()->getId(),
            'invitation_token' => $context['invitation_token'] ?? null
        ]));

        // Determine recovery strategy
        $recoveryStrategy = $this->getSessionRecoveryStrategy($errorType, $context);

        return [
            'error_type' => $errorType,
            'user_message' => $this->getUserFriendlyMessage('session', $context),
            'recovery_strategy' => $recoveryStrategy,
            'requires_fresh_start' => in_array($errorType, ['session_corrupted', 'session_expired']),
            'data_recoverable' => $this->isSessionDataRecoverable($errorType, $context),
            'requires_attention' => $this->requiresImmediateAttention($exception)
        ];
    }

    /**
     * Handle email notification errors
     */
    public function handleEmailError(Throwable $exception, Request $request, array $context = []): array
    {
        $errorType = $this->classifyEmailError($exception);
        
        // Log email error
        $this->logger->logEmailError($exception, $request, array_merge($context, [
            'error_type' => $errorType,
            'recipient' => $context['recipient'] ?? null,
            'email_type' => $context['email_type'] ?? null
        ]));

        // Determine retry strategy
        $retryStrategy = $this->getEmailRetryStrategy($errorType);

        return [
            'error_type' => $errorType,
            'user_message' => $this->getUserFriendlyMessage('email', $context),
            'retry_strategy' => $retryStrategy,
            'alternative_delivery' => $this->getAlternativeDeliveryMethods($context),
            'requires_attention' => $this->requiresImmediateAttention($exception),
            'queue_for_retry' => $this->shouldQueueForRetry($errorType)
        ];
    }

    /**
     * Handle system errors with graceful degradation
     */
    public function handleSystemError(Throwable $exception, Request $request, array $context = []): array
    {
        $errorType = $this->classifySystemError($exception);
        
        // Log system error with full context
        $this->logger->logSystemError($exception, $request, array_merge($context, [
            'error_type' => $errorType,
            'system_load' => $this->getSystemLoad(),
            'memory_usage' => memory_get_usage(true),
            'database_status' => $this->getDatabaseStatus()
        ]));

        // Update system health monitoring
        if ($this->healthMonitor) {
            $this->healthMonitor->recordSystemError($errorType, $exception);
        }

        // Determine degradation strategy
        $degradationStrategy = $this->getGracefulDegradationStrategy($errorType);

        return [
            'error_type' => $errorType,
            'user_message' => $this->getUserFriendlyMessage('system', $context),
            'degradation_strategy' => $degradationStrategy,
            'fallback_available' => $this->hasFallbackService($errorType),
            'requires_attention' => $this->requiresImmediateAttention($exception),
            'estimated_recovery_time' => $this->getEstimatedRecoveryTime($errorType),
            'alternative_actions' => $this->getAlternativeActions($errorType, $context)
        ];
    }

    /**
     * Handle security-related errors
     */
    public function handleSecurityError(Throwable $exception, Request $request, array $context = []): array
    {
        $errorType = $this->classifySecurityError($exception);
        
        // Log security error with high priority
        $this->logger->logSecurityError($exception, $request, array_merge($context, [
            'error_type' => $errorType,
            'threat_level' => $this->assessThreatLevel($errorType),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]));

        // Determine security response
        $securityResponse = $this->getSecurityResponse($errorType, $context);

        return [
            'error_type' => $errorType,
            'user_message' => $this->getUserFriendlyMessage('security', $context),
            'security_response' => $securityResponse,
            'block_required' => $this->requiresBlocking($errorType),
            'requires_attention' => true, // All security errors require attention
            'escalation_needed' => $this->requiresEscalation($errorType),
            'recovery_time' => $this->getSecurityRecoveryTime($errorType)
        ];
    }

    /**
     * Get user-friendly error message
     */
    public function getUserFriendlyMessage(string $errorType, array $context = []): string
    {
        $messages = [
            'authentication' => [
                'session_timeout' => 'Your session has expired. Please log in again to continue with your apartment application.',
                'registration_failure' => 'We encountered an issue creating your account. Please try again or contact support if the problem persists.',
                'login_failure' => 'Login failed. Please check your credentials and try again.',
                'invalid_credentials' => 'The email or password you entered is incorrect. Please try again.',
                'account_locked' => 'Your account has been temporarily locked for security reasons. Please contact support.',
                'default' => 'We encountered an authentication issue. Please try logging in again.'
            ],
            'payment' => [
                'gateway_timeout' => 'The payment system is temporarily unavailable. Please try again in a few minutes.',
                'insufficient_funds' => 'Your payment was declined due to insufficient funds. Please check your account balance or try a different payment method.',
                'network_error' => 'We\'re experiencing connectivity issues. Your payment is being processed and we\'ll update you shortly.',
                'invalid_card' => 'The payment information provided is invalid. Please check your details and try again.',
                'payment_declined' => 'Your payment was declined by your bank. Please contact your bank or try a different payment method.',
                'temporary_failure' => 'We\'re experiencing temporary payment processing issues. Please try again in a few minutes.',
                'default' => 'We encountered an issue processing your payment. Please try again or contact support.'
            ],
            'session' => [
                'session_expired' => 'Your session has expired. Please start over by accessing the apartment invitation link again.',
                'session_corrupted' => 'We encountered an issue with your session data. Please start fresh by accessing the invitation link again.',
                'storage_failure' => 'We\'re having trouble saving your application data. Please try again.',
                'default' => 'We encountered a session issue. Please refresh the page and try again.'
            ],
            'email' => [
                'delivery_failure' => 'We\'re having trouble sending emails right now. We\'ll keep trying and notify you once delivered.',
                'invalid_recipient' => 'The email address appears to be invalid. Please check and update your email address.',
                'quota_exceeded' => 'We\'ve reached our email sending limit. Your email will be sent shortly.',
                'default' => 'We encountered an issue sending your email notification. We\'ll keep trying to deliver it.'
            ],
            'system' => [
                'database_error' => 'We\'re experiencing database connectivity issues. Please try again in a few minutes.',
                'service_unavailable' => 'Some services are temporarily unavailable. Core functionality remains available.',
                'high_load' => 'We\'re experiencing high traffic. Please be patient as we process your request.',
                'maintenance_mode' => 'The system is undergoing maintenance. Please try again shortly.',
                'default' => 'We\'re experiencing technical difficulties. Please try again in a few minutes.'
            ],
            'security' => [
                'suspicious_activity' => 'Suspicious activity has been detected. Access has been temporarily restricted for security.',
                'rate_limit_exceeded' => 'Too many requests have been made. Please wait before trying again.',
                'invalid_token' => 'The invitation link appears to be invalid or has been tampered with.',
                'access_blocked' => 'Access has been blocked due to security concerns. Please contact support if you believe this is an error.',
                'default' => 'A security issue has been detected. Please contact support for assistance.'
            ]
        ];

        $categoryMessages = $messages[$errorType] ?? $messages['system'];
        $specificType = $context['error_type'] ?? 'default';
        
        return $categoryMessages[$specificType] ?? $categoryMessages['default'];
    }

    /**
     * Determine if error requires immediate attention
     */
    public function requiresImmediateAttention(Throwable $exception): bool
    {
        $criticalErrors = [
            'database_connection_failed',
            'payment_gateway_down',
            'security_breach_detected',
            'data_corruption_detected',
            'service_completely_unavailable'
        ];

        $errorMessage = strtolower($exception->getMessage());
        
        foreach ($criticalErrors as $criticalError) {
            if (strpos($errorMessage, str_replace('_', ' ', $criticalError)) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get recovery options for specific error types
     */
    public function getRecoveryOptions(string $errorType, array $context = []): array
    {
        switch ($errorType) {
            case 'authentication':
                return $this->getAuthenticationRecoveryOptions($context['error_type'] ?? 'default', $context);
            case 'payment':
                return $this->getPaymentRecoveryOptions($context['error_type'] ?? 'default', $context);
            case 'session':
                return $this->getSessionRecoveryOptions($context['error_type'] ?? 'default', $context);
            case 'email':
                return $this->getEmailRecoveryOptions($context['error_type'] ?? 'default', $context);
            case 'system':
                return $this->getSystemRecoveryOptions($context['error_type'] ?? 'default', $context);
            case 'security':
                return $this->getSecurityRecoveryOptions($context['error_type'] ?? 'default', $context);
            default:
                return ['retry' => true, 'contact_support' => true];
        }
    }

    // Private helper methods

    private function classifyAuthenticationError(Throwable $exception): string
    {
        $message = strtolower($exception->getMessage());
        
        if (strpos($message, 'session') !== false && strpos($message, 'expired') !== false) {
            return 'session_timeout';
        }
        if (strpos($message, 'credentials') !== false || strpos($message, 'password') !== false) {
            return 'invalid_credentials';
        }
        if (strpos($message, 'registration') !== false) {
            return 'registration_failure';
        }
        if (strpos($message, 'locked') !== false || strpos($message, 'blocked') !== false) {
            return 'account_locked';
        }
        
        return 'login_failure';
    }

    private function classifyPaymentError(Throwable $exception): string
    {
        $message = strtolower($exception->getMessage());
        
        if (strpos($message, 'timeout') !== false || strpos($message, 'connection') !== false) {
            return 'gateway_timeout';
        }
        if (strpos($message, 'insufficient') !== false || strpos($message, 'funds') !== false) {
            return 'insufficient_funds';
        }
        if (strpos($message, 'declined') !== false) {
            return 'payment_declined';
        }
        if (strpos($message, 'invalid') !== false && strpos($message, 'card') !== false) {
            return 'invalid_card';
        }
        if (strpos($message, 'network') !== false) {
            return 'network_error';
        }
        
        return 'temporary_failure';
    }

    private function classifySessionError(Throwable $exception): string
    {
        $message = strtolower($exception->getMessage());
        
        if (strpos($message, 'expired') !== false) {
            return 'session_expired';
        }
        if (strpos($message, 'corrupted') !== false || strpos($message, 'invalid') !== false) {
            return 'session_corrupted';
        }
        if (strpos($message, 'storage') !== false || strpos($message, 'save') !== false) {
            return 'storage_failure';
        }
        
        return 'session_error';
    }

    private function classifyEmailError(Throwable $exception): string
    {
        $message = strtolower($exception->getMessage());
        
        if (strpos($message, 'delivery') !== false || strpos($message, 'send') !== false) {
            return 'delivery_failure';
        }
        if (strpos($message, 'invalid') !== false && strpos($message, 'email') !== false) {
            return 'invalid_recipient';
        }
        if (strpos($message, 'quota') !== false || strpos($message, 'limit') !== false) {
            return 'quota_exceeded';
        }
        
        return 'email_error';
    }

    private function classifySystemError(Throwable $exception): string
    {
        $message = strtolower($exception->getMessage());
        
        if (strpos($message, 'database') !== false || strpos($message, 'connection') !== false) {
            return 'database_error';
        }
        if (strpos($message, 'service') !== false && strpos($message, 'unavailable') !== false) {
            return 'service_unavailable';
        }
        if (strpos($message, 'memory') !== false || strpos($message, 'load') !== false) {
            return 'high_load';
        }
        if (strpos($message, 'maintenance') !== false) {
            return 'maintenance_mode';
        }
        
        return 'system_error';
    }

    private function classifySecurityError(Throwable $exception): string
    {
        $message = strtolower($exception->getMessage());
        
        if (strpos($message, 'suspicious') !== false) {
            return 'suspicious_activity';
        }
        if (strpos($message, 'rate') !== false && strpos($message, 'limit') !== false) {
            return 'rate_limit_exceeded';
        }
        if (strpos($message, 'token') !== false && strpos($message, 'invalid') !== false) {
            return 'invalid_token';
        }
        if (strpos($message, 'blocked') !== false || strpos($message, 'denied') !== false) {
            return 'access_blocked';
        }
        
        return 'security_error';
    }

    private function getAuthenticationRecoveryOptions(string $errorType, array $context): array
    {
        switch ($errorType) {
            case 'session_timeout':
                return [
                    'retry_login' => true,
                    'preserve_context' => true,
                    'redirect_to_login' => true,
                    'message' => 'Your application details have been saved. Please log in to continue.'
                ];
            case 'registration_failure':
                return [
                    'retry_registration' => true,
                    'try_login_instead' => true,
                    'contact_support' => true,
                    'preserve_invitation_context' => true
                ];
            case 'invalid_credentials':
                return [
                    'retry_login' => true,
                    'password_reset' => true,
                    'try_registration' => true
                ];
            default:
                return [
                    'retry' => true,
                    'contact_support' => true,
                    'preserve_context' => true
                ];
        }
    }

    private function getPaymentRecoveryOptions(string $errorType, array $context): array
    {
        switch ($errorType) {
            case 'gateway_timeout':
            case 'network_error':
                return [
                    'retry_payment' => true,
                    'retry_delay' => 60, // seconds
                    'max_retries' => 3,
                    'preserve_application_state' => true
                ];
            case 'insufficient_funds':
                return [
                    'try_different_card' => true,
                    'contact_bank' => true,
                    'save_application' => true
                ];
            case 'payment_declined':
                return [
                    'try_different_card' => true,
                    'contact_bank' => true,
                    'verify_details' => true
                ];
            default:
                return [
                    'retry_payment' => true,
                    'contact_support' => true,
                    'preserve_state' => true
                ];
        }
    }

    private function getSessionRecoveryOptions(string $errorType, array $context): array
    {
        switch ($errorType) {
            case 'session_expired':
                return [
                    'restart_from_invitation' => true,
                    'message' => 'Please access the apartment invitation link again to start fresh.'
                ];
            case 'session_corrupted':
                return [
                    'clear_session' => true,
                    'restart_process' => true,
                    'contact_support_if_persists' => true
                ];
            default:
                return [
                    'refresh_page' => true,
                    'restart_if_needed' => true
                ];
        }
    }

    private function getEmailRecoveryOptions(string $errorType, array $context): array
    {
        return [
            'retry_automatically' => true,
            'check_spam_folder' => true,
            'update_email_address' => true,
            'contact_support' => true
        ];
    }

    private function getSystemRecoveryOptions(string $errorType, array $context): array
    {
        switch ($errorType) {
            case 'database_error':
                return [
                    'retry_after_delay' => true,
                    'delay_seconds' => 30,
                    'fallback_available' => false
                ];
            case 'service_unavailable':
                return [
                    'retry_after_delay' => true,
                    'delay_seconds' => 60,
                    'partial_functionality' => true
                ];
            default:
                return [
                    'retry_after_delay' => true,
                    'contact_support' => true
                ];
        }
    }

    private function getSecurityRecoveryOptions(string $errorType, array $context): array
    {
        switch ($errorType) {
            case 'rate_limit_exceeded':
                return [
                    'wait_and_retry' => true,
                    'wait_time_minutes' => 15
                ];
            case 'suspicious_activity':
                return [
                    'contact_support' => true,
                    'verify_identity' => true
                ];
            default:
                return [
                    'contact_support' => true
                ];
        }
    }

    private function getAuthenticationRedirectUrl(string $errorType, Request $request): ?string
    {
        if (in_array($errorType, ['session_timeout', 'login_failure'])) {
            return route('login', ['invitation_redirect' => true]);
        }
        if ($errorType === 'registration_failure') {
            return route('register', ['invitation_redirect' => true]);
        }
        return null;
    }

    private function isRetryAllowed(string $errorType): bool
    {
        $noRetryErrors = ['account_locked', 'access_blocked', 'security_breach'];
        return !in_array($errorType, $noRetryErrors);
    }

    private function getPaymentRetryConfiguration(string $errorType): array
    {
        $retryConfigs = [
            'gateway_timeout' => ['max_retries' => 3, 'delay' => 60, 'exponential_backoff' => true],
            'network_error' => ['max_retries' => 3, 'delay' => 30, 'exponential_backoff' => true],
            'temporary_failure' => ['max_retries' => 2, 'delay' => 120, 'exponential_backoff' => false],
            'insufficient_funds' => ['max_retries' => 0, 'delay' => 0, 'exponential_backoff' => false],
            'payment_declined' => ['max_retries' => 1, 'delay' => 0, 'exponential_backoff' => false]
        ];

        return $retryConfigs[$errorType] ?? ['max_retries' => 1, 'delay' => 60, 'exponential_backoff' => false];
    }

    private function hasFallbackPaymentMethod(array $context): bool
    {
        // Check if alternative payment methods are available
        return isset($context['alternative_methods']) && !empty($context['alternative_methods']);
    }

    private function generateSupportReference(array $context): string
    {
        return 'ER-' . date('Ymd') . '-' . substr(md5(json_encode($context) . time()), 0, 8);
    }

    private function getSessionRecoveryStrategy(string $errorType, array $context): array
    {
        return [
            'clear_corrupted_data' => in_array($errorType, ['session_corrupted']),
            'preserve_invitation_context' => true,
            'restart_required' => in_array($errorType, ['session_expired', 'session_corrupted']),
            'data_recovery_possible' => $this->isSessionDataRecoverable($errorType, $context)
        ];
    }

    private function isSessionDataRecoverable(string $errorType, array $context): bool
    {
        // Session data is recoverable if it's stored in multiple places
        return isset($context['invitation_token']) && 
               !in_array($errorType, ['session_corrupted', 'storage_failure']);
    }

    private function getEmailRetryStrategy(string $errorType): array
    {
        $strategies = [
            'delivery_failure' => ['retry_count' => 3, 'delay_minutes' => [5, 15, 60]],
            'quota_exceeded' => ['retry_count' => 1, 'delay_minutes' => [60]],
            'invalid_recipient' => ['retry_count' => 0, 'delay_minutes' => []]
        ];

        return $strategies[$errorType] ?? ['retry_count' => 2, 'delay_minutes' => [10, 30]];
    }

    private function getAlternativeDeliveryMethods(array $context): array
    {
        $alternatives = [];
        
        if (isset($context['phone'])) {
            $alternatives[] = 'sms';
        }
        
        $alternatives[] = 'dashboard_notification';
        $alternatives[] = 'in_app_message';
        
        return $alternatives;
    }

    private function shouldQueueForRetry(string $errorType): bool
    {
        return !in_array($errorType, ['invalid_recipient', 'quota_exceeded']);
    }

    private function getGracefulDegradationStrategy(string $errorType): array
    {
        $strategies = [
            'database_error' => [
                'cache_fallback' => true,
                'read_only_mode' => true,
                'essential_functions_only' => true
            ],
            'service_unavailable' => [
                'core_functions_available' => true,
                'non_essential_disabled' => true,
                'queue_requests' => true
            ],
            'high_load' => [
                'rate_limiting' => true,
                'cache_aggressive' => true,
                'defer_non_critical' => true
            ]
        ];

        return $strategies[$errorType] ?? [
            'basic_functionality' => true,
            'queue_requests' => true
        ];
    }

    private function hasFallbackService(string $errorType): bool
    {
        $fallbackAvailable = [
            'database_error' => true, // Cache fallback
            'email_service_down' => true, // Queue for later
            'payment_gateway_down' => false // No fallback for payments
        ];

        return $fallbackAvailable[$errorType] ?? false;
    }

    private function getEstimatedRecoveryTime(string $errorType): string
    {
        $recoveryTimes = [
            'database_error' => '5-10 minutes',
            'service_unavailable' => '10-15 minutes',
            'high_load' => '2-5 minutes',
            'network_error' => '1-3 minutes'
        ];

        return $recoveryTimes[$errorType] ?? '5-15 minutes';
    }

    private function getAlternativeActions(string $errorType, array $context): array
    {
        switch ($errorType) {
            case 'database_error':
                return [
                    'view_cached_data' => 'View previously loaded apartment information',
                    'save_offline' => 'Save your application details for later submission',
                    'contact_landlord' => 'Contact the landlord directly using provided contact information'
                ];
            case 'payment_gateway_down':
                return [
                    'save_application' => 'Save your application and complete payment later',
                    'alternative_payment' => 'Try alternative payment methods if available',
                    'contact_support' => 'Contact support for manual payment processing'
                ];
            default:
                return [
                    'try_later' => 'Try again in a few minutes',
                    'contact_support' => 'Contact support if the issue persists'
                ];
        }
    }

    private function assessThreatLevel(string $errorType): string
    {
        $threatLevels = [
            'suspicious_activity' => 'medium',
            'rate_limit_exceeded' => 'low',
            'invalid_token' => 'high',
            'access_blocked' => 'high'
        ];

        return $threatLevels[$errorType] ?? 'medium';
    }

    private function getSecurityResponse(string $errorType, array $context): array
    {
        switch ($errorType) {
            case 'suspicious_activity':
                return [
                    'temporary_block' => true,
                    'block_duration_minutes' => 15,
                    'notify_admin' => true,
                    'log_details' => true
                ];
            case 'rate_limit_exceeded':
                return [
                    'temporary_block' => true,
                    'block_duration_minutes' => 10,
                    'notify_admin' => false,
                    'log_details' => true
                ];
            case 'invalid_token':
                return [
                    'invalidate_token' => true,
                    'notify_admin' => true,
                    'log_details' => true,
                    'investigate_source' => true
                ];
            default:
                return [
                    'log_details' => true,
                    'notify_admin' => true
                ];
        }
    }

    private function requiresBlocking(string $errorType): bool
    {
        return in_array($errorType, ['suspicious_activity', 'rate_limit_exceeded', 'access_blocked']);
    }

    private function requiresEscalation(string $errorType): bool
    {
        return in_array($errorType, ['invalid_token', 'security_breach_detected', 'data_tampering']);
    }

    private function getSecurityRecoveryTime(string $errorType): string
    {
        $recoveryTimes = [
            'rate_limit_exceeded' => '10-15 minutes',
            'suspicious_activity' => '15-30 minutes',
            'invalid_token' => 'Contact support required',
            'access_blocked' => 'Manual review required'
        ];

        return $recoveryTimes[$errorType] ?? 'Contact support required';
    }

    private function getSystemLoad(): array
    {
        return [
            'cpu_usage' => sys_getloadavg()[0] ?? 0,
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true)
        ];
    }

    private function getDatabaseStatus(): string
    {
        try {
            \DB::connection()->getPdo();
            return 'connected';
        } catch (\Exception $e) {
            return 'disconnected';
        }
    }
}