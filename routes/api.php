<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PropertyApiController;
use App\Http\Controllers\Api\ApartmentApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\PaymentApiController;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileInvitationController;
use App\Http\Controllers\Api\MobilePaymentController;
use App\Http\Controllers\Api\MobileSessionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public API routes (no authentication required)
Route::prefix('v1')->group(function () {
    // Properties
    Route::get('/properties', [PropertyApiController::class, 'index']);
    Route::get('/properties/{id}', [PropertyApiController::class, 'show']);
    
    // Apartments
    Route::get('/apartments', [ApartmentApiController::class, 'index']);
    Route::get('/apartments/{id}', [ApartmentApiController::class, 'show']);
    
    // Search
    Route::get('/search/properties', [PropertyApiController::class, 'search']);
});

// Protected API routes (require API key authentication)
Route::prefix('v1')->middleware(['api.auth'])->group(function () {
    // Properties management
    Route::post('/properties', [PropertyApiController::class, 'store']);
    Route::put('/properties/{id}', [PropertyApiController::class, 'update']);
    Route::delete('/properties/{id}', [PropertyApiController::class, 'destroy']);
    
    // Apartments management
    Route::post('/apartments', [ApartmentApiController::class, 'store']);
    Route::put('/apartments/{id}', [ApartmentApiController::class, 'update']);
    Route::delete('/apartments/{id}', [ApartmentApiController::class, 'destroy']);
    
    // User management
    Route::get('/users/{id}', [UserApiController::class, 'show']);
    Route::get('/users/{id}/properties', [UserApiController::class, 'properties']);
    Route::put('/users/{id}', [UserApiController::class, 'update']);
    
    // Payments
    Route::get('/payments', [PaymentApiController::class, 'index']);
    Route::get('/payments/{id}', [PaymentApiController::class, 'show']);
    Route::post('/payments', [PaymentApiController::class, 'store']);
    
    // Payment calculation endpoints with security middleware
    Route::middleware([
        'payment.calculation.rate.limit',
        'payment.calculation.input.validation'
    ])->group(function () {
        Route::post('/payments/calculate', [PaymentApiController::class, 'calculate'])->name('api.payment.calculate');
        Route::post('/proforma/calculate', [PaymentApiController::class, 'calculateProforma'])->name('api.proforma.calculate');
        
        // Enhanced mobile calculation endpoints
        Route::post('/payments/calculate/mobile', [PaymentApiController::class, 'calculateMobile'])->name('api.payment.calculate.mobile');
        Route::post('/proforma/calculate/mobile', [PaymentApiController::class, 'calculateProformaMobile'])->name('api.proforma.calculate.mobile');
    });
    
    // Pricing configuration endpoints with access control
    Route::middleware([
        'pricing.configuration.access.control'
    ])->group(function () {
        Route::put('/apartments/{id}/pricing', [ApartmentApiController::class, 'updatePricing'])->name('api.apartments.update-pricing');
        Route::put('/properties/{id}/pricing', [PropertyApiController::class, 'updatePricing'])->name('api.properties.update-pricing');
    });
});

