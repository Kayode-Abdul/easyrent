<?php

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
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ConfirmPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\BillingController;

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
    return view('home');
});
Route::get('/about', function () {
    return view('about');
});

Route::get('/services', function () {
    return view('services');
})->name('services'); 

Route::get('/contact', [App\Http\Controllers\ContactController::class, 'index'])->name('contact');
Route::post('/contact', [App\Http\Controllers\ContactController::class, 'submit'])->name('contact.submit');

Route::get('/benefits', function () {
    return view('benefits');
})->name('benefits');

Route::get('/faq', function () {
    return view('faq');
})->name('faq'); 


Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');  
Route::get('/dashupload', function () {
    return view('show');
});  

//Property Route
Route::get('/listing', [PropertyController::class, 'add']);
Route::post('/listing', [PropertyController::class, 'add']);
Route::get('/apartment', [PropertyController::class, 'addApartment']);
Route::post('/apartment', [PropertyController::class, 'addApartment']);

// Property CRUD routes
Route::get('/dashboard/property/{propId}', [PropertyController::class, 'show']);
Route::get('/dashboard/property/{propId}/edit', [PropertyController::class, 'edit']);
Route::put('/dashboard/property/{propId}', [PropertyController::class, 'update']);
Route::delete('/dashboard/property/{propId}', [PropertyController::class, 'destroy']);
// AJAX property delete endpoint
Route::delete('/dashboard/property/{propId}/ajax', [PropertyController::class, 'ajaxDestroy']);
// Keep original routes for backward compatibility
Route::get('/dashboard/property/{propId}', [PropertyController::class, 'show'])->name('property.show');
Route::get('/property/{propId}/edit', [PropertyController::class, 'edit']);
Route::put('/property/{propId}', [PropertyController::class, 'update']);
Route::delete('/property/{propId}', [PropertyController::class, 'destroy']);

// Apartment CRUD routes
Route::get('/dashboard/apartment/{id}', [PropertyController::class, 'showApartment']);
Route::get('/dashboard/apartment/{id}/edit', [PropertyController::class, 'editApartment']);
Route::put('/dashboard/apartment/{id}', [PropertyController::class, 'updateApartment']);
Route::delete('/dashboard/apartment/{id}', [PropertyController::class, 'destroyApartment']);
// Keep original routes for backward compatibility
Route::get('/apartment/{id}', [PropertyController::class, 'showApartment']);
Route::get('/apartment/{id}/edit', [PropertyController::class, 'editApartment']);
Route::put('/apartment/{id}', [PropertyController::class, 'updateApartment']);
Route::delete('/apartment/{id}', [PropertyController::class, 'destroyApartment']);

Route::get('/dashboard/properties', [PropertyController::class, 'properties']);
Route::get('/dashboard/myproperty', [PropertyController::class, 'userProperty']);
Route::get('/dashboard/users', [UserController::class, 'allUsers']);
Route::get('/dashboard/user', [UserController::class, 'user']);
Route::get('/dashboard/tenant/{id}', [UserController::class, 'getTenantDetails']);
// Billing Route
Route::get('/dashboard/billing', [BillingController::class, 'index'])->middleware('auth')->name('billing.index');
//User Route
Route::get('/blog', [UserController::class, 'blog']);
Route::get('/readmore/{topic_url}', [App\Http\Controllers\BlogController::class, 'show'])->name('blog.show');

// Admin Blog Management Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/blog', [App\Http\Controllers\BlogController::class, 'adminIndex'])->name('admin.blog.index');
    Route::get('/blog/create', [App\Http\Controllers\BlogController::class, 'create'])->name('admin.blog.create');
    Route::post('/blog', [App\Http\Controllers\BlogController::class, 'store'])->name('admin.blog.store');
    Route::get('/blog/{id}/edit', [App\Http\Controllers\BlogController::class, 'edit'])->name('admin.blog.edit');
    Route::put('/blog/{id}', [App\Http\Controllers\BlogController::class, 'update'])->name('admin.blog.update');
    Route::delete('/blog/{id}', [App\Http\Controllers\BlogController::class, 'destroy'])->name('admin.blog.destroy');
});

