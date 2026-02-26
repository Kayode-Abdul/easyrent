# Payment Flow Bugs - Detailed Analysis and Fixes

## BUG #1: Benefactor Payment Callback Route Accepts Only GET

### Issue
- **Route Definition** (routes/web.php:549):
  ```php
  Route::get('/payment/callback', [BenefactorPaymentController::class, 'paymentCallback'])
  ```
- **Problem**: Route only accepts GET requests
- **Paystack Behavior**: Sends callback as POST request
- **Result**: Paystack callback is rejected with 405 Method Not Allowed

### Fix
Change route to accept both GET and POST:
```php
Route::match(['get', 'post'], '/payment/callback', [BenefactorPaymentController::class, 'paymentCallback'])->name('payment.callback');
```

### Impact
- Benefactor payments will now properly receive callback from Paystack
- User will be redirected to success page instead of login

---

## BUG #2: Proforma Payment Redirects to Receipt Instead of Success Page

### Issue
- **Location**: `app/Http/Controllers/PaymentController.php` line 835
- **Current Code**:
  ```php
  return redirect()->route('payment.receipt', ['id' => $payment->id])
      ->with('success', 'Payment was successful! Your receipt has been generated.');
  ```
- **Problem**: 
  - Redirects to receipt view (which is just a document view)
  - Not a proper success page
  - User doesn't see confirmation that apartment is assigned
  - Payment doesn't reflect in account because success page logic isn't triggered

### Root Cause
For regular proforma payments (non-invitation), the code should redirect to a success page, not a receipt page.

### Fix
Create a proforma payment success page and redirect to it:

**Step 1**: Create success view at `resources/views/proforma/payment-success.blade.php`

**Step 2**: Update PaymentController redirect (line 835):
```php
return redirect()->route('proforma.payment.success', ['payment' => $payment->id])
    ->with('success', 'Payment completed successfully! Your apartment has been assigned.');
```

**Step 3**: Add route in routes/web.php:
```php
Route::get('/proforma/payment/success/{payment}', [PaymentController::class, 'proformaPaymentSuccess'])
    ->name('proforma.payment.success');
```

**Step 4**: Add controller method:
```php
public function proformaPaymentSuccess($paymentId)
{
    $payment = Payment::with(['tenant', 'landlord', 'apartment.property'])->findOrFail($paymentId);
    
    // Verify user owns this payment
    if ($payment->tenant_id !== auth()->id() && $payment->landlord_id !== auth()->id()) {
        abort(403);
    }
    
    return view('proforma.payment-success', compact('payment'));
}
```

### Impact
- User sees proper success confirmation
- Apartment assignment is confirmed
- Payment reflects in account

---

## BUG #3: Apartment Assignment May Fail Due to Invitation Lookup

### Issue
- **Location**: `app/Services/Payment/PaymentIntegrationService.php` lines 182-280
- **Problem**: `findRelatedInvitation()` may return null if:
  1. Payment metadata doesn't contain `invitation_token`
  2. Invitation token format is wrong
  3. Invitation record doesn't exist in database

### Root Cause
When payment is created, the `invitation_token` may not be properly stored in `payment_meta`.

### Investigation Needed
1. Check if `invitation_token` is being set when payment is created
2. Verify invitation record exists in database
3. Check if token format matches

### Temporary Workaround
If invitation lookup fails, fall back to direct apartment assignment:

**Location**: `app/Services/Payment/PaymentIntegrationService.php` line 127-180

Add fallback logic:
```php
// If invitation not found but we have apartment_id and tenant_id, assign directly
if (!$invitation && $payment->apartment_id && $payment->tenant_id) {
    Log::warning('Invitation not found, attempting direct apartment assignment', [
        'payment_id' => $payment->id,
        'apartment_id' => $payment->apartment_id,
        'tenant_id' => $payment->tenant_id
    ]);
    
    $this->assignApartmentToTenant($payment->apartment, $payment);
    
    return [
        'success' => true,
        'apartment_assigned' => true,
        'message' => 'Payment processed and apartment assigned (via fallback)'
    ];
}
```

---

## BUG #4: Billing Display May Not Show Payments

### Issue
- **Location**: `app/Http/Controllers/BillingController.php` and `resources/views/billing/index.blade.php`
- **Problem**: Payments are created but not visible in billing view
- **Likely Causes**:
  1. Query filters by wrong tenant_id field
  2. Apartment ID field mismatch (using `apartment_id` vs `apartment.id`)
  3. Status filter excludes completed payments

### Investigation Needed
1. Check BillingController query
2. Verify it uses `tenant_id` field correctly
3. Check if it filters by status

### Fix
Ensure billing query:
```php
$payments = Payment::where('tenant_id', auth()->user()->user_id)
    ->where('status', 'completed')
    ->with(['apartment.property', 'landlord'])
    ->orderBy('paid_at', 'desc')
    ->paginate(15);
```

---

## Summary of Required Fixes

| Bug | Severity | Fix Type | Effort |
|-----|----------|----------|--------|
| Benefactor route GET/POST | HIGH | Route change | 5 min |
| Proforma redirect | HIGH | Create success page + route | 15 min |
| Apartment assignment fallback | MEDIUM | Add fallback logic | 10 min |
| Billing display | MEDIUM | Verify query | 10 min |

---

## Testing Checklist

After fixes:

- [ ] Benefactor payment callback is received
- [ ] Benefactor redirected to success page
- [ ] Proforma payment redirected to success page
- [ ] Apartment assigned to tenant
- [ ] Payment visible in billing
- [ ] ER Link authentication flow works
- [ ] Guest checkout works
- [ ] Registered user checkout works

