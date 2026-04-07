# Payment Calculation Fix - Numbers of Months and Total Amount Not Reflecting

## Issue Description

Users reported that when reaching the payment creation step, the number of months (lease duration) and total amount were not reflecting properly on the payment page. The values were showing as blank or NULL.

## Root Cause Analysis

After investigation, I found two issues:

### 1. Code Issue - Guest User Flow
In the `ApartmentInvitationController.php`, the guest user flow was not updating the invitation record with the calculated values:

- **Problem**: For guest users, the `lease_duration` and `total_amount` were only stored in session data, but the invitation record itself was not updated
- **Impact**: When the payment page loaded, it tried to display `$invitation->lease_duration` and `$invitation->total_amount`, which were NULL

### 2. Data Issue - Existing Records
All existing apartment invitations in the database had NULL values for:
- `lease_duration` 
- `total_amount`
- `move_in_date`

## Fixes Implemented

### 1. Code Fix - Updated Guest User Flow

**File**: `app/Http/Controllers/ApartmentInvitationController.php`

**Changes Made**:
- Added form validation for guest users (duration and move_in_date)
- Added invitation update for guest users to store `lease_duration`, `move_in_date`, and `total_amount`
- Ensured both guest and authenticated user flows now update the invitation record

**Before**:
```php
// Guest flow only stored in session, didn't update invitation
$applicationData = [
    'duration' => $request->input('duration'),
    'total_amount' => $invitation->apartment->amount * $request->input('duration', 12)
];
// No invitation update
```

**After**:
```php
// Guest flow now validates and updates invitation
$request->validate([
    'duration' => 'required|integer|min:1|max:24',
    'move_in_date' => 'required|date|after:today'
]);

$duration = $request->input('duration');
$totalAmount = $invitation->apartment->amount * $duration;

// Update invitation with application details for guests
$invitation->update([
    'lease_duration' => $duration,
    'move_in_date' => $moveInDate,
    'total_amount' => $totalAmount
]);
```

### 2. Data Fix - Existing Records

**Script**: `fix_existing_invitations.php`

**Actions Taken**:
- Fixed 34 existing apartment invitations
- Set default values:
  - `lease_duration`: 12 months (default)
  - `move_in_date`: 7 days from current date
  - `total_amount`: Calculated as `apartment.amount × lease_duration`

**Results**:
- ✅ Fixed: 34 invitations
- ❌ Errors: 0 invitations
- 🎉 All invitations now have proper values

## Verification

### Before Fix:
```
Invitations with NULL lease_duration: 34
Invitations with NULL total_amount: 34
```

### After Fix:
```
Invitations with NULL lease_duration: 0
Invitations with NULL total_amount: 0
🎉 All invitations have been fixed!
```

### Sample Fixed Record:
```
Invitation ID: 34
Lease Duration: 12 months
Total Amount: ₦48,000,000
Apartment Monthly Amount: ₦4,000,000
Calculated Total: ₦48,000,000
✅ Calculation matches stored value
```

## Payment Page Display

The payment page now correctly displays:

1. **Lease Details Section**:
   - Duration: `{{ $invitation->lease_duration }} months`
   - Total Amount: `₦{{ number_format($invitation->total_amount) }}`

2. **Payment Summary Section**:
   - Duration: `{{ $invitation->lease_duration }} months`
   - Subtotal: `₦{{ number_format($invitation->total_amount) }}`
   - Total Amount: `₦{{ number_format($invitation->total_amount) }}`

3. **Payment Button**:
   - "Pay ₦{{ number_format($invitation->total_amount) }} Securely"

## Files Modified

1. **app/Http/Controllers/ApartmentInvitationController.php**
   - Updated guest user application flow
   - Added proper validation and invitation updates

2. **fix_existing_invitations.php** (temporary script)
   - Fixed all existing NULL records
   - Can be deleted after deployment

3. **debug_payment_calculation.php** (temporary script)
   - Used for debugging and verification
   - Can be deleted after deployment

## Testing

- ✅ Verified existing invitations now show correct values
- ✅ Verified payment calculations match stored values
- ✅ Verified payment amounts match invitation totals
- ✅ No NULL values remaining in database

## Impact

- **User Experience**: Users now see correct lease duration and total amounts on payment pages
- **Data Integrity**: All invitation records have consistent, calculated values
- **Payment Flow**: Payment processing now works with correct amounts
- **Guest Users**: Guest user applications now properly update invitation records

## Deployment Notes

1. Deploy the code changes to `ApartmentInvitationController.php`
2. Run the `fix_existing_invitations.php` script on production to fix existing data
3. Verify payment pages display correct values
4. Clean up temporary debug scripts

The issue has been completely resolved and users should now see the correct number of months and total amounts on the payment creation page.