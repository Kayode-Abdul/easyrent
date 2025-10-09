<?php
// This file is legacy and should not be loaded by RouteServiceProvider.
// Keeping it for reference only. If accidentally included, exit to prevent duplicate routes.
return;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfomaController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/users', [UserController::class, 'allUsers']);
Route::get('/dashboard/user', [UserController::class, 'user']);
Route::get('/dashboard/tenant/{id}', [UserController::class, 'getTenantDetails']);

// User Route
Route::get('/blog', [UserController::class, 'blog']);

// Auth routes
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// Property routes
Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
Route::get('/property/{id}', [PropertyController::class, 'show'])->name('property.show');
Route::post('/property/store', [PropertyController::class, 'store'])->name('property.store');
Route::post('/property/update/{id}', [PropertyController::class, 'update'])->name('property.update');
Route::delete('/property/delete/{id}', [PropertyController::class, 'destroy'])->name('property.destroy');

// Booking routes
Route::post('/booking/store', [BookingController::class, 'store'])->name('booking.store');
Route::get('/booking/show/{id}', [BookingController::class, 'show'])->name('booking.show');
Route::post('/booking/update/{id}', [BookingController::class, 'update'])->name('booking.update');
Route::delete('/booking/delete/{id}', [BookingController::class, 'destroy'])->name('booking.destroy');

// Message routes
Route::get('/messages/inbox', [MessageController::class, 'inbox'])->name('messages.inbox');
Route::get('/messages/sent', [MessageController::class, 'sent'])->name('messages.sent');
Route::get('/messages/compose', [MessageController::class, 'compose'])->name('messages.compose');
Route::post('/messages/send', [MessageController::class, 'send'])->name('messages.send');
Route::get('/messages/show/{id}', [MessageController::class, 'show'])->name('messages.show');

// Proforma routes
Route::middleware(['auth'])->group(function () {
    Route::post('/proforma/send/{apartmentId}', [ProfomaController::class, 'send'])->name('proforma.send');
    Route::get('/proforma/view/{id}', [ProfomaController::class, 'view'])->name('proforma.view');
    
    // Payment routes
    Route::post('/payment/callback', [PaymentController::class, 'handleGatewayCallback'])->name('payment.callback');
    Route::post('/payment/pay', [PaymentController::class, 'redirectToGateway'])->name('payment.pay');
});
