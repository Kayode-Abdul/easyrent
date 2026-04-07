# Design Document

## Overview

This design addresses the critical foreign key misconfiguration where `apartments.property_id` incorrectly references `properties.id` instead of `properties.property_id`. The solution involves creating a database migration to fix the foreign key constraint, updating application code to use correct references, and ensuring apartment display functionality works properly.

## Architecture

The fix involves three main components:

1. **Database Layer**: Migration to correct foreign key constraints
2. **Model Layer**: Update Eloquent relationships and queries
3. **Controller/View Layer**: Ensure proper property-apartment associations in UI

## Components and Interfaces

### Migration Component
- **Purpose**: Fix foreign key constraint to reference correct column
- **Responsibilities**: 
  - Drop existing incorrect foreign key
  - Create new foreign key referencing properties.property_id
  - Handle data migration for existing records
  - Validate constraint integrity

### Model Updates
- **Apartment Model**: Update foreign key relationships
- **Property Model**: Ensure correct inverse relationships
- **Query Scopes**: Update apartment retrieval logic

### Controller Updates
- **PropertyController**: Fix apartment display queries
- **ApartmentController**: Ensure correct property validation
- **Form Handling**: Update property dropdown population

## Data Models

### Current Problematic Structure
```sql
-- INCORRECT (current state)
apartments.property_id -> properties.id
```

### Target Corrected Structure  
```sql
-- CORRECT (target state)
apartments.property_id -> properties.property_id
```

### Migration Strategy
1. Identify existing apartments with invalid property_id references
2. Drop current foreign key constraint
3. Update apartment records to use correct property_id values
4. Create new foreign key constraint
5. Validate all relationships are intact

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

Property 1: Foreign key constraint enforcement
*For any* apartment creation attempt, the database constraint should only allow apartments with property_id values that exist in properties.property_id
**Validates: Requirements 1.3, 2.5**

Property 2: Successful apartment creation with valid property references
*For any* valid property_id that exists in properties.property_id, creating an apartment with that property_id should succeed and return the created apartment details
**Validates: Requirements 2.1, 2.3**

Property 3: Property reference validation consistency
*For any* property_id value, the validation logic should consistently confirm whether it exists in properties.property_id
**Validates: Requirements 2.2, 4.5**

Property 4: Invalid property reference rejection
*For any* property_id value that does not exist in properties.property_id, apartment creation should be rejected with an appropriate error message
**Validates: Requirements 2.4**

Property 5: Apartment display completeness
*For any* property with associated apartments, the property page should display all apartments that reference that property's property_id
**Validates: Requirements 3.1, 3.3**

Property 6: Real-time apartment display updates
*For any* successful apartment creation, the apartment should immediately appear on the corresponding property page
**Validates: Requirements 3.2**

Property 7: Apartment display state consistency
*For any* apartment update or deletion operation, the property page should reflect the current state of apartments in the database
**Validates: Requirements 3.5**

Property 8: Query join correctness
*For any* database query that joins apartments and properties, the join condition should use apartments.property_id = properties.property_id
**Validates: Requirements 4.2, 4.4**

## Error Handling

### Migration Error Scenarios
- **Orphaned Data**: Handle apartments with property_id values that don't exist in properties.property_id
- **Constraint Conflicts**: Manage cases where existing data violates the new foreign key constraint
- **Migration Rollback**: Provide rollback capability if migration fails

### Runtime Error Scenarios
- **Invalid Property References**: Clear error messages when apartment creation fails due to invalid property_id
- **Database Connection Issues**: Graceful handling of database connectivity problems
- **Concurrent Modifications**: Handle race conditions during apartment creation and property updates

## Testing Strategy

### Unit Testing
- Test migration logic with various data scenarios
- Test model relationships and validation logic
- Test controller methods for apartment creation and display
- Test form population and validation

### Property-Based Testing
The system will use **PHPUnit with Pest** for property-based testing, configured to run a minimum of 100 iterations per property test.

Each property-based test will be tagged with comments referencing the specific correctness property from this design document using the format: **Feature: apartment-creation-foreign-key-fix, Property {number}: {property_text}**

### Integration Testing
- Test complete apartment creation workflow from form submission to database storage
- Test property page display after apartment creation
- Test migration execution on sample datasets
- Test error handling across the entire application stack

### Database Testing
- Verify foreign key constraints are properly configured
- Test referential integrity enforcement
- Validate migration success with various data states
- Test rollback procedures