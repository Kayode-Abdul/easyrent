# Foreign Key Analysis - Apartments Table

## Task 1: Database Schema Analysis Complete

### Current Database Schema Issues Identified

#### 1. Foreign Key Constraint Mismatch
- **Current Constraint**: `apartments_property_id_foreign` references `properties.id`
- **Required Constraint**: Should reference `properties.property_id`
- **Impact**: Apartments are linked to auto-increment IDs instead of business identifiers

#### 2. Properties Table Structure
```sql
CREATE TABLE `properties` (
  `id` bigint(20) UNSIGNED NOT NULL,           -- Auto-increment primary key
  `property_id` bigint(20) UNSIGNED NOT NULL,  -- Business identifier (unique)
  -- other columns...
)
```

#### 3. Apartments Table Structure
```sql
CREATE TABLE `apartments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `property_id` bigint(20) UNSIGNED NOT NULL,  -- Currently references properties.id
  -- other columns...
)
```

#### 4. Current Foreign Key Constraint
```sql
ALTER TABLE `apartments`
  ADD CONSTRAINT `apartments_property_id_foreign` 
  FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
```

### Data Analysis Results

#### Properties Data
| id | property_id | address | user_id |
|----|-------------|---------|---------|
| 1  | 9533782     | 9 point road apapa lagos | 993033 |
| 2  | 1416028     | 33 Adegoke Street | 993033 |

#### Apartments Data (Current State)
| id | property_id | apartment_type | user_id | apartment_id |
|----|-------------|----------------|---------|--------------|
| 1  | 1416028     | Store Unit     | 993033  | 7589367      |
| 2  | 1416028     | 2-Bedroom      | 993033  | 4991441      |
| 3  | 1416028     | Store Unit     | 993033  | 7589367      |
| 4  | 1416028     | 2-Bedroom      | 993033  | 4991441      |
| 5  | 9533782     | 1-Bedroom      | 993033  | 7282463      |

### Issue Identification

#### 1. ✅ Apartments Already Reference Correct Field
- **Status**: Apartments correctly reference `properties.property_id` (business identifiers)
- **Count**: 5 apartments all using correct property_id values
- **Data Integrity**: All property_id values match existing properties.property_id

#### 2. ❌ Missing Foreign Key Constraint
- **Problem**: No foreign key constraint found on apartments.property_id
- **Impact**: Database cannot enforce referential integrity
- **Required**: Create foreign key constraint apartments.property_id -> properties.property_id

### Required Corrections

#### 1. ✅ Apartment Records Already Correct
- All apartments already reference correct property_id values
- No data updates needed

#### 2. Create Missing Foreign Key Constraint
```sql
-- Create: apartments_property_id_foreign (references properties.property_id)
-- Ensure constraint enforces referential integrity
```

### Migration Strategy

#### Phase 1: ✅ Data Already Correct
- All apartments already reference properties.property_id correctly
- No data correction needed

#### Phase 2: Create Foreign Key Constraint
1. Verify no existing foreign key constraint exists
2. Create new constraint referencing properties.property_id
3. Validate constraint creation successful

#### Phase 3: Verification
1. ✅ Confirm all apartments have valid property_id references (DONE)
2. Test apartment creation with new constraint
3. Verify apartment display on property pages

### Validation Queries

#### Check Current State
```sql
-- Show apartments with their property references
SELECT 
    a.id as apartment_id,
    a.property_id as current_property_id,
    p.id as properties_table_id,
    p.property_id as correct_property_id,
    p.address
FROM apartments a
LEFT JOIN properties p ON a.property_id = p.id;
```

#### Expected Result After Fix
```sql
-- After migration, this should show proper references
SELECT 
    a.id as apartment_id,
    a.property_id,
    p.property_id,
    p.address
FROM apartments a
LEFT JOIN properties p ON a.property_id = p.property_id;
```

## Conclusion

**Task 1 Status**: ✅ COMPLETE

- **Foreign key issues identified**: Missing foreign key constraint
- **Apartments with invalid references**: 0 apartments (all reference correct property_id)
- **Property_id validity**: ✅ All apartments have valid properties.property_id references
- **Migration required**: Create missing foreign key constraint only
- **No orphaned records**: ✅ All apartments have corresponding properties
- **Data integrity**: ✅ All apartment.property_id values match existing properties.property_id

**Next Steps**: Proceed to Task 2.1 - Create migration to fix foreign key constraint

---
*Analysis completed on: December 17, 2025*
*Requirements validated: 1.4, 2.2*