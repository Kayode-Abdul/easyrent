# Benefactor Email Field Fix

## Issue
When processing benefactor payments (both one-time and recurring), users encountered a database error:
```
SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'email' cannot be null
(SQL: insert into `benefactors` (`email`, `full_name`, `phone`, `type`, `relationship_type`, `is_registered`, `updated_at`, `created_at`) 
values (?, hjkgggiuigug, 07063040663, guest, employer, 0, 2026-01-21 15:52:09, 2026-01-21 15:52:09))
```

## Root Cause
The benefactor payment form was missing an email input field for guest users. The controller was trying to use `$invitation->benefactor_email`, which could be null, resulting in the database constraint violation.

## Solution

### 1. Added Email Field to Form (`resources/views/benefactor/payment.blade.php`)

**Added:**
```html
<div class="form-group">
    <label for="email">Email Address *</label>
    <input type="email" name="email" id="email" class="form-control" 
           value="{{ old('email', $invitation->benefactor_email ?? '') }}" required>
    <small class="form-text text-muted">We'll send payment confirmation to this email</small>
</div>
```

**Features:**
- ✅ Required field for guest users
- ✅ Pre-fills with invitation email if available
- ✅ Preserves old input on validation errors
- ✅ Clear helper text explaining purpose
- ✅ Proper email validation (type="email")

### 2. Updated Controller Validation (`app/Http/Controllers/BenefactorPaymentController.php`)

**Before:**
```php
if (!$isLoggedIn) {
    $validationRules['full_name'] = 'required|string|max:255';
    $validationRules['create_account'] = 'nullable|boolean';
    // ... password rules
}
```

**After:**
```php
if (!$isLoggedIn) {
    $validationRules['full_name'] = 'required|string|max:255';
    $validationRules['email'] = 'required|email|max:255';
    $validationRules['create_account'] = 'nullable|boolean';
    // ... password rules
}
```

**Key Changes:**
- ✅ Added email validation for guest users
- ✅ Validates email format
- ✅ Required field for non-logged-in users

### 3. Updated Benefactor Creation Logic

**Before:**
```php
// Guest user
$benefactor = Benefactor::create([
    'email' => $invitation->benefactor_email, // Could be null!
    'full_name' => $request->full_name,
    // ...
]);
```

**After:**
```php
// Guest user
$guestEmail = $request->email ?? $invitation->benefactor_email;

if (!$guestEmail) {
    throw new \Exception('Email address is required for payment processing.');
}

$benefactor = Benefactor::create([
    'email' => $guestEmail, // Always has a value
    'full_name' => $request->full_name,
    // ...
]);
```

**Key Changes:**
- ✅ Uses email from form request first
- ✅ Falls back to invitation email if available
- ✅ Throws clear error if no email provided
- ✅ Prevents null email from reaching database

## Form Field Order

The guest checkout section now has this field order:
1. **Full Name** (required)
2. **Email Address** (required) ← NEW
3. **Phone Number** (optional)
4. Account creation checkbox (for recurring only)
5. Password fields (if creating account)

## User Experience

### For Guest Users (One-Time Payment)
```
1. Select "One-Time Payment"
2. Choose relationship type
3. Enter full name
4. Enter email address ← NEW REQUIRED FIELD
5. Enter phone (optional)
6. Proceed to payment
```

### For Guest Users (Recurring Payment)
```
1. Select "Recurring Payment"
2. Choose frequency
3. Choose relationship type
4. Enter full name
5. Enter email address ← NEW REQUIRED FIELD
6. Enter phone (optional)
7. Optionally create account
8. Proceed to payment
```

### For Logged-In Users
```
1. Select payment type
2. Choose relationship type
3. Proceed to payment
(Email comes from user account)
```

## Database Schema

The `benefactors` table requires these fields:
- `email` - NOT NULL (this was causing the error)
- `full_name` - NOT NULL
- `phone` - NULLABLE
- `type` - NOT NULL (guest/registered)
- `relationship_type` - NOT NULL
- `is_registered` - NOT NULL (boolean)

## Benefits

1. **No More Database Errors:** Email is always provided
2. **Better Communication:** System can send payment confirmations
3. **User Tracking:** Can identify returning benefactors by email
4. **Account Creation:** Email is available if user wants to create account later
5. **Data Integrity:** Ensures all benefactor records have valid contact info

## Testing Checklist

- [x] Guest user with one-time payment can enter email
- [x] Guest user with recurring payment can enter email
- [x] Email field is required for guests
- [x] Email validation works (rejects invalid emails)
- [x] Pre-fills email from invitation if available
- [x] Logged-in users don't see email field (uses account email)
- [x] No database constraint violations
- [x] Payment confirmation emails sent to correct address

## Files Modified

1. **resources/views/benefactor/payment.blade.php**
   - Added email input field for guest users
   - Added helper text
   - Pre-fills from invitation if available

2. **app/Http/Controllers/BenefactorPaymentController.php**
   - Added email validation rule for guests
   - Updated benefactor creation to use form email
   - Added fallback logic and error handling

## Error Handling

The controller now handles these scenarios:
1. ✅ Email provided in form → Use it
2. ✅ Email in invitation → Use as fallback
3. ✅ No email available → Throw clear error
4. ✅ Invalid email format → Validation error

## Related Issues

This fix also resolves:
- Missing payment confirmation emails (no email to send to)
- Inability to contact benefactors for payment issues
- Duplicate benefactor records (can now match by email)

## Summary

Added a required email field to the benefactor payment form for guest users. The controller now validates and uses this email when creating benefactor records, preventing the "Column 'email' cannot be null" database error. Logged-in users continue to use their account email automatically.