// Auth routes
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Password Reset routes (fixes Route [password.request] not defined)
Route::middleware('guest')->group(function () {
    Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

    // Password confirmation
    Route::get('password/confirm', [ConfirmPasswordController::class, 'showConfirmForm'])->name('password.confirm');
    Route::post('password/confirm', [ConfirmPasswordController::class, 'confirm']);
});

// Email Verification routes
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [VerificationController::class, 'show'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::post('/email/resend', [VerificationController::class, 'resend'])->name('verification.resend');
});

// Session Management API
Route::get('/api/session-status', function () {
    if (!auth()->check()) {
        return response()->json(['authenticated' => false], 401);
    }
    
    $sessionLifetime = config('session.lifetime') * 60; // Convert minutes to seconds
    $lastActivity = session()->get('_token') ? time() - session()->get('last_activity', time()) : 0;
    $expiresIn = max(0, $sessionLifetime - $lastActivity) * 1000; // Convert to milliseconds
    
    return response()->json([
        'authenticated' => true,
        'expires_in' => $expiresIn,
        'user_id' => auth()->id(),
        'session_lifetime' => $sessionLifetime
    ]);
})->middleware('web');

Route::get('/users', [UserController::class, 'users']);
Route::put('/user/{id}', [UserController::class, 'update']);
Route::post('/user/{id}', [UserController::class, 'update']);
Route::post('/dashboard/users/profile/{id}', [UserController::class, 'show'])->name('users.profile');
Route::get('/dashboard/users/profile/{id}', [UserController::class, 'show'])->name('users.profile');
 
// Booking routes
Route::post('/bookings', [BookingController::class, 'store']);
Route::put('/bookings/{booking}', [BookingController::class, 'update']);
Route::get('/bookings', [BookingController::class, 'index']);
Route::get('/dashboard/bookings', [BookingController::class, 'index']);
Route::get('/dashboard/property/{propId}/assign-agent', [PropertyController::class, 'assignAgent']);
Route::post('/dashboard/property/{propId}/assign-agent', [PropertyController::class, 'assignAgent']);
Route::get('/dashboard/agent/{id}', [UserController::class, 'showAgent']);
Route::get('/dashboard/agent/{id}/json', [UserController::class, 'getAgentJson']);
// Remove agent from property
Route::post('/dashboard/property/{propId}/remove-agent', [PropertyController::class, 'removeAgent']);
// Resend profoma receipt (POST for security)
Route::post('/dashboard/profoma/{id}/resend', [PropertyController::class, 'resendProfoma'])->name('profoma.resend');
Route::post('/dashboard/switch-mode', [App\Http\Controllers\PropertyController::class, 'switchDashboardMode'])->name('dashboard.switchMode');
// Send profoma for an apartment (AJAX)
Route::post('/dashboard/apartment/{apartmentId}/send-profoma', [ProfomaController::class, 'send'])->middleware('auth');
Route::get('/dashboard/apartment/{apartmentId}/send-profoma', [ProfomaController::class, 'send'])->middleware('auth');
// Notification counts for navbar badge (AJAX)
Route::get('/dashboard/notifications/counts', [PropertyController::class, 'getNotificationCounts'])->middleware('auth');
// Mark notifications as seen (profoma receipts and future types)
Route::post('/dashboard/notifications/mark-seen', [PropertyController::class, 'markNotificationsSeen'])->middleware('auth');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/messages/inbox', [MessageController::class, 'inbox'])->name('messages.inbox');
    Route::get('/dashboard/messages/sent', [MessageController::class, 'sent'])->name('messages.sent');
    Route::get('/dashboard/messages/compose', [MessageController::class, 'compose'])->name('messages.compose');
    Route::post('/dashboard/messages/send', [MessageController::class, 'send'])->name('messages.send');
    Route::get('/dashboard/messages/{id}', [MessageController::class, 'show'])->name('messages.show');
    
    // Payment routes
    Route::get('/dashboard/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/dashboard/payments/analytics', [PaymentController::class, 'analytics'])->name('payments.analytics');
});
// AJAX: Find verified agents for property assignment
Route::get('/dashboard/agents/search', [UserController::class, 'searchAgents'])->middleware('auth');
// Agent Ratings
// Payment routes - publicly accessible for callbacks
Route::post('/pay', [PaymentController::class, 'redirectToGateway'])->name('pay');
Route::get('/payment/callback', [PaymentController::class, 'handleGatewayCallback'])->name('payment.callback');
Route::post('/payment/callback', [PaymentController::class, 'handleGatewayCallback'])->name('payment.callback.post');

