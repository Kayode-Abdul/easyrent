# Proforma Send Fix

**Date:** December 7, 2025  
**Issue:** Unable to send proforma invoices due to foreign key constraint violation

---

## Problem Identified

The proforma sending functionality was failing with a foreign key constraint violation:

```
SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: 
a foreign key constraint fails (`easyrent`.`profoma_receipt`, 
CONSTRAINT `profoma_receipt_apartment_id_foreign` FOREIGN KEY (`apartment_id`) 
REFERENCES `apartments` (`id`) ON DELETE CASCADE)
```

### Root Cause

The `profoma_receipt` table has a foreign key constraint that references `apartments.id` (the primary key), but the controller was trying to insert the public `apartment_id` instead of the internal `id`.

**Database Schema:**
- `apartments.id` - Primary key (auto-increment)
- `apartments.apartment_id` - Public identifier (unique string/number)
- `profoma_receipt.apartment_id` - Foreign key that should reference `apartments.id`

**The Issue:**
```php
// WRONG - Using public apartment_id
'apartment_id' => $apartment->apartment_id,

// CORRECT - Using internal primary key
'apartment_id' => $apartment->id,
```

---

## Fix Applied

### 1. Fixed ProfomaController.php

**File:** `app/Http/Controllers/ProfomaController.php`

```php
// OLD CODE (Line ~85)
'apartment_id' => $apartment->apartment_id,

// NEW CODE
'apartment_id' => $apartment->id, // Use internal PK, not public apartment_id
```

### 2. Fixed PropertyController.php

**File:** `app/Http/Controllers/PropertyController.php`

Added proper apartment resolution in `sendProfomaForApartment` method:

```php
// OLD CODE
$profoma = \App\Models\ProfomaReceipt::where('apartment_id', $apartmentId)->first();

// NEW CODE
// Resolve apartment by either numeric PK id or public apartment_id
$apartment = Apartment::find($apartmentId);
if (!$apartment) {
    $apartment = Apartment::where('apartment_id', $apartmentId)->first();
}
if (!$apartment) {
    return response()->json([
        'success' => false,
        'message' => 'Apartment not found.'
    ], 404);
}

$profoma = \App\Models\ProfomaReceipt::where('apartment_id', $apartment->id)->first();
```

### 3. Fixed Property Show View

**File:** `resources/views/property/show.blade.php`

```php
// OLD CODE
$profoma = \App\Models\ProfomaReceipt::where('apartment_id', $apartment->apartment_id)->first();

// NEW CODE
$profoma = \App\Models\ProfomaReceipt::where('apartment_id', $apartment->id)->first();
```

---

## Testing Results

Created and ran `test_proforma_send.php` to verify the fix:

```
=== PROFORMA SEND DIAGNOSTIC TEST ===

1. Checking apartments with tenants...
✅ Found 1 apartments with tenants

2. Testing ProfomaController route...
✅ Using apartment: 1599327 (Tenant ID: 627116)

3. Verifying tenant exists...
✅ Tenant found: adegoke jimoh (adegoke@easyrent.africa)

4. Verifying landlord exists...
✅ Landlord found: kayode abdul (moshoodkayodeabdul@gmail.com)

5. Testing proforma send functionality...
✅ Proforma sent successfully!
✅ Proforma record created in database
   Transaction ID: 8660693
   Total: ₦86,000.00
   Status: New

6. Checking email configuration...
   Mail driver: log (configured for development)

7. Checking recent proforma records...
✅ Found 1 recent proformas
```

---

## Key Changes Summary

1. **Fixed foreign key reference** - Use `apartments.id` instead of `apartments.apartment_id`
2. **Added apartment resolution** - Handle both internal ID and public apartment_id
3. **Consistent database queries** - All proforma queries now use correct apartment ID
4. **Maintained backward compatibility** - Code works with both ID formats

---

## Files Modified

1. `app/Http/Controllers/ProfomaController.php` - Fixed apartment_id reference
2. `app/Http/Controllers/PropertyController.php` - Added apartment resolution
3. `resources/views/property/show.blade.php` - Fixed proforma lookup
4. `test_proforma_send.php` - Created diagnostic test

---

## How to Test

1. Navigate to any apartment with an assigned tenant
2. Click "Send Proforma" button
3. Fill in the proforma details
4. Click "Send Proforma"
5. Verify success message appears
6. Check that proforma record is created in database
7. Verify tenant receives notification message

---

## Status

✅ **FIXED** - Proforma sending now works correctly and creates proper database records with correct foreign key relationships.

The issue was a simple but critical database relationship problem where the wrong field was being used for the foreign key reference.