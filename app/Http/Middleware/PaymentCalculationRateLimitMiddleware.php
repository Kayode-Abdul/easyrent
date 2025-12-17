<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\Security\PaymentCalculationSecurityService;

class PaymentCalculationRateLimitMiddleware
{
    protected $securityService;

    public function __construct(PaymentCalculationSecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * Handle an incoming request for payment calculation endpoints
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if this is a calculation-related endpoint
        if (!$this->isCalculationEndpoint($request)) {
            return $next($request);
        }

        // Check rate limits
        $rateLimitResult = $this->securityService->checkCalculationRateLimit($request);
        
        if (!$rateLimitResult['allowed']) {
            $this->logRateLimitViolation($request, $rateLimitResult);
            
            // Return appropriate response based on request type
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Rate limit exceeded',
                    'message' => $rateLimitResult['reason'],
                    'retry_after' => $rateLimitResult['retry_after'],
                    'timestamp' => now()->toISOString()
                ], 429);
            }
            
            // For web requests, return a view
            return response()->view('errors.rate-limited', [
                'message' => $rateLimitResult['reason'],
                'retry_after' => $rateLimitResult['retry_after'],
                'is_suspicious' => $rateLimitResult['is_suspicious']
            ], 429);
        }

        // Record the request for rate limiting
        $this->securityService->recordCalculationRequest($request);

        return $next($request);
    }

    /**
     * Check if the request is for a calculation endpoint
     */
    protected function isCalculationEndpoint(Request $request): bool
    {
        $calculationRoutes = [
            'proforma.calculate',
            'apartment.invite.calculate',
            'payment.calculate',
            'api.payment.calculate',
            'api.proforma.calculate'
        ];

        $routeName = $request->route() ? $request->route()->getName() : null;
        
        if ($routeName && in_array($routeName, $calculationRoutes)) {
            return true;
        }

        // Check URL patterns for API endpoints
        $calculationPaths = [
            '/api/v1/payments/calculate',
            '/api/v1/proforma/calculate',
            '/api/v1/mobile/payments/calculate',
            '/proforma/calculate',
            '/apartment/invite/*/calculate',
            '/payment/calculate'
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
     * Log rate limit violation
     */
    protected function logRateLimitViolation(Request $request, array $rateLimitResult): void
    {
        $logLevel = $rateLimitResult['is_suspicious'] ? 'warning' : 'info';
        
        Log::log($logLevel, 'Payment calculation rate limit exceeded', [
            'ip_address' => $request->ip(),
            'user_id' => $request->user() ? $request->user()->id : null,
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'reason' => $rateLimitResult['reason'],
            'retry_after' => $rateLimitResult['retry_after'],
            'is_suspicious' => $rateLimitResult['is_suspicious'],
            'timestamp' => now()->toISOString()
        ]);

        // Log to security channel if suspicious
        if ($rateLimitResult['is_suspicious']) {
            Log::channel('security')->warning('Suspicious payment calculation activity', [
                'ip_address' => $request->ip(),
                'user_id' => $request->user() ? $request->user()->id : null,
                'reason' => $rateLimitResult['reason'],
                'timestamp' => now()->toISOString()
            ]);
        }
    }
}