# Apartment Name Column Fix

## Issue
The code was referencing a non-existent `apartment_name` column in the apartments table, causing SQL errors when using the EasyRent invitation links.

## Error
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'apartment_name' in 'field list'
```

## Root Cause
Several files were trying to select `apartment_name` from the apartments table, but this column doesn't exist. The apartments table has these columns:
- `apartment_id` - Unique identifier
- `apartment_type` - Type name (string)
- `apartment_type_id` - Foreign key to apartment_types table
- `property_id` - Foreign key to properties table
- `amount` - Rent amount
- `range_start` - Lease start date
- `range_end` - Lease end date
- `tenant_id` - Current tenant
- `user_id` - Owner
- `occupied` - Occupancy status

The code was also referencing other non-existent columns like:
- `duration`
- `status`
- `bedrooms`
- `bathrooms`
- `size_sqft`

## Files Fixed

### 1. ApartmentInvitationController.php
**Changed**: Removed references to `apartment_name` and other non-existent columns
**Updated select to**: `apartment_id`, `property_id`, `apartment_type`, `apartment_type_id`, `amount`, `range_start`, `range_end`, `tenant_id`, `user_id`, `occupied`

### 2. EasyRentCacheService.php
**Changed**: Fixed multiple occurrences of `apartment_name` references
**Updated**: All apartment queries to use actual table columns
**Fixed**: Cache data structure to use `apartment_type` instead of `apartment_name`

### 3. RegisterController.php
**Changed**: `$invitationData['apartment_name']` to use property name and apartment type
**Updated to**:
```php
$invitationData['property_name'] = $invitation->apartment->property->prop_name ?? 'Property';
$invitationData['apartment_type'] = $invitation->apartment->apartment_type ?? 'Apartment';
```

## Solution
Replaced all references to non-existent columns with actual columns from the apartments table:
- Use `apartment_type` for the type of apartment
- Use `property->prop_name` for the property name
- Use actual date ranges (`range_start`, `range_end`) instead of `duration`
- Use `occupied` status instead of generic `status`

## Testing
To verify the fix works:

```bash
# Test that apartments can be queried
php artisan tinker --execute="
\$apartment = \App\Models\Apartment::with('property', 'apartmentType')->first();
if (\$apartment) {
    echo 'Apartment ID: ' . \$apartment->apartment_id . PHP_EOL;
    echo 'Type: ' . \$apartment->apartment_type . PHP_EOL;
    echo 'Property: ' . \$apartment->property->prop_name . PHP_EOL;
}
"
```

## Impact
- ✅ EasyRent invitation links now work correctly
- ✅ Apartment data can be properly queried
- ✅ Cache service works with correct columns
- ✅ Registration flow with invitations works

## Related
This fix is part of the property and apartment types normalization work where we:
1. Created lookup tables for property and apartment types
2. Added `apartment_type_id` foreign key
3. Maintained backward compatibility with `apartment_type` string field
4. Fixed code that was referencing non-existent columns

## Status: FIXED ✅
All references to `apartment_name` and other non-existent columns have been corrected to use actual table columns.
