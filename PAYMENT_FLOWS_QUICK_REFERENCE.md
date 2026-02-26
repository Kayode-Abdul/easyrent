# Payment Flows - Quick Reference & Troubleshooting

## Quick Route Reference

### EasyRent Link (Apartment Invitation)
```
GET  /apartment/invite/{token}                    → Show apartment details
POST /apartment/invite/{token}/apply              → Submit application
GET  /apartment/invite/{token}/payment            → Show payment form
POST /apartment/invite/{token}/payment/callback   → Handle Paystack callback
GET  /apartment/invite/{token}/success            → Show success page
```

### Direct Payment (Proforma)
```
GET  /proforma/{id}/payment                       → Show payment form
POST /pay                                         → Redirect to Paystack
GET  /payment/callback                            → Handle Paystack callback
GET  /proforma/payment/success/{payment_id}       → Show success page
GET  /dashboard/billing                           → View payment history
```

### Benefactor Link
```
GET  /benefactor/payment/{token}                  → Show approval page
POST /benefactor/payment/{token}/approve          → Approve payment
POST /benefactor/payment/{token}/process          → Collect benefactor info
GET  /benefactor/gateway/{payment_id}             → Show payment form
GET  /benefactor/payment/callback                 → Handle Paystack callback
GET  /benefactor/payment/success/{payment_id}     → Show success page
GET  /benefactor/dashboard                        → Benefactor dashboard
```

---

## Controller Methods

### ApartmentInvitationController
```php
show($token)                    // Display apartment details
apply($token)                   // Process application
paymentDirect($token)           // Show payment form (direct flow)
payment($token, $payment)       // Show payment form (with payment ID)
paymentCallback($token)         // Handle payment callback
success($token)                 // Show success page
storeSession($request)          // Store session data (AJAX)
```

### PaymentController
```php
redirectToGateway()             // Redirect to Paystack
handleGatewayCallback()         // Process Paystack callback
showProformaPaymentForm()        // Show proforma payment form
proformaPaymentSuccess()         // Show proforma success page
showReceipt()                    // Display payment receipt
```

### BenefactorPaymentController
```php
show($token)                    // Show approval page
approve($token)                 // Approve payment
decline($token)                 // Decline payment
processPayment($token)          // Collect benefactor info
paymentGateway($paymentId)      // Show payment form
paymentCallback()               // Handle Paystack callback
paymentSuccess($paymentId)      // Show success page
dashboard()                     // Benefactor dashboard
pauseRecurring($paymentId)      // Pause recurring payment
resumeRecurring($paymentId)     // Resume recurring payment
cancelRecurring($paymentId)     // Cancel recurring payment
```

---

## Common Issues & Solutions

### Issue 1: Payment Not Showing in Billing Page

**Symptoms:**
- User completed payment successfully
- Payment doesn't appear in `/dashboard/billing`

**Root Causes:**
1. `tenant_id` is NULL in Payment record
2. Payment status is not 'completed'
3. User viewing wrong account

**Solutions:**
```php
// Check payment was created with correct tenant_id
SELECT * FROM payments 
WHERE tenant_id = {user_id} 
AND status IN ('completed', 'success');

// If tenant_id is NULL, payment was guest payment
// Need to update after user registration
UPDATE payments 
SET tenant_id = {user_id} 
WHERE transaction_id = '{reference}';
```

**Prevention:**
- Ensure `tenant_id` is set during payment creation
- Verify payment status is updated to 'completed' in callback
- Test with real payment flow

---

### Issue 2: Unauthenticated User Sees Payment Button

**Symptoms:**
- Unauthenticated user on EasyRent link sees "Pay Now" button
- Should only see "Login" and "Sign Up" buttons

**Root Cause:**
- View logic checking wrong authentication condition

**Solution:**
```blade
@auth
    <!-- Show payment form for authenticated users -->
@else
    <!-- Show login/signup buttons for unauthenticated users -->
@endauth
```

**File:** `resources/views/apartment/invite/show.blade.php`

---

### Issue 3: Session Data Lost After Login

**Symptoms:**
- User fills application form
- Clicks login
- After login, form data is empty

**Root Cause:**
- Session data not properly stored before redirect
- Session key mismatch

**Solution:**
```php
// Store in session before redirect
session([
    'easyrent_application_data' => [
        'duration' => $duration,
        'move_in_date' => $moveInDate,
        'apartment_id' => $apartmentId
    ],
    'easyrent_invitation_token' => $token
]);

// Retrieve after login
$appData = session('easyrent_application_data');
```

**File:** `app/Http/Controllers/ApartmentInvitationController.php`

---

### Issue 4: Payment Callback Not Received

**Symptoms:**
- User completes payment on Paystack
- Not redirected to success page
- Payment status remains 'pending'

**Root Causes:**
1. Callback route not accepting POST
2. Paystack webhook not configured
3. Server firewall blocking Paystack IPs

