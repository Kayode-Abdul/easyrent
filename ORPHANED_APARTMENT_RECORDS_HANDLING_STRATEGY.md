# Orphaned Apartment Records Handling Strategy

## Overview

This document outlines the comprehensive strategy for handling orphaned apartment records during the foreign key migration from `apartments.property_id → properties.id` to `apartments.property_id → properties.property_id`.

## Problem Definition

The apartment creation system has been failing due to incorrect foreign key configuration where:
- **Current (Incorrect)**: `apartments.property_id` references `properties.id`
- **Target (Correct)**: `apartments.property_id` should reference `properties.property_id`

## Types of Orphaned Records

### 1. Wrong Field Reference Apartments
- **Description**: Apartments that currently reference `properties.id` but should reference `properties.property_id`
- **Cause**: Historical foreign key misconfiguration
- **Impact**: These apartments exist but have incorrect relationships
- **Resolution**: Update `apartments.property_id` to use the correct `properties.property_id` value

### 2. Truly Orphaned Apartments
- **Description**: Apartments with `property_id` values that don't exist in either `properties.id` or `properties.property_id`
- **Cause**: Data corruption, manual deletions, or import errors
- **Impact**: These apartments have no valid property relationship
- **Resolution**: Backup and delete these records to maintain data integrity

## Cleanup Strategy Implementation

### Phase 1: Identification and Analysis
```sql
-- Identify apartments referencing wrong field
SELECT a.id, a.property_id, a.apartment_id, p.id as prop_table_id, p.property_id as correct_property_id
FROM apartments a
INNER JOIN properties p ON a.property_id = p.id
WHERE p.property_id IS NOT NULL;

-- Identify truly orphaned apartments
SELECT a.id, a.property_id, a.apartment_id
FROM apartments a
WHERE NOT EXISTS (SELECT 1 FROM properties p WHERE p.id = a.property_id)
AND NOT EXISTS (SELECT 1 FROM properties p WHERE p.property_id = a.property_id);
```

### Phase 2: Audit Logging
All identified issues are logged to the `audit_logs` table with:
- **table_name**: 'apartments'
- **record_id**: Apartment ID
- **action**: 'orphaned_record_identified'
- **old_values**: Current property_id and issue type
- **new_values**: Correct property_id (for wrong field references)

### Phase 3: Data Correction
1. **Fix Wrong Field References**:
   ```sql
   UPDATE apartments a
   INNER JOIN properties p ON a.property_id = p.id
   SET a.property_id = p.property_id
   WHERE p.property_id IS NOT NULL;
   ```

2. **Handle Truly Orphaned Records**:
   - Create backup table: `orphaned_apartments_backup`
   - Backup orphaned apartment data
   - Delete orphaned apartments from main table
   - Log all deletions for audit trail

### Phase 4: Backup Strategy
The `orphaned_apartments_backup` table stores:
- All original apartment data
- Backup timestamp
- Original creation timestamp
- Indexed by property_id and apartment_id for easy recovery

## Audit Trail

### Log Types
1. **orphaned_record_identified**: Initial identification of problematic records
2. **property_id_corrected**: Successful correction of wrong field references
3. **orphaned_record_deleted**: Deletion of truly orphaned records

### Log Structure
```json
{
  "table_name": "apartments",
  "record_id": 123,
  "action": "property_id_corrected",
  "old_values": {"property_id": "1"},
  "new_values": {"property_id": "9533782"},
  "user_id": null,
  "created_at": "2025-12-16 12:00:00"
}
```

## Recovery Procedures

### Recovering Deleted Orphaned Apartments
If orphaned apartments need to be recovered:

1. **Check Backup Table**:
   ```sql
   SELECT * FROM orphaned_apartments_backup WHERE property_id = 'target_property_id';
   ```

2. **Restore with Valid Property Reference**:
   ```sql
   INSERT INTO apartments (property_id, apartment_id, apartment_type, ...)
   SELECT 'valid_property_id', apartment_id, apartment_type, ...
   FROM orphaned_apartments_backup
   WHERE id = target_apartment_id;
   ```

### Rollback Procedures
The migration includes rollback functionality that:
1. Drops the new foreign key constraint
2. Reverts property_id values back to properties.id references
3. Restores original foreign key constraint

## Validation and Testing

### Pre-Migration Testing
Run `test_orphaned_apartment_handling.php` to:
- Identify all orphaned records
- Simulate cleanup actions
- Verify audit logging capability
- Check foreign key constraints

### Post-Migration Validation
```sql
-- Verify no orphaned records remain
SELECT COUNT(*) as orphaned_count
FROM apartments a
LEFT JOIN properties p ON a.property_id = p.property_id
WHERE p.property_id IS NULL;

-- Should return 0
```

## Risk Mitigation

### Data Loss Prevention
- Complete backup of orphaned apartments before deletion
- Comprehensive audit logging of all changes
- Rollback capability for the entire migration
- Test script validation before migration execution

### Performance Considerations
- Batch processing for large datasets
- Indexed backup table for fast recovery
- Minimal downtime during migration
- Progress reporting during execution

## Monitoring and Alerts

### Success Indicators
- Zero orphaned records after migration
- All apartments have valid property_id references
- Foreign key constraint successfully created
- Complete audit trail in logs

### Failure Indicators
- Migration throws constraint violation errors
- Orphaned records remain after cleanup
- Audit logging failures
- Backup table creation failures

## Maintenance

### Regular Checks
Implement periodic checks to prevent future orphaned records:
```sql
-- Monthly orphaned record check
SELECT COUNT(*) as potential_orphans
FROM apartments a
LEFT JOIN properties p ON a.property_id = p.property_id
WHERE p.property_id IS NULL;
```

### Prevention Measures
- Proper foreign key constraints (implemented by this migration)
- Application-level validation before apartment creation
- Regular data integrity checks
- Proper cascade deletion rules

## Implementation Status

- ✅ Orphaned record identification logic
- ✅ Comprehensive audit logging
- ✅ Backup and recovery procedures
- ✅ Data correction algorithms
- ✅ Validation and testing scripts
- ✅ Documentation and procedures

## Files Modified/Created

1. **Migration**: `database/migrations/2025_12_16_120000_fix_apartments_property_id_foreign_key.php`
   - Enhanced with comprehensive orphaned record handling

2. **Test Script**: `test_orphaned_apartment_handling.php`
   - Pre-migration validation and testing

3. **Documentation**: `ORPHANED_APARTMENT_RECORDS_HANDLING_STRATEGY.md`
   - Complete strategy documentation

## Requirements Validation

This implementation satisfies all requirements from task 2.3:

- ✅ **Identify apartments with property_id values not in properties.property_id**
  - Comprehensive identification of both wrong field references and truly orphaned records

- ✅ **Create cleanup strategy for orphaned records**
  - Multi-phase cleanup with data correction and backup procedures

- ✅ **Log all data changes for audit purposes**
  - Complete audit trail with detailed logging of all operations

- ✅ **Requirements: 1.4**
  - Addresses requirement 1.4 for handling existing data conflicts during migration