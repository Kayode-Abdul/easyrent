# Database Foreign Key Analysis Report

## Current State Analysis

### Foreign Key Constraints on Apartments Table
The current foreign key constraints on the apartments table are:
- `apartments_property_id_foreign`: apartments.property_id -> properties.id ❌ **INCORRECT**
- `apartments_tenant_id_foreign`: apartments.tenant_id -> users.user_id ✅ **CORRECT**
- `apartments_user_id_foreign`: apartments.user_id -> users.user_id ✅ **CORRECT**
- `apartments_apartment_type_id_foreign`: apartments.apartment_type_id -> apartment_types.id ✅ **CORRECT**

### Problem Identified
The main issue is that `apartments.property_id` is currently referencing `properties.id` instead of `properties.property_id`.

### Database Schema Structure

#### Properties Table
- `id`: bigint(20) unsigned (Primary Key - Auto-increment)
- `property_id`: bigint(20) unsigned (Unique Key - Business identifier)
- Other fields: user_id, prop_type, address, state, lga, etc.

#### Apartments Table  
- `id`: bigint(20) unsigned (Primary Key - Auto-increment)
- `property_id`: bigint(20) unsigned (Foreign Key - Currently referencing properties.id)
- `apartment_id`: bigint(20) unsigned (Unique Key - Business identifier)
- Other fields: apartment_type, tenant_id, user_id, etc.

### Data Analysis Results

#### Total Records
- Total apartments: 2
- Total properties: 2

#### Invalid References
- Apartments referencing properties.id (incorrect): 2
  - Apartment ID 10: property_id=2, should be 1416028
  - Apartment ID 11: property_id=2, should be 1416028
- Apartments correctly referencing properties.property_id: 0
- Truly orphaned apartments (no matching property): 0

### Model Relationship Issues

#### Apartment Model
```php
public function property(): BelongsTo
{
    // INCORRECT: Currently referencing properties.id
    return $this->belongsTo(Property::class, 'property_id', 'id');
}
```

#### Property Model
```php
public function apartments(): HasMany
{
    // CORRECT: Already configured to use property_id
    return $this->hasMany(Apartment::class, 'property_id', 'property_id');
}
```

### Migration Status
A migration file exists (`2025_12_16_120000_fix_apartments_property_id_foreign_key.php`) that appears to address this issue, but it may not have been run yet or may need to be executed.

## Required Fixes

### 1. Database Migration
- Drop existing foreign key constraint: `apartments_property_id_foreign`
- Update apartment records to reference correct property_id values
- Create new foreign key constraint: apartments.property_id -> properties.property_id

### 2. Model Relationship Fix
- Update Apartment model property() relationship to reference properties.property_id
- Ensure Property model apartments() relationship uses correct foreign key

### 3. Application Code Updates
- Update any queries that join apartments and properties
- Fix apartment creation validation logic
- Update apartment display logic in controllers and views

## Data Migration Strategy
Since we have 2 apartments that need to be updated:
- Apartment ID 10: Change property_id from 2 to 1416028
- Apartment ID 11: Change property_id from 2 to 1416028

No orphaned records need to be handled as all apartments have valid corresponding properties.

## Validation Requirements
After the fix:
- All apartments should reference valid properties.property_id values
- Foreign key constraint should enforce referential integrity
- Apartment creation should validate against properties.property_id
- Property pages should display associated apartments correctly