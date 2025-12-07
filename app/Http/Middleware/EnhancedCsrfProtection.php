<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\Security\SuspiciousActivityDetector;

class EnhancedCsrfProtection
{
    protected $detector;

    public function __construct(SuspiciousActivityDetector $detector)
    {
        $this->detector = $detector;
    }

    public function handle(Request $request, Closure $next)
    {
        // Enhanced CSRF validation for invitation forms
        if ($this->shouldValidateCsrf($request)) {
            $validation = $this->validateCsrfToken($request);
            
            if (!$validation['is_valid']) {
                $this->logCsrfViolation($request, $validation);
                
                if ($validation['is_suspicious']) {
                    $this->detector->recordFailedTokenAttempt($request->ip());
                }
                
                return response()->view('apartment.invite.security-blocked', [
                    'message' => 'Security validation failed. Please refresh and try again.'
                ], 419);
            }
        }
        
        return $next($request);
    }
    
    protected function shouldValidateCsrf(Request $request): bool
    {
        $routes = [
            'apartment.invite.apply',
            'apartment.invite.store-session'
        ];
        
        return in_array($request->route()->getName(), $routes) && 
               $request->isMethod('POST');
    }
    
    protected function validateCsrfToken(Request $request): array
    {
        $token = $request->input('_token') ?? $request->header('X-CSRF-TOKEN');
        
        if (!$token) {
            return ['is_valid' => false, 'is_suspicious' => true, 'reason' => 'missing_token'];
        }
        
        if (!hash_equals(session()->token(), $token)) {
            return ['is_valid' => false, 'is_suspicious' => true, 'reason' => 'invalid_token'];
        }
        
        return ['is_valid' => true, 'is_suspicious' => false];
    }
    
    protected function logCsrfViolation(Request $request, array $validation): void
    {
        Log::warning('CSRF validation failed', [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'reason' => $validation['reason'] ?? 'unknown',
            'timestamp' => now()
        ]);
    }
}