<?php

namespace App\Services\ErrorHandling;

use Throwable;
use Illuminate\Http\Request;

interface ErrorHandlerInterface
{
    /**
     * Handle authentication flow errors
     */
    public function handleAuthenticationError(Throwable $exception, Request $request, array $context = []): array;

    /**
     * Handle payment processing errors
     */
    public function handlePaymentError(Throwable $exception, Request $request, array $context = []): array;

    /**
     * Handle session management errors
     */
    public function handleSessionError(Throwable $exception, Request $request, array $context = []): array;

    /**
     * Handle email notification errors
     */
    public function handleEmailError(Throwable $exception, Request $request, array $context = []): array;

    /**
     * Handle system errors with graceful degradation
     */
    public function handleSystemError(Throwable $exception, Request $request, array $context = []): array;

    /**
     * Handle security-related errors
     */
    public function handleSecurityError(Throwable $exception, Request $request, array $context = []): array;

    /**
     * Get user-friendly error message
     */
    public function getUserFriendlyMessage(string $errorType, array $context = []): string;

    /**
     * Determine if error requires immediate attention
     */
    public function requiresImmediateAttention(Throwable $exception): bool;

    /**
     * Get recovery options for specific error types
     */
    public function getRecoveryOptions(string $errorType, array $context = []): array;
}