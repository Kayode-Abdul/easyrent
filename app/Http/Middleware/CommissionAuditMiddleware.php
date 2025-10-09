<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Audit\CommissionAuditService;
use Symfony\Component\HttpFoundation\Response;

class CommissionAuditMiddleware
{
    protected $auditService;

    public function __construct(CommissionAuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only audit commission-related operations
        if ($this->shouldAudit($request)) {
            $this->auditCommissionOperation($request, $response);
        }

        return $response;
    }

    /**
     * Determine if the request should be audited
     */
    private function shouldAudit(Request $request): bool
    {
        $auditableRoutes = [
            'commission.calculate',
            'commission.distribute',
            'payments.process',
            'referrals.create',
            'admin.commission-rates.update'
        ];

        $routeName = $request->route() ? $request->route()->getName() : null;
        
        return in_array($routeName, $auditableRoutes) || 
               str_contains($request->path(), 'commission') ||
               str_contains($request->path(), 'payment');
    }

    /**
     * Audit the commission operation
     */
    private function auditCommissionOperation(Request $request, Response $response): void
    {
        try {
            $auditData = [
                'method' => $request->method(),
                'path' => $request->path(),
                'route_name' => $request->route() ? $request->route()->getName() : null,
                'user_id' => auth()->id(),
                'request_data' => $request->except(['password', '_token']),
                'response_status' => $response->getStatusCode(),
                'timestamp' => now()->toISOString()
            ];

            // Extract payment ID if available
            if ($request->has('payment_id')) {
                $auditData['payment_id'] = $request->input('payment_id');
            }

            // Extract commission calculation data if available
            if ($request->has('commission_data')) {
                $auditData['commission_data'] = $request->input('commission_data');
            }

            $this->auditService->createAuditTrail([
                'input' => $auditData['request_data'],
                'output' => ['status' => $response->getStatusCode()],
                'method' => 'middleware_audit',
                'calculated_by' => auth()->id(),
                'payment_id' => $auditData['payment_id'] ?? null
            ]);

        } catch (\Exception $e) {
            // Don't let audit failures break the main operation
            $this->auditService->logCalculationError('audit_middleware_error', [
                'error' => $e->getMessage(),
                'request_path' => $request->path(),
                'user_id' => auth()->id()
            ], 'warning');
        }
    }
}