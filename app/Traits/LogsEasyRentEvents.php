<?php

namespace App\Traits;

use App\Services\Logging\EasyRentLogger;
use Illuminate\Http\Request;
use App\Models\ApartmentInvitation;
use App\Models\User;
use App\Models\Payment;

trait LogsEasyRentEvents
{
    protected function getEasyRentLogger(): EasyRentLogger
    {
        return app(EasyRentLogger::class);
    }

    /**
     * Log invitation access with automatic context detection
     */
    protected function logInvitationAccess(ApartmentInvitation $invitation, Request $request = null): void
    {
        $request = $request ?? request();
        $user = auth()->user();
        
        $this->getEasyRentLogger()->logInvitationAccess($invitation, $request, $user);
    }

    /**
     * Log authentication events with automatic context
     */
    protected function logAuthEvent(string $event, array $context = [], Request $request = null): void
    {
        $request = $request ?? request();
        $user = auth()->user();
        
        $this->getEasyRentLogger()->logAuthenticationEvent($event, $request, $user, $context);
    }

    /**
     * Log payment events with automatic context
     */
    protected function logPaymentEvent(string $event, Payment $payment, array $context = [], Request $request = null): void
    {
        $request = $request ?? request();
        
        $this->getEasyRentLogger()->logPaymentTransaction($event, $payment, $request, $context);
    }

    /**
     * Log errors with automatic context
     */
    protected function logEasyRentError(string $message, \Throwable $exception, array $context = [], Request $request = null): void
    {
        $request = $request ?? request();
        
        $this->getEasyRentLogger()->logError($message, $exception, $request, $context);
    }

    /**
     * Log security events with automatic context
     */
    protected function logSecurityEvent(string $event, array $context = [], Request $request = null): void
    {
        $request = $request ?? request();
        
        $this->getEasyRentLogger()->logSecurityEvent($event, $request, $context);
    }

    /**
     * Log session events with automatic context
     */
    protected function logSessionEvent(string $event, array $context = [], Request $request = null): void
    {
        $request = $request ?? request();
        
        $this->getEasyRentLogger()->logSessionEvent($event, $request, $context);
    }

    /**
     * Log email events
     */
    protected function logEmailEvent(string $event, string $emailType, array $recipients, array $context = []): void
    {
        $this->getEasyRentLogger()->logEmailEvent($event, $emailType, $recipients, $context);
    }

    /**
     * Wrap a closure with performance monitoring
     */
    protected function withPerformanceLogging(string $operation, callable $callback, Request $request = null)
    {
        $request = $request ?? request();
        $startTime = microtime(true);
        
        try {
            $result = $callback();
            
            $executionTime = microtime(true) - $startTime;
            $this->getEasyRentLogger()->logPerformanceMetric($operation, $executionTime, $request);
            
            return $result;
        } catch (\Throwable $e) {
            $executionTime = microtime(true) - $startTime;
            $this->getEasyRentLogger()->logPerformanceMetric($operation, $executionTime, $request, [
                'error' => true,
                'error_message' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }
}