// Test route for payment callback (remove in production)
Route::get('/test-payment-callback', function() {
    return app(App\Http\Controllers\PaymentController::class)->handleGatewayCallback(
        new Illuminate\Http\Request(['reference' => 'test_' . time()])
    );
})->name('test.payment.callback');

// Debug route to check if callback is reached
Route::any('/debug-callback', function(Illuminate\Http\Request $request) {
    \Log::info('Debug callback reached', [
        'method' => $request->method(),
        'all_data' => $request->all(),
        'headers' => $request->headers->all()
    ]);
    
    return response()->json([
        'status' => 'callback_reached',
        'method' => $request->method(),
        'data' => $request->all(),
        'timestamp' => now()
    ]);
})->name('debug.callback');

// Manual payment creation for testing
Route::get('/create-test-payment', function() {
    try {
        $user = App\Models\User::first();
        $apartment = App\Models\Apartment::first();
        
        if (!$user || !$apartment) {
            return response()->json(['error' => 'No user or apartment found for testing']);
        }
        
        $payment = new App\Models\Payment();
        $payment->transaction_id = 'manual_test_' . time();
        $payment->payment_reference = 'manual_ref_' . time();
        $payment->amount = 25000;
        $payment->tenant_id = $user->user_id;
        $payment->landlord_id = $user->user_id;
        $payment->apartment_id = $apartment->apartment_id;
        $payment->status = 'completed';
        $payment->payment_method = 'manual_test';
        $payment->duration = 12;
        $payment->paid_at = now();
        
        $saved = $payment->save();
        
        if ($saved) {
            return response()->json([
                'success' => true,
                'message' => 'Test payment created successfully',
                'payment_id' => $payment->id,
                'payment_data' => $payment->toArray()
            ]);
        } else {
            return response()->json(['error' => 'Payment save returned false']);
        }
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
})->name('create.test.payment');
Route::get('/payment/receipt/{id}', [PaymentController::class, 'showReceipt'])->name('payment.receipt');
Route::get('/payment/download/{id}', [PaymentController::class, 'downloadReceipt'])->name('payment.download');
Route::get('/dashboard/payments/{reference}/receipt', [PaymentController::class, 'showReceiptByReference'])->name('payment.receipt.reference');

Route::middleware(['auth'])->group(function() {
    Route::post('/dashboard/agent/rate', [\App\Http\Controllers\AgentRatingController::class, 'store'])->name('agent.rate');
    Route::get('/dashboard/agent/{agentId}/ratings', [\App\Http\Controllers\AgentRatingController::class, 'show'])->name('agent.ratings');
    Route::get('/proforma/view/{id}', [ProfomaController::class, 'view'])->name('proforma.view');
Route::post('/proforma/{id}/accept', [ProfomaController::class, 'accept'])->name('proforma.accept');
Route::post('/proforma/{id}/reject', [ProfomaController::class, 'reject'])->name('proforma.reject');
Route::get('/proforma/{id}/accept', [ProfomaController::class, 'accept'])->name('proforma.accept');
Route::get('/proforma/{id}/reject', [ProfomaController::class, 'reject'])->name('proforma.reject');
Route::get('/proforma/{id}/payment', [PaymentController::class, 'showProformaPaymentForm'])->name('proforma.payment.form');
});

// Super Admin Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [App\Http\Controllers\Admin\AdminController::class, 'userManagement'])->name('users');
    Route::get('/properties', [App\Http\Controllers\Admin\AdminController::class, 'propertyOversight'])->name('properties');
    Route::get('/system-health', [App\Http\Controllers\Admin\AdminController::class, 'systemHealth'])->name('system-health');
    Route::get('/reports', [App\Http\Controllers\Admin\AdminController::class, 'reports'])->name('reports');
    
    // User management actions
    Route::get('/users/{user}/edit', [App\Http\Controllers\Admin\AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [App\Http\Controllers\Admin\AdminController::class, 'updateUser'])->name('users.update');
    Route::patch('/users/{user}/toggle-status', [App\Http\Controllers\Admin\AdminController::class, 'toggleUserStatus'])->name('users.toggle-status');
    Route::delete('/users/{user}', [App\Http\Controllers\Admin\AdminController::class, 'deleteUser'])->name('users.delete');

    // Advanced Admin Features
    Route::get('/audit-logs', [App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-logs');
    Route::get('/audit-logs/{auditLog}', [App\Http\Controllers\Admin\AuditLogController::class, 'show'])->name('audit-logs.show');
    Route::get('/audit-logs/export', [App\Http\Controllers\Admin\AuditLogController::class, 'export'])->name('audit-logs.export');
    Route::delete('/audit-logs/cleanup', [App\Http\Controllers\Admin\AuditLogController::class, 'cleanup'])->name('audit-logs.cleanup');
    
    // Backup & Restore System
    Route::get('/backup', [App\Http\Controllers\Admin\BackupController::class, 'index'])->name('backup');
    Route::post('/backup/create', [App\Http\Controllers\Admin\BackupController::class, 'create'])->name('backup.create');
    Route::get('/backup/download/{filename}', [App\Http\Controllers\Admin\BackupController::class, 'download'])->name('backup.download');
    Route::delete('/backup/delete/{filename}', [App\Http\Controllers\Admin\BackupController::class, 'delete'])->name('backup.delete');
    Route::post('/backup/restore', [App\Http\Controllers\Admin\BackupController::class, 'restore'])->name('backup.restore');
    
    // Security Center
    Route::get('/security', [App\Http\Controllers\Admin\SecurityController::class, 'index'])->name('security');
    Route::post('/security/update', [App\Http\Controllers\Admin\SecurityController::class, 'updateSecuritySettings'])->name('security.update');
    Route::post('/security/block-user', [App\Http\Controllers\Admin\SecurityController::class, 'blockUser'])->name('security.block-user');
    Route::post('/security/unblock-user/{userId}', [App\Http\Controllers\Admin\SecurityController::class, 'unblockUser'])->name('security.unblock-user');
    Route::post('/security/clear-attempts', [App\Http\Controllers\Admin\SecurityController::class, 'clearLoginAttempts'])->name('security.clear-attempts');
    
    // Email Center
    Route::get('/email-center', [App\Http\Controllers\Admin\EmailCenterController::class, 'index'])->name('email-center');
    Route::get('/email-center/compose', [App\Http\Controllers\Admin\EmailCenterController::class, 'compose'])->name('email-center.compose');
    Route::post('/email-center/send', [App\Http\Controllers\Admin\EmailCenterController::class, 'send'])->name('email-center.send');
    Route::get('/email-center/templates', [App\Http\Controllers\Admin\EmailCenterController::class, 'templates'])->name('email-center.templates');
    Route::get('/email-center/settings', [App\Http\Controllers\Admin\EmailCenterController::class, 'settings'])->name('email-center.settings');
    Route::post('/email-center/test', [App\Http\Controllers\Admin\EmailCenterController::class, 'sendTest'])->name('email-center.test');
    
    Route::post('/maintenance/toggle', [App\Http\Controllers\Admin\AdminController::class, 'toggleMaintenance'])->name('maintenance.toggle');
    Route::get('/api-management', [App\Http\Controllers\Admin\AdminController::class, 'apiManagement'])->name('api-management');
    
    // System Logs Routes
    Route::get('/logs', [App\Http\Controllers\Admin\AdminController::class, 'systemLogs'])->name('logs');
    Route::get('/logs/content', [App\Http\Controllers\Admin\AdminController::class, 'getLogContent'])->name('logs.content');
    Route::get('/logs/download', [App\Http\Controllers\Admin\AdminController::class, 'downloadLog'])->name('logs.download');
    Route::delete('/logs/clear-old', [App\Http\Controllers\Admin\AdminController::class, 'clearOldLogs'])->name('logs.clear-old');
    
    // Marketer Management Routes
    Route::prefix('marketers')->name('marketers.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\MarketerManagementController::class, 'index'])->name('index');
        Route::get('/list', [App\Http\Controllers\Admin\MarketerManagementController::class, 'marketers'])->name('list');
        Route::get('/{marketer}', [App\Http\Controllers\Admin\MarketerManagementController::class, 'show'])->name('show');
        Route::post('/{marketer}/approve', [App\Http\Controllers\Admin\MarketerManagementController::class, 'approve'])->name('approve');
        Route::post('/{marketer}/reject', [App\Http\Controllers\Admin\MarketerManagementController::class, 'reject'])->name('reject');
        Route::post('/{marketer}/suspend', [App\Http\Controllers\Admin\MarketerManagementController::class, 'suspend'])->name('suspend');
        Route::post('/{marketer}/reactivate', [App\Http\Controllers\Admin\MarketerManagementController::class, 'reactivate'])->name('reactivate');
        Route::patch('/{marketer}/commission-rate', [App\Http\Controllers\Admin\MarketerManagementController::class, 'updateCommissionRate'])->name('update-commission-rate');
        
        // Rewards management
        Route::get('/rewards/list', [App\Http\Controllers\Admin\MarketerManagementController::class, 'rewards'])->name('rewards');
        Route::post('/rewards/{reward}/approve', [App\Http\Controllers\Admin\MarketerManagementController::class, 'approveReward'])->name('rewards.approve');
        Route::post('/rewards/{reward}/reject', [App\Http\Controllers\Admin\MarketerManagementController::class, 'rejectReward'])->name('rewards.reject');
        
        // Payments management
        Route::get('/payments/list', [App\Http\Controllers\Admin\MarketerManagementController::class, 'payments'])->name('payments');
        Route::post('/payments/{payment}/process', [App\Http\Controllers\Admin\MarketerManagementController::class, 'processPayment'])->name('payments.process');
        Route::post('/payments/{payment}/complete', [App\Http\Controllers\Admin\MarketerManagementController::class, 'completePayment'])->name('payments.complete');
        
        // Analytics
        Route::get('/analytics/reports', [App\Http\Controllers\Admin\MarketerManagementController::class, 'analytics'])->name('analytics');
    });

    // Commission Rates Management Routes
    Route::prefix('commission-rates')->name('commission-rates.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\RegionalCommissionController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\RegionalCommissionController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\RegionalCommissionController::class, 'store'])->name('store');
        Route::get('/bulk-update', [App\Http\Controllers\Admin\RegionalCommissionController::class, 'bulkUpdateForm'])->name('bulk-update');
        Route::post('/bulk-update', [App\Http\Controllers\Admin\RegionalCommissionController::class, 'bulkUpdate'])->name('bulk-update.process');
        Route::get('/{commissionRate}', [App\Http\Controllers\Admin\RegionalCommissionController::class, 'show'])->name('show');
        Route::get('/{commissionRate}/edit', [App\Http\Controllers\Admin\RegionalCommissionController::class, 'edit'])->name('edit');
        Route::put('/{commissionRate}', [App\Http\Controllers\Admin\RegionalCommissionController::class, 'update'])->name('update');
        Route::patch('/{commissionRate}', [App\Http\Controllers\Admin\RegionalCommissionController::class, 'update']);
        Route::delete('/{commissionRate}', [App\Http\Controllers\Admin\RegionalCommissionController::class, 'destroy'])->name('destroy');
        // Optional: history endpoint for AJAX
        Route::get('/history/json', [App\Http\Controllers\Admin\RegionalCommissionController::class, 'history'])->name('history');
    });

    // Commission Management (scenario-based) Routes
    Route::prefix('commission-management')->name('commission-management.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\CommissionManagementController::class, 'index'])->name('index');
        Route::get('/regional-manager', [App\Http\Controllers\Admin\CommissionManagementController::class, 'regionalManager'])->name('regional-manager');
        Route::get('/create', [App\Http\Controllers\Admin\CommissionManagementController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\CommissionManagementController::class, 'store'])->name('store');
        Route::get('/region/{region}', [App\Http\Controllers\Admin\CommissionManagementController::class, 'getRegionRates'])->name('region.rates');
        Route::post('/region/bulk-save', [App\Http\Controllers\Admin\CommissionManagementController::class, 'bulkSaveRegion'])->name('region.bulk-save');
        Route::get('/rate/{commissionRate}/edit', [App\Http\Controllers\Admin\CommissionManagementController::class, 'edit'])->name('edit');
        Route::put('/rate/{commissionRate}', [App\Http\Controllers\Admin\CommissionManagementController::class, 'update'])->name('update');
        Route::delete('/rate/{commissionRate}', [App\Http\Controllers\Admin\CommissionManagementController::class, 'destroy'])->name('destroy');
        Route::post('/region/{region}/bulk-update', [App\Http\Controllers\Admin\CommissionManagementController::class, 'bulkUpdate'])->name('bulk-update');
        Route::post('/breakdown', [App\Http\Controllers\Admin\CommissionManagementController::class, 'getCommissionBreakdown'])->name('breakdown');
    });
    // Regional Manager Management Routes
    Route::prefix('regional-managers')->name('regional-managers.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\RegionalManagerManagementController::class, 'index'])->name('index');
        Route::get('/{regionalManager}', [App\Http\Controllers\Admin\RegionalManagerManagementController::class, 'show'])->name('show');
        Route::get('/{regionalManager}/assign-regions', [App\Http\Controllers\Admin\RegionalManagerManagementController::class, 'assignRegions'])->name('assign-regions');
        Route::post('/{regionalManager}/assignments', [App\Http\Controllers\Admin\RegionalManagerManagementController::class, 'storeRegionalAssignments'])->name('store-assignments');
        Route::delete('/{regionalManager}/remove-scope', [App\Http\Controllers\Admin\RegionalManagerManagementController::class, 'removeRegionalScope'])->name('remove-scope');
        Route::delete('/{regionalManager}/remove-all-scopes', [App\Http\Controllers\Admin\RegionalManagerManagementController::class, 'removeAllRegionalScopes'])->name('remove-all-scopes');
        Route::post('/bulk-assign', [App\Http\Controllers\Admin\RegionalManagerManagementController::class, 'bulkAssignRegions'])->name('bulk-assign');
        Route::put('/{regionalManager}/update', [App\Http\Controllers\Admin\RegionalManagerManagementController::class, 'updateRegionalManager'])->name('update');
        // Optional AJAX data endpoint
        Route::get('/data', [App\Http\Controllers\Admin\RegionalManagerManagementController::class, 'getRegionalManagersData'])->name('data');
    });
});