**Solutions:**
```php
// Ensure route accepts both GET and POST
Route::match(['get', 'post'], '/payment/callback', 
    [PaymentController::class, 'handleGatewayCallback']
)->name('payment.callback');

// Check Paystack webhook settings
// Settings → Webhooks → Add: {your_domain}/webhooks/paystack

// Verify server allows Paystack IPs
// Whitelist: 52.31.139.75, 52.49.173.169, 52.214.14.220
```

**Files:**
- `routes/web.php`
- Paystack Dashboard Settings

---

### Issue 5: Apartment Not Assigned After Payment

**Symptoms:**
- Payment successful
- Apartment not assigned to tenant
- Tenant can't see apartment in dashboard

**Root Cause:**
- `processInvitationPayment()` not called or failed

**Solution:**
```php
// Verify payment integration service is called
$result = $this->paymentIntegrationService->processInvitationPayment(
    $payment,
    $paymentDetails
);

// Check result
if (!$result['success']) {
    Log::error('Payment processing failed', [
        'error' => $result['error'],
        'payment_id' => $payment->id
    ]);
}
```

**File:** `app/Http/Controllers/PaymentController.php`

---

### Issue 6: Benefactor Payment Not Creating

**Symptoms:**
- Benefactor completes payment
- No BenefactorPayment record created
- No confirmation email sent

**Root Cause:**
- Database transaction rolled back
- Benefactor record creation failed

**Solution:**
```php
// Check Benefactor table for email
SELECT * FROM benefactors WHERE email = '{email}';

// Check BenefactorPayment table
SELECT * FROM benefactor_payments 
WHERE benefactor_id = {id};

// Check logs for transaction errors
tail -f storage/logs/laravel.log
```

**File:** `app/Http/Controllers/BenefactorPaymentController.php`

---

## Testing Checklist

### EasyRent Link
- [ ] Unauthenticated user can view apartment details
- [ ] Unauthenticated user sees Login/Signup buttons (NOT Pay button)
- [ ] User can login and see pre-filled form
- [ ] User can register and see pre-filled form
- [ ] Payment calculation is correct
- [ ] Payment callback updates status to 'completed'
- [ ] Apartment is assigned to tenant
- [ ] Emails sent to landlord and tenant
- [ ] Payment appears in tenant's billing page

### Direct Payment
- [ ] Authenticated user can access proforma payment link
- [ ] Unauthenticated user can access and pay
- [ ] Payment amount is correct
- [ ] Payment callback updates status
- [ ] Payment appears in billing page
- [ ] Emails sent to both parties

### Benefactor Link
- [ ] Benefactor receives invitation email
- [ ] Benefactor can approve/decline
- [ ] Guest checkout works
- [ ] Account creation works
- [ ] Recurring payment setup works
- [ ] Payment callback processes correctly
- [ ] Benefactor can pause/resume/cancel
- [ ] Emails sent to all parties

---

## Performance Optimization

### Caching
```php
// Cache apartment data
$this->cacheService->cacheApartmentData($apartmentId);

// Cache invitation data
$this->cacheService->cacheInvitationData($token);

// Retrieve from cache
$data = $this->cacheService->getCachedApartmentData($apartmentId);
```

### Query Optimization
```php
// Use eager loading
$invitation = ApartmentInvitation::with([
    'apartment.property',
    'landlord'
])->where('invitation_token', $token)->first();

// Select only needed columns
$invitation = ApartmentInvitation::select([
    'id', 'apartment_id', 'landlord_id', 'invitation_token'
])->first();
```

### Session Management
```php
// Clean up expired sessions
php artisan session:cleanup

// Monitor session size
SELECT SUM(LENGTH(payload)) FROM sessions;
```

---

## Monitoring & Logging

### Key Metrics to Monitor
```
- Payment success rate
- Average payment processing time
- Failed payment attempts
- Session abandonment rate
- Callback response time
```

### Log Locations
```
storage/logs/laravel.log          // Main application log
storage/logs/payment.log          // Payment-specific log
storage/logs/easyrent.log         // EasyRent link log
```

### Debug Mode
```php
// Enable debug logging
Log::info('Payment processing', [
    'payment_id' => $payment->id,
    'amount' => $payment->amount,
    'status' => $payment->status
]);

// Check logs
tail -f storage/logs/laravel.log | grep "Payment processing"
```

---

## Security Checklist

- [ ] CSRF tokens on all forms
- [ ] Rate limiting on payment endpoints
- [ ] Input validation on all requests
- [ ] SQL injection prevention (use Eloquent)
- [ ] XSS prevention (escape output)
- [ ] Secure token generation (crypto_random_bytes)
- [ ] HTTPS enforced
- [ ] Paystack API key in .env (not hardcoded)
- [ ] Session data encrypted
- [ ] Payment data logged securely

---

## Deployment Checklist

- [ ] Environment variables configured
- [ ] Database migrations run
- [ ] Cache cleared
- [ ] Session storage configured
- [ ] Email service configured
- [ ] Paystack webhook configured
- [ ] SSL certificate installed
- [ ] Firewall rules updated
- [ ] Backup system configured
- [ ] Monitoring alerts set up