// Mobile API Routes for EasyRent Link Authentication System
Route::prefix('v1/mobile')->group(function () {
    
    // Public mobile authentication routes (no API key required)
    Route::post('/auth/login', [MobileAuthController::class, 'login']);
    Route::post('/auth/register', [MobileAuthController::class, 'register']);
    
    // Public invitation routes (no authentication required for viewing)
    Route::get('/invitations/{token}', [MobileInvitationController::class, 'show']);
    Route::get('/invitations/{token}/session', [MobileInvitationController::class, 'getSession']);
    Route::post('/invitations/session/store', [MobileInvitationController::class, 'storeSession']);
    Route::post('/invitations/{token}/calculate', [MobileInvitationController::class, 'getPaymentCalculation']);
    
    // Public payment callback (no authentication required)
    Route::post('/payments/callback', [MobilePaymentController::class, 'paymentCallback']);
    Route::get('/payments/callback', [MobilePaymentController::class, 'paymentCallback']);
    
    // Protected mobile routes (require Bearer token authentication)
    Route::middleware(['auth:sanctum'])->group(function () {
        
        // Authentication management
        Route::post('/auth/logout', [MobileAuthController::class, 'logout']);
        Route::get('/auth/profile', [MobileAuthController::class, 'profile']);
        Route::post('/auth/refresh-token', [MobileAuthController::class, 'refreshToken']);
        
        // Invitation management (authenticated users)
        Route::post('/invitations/{token}/apply', [MobileInvitationController::class, 'apply']);
        Route::post('/invitations/generate', [MobileInvitationController::class, 'generateLink']);
        Route::delete('/invitations/{token}/session', [MobileInvitationController::class, 'clearSession']);
        
        // Payment management
        Route::get('/payments/{paymentId}', [MobilePaymentController::class, 'show']);
        Route::post('/payments/initialize', [MobilePaymentController::class, 'initializePayment']);
        Route::get('/payments/user/history', [MobilePaymentController::class, 'getUserPayments']);
        Route::post('/payments/{paymentId}/cancel', [MobilePaymentController::class, 'cancelPayment']);
        
        // Payment calculation endpoints
        Route::post('/payments/calculate/preview', [MobilePaymentController::class, 'calculatePaymentPreview']);
        Route::post('/payments/validate/calculation', [MobilePaymentController::class, 'validatePaymentCalculation']);
        
        // Session management (authenticated)
        Route::post('/sessions', [MobileSessionController::class, 'store']);
        Route::get('/sessions/{sessionKey}', [MobileSessionController::class, 'show']);
        Route::put('/sessions/{sessionKey}', [MobileSessionController::class, 'update']);
        Route::delete('/sessions/{sessionKey}', [MobileSessionController::class, 'destroy']);
        Route::get('/sessions/{sessionKey}/exists', [MobileSessionController::class, 'exists']);
    });
    
    // Admin mobile routes (require API key authentication)
    Route::middleware(['api.auth'])->group(function () {
        Route::post('/sessions/cleanup', [MobileSessionController::class, 'cleanup']);
        Route::get('/sessions/stats', [MobileSessionController::class, 'stats']);
    });
});

// CSRF Token refresh endpoint (no authentication required)
Route::get('/csrf-token', function () {
    return response()->json([
        'token' => csrf_token(),
        'timestamp' => now()->toISOString()
    ]);
});

// API status endpoint
Route::get('/status', function () {
    return response()->json([
        'status' => 'active',
        'version' => '1.0.0',
        'timestamp' => now()->toISOString(),
        'endpoints' => [
            'properties' => '/api/v1/properties',
            'apartments' => '/api/v1/apartments',
            'users' => '/api/v1/users',
            'payments' => '/api/v1/payments',
            'mobile' => '/api/v1/mobile'
        ],
        'mobile_endpoints' => [
            'auth' => '/api/v1/mobile/auth',
            'invitations' => '/api/v1/mobile/invitations',
            'payments' => '/api/v1/mobile/payments',
            'sessions' => '/api/v1/mobile/sessions'
        ],
        'calculation_endpoints' => [
            'general_calculation' => '/api/v1/payments/calculate',
            'proforma_calculation' => '/api/v1/proforma/calculate',
            'mobile_calculation' => '/api/v1/payments/calculate/mobile',
            'mobile_proforma' => '/api/v1/proforma/calculate/mobile',
            'mobile_payment_preview' => '/api/v1/mobile/payments/calculate/preview',
            'invitation_calculation' => '/api/v1/mobile/invitations/{token}/calculate'
        ],
        'features' => [
            'centralized_calculation_service' => true,
            'mobile_optimized_responses' => true,
            'detailed_calculation_breakdown' => true,
            'pricing_structure_transparency' => true,
            'comprehensive_error_handling' => true,
            'audit_trail_logging' => true,
            'input_validation_security' => true,
            'calculation_consistency_validation' => true
        ],
        'supported_pricing_types' => [
            'total' => 'Complete rental amount (no duration multiplication)',
            'monthly' => 'Monthly rent (multiplied by rental duration)'
        ]
    ]);
});