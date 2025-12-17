# Property ID Column Fix

## Issue
The seeder was failing with an error indicating that the `property_id` column doesn't exist in the `properties` table:

```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'property_id' in 'field list'
select `property_id` from `properties` where `user_id` = 340336
```

## Root Cause
The `properties` table was originally created with a column named `prop_id`. A migration was created to rename it to `property_id` for consistency, but this migration may not have run on all databases.

## Database Schema History

### Original Schema (2024-01-09)
```php
Schema::create('properties', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->unsignedBigInteger('prop_id')->unique();  // Original name
    // ...
});
```

### Intended Rename (2025-12-06)
```php
// Migration: 2025_12_06_082932_rename_prop_id_to_property_id_in_properties_table.php
$table->renameColumn('prop_id', 'property_id');
```

## Solution

### 1. Model Update
Added `property_id` to the `$fillable` array in `app/Models/Property.php` to match the renamed column:

**Before:**
```php
protected $fillable = [
    'user_id',
    'prop_type',  // property_id was missing
    // ...
];
```

**After:**
```php
protected $fillable = [
    'user_id',
    'property_id',  // ✅ Added to match renamed column
    'prop_type',
    // ...
];
```

### 2. Migration Fix
Created a new migration `2025_12_07_040000_ensure_property_id_column_exists.php` that:
- Checks if `prop_id` exists and `property_id` doesn't → Renames `prop_id` to `property_id`
- Checks if neither exists → Creates `property_id` column
- Otherwise → Does nothing (column already exists)

This ensures the database schema is consistent regardless of which migrations have run.

## Running the Fix

```bash
# Run the migration
php artisan migrate

# Verify the column exists
php artisan tinker --execute="
\$columns = DB::select('DESCRIBE properties');
foreach (\$columns as \$column) {
    if (str_contains(\$column->Field, 'property') || str_contains(\$column->Field, 'prop')) {
        echo \$column->Field . PHP_EOL;
    }
}
"
```

Expected output should show `property_id` (not `prop_id`).

## Files Modified
1. `app/Models/Property.php` - Added `property_id` to fillable array (to match renamed column)
2. `database/migrations/2025_12_07_040000_ensure_property_id_column_exists.php` - New migration to ensure `prop_id` is renamed to `property_id`
3. `database/seeders/MarketerSystemSeeder.php` - Fixed to use `referral_status` instead of `status`

## Related Issues
This fix is part of the broader property ID standardization effort documented in:
- `PROP_ID_TO_PROPERTY_ID_MIGRATION.md`
- `PROPERTY_ID_STANDARDIZATION_COMPLETE.md`

## Testing
After running the migration, test that properties can be queried:

```bash
php artisan tinker --execute="
\$property = \App\Models\Property::first();
if (\$property) {
    echo 'Property ID: ' . \$property->id . PHP_EOL;
    echo 'Has property_id attribute: ' . (isset(\$property->property_id) ? 'YES' : 'NO') . PHP_EOL;
}
"
```

## Date
December 7, 2025
