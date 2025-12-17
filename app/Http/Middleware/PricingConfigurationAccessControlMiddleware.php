<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\Security\PaymentCalculationSecurityService;

class PricingConfigurationAccessControlMiddleware
{
    protected $securityService;

    public function __construct(PaymentCalculationSecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * Handle an incoming request for pricing configuration access control
     */
    public function handle(Request $request, Closure $next)
    {
        // Only check access for pricing configuration modification requests
        if (!$this->isPricingConfigurationRequest($request)) {
            return $next($request);
        }

        // Validate access permissions
        $accessResult = $this->securityService->validatePricingConfigurationAccess($request);

        if (!$accessResult['allowed']) {
            $this->logAccessDenied($request, $accessResult['reason']);
            
            return $this->handleAccessDenied($request, $accessResult['reason']);
        }

        // Log successful access for audit trail
        $this->logAccessGranted($request);

        return $next($request);
    }

    /**
     * Check if request is for pricing configuration modification
     */
    protected function isPricingConfigurationRequest(Request $request): bool
    {
        // Only check modification requests (POST, PUT, PATCH, DELETE)
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return false;
        }

        // Check if request contains pricing configuration data
        $pricingFields = [
            'pricing_type',
            'pricing_configuration',
            'price_configuration'
        ];

        foreach ($pricingFields as $field) {
            if ($request->has($field)) {
                return true;
            }
        }

        // Check specific routes that modify pricing configuration
        $pricingConfigRoutes = [
            'admin.apartments.update-pricing',
            'admin.properties.update-pricing',
            'api.apartments.update-pricing',
            'api.properties.update-pricing'
        ];

        $routeName = $request->route() ? $request->route()->getName() : null;
        
        if ($routeName && in_array($routeName, $pricingConfigRoutes)) {
            return true;
        }

        // Check URL patterns for pricing configuration endpoints
        $pricingPaths = [
            '/admin/apartments/*/pricing',
            '/admin/properties/*/pricing',
            '/api/v1/apartments/*/pricing',
            '/api/v1/properties/*/pricing'
        ];

        $path = $request->path();
        
        foreach ($pricingPaths as $pattern) {
            if (fnmatch($pattern, $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle access denied
     */
    protected function handleAccessDenied(Request $request, string $reason): \Illuminate\Http\Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Access denied',
                'message' => $reason,
                'timestamp' => now()->toISOString()
            ], 403);
        }

        // For web requests, redirect or show error page
        if ($request->user()) {
            // Authenticated user with insufficient permissions
            return response()->view('errors.insufficient-permissions', [
                'message' => $reason,
                'required_permission' => 'pricing_configuration_management'
            ], 403);
        } else {
            // Unauthenticated user
            return redirect()->route('login')->with('error', 'Please log in to access this resource.');
        }
    }

    /**
     * Log access denied attempt
     */
    protected function logAccessDenied(Request $request, string $reason): void
    {
        Log::warning('Pricing configuration access denied', [
            'ip_address' => $request->ip(),
            'user_id' => $request->user() ? $request->user()->id : null,
            'user_email' => $request->user() ? $request->user()->email : null,
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'reason' => $reason,
            'timestamp' => now()->toISOString()
        ]);

        // Log to security channel for monitoring
        Log::channel('security')->warning('Unauthorized pricing configuration access attempt', [
            'ip_address' => $request->ip(),
            'user_id' => $request->user() ? $request->user()->id : null,
            'reason' => $reason,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Log successful access for audit trail
     */
    protected function logAccessGranted(Request $request): void
    {
        Log::info('Pricing configuration access granted', [
            'ip_address' => $request->ip(),
            'user_id' => $request->user()->id,
            'user_email' => $request->user()->email,
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'timestamp' => now()->toISOString()
        ]);
    }
}