// Marketer Routes (Protected by marketer middleware)
Route::prefix('marketer')->name('marketer.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\MarketerController::class, 'dashboard'])->name('dashboard');
    
    // Profile management
    Route::get('/profile', [App\Http\Controllers\MarketerController::class, 'profile'])->name('profile.show');
    Route::get('/profile/create', [App\Http\Controllers\MarketerController::class, 'createProfile'])->name('profile.create');
    Route::post('/profile', [App\Http\Controllers\MarketerController::class, 'storeProfile'])->name('profile.store');
    Route::get('/profile/edit', [App\Http\Controllers\MarketerController::class, 'editProfile'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\MarketerController::class, 'updateProfile'])->name('profile.update');
    
    // Campaign management
    Route::get('/campaigns', [App\Http\Controllers\MarketerController::class, 'campaigns'])->name('campaigns.index');
    Route::get('/campaigns/create', [App\Http\Controllers\MarketerController::class, 'createCampaign'])->name('campaigns.create');
    Route::post('/campaigns', [App\Http\Controllers\MarketerController::class, 'storeCampaign'])->name('campaigns.store');
    Route::get('/campaigns/{campaign}', [App\Http\Controllers\MarketerController::class, 'showCampaignDetailed'])->name('campaigns.show');
    Route::patch('/campaigns/{campaign}/pause', [App\Http\Controllers\MarketerController::class, 'pauseCampaign'])->name('campaigns.pause');
    Route::patch('/campaigns/{campaign}/resume', [App\Http\Controllers\MarketerController::class, 'resumeCampaign'])->name('campaigns.resume');
    Route::get('/campaigns/{campaign}/qr-code', [App\Http\Controllers\MarketerController::class, 'getCampaignQRCode'])->name('campaigns.qr-code');
    
    // Referrals and earnings
    Route::get('/referrals', [App\Http\Controllers\MarketerController::class, 'getReferrals'])->name('referrals.index');
    Route::get('/referrals/{referral}', [App\Http\Controllers\MarketerController::class, 'showReferral'])->name('referrals.show');
    
    // Payments
    Route::get('/payments', [App\Http\Controllers\MarketerController::class, 'payments'])->name('payments.index');
    Route::post('/payments/request', [App\Http\Controllers\MarketerController::class, 'requestPayment'])->name('payments.request');
    Route::get('/payments/{payment}', [App\Http\Controllers\MarketerController::class, 'showPayment'])->name('payments.show');
    Route::patch('/payments/{payment}/cancel', [App\Http\Controllers\MarketerController::class, 'cancelPayment'])->name('payments.cancel');
});

