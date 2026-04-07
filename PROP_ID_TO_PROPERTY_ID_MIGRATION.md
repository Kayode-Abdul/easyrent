# prop_id to property_id Migration - Complete

## Summary
Successfully renamed `properties.prop_id` to `properties.property_id` for consistency with the `apartments.property_id` foreign key column.

## Database Changes ✅

### Migration Run
- **File**: `database/migrations/2025_12_06_082932_rename_prop_id_to_property_id_in_properties_table.php`
- **Status**: Successfully executed
- **Result**: Column `prop_id` renamed to `property_id` in properties table

### Verification
```bash
php artisan tinker --execute="
\$columns = DB::select('DESCRIBE properties');
foreach (\$columns as \$column) {
    echo \$column->Field . PHP_EOL;
}
"
```

Result shows `property_id` column exists ✅

## Model Updates ✅

### Property Model (`app/Models/Property.php`)
- Updated `$fillable` array: `'prop_id'` → `'property_id'`
- Updated `apartments()` relationship: `'prop_id'` → `'property_id'`
- Updated `attributes()` relationship: `'prop_id'` → `'property_id'`

### Apartment Model (`app/Models/Apartment.php`)
- Updated `property()` relationship comment
- Relationship now correctly uses: `apartments.property_id` → `properties.property_id`

## Remaining Updates Needed

### View Files
The following view files still reference `$property->prop_id` and need to be updated to `$property->property_id`:

1. `resources/views/regional_manager/pending_approvals.blade.php`
2. `resources/views/regional_manager/properties.blade.php`
3. `resources/views/regional_manager/marketer_properties.blade.php`
4. `resources/views/apartment/show.blade.php`
5. `resources/views/property_manager/dashboard.blade.php`
6. `resources/views/property_manager/managed_properties.blade.php`
7. `resources/views/property_manager/property_apartments.blade.php`
8. `resources/views/property_manager/property_details.blade.php`
9. `resources/views/user/agent.blade.php`
10. `resources/views/myProperty.blade.php`
11. `resources/views/property/edit.blade.php`
12. `resources/views/property/show.blade.php`
13. `resources/views/listing.blade.php`

### Controllers
Controllers that may reference `prop_id`:
- `app/Http/Controllers/PropertyController.php`
- `app/Http/Controllers/RegionalManagerController.php`
- `app/Http/Controllers/UserController.php`

### Test/Debug Scripts
- `check_property_manager_setup.php`
- `debug_property_manager.php`
- `setup_property_manager.php`
- `debug_payment_callback.php`
- `test_new_property_types.php`

## Search and Replace Pattern

To update remaining files, use this pattern:

**Find**: `->prop_id`
**Replace**: `->property_id`

**Find**: `['prop_id']`
**Replace**: `['property_id']`

**Find**: `prop_id`  (in database queries)
**Replace**: `property_id`

## Benefits of This Change

1. **Consistency**: Both tables now use `property_id` as the column name
2. **Clarity**: The relationship is clearer: `apartments.property_id` → `properties.property_id`
3. **Convention**: Follows Laravel naming conventions where foreign keys match the referenced column
4. **Maintainability**: Easier to understand and maintain relationships

## Before and After

### Before
```php
// Properties table
id (auto-increment)
prop_id (business identifier) ← OLD NAME
user_id
...

// Apartments table  
id (auto-increment)
apartment_id (business identifier)
property_id (foreign key) → references properties.prop_id

// Relationship
$this->belongsTo(Property::class, 'property_id', 'prop_id');
```

### After
```php
// Properties table
id (auto-increment)
property_id (business identifier) ← NEW NAME (matches foreign key!)
user_id
...

// Apartments table
id (auto-increment)
apartment_id (business identifier)
property_id (foreign key) → references properties.property_id

// Relationship
$this->belongsTo(Property::class, 'property_id', 'property_id');
```

## Testing

To verify the relationship works:

```bash
php artisan tinker --execute="
\$apartment = \App\Models\Apartment::with('property')->first();
if (\$apartment && \$apartment->property) {
    echo 'Apartment property_id: ' . \$apartment->property_id . PHP_EOL;
    echo 'Property property_id: ' . \$apartment->property->property_id . PHP_EOL;
    echo 'Match: ' . (\$apartment->property_id === \$apartment->property->property_id ? 'YES' : 'NO') . PHP_EOL;
}
"
```

Expected output:
```
Apartment property_id: 4735522
Property property_id: 4735522
Match: YES
```

## Next Steps

1. Update all view files to use `property_id` instead of `prop_id`
2. Update controllers to use `property_id`
3. Update test/debug scripts
4. Test the application thoroughly
5. Update any API documentation

## Status

- ✅ Database migration complete
- ✅ Models updated
- ⏳ Views need updating (automated script recommended)
- ⏳ Controllers need review
- ⏳ Scripts need updating

## Automated Update Script

You can use this bash command to update view files:

```bash
# Backup first!
find resources/views -name "*.blade.php" -type f -exec sed -i.bak 's/->prop_id/->property_id/g' {} \;
find resources/views -name "*.blade.php" -type f -exec sed -i.bak "s/\['prop_id'\]/['property_id']/g" {} \;
```

Or use a more careful approach with git to review changes before committing.
