<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PropertyApiController;
use App\Http\Controllers\Api\ApartmentApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\BookingApiController;
use App\Http\Controllers\Api\PaymentApiController;

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
    
    // Bookings
    Route::get('/bookings', [BookingApiController::class, 'index']);
    Route::post('/bookings', [BookingApiController::class, 'store']);
    Route::get('/bookings/{id}', [BookingApiController::class, 'show']);
    Route::put('/bookings/{id}', [BookingApiController::class, 'update']);
    
    // Payments
    Route::get('/payments', [PaymentApiController::class, 'index']);
    Route::get('/payments/{id}', [PaymentApiController::class, 'show']);
    Route::post('/payments', [PaymentApiController::class, 'store']);
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
            'bookings' => '/api/v1/bookings',
            'payments' => '/api/v1/payments'
        ]
    ]);
});