// Role switching (session-based)
Route::post('/switch-role', [App\Http\Controllers\RoleController::class, 'switchRole'])->middleware('auth')->name('switch.role');

// Admin Role Management consolidated routes under /admin/dashboard/*
Route::middleware(['auth'])->prefix('admin/dashboard')->name('admin.')->group(function () {
    Route::get('/roles', [App\Http\Controllers\Admin\RoleManagementController::class, 'index'])->name('roles.index');
    Route::get('/roles/assign', [App\Http\Controllers\Admin\RoleManagementController::class, 'assign'])->name('roles.assign');
    Route::post('/roles/assign', [App\Http\Controllers\Admin\RoleManagementController::class, 'assignPost'])->name('roles.assign.post');
    Route::get('/roles/{id}', [App\Http\Controllers\Admin\RoleManagementController::class, 'show'])->name('roles.show');
    // Back-compat: some views link to admin.roles.create; point it to the assign form
    Route::get('/roles/create', [App\Http\Controllers\Admin\RoleManagementController::class, 'assign'])->name('roles.create');
    Route::get('/roles/audits/export', [App\Http\Controllers\Admin\RoleManagementController::class, 'exportAudits'])->name('roles.audits.export');
    Route::post('/roles/audits/prune', [App\Http\Controllers\Admin\RoleManagementController::class, 'pruneAudits'])->name('roles.audits.prune');
});

