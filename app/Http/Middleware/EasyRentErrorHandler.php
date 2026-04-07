<?php

namespace App\Http\Middleware;

use Closure;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\ErrorHandling\EasyRentErrorHandler;
use App\Services\ErrorHandling\ErrorRecoveryService;
use App\Services\Monitoring\ErrorMonitoringService;

class EasyRentErrorHandler
{
    protected $errorHandler;
    protected $recoveryService;
    protected $monitoringService;

    public function __construct(
        EasyRentErrorHandler $errorHandler,
        ErrorRecoveryService $recoveryService,
        ErrorMonitoringService $monitoringService
    ) {
        $this->errorHandler = $errorHandler;
        $this->recoveryService = $recoveryService;
        $this->monitoringService = $monitoringService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (Throwable $exception) {
            return $this->handleException($exception, $request);
        }
    }

    /**
     * Handle exceptions with comprehensive error handling
     */
    protected function handleException(Throwable $exception, Request $request)
    {
        // Determine error type and context
        $errorType = $this->determineErrorType($exception, $request);
        $context = $this->buildErrorContext($exception, $request);

        // Track error for monitoring
        $this->monitoringService->trackError($exception, $request, $errorType, $context);

        // Handle specific error types
        switch ($errorType) {
            case 'authentication':
                return $this->handleAuthenticationError($exception, $request, $context);
            case 'payment':
                return $this->handlePaymentError($exception, $request, $context);
            case 'session':
                return $this->handleSessionError($exception, $request, $context);
            case 'email':
                return $this->handleEmailError($exception, $request, $context);
            case 'security':
                return $this->handleSecurityError($exception, $request, $context);
            case 'system':
            default:
                return $this->handleSystemError($exception, $request, $context);
        }
    }

