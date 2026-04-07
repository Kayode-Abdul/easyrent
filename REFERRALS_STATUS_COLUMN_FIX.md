# Referrals Status Column Fix

## Issue
The `MarketerSystemSeeder` was attempting to insert data into a column named `status` in the `referrals` table, but the actual column name is `referral_status`.

## Error Message
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'status' in 'field list' 
(SQL: insert into `referrals` (`referrer_id`, `referred_id`, `referral_code`, `status`, `commission_amount`, `commission_status`, `campaign_id`, `referral_source`, `conversion_date`, `updated_at`, `created_at`) values (...))
```

## Root Cause
The seeder was using the incorrect column name `status` instead of `referral_status` when creating referral records.

## Solution
Updated `database/seeders/MarketerSystemSeeder.php` to use the correct column name:

**Before:**
```php
$referral = Referral::create([
    'referrer_id' => $marketer->user_id,
    'referred_id' => $landlord->user_id,
    'referral_code' => $marketer->referral_code,
    'status' => 'completed',  // ❌ Wrong column name
    // ...
]);
```

**After:**
```php
$referral = Referral::create([
    'referrer_id' => $marketer->user_id,
    'referred_id' => $landlord->user_id,
    'referral_code' => $marketer->referral_code,
    'referral_status' => 'completed',  // ✅ Correct column name
    // ...
]);
```

## Database Schema
The `referrals` table has the following status-related columns:
- `referral_status` - ENUM('pending', 'active', 'completed', 'cancelled') - Status of the referral itself
- `commission_status` - Status of the commission payment (pending, approved, paid)

## Verification
The `Referral` model already has `referral_status` in its `$fillable` array, so no model changes were needed.

## Files Modified
1. `database/seeders/MarketerSystemSeeder.php` - Fixed column name from `status` to `referral_status`

## Testing
After this fix, the seeder should run successfully:
```bash
php artisan db:seed --class=MarketerSystemSeeder
```

## Date
December 7, 2025
