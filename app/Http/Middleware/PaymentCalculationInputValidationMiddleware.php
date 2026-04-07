<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\Security\PaymentCalculationSecurityService;

class PaymentCalculationInputValidationMiddleware
{
    protected $securityService;

    public function __construct(PaymentCalculationSecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * Handle an incoming request for input validation
     */
    public function handle(Request $request, Closure $next)
    {
        // Only validate calculation-related requests
        if (!$this->shouldValidateRequest($request)) {
            return $next($request);
        }

        // Get all input data
        $inputData = $this->extractCalculationInputs($request);
        
        if (empty($inputData)) {
            return $next($request);
        }

        // Sanitize and validate inputs
        $validationResult = $this->securityService->sanitizeCalculationInputs($inputData);

        // Handle validation failures
        if (!$validationResult['is_valid']) {
            $this->logValidationFailure($request, $validationResult);
            
            return $this->handleValidationFailure($request, $validationResult);
        }

        // Handle security issues
        if (!empty($validationResult['security_issues'])) {
            $this->logSecurityIssues($request, $validationResult['security_issues']);
            
            return $this->handleSecurityIssues($request, $validationResult['security_issues']);
        }

        // Replace request inputs with sanitized values
        $request->merge($validationResult['sanitized_inputs']);

        return $next($request);
    }

    /**
     * Check if request should be validated
     */
    protected function shouldValidateRequest(Request $request): bool
    {
        // Validate POST, PUT, PATCH requests to calculation endpoints
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            return false;
        }

        $calculationRoutes = [
            'proforma.store',
            'proforma.update',
            'proforma.calculate',
            'apartment.invite.apply',
            'apartment.invite.calculate',
            'payment.store',
            'payment.calculate',
            'api.payment.store',
            'api.payment.calculate',
            'api.proforma.store',
            'api.proforma.calculate'
        ];

        $routeName = $request->route() ? $request->route()->getName() : null;
        
        if ($routeName && in_array($routeName, $calculationRoutes)) {
            return true;
        }

        // Check URL patterns
        $calculationPaths = [
            '/api/v1/payments/*',
            '/api/v1/proforma/*',
            '/api/v1/mobile/payments/*',
            '/proforma/*',
            '/apartment/invite/*',
            '/payment/*'
        ];

        $path = $request->path();
        
        foreach ($calculationPaths as $pattern) {
            if (fnmatch($pattern, $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract calculation-related inputs from request
     */
    protected function extractCalculationInputs(Request $request): array
    {
        $calculationFields = [
            'apartment_price',
            'amount',
            'total_amount',
            'rental_duration',
            'duration',
            'lease_duration',
            'pricing_type',
            'pricing_configuration',
            'price_configuration',
            'calculation_method'
        ];

        $inputs = [];
        
        foreach ($calculationFields as $field) {
            if ($request->has($field)) {
                $inputs[$field] = $request->input($field);
            }
        }

        return $inputs;
    }

    /**
     * Handle validation failure
     */
    protected function handleValidationFailure(Request $request, array $validationResult): \Illuminate\Http\Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => 'Invalid input data provided',
                'validation_errors' => $validationResult['validation_errors'],
                'timestamp' => now()->toISOString()
            ], 422);
        }

        // For web requests, redirect back with errors
        return redirect()->back()
            ->withErrors($validationResult['validation_errors'])
            ->withInput($request->except(['apartment_price', 'amount', 'total_amount'])); // Don't flash sensitive data
    }

    /**
     * Handle security issues
     */
    protected function handleSecurityIssues(Request $request, array $securityIssues): \Illuminate\Http\Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Security validation failed',
                'message' => 'Request blocked due to security concerns',
                'timestamp' => now()->toISOString()
            ], 403);
        }

        // For web requests, show security error page
        return response()->view('errors.security-blocked', [
            'message' => 'Request blocked due to security concerns',
            'contact_support' => true
        ], 403);
    }

    /**
     * Log validation failure
     */
    protected function logValidationFailure(Request $request, array $validationResult): void
    {
        Log::info('Payment calculation input validation failed', [
            'ip_address' => $request->ip(),
            'user_id' => $request->user() ? $request->user()->id : null,
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'validation_errors' => $validationResult['validation_errors'],
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Log security issues
     */
    protected function logSecurityIssues(Request $request, array $securityIssues): void
    {
        Log::warning('Payment calculation security issues detected', [
            'ip_address' => $request->ip(),
            'user_id' => $request->user() ? $request->user()->id : null,
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'security_issues' => $securityIssues,
            'timestamp' => now()->toISOString()
        ]);

        // Also log to security channel
        Log::channel('security')->warning('Payment calculation security threat detected', [
            'ip_address' => $request->ip(),
            'user_id' => $request->user() ? $request->user()->id : null,
            'security_issues' => $securityIssues,
            'timestamp' => now()->toISOString()
        ]);
    }
}