// Canonical Regional Manager routes under /dashboard/regional
Route::middleware(['auth'])
    ->prefix('dashboard/regional')
    ->name('regional.')
    ->group(function () {
        Route::get('/', [App\Http\Controllers\RegionalManagerController::class, 'dashboard'])->name('dashboard');
        Route::get('/properties', [App\Http\Controllers\RegionalManagerController::class, 'properties'])->name('properties');
        Route::get('/marketers', [App\Http\Controllers\RegionalManagerController::class, 'marketers'])->name('marketers');
        Route::get('/analytics', [App\Http\Controllers\RegionalManagerController::class, 'analytics'])->name('analytics');
        Route::get('/pending-approvals', [App\Http\Controllers\RegionalManagerController::class, 'pendingApprovals'])->name('pending_approvals');
        Route::get('/marketer/{id}/properties', [App\Http\Controllers\RegionalManagerController::class, 'marketerProperties'])->name('marketer.properties');
        Route::post('/property/{propId}/approve', [App\Http\Controllers\RegionalManagerController::class, 'approveProperty'])->name('property.approve');
        Route::post('/property/{propId}/reject', [App\Http\Controllers\RegionalManagerController::class, 'rejectProperty'])->name('property.reject');
        // Newly added for activation/suspension (back-compat with legacy blade references)
        Route::post('/property/{propId}/activate', [App\Http\Controllers\RegionalManagerController::class, 'activateProperty'])->name('property.activate');
        Route::post('/property/{propId}/suspend', [App\Http\Controllers\RegionalManagerController::class, 'suspendProperty'])->name('property.suspend');
        Route::get('/analytics/export', [App\Http\Controllers\RegionalManagerController::class, 'exportAnalytics'])->name('analytics.export');
        Route::get('/analytics/export/multi-tier', [App\Http\Controllers\RegionalManagerController::class, 'exportMultiTierAnalytics'])->name('analytics.export.multi-tier');
    });

    // Property Manager Routes
    Route::prefix('property-manager')->name('property-manager.')->middleware(['auth'])->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\PropertyManagerController::class, 'dashboard'])->name('dashboard');
        Route::get('/managed-properties', [App\Http\Controllers\PropertyManagerController::class, 'managedProperties'])->name('managed-properties');
        Route::get('/property/{propertyId}/details', [App\Http\Controllers\PropertyManagerController::class, 'propertyDetails'])->name('property-details');
        Route::get('/property/{propertyId}/apartments', [App\Http\Controllers\PropertyManagerController::class, 'propertyApartments'])->name('property-apartments');
        Route::get('/payments', [App\Http\Controllers\PropertyManagerController::class, 'payments'])->name('payments');
        Route::get('/analytics', [App\Http\Controllers\PropertyManagerController::class, 'analytics'])->name('analytics');
    });

    // Property Manager Dashboard Mode Switching
    Route::post('/dashboard/switch-property-manager-mode', [App\Http\Controllers\DashboardController::class, 'switchPropertyManagerMode'])->middleware('auth');
    
    // Admin Dashboard Mode Switching
    Route::post('/dashboard/switch-admin-mode', [App\Http\Controllers\DashboardController::class, 'switchAdminMode'])->middleware('auth');

// Commission Transparency Routes
Route::middleware(['auth'])->group(function(){
    Route::get('/dashboard/commission-transparency', [App\Http\Controllers\PropertyController::class, 'commissionTransparency'])->name('landlord.commission-transparency');
    Route::get('/dashboard/commission-rate-history', [App\Http\Controllers\PropertyController::class, 'getCommissionRateHistory']);
    Route::get('/dashboard/commission-notifications', [App\Http\Controllers\PropertyController::class, 'getCommissionRateNotifications']);
    Route::get('/dashboard/payment/{paymentId}/commission-details', [App\Http\Controllers\PropertyController::class, 'getPaymentCommissionDetails']);
    Route::get('/dashboard/commission-report/export', [App\Http\Controllers\PropertyController::class, 'exportCommissionReport'])->name('landlord.commission-report.export');
});
