# Property ID Usage Guide

## Critical: Use prop_id, NOT id

The `properties` table has TWO identifier columns:
- `id` - Auto-increment primary key (for internal database use only)
- `prop_id` - Business identifier (THIS is what the application uses)

## ⚠️ IMPORTANT RULE
**Always use `prop_id` when referencing properties in the application.**

The `id` column is just an auto-increment field for database internal use. All application logic, foreign keys, and relationships MUST use `prop_id`.

## Current Schema

### Properties Table
```
id (bigint) - Auto-increment, internal use only
prop_id (bigint) - Business identifier, USE THIS
user_id (bigint) - Owner
prop_type (int) - Foreign key to property_types.id
address (varchar)
state (varchar)
lga (varchar)
... other fields
```

### Apartments Table
```
id (bigint) - Auto-increment primary key
apartment_id (bigint) - Business identifier
property_id (bigint) - Foreign key to properties.prop_id ✅
apartment_type (varchar) - Type name
apartment_type_id (bigint) - Foreign key to apartment_types.id
... other fields
```

## Correct Model Relationships

### Apartment Model ✅ CORRECT
```php
public function property(): BelongsTo
{
    // Correct: Uses prop_id as the owner key
    return $this->belongsTo(Property::class, 'property_id', 'prop_id');
}
```

### Property Model ✅ CORRECT
```php
public function apartments(): HasMany
{
    // Correct: Uses prop_id as the local key
    return $this->hasMany(Apartment::class, 'property_id', 'prop_id');
}
```

## ❌ WRONG Examples (DO NOT DO THIS)

```php
// WRONG - Using id instead of prop_id
$this->belongsTo(Property::class, 'property_id', 'id');

// WRONG - Querying by id
Property::where('id', $propertyId)->first();

// WRONG - Joining on id
->join('properties', 'apartments.property_id', '=', 'properties.id')
```

## ✅ CORRECT Examples

```php
// CORRECT - Using prop_id
$this->belongsTo(Property::class, 'property_id', 'prop_id');

// CORRECT - Querying by prop_id
Property::where('prop_id', $propertyId)->first();

// CORRECT - Joining on prop_id
->join('properties', 'apartments.property_id', '=', 'properties.prop_id')

// CORRECT - Creating apartment with property reference
Apartment::create([
    'property_id' => $property->prop_id, // Use prop_id
    'apartment_type_id' => $typeId,
    // ... other fields
]);
```

## Database Foreign Keys

All foreign keys referencing properties MUST point to `prop_id`:

```sql
-- CORRECT
ALTER TABLE apartments 
ADD CONSTRAINT fk_apartments_property 
FOREIGN KEY (property_id) REFERENCES properties(prop_id);

-- WRONG (DO NOT DO THIS)
ALTER TABLE apartments 
ADD CONSTRAINT fk_apartments_property 
FOREIGN KEY (property_id) REFERENCES properties(id);
```

## Why This Matters

1. **Data Integrity**: The `prop_id` is the stable business identifier
2. **Consistency**: All existing data uses `prop_id` for relationships
3. **Foreign Keys**: Database constraints are set up to use `prop_id`
4. **Legacy Code**: Existing codebase expects `prop_id`

## Verification Checklist

When working with properties, always verify:

- [ ] Model relationships use `prop_id` as the owner/local key
- [ ] Queries filter by `prop_id`, not `id`
- [ ] Foreign key columns store `prop_id` values
- [ ] Joins use `prop_id` for matching
- [ ] API responses return `prop_id` as the identifier

## Similar Pattern for Other Tables

This pattern applies to other tables too:

### Users Table
- `id` - Auto-increment (internal)
- `user_id` - Business identifier (USE THIS)

### Apartments Table  
- `id` - Auto-increment (internal)
- `apartment_id` - Business identifier (USE THIS)

## Testing

To verify relationships are correct:

```bash
php artisan tinker --execute="
\$apartment = \App\Models\Apartment::with('property')->first();
if (\$apartment && \$apartment->property) {
    echo 'Apartment property_id: ' . \$apartment->property_id . PHP_EOL;
    echo 'Property prop_id: ' . \$apartment->property->prop_id . PHP_EOL;
    echo 'Match: ' . (\$apartment->property_id === \$apartment->property->prop_id ? 'YES' : 'NO') . PHP_EOL;
}
"
```

Expected output:
```
Apartment property_id: 4735522
Property prop_id: 4735522
Match: YES
```

## Summary

✅ **DO**: Use `prop_id` for all property references
❌ **DON'T**: Use `id` for property references

The `id` column exists only for database internal operations. All application code must use `prop_id`.

## Status: DOCUMENTED ✅

This guide ensures consistent usage of `prop_id` throughout the application.