    /**
     * Handle authentication errors
     */
    protected function handleAuthenticationError(Throwable $exception, Request $request, array $context)
    {
        $errorData = $this->errorHandler->handleAuthenticationError($exception, $request, $context);
        $recoveryResult = $this->recoveryService->recoverFromAuthenticationError($errorData, $request);

        // If this is an AJAX request, return JSON response
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error_type' => 'authentication',
                'message' => $errorData['user_message'],
                'recovery_options' => $errorData['recovery_options'],
                'redirect_url' => $errorData['redirect_url'] ?? null
            ], 401);
        }

        // For web requests, redirect with error information
        if (isset($errorData['redirect_url'])) {
            return redirect($errorData['redirect_url'])
                ->with('error', $errorData['user_message'])
                ->with('recovery_options', $errorData['recovery_options']);
        }

        return redirect()->route('login')
            ->with('error', $errorData['user_message'])
            ->with('recovery_options', $errorData['recovery_options']);
    }

    /**
     * Handle payment errors
     */
    protected function handlePaymentError(Throwable $exception, Request $request, array $context)
    {
        $errorData = $this->errorHandler->handlePaymentError($exception, $request, $context);
        $payment = $context['payment'] ?? null;
        $recoveryResult = $this->recoveryService->recoverFromPaymentError($errorData, $request, $payment);

        // If this is an AJAX request, return JSON response
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error_type' => 'payment',
                'message' => $errorData['user_message'],
                'recovery_options' => $errorData['recovery_options'],
                'support_reference' => $errorData['support_reference'] ?? null,
                'retry_config' => $errorData['retry_config'] ?? null
            ], 400);
        }

        // For web requests, show payment error page
        return response()->view('errors.payment-error', [
            'error_message' => $errorData['user_message'],
            'recovery_options' => $errorData['recovery_options'],
            'support_reference' => $errorData['support_reference'] ?? null,
            'payment_details' => $context['payment_details'] ?? null,
            'fallback_methods' => session('fallback_methods'),
            'retry_allowed' => $errorData['retry_config']['max_retries'] > 0
        ], 400);
    }

    /**
     * Handle session errors
     */
    protected function handleSessionError(Throwable $exception, Request $request, array $context)
    {
        $errorData = $this->errorHandler->handleSessionError($exception, $request, $context);
        $recoveryResult = $this->recoveryService->recoverFromSessionError($errorData, $request);

        // If this is an AJAX request, return JSON response
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error_type' => 'session',
                'message' => $errorData['user_message'],
                'recovery_strategy' => $errorData['recovery_strategy'],
                'requires_fresh_start' => $errorData['requires_fresh_start']
            ], 400);
        }

        // For web requests, show session error page
        return response()->view('errors.session-error', [
            'error_message' => $errorData['user_message'],
            'recovery_strategy' => $errorData['recovery_strategy'],
            'recovered_data' => $recoveryResult['recovered_data'] ?? null,
            'session_info' => [
                'session_id' => $request->session()->getId(),
                'last_activity' => session('last_activity'),
                'expiry_time' => $context['expiry_time'] ?? null
            ]
        ], 400);
    }

    /**
     * Handle email errors
     */
    protected function handleEmailError(Throwable $exception, Request $request, array $context)
    {
        $errorData = $this->errorHandler->handleEmailError($exception, $request, $context);
        $recoveryResult = $this->recoveryService->recoverFromEmailError($errorData, $request, $context);

        // Email errors are usually handled in background, so log and continue
        Log::warning('Email delivery error handled', [
            'error_type' => $errorData['error_type'],
            'recovery_actions' => $recoveryResult['actions_taken']
        ]);

        // If this is an AJAX request, return JSON response
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Request processed successfully. Email notifications may be delayed.',
                'email_status' => 'delayed'
            ]);
        }

        // For web requests, continue with success but show email delay notice
        $request->session()->flash('email_delay_notice', $errorData['user_message']);
        
        // Return to previous page or continue normal flow
        return redirect()->back()->with('info', 'Your request was processed successfully. Email notifications may be delayed.');
    }

    /**
     * Handle security errors
     */
    protected function handleSecurityError(Throwable $exception, Request $request, array $context)
    {
        $errorData = $this->errorHandler->handleSecurityError($exception, $request, $context);
        $recoveryResult = $this->recoveryService->recoverFromSecurityError($errorData, $request);

        // Security errors require immediate attention
        Log::critical('Security error handled', [
            'error_type' => $errorData['error_type'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'security_response' => $errorData['security_response']
        ]);

        // If this is an AJAX request, return JSON response
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error_type' => 'security',
                'message' => $errorData['user_message'],
                'blocked' => $errorData['block_required'],
                'recovery_time' => $errorData['recovery_time']
            ], 403);
        }

        // For web requests, show security block page
        return response()->view('apartment.invite.security-blocked', [
            'message' => $errorData['user_message'],
            'recovery_time' => $errorData['recovery_time'],
            'contact_support' => true
        ], 403);
    }

    /**
     * Handle system errors with graceful degradation
     */
    protected function handleSystemError(Throwable $exception, Request $request, array $context)
    {
        $errorData = $this->errorHandler->handleSystemError($exception, $request, $context);
        $recoveryResult = $this->recoveryService->recoverFromSystemError($errorData, $request);

        // If this is an AJAX request, return JSON response
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error_type' => 'system',
                'message' => $errorData['user_message'],
                'degradation_strategy' => $errorData['degradation_strategy'],
                'estimated_recovery_time' => $errorData['estimated_recovery_time'],
                'alternative_actions' => $errorData['alternative_actions']
            ], 500);
        }

        // For web requests, show system error page
        return response()->view('errors.system-error', [
            'error_message' => $errorData['user_message'],
            'estimated_recovery_time' => $errorData['estimated_recovery_time'],
            'alternative_actions' => $errorData['alternative_actions'],
            'support_reference' => $this->generateSupportReference($context),
            'system_status' => $this->getSystemStatus()
        ], 500);
    }

    /**
     * Determine error type based on exception and context
     */
    protected function determineErrorType(Throwable $exception, Request $request): string
    {
        $message = strtolower($exception->getMessage());
        $route = $request->route() ? $request->route()->getName() : '';

        // Check for authentication-related errors
        if (strpos($message, 'unauthenticated') !== false || 
            strpos($message, 'session') !== false ||
            strpos($message, 'login') !== false ||
            strpos($message, 'credentials') !== false) {
            return 'authentication';
        }

        // Check for payment-related errors
        if (strpos($route, 'payment') !== false ||
            strpos($message, 'payment') !== false ||
            strpos($message, 'paystack') !== false ||
            strpos($message, 'transaction') !== false) {
            return 'payment';
        }

        // Check for session-related errors
        if (strpos($message, 'session') !== false ||
            strpos($message, 'expired') !== false ||
            strpos($message, 'token') !== false) {
            return 'session';
        }

        // Check for email-related errors
        if (strpos($message, 'mail') !== false ||
            strpos($message, 'email') !== false ||
            strpos($message, 'smtp') !== false) {
            return 'email';
        }

        // Check for security-related errors
        if (strpos($message, 'suspicious') !== false ||
            strpos($message, 'blocked') !== false ||
            strpos($message, 'rate limit') !== false ||
            strpos($message, 'security') !== false) {
            return 'security';
        }

        // Default to system error
        return 'system';
    }

    /**
     * Build error context from exception and request
     */
    protected function buildErrorContext(Throwable $exception, Request $request): array
    {
        return [
            'exception_class' => get_class($exception),
            'exception_message' => $exception->getMessage(),
            'exception_file' => $exception->getFile(),
            'exception_line' => $exception->getLine(),
            'request_url' => $request->fullUrl(),
            'request_method' => $request->method(),
            'request_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => $request->session()->getId(),
            'user_id' => auth()->id(),
            'invitation_token' => $request->route('token') ?? session('easyrent_invitation_token'),
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Generate support reference for user
     */
    protected function generateSupportReference(array $context): string
    {
        return 'ER-' . date('Ymd') . '-' . substr(md5(json_encode($context) . time()), 0, 8);
    }

    /**
     * Get current system status
     */
    protected function getSystemStatus(): array
    {
        try {
            \DB::connection()->getPdo();
            $databaseStatus = 'connected';
        } catch (\Exception $e) {
            $databaseStatus = 'disconnected';
        }

        return [
            'database' => $databaseStatus,
            'services' => 'operational', // This could be enhanced with actual service checks
            'timestamp' => now()->toISOString()
        ];
    }
}