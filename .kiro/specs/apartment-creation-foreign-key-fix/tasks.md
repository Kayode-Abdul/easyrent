# Implementation Plan

- [x] 1. Analyze current database schema and identify foreign key issues
  - Examine current apartments table foreign key constraints
  - Identify all apartments with invalid property_id references
  - Document current property_id values and their validity
  - _Requirements: 1.4, 2.2_

- [x] 2. Create database migration to fix foreign key constraint
  - [x] 2.1 Create migration file to correct apartments.property_id foreign key
    - Drop existing foreign key constraint on apartments.property_id
    - Update apartment records to use correct property_id values from properties.property_id
    - Create new foreign key constraint referencing properties.property_id
    - Add validation to ensure data integrity
    - _Requirements: 1.1, 1.2, 1.3_

  - [ ]* 2.2 Write property test for foreign key constraint enforcement
    - **Property 1: Foreign key constraint enforcement**
    - **Validates: Requirements 1.3, 2.5**

  - [x] 2.3 Handle orphaned apartment records during migration
    - Identify apartments with property_id values not in properties.property_id
    - Create cleanup strategy for orphaned records
    - Log all data changes for audit purposes
    - _Requirements: 1.4_

- [ ] 3. Update Apartment model relationships
  - [ ] 3.1 Fix Apartment model foreign key definition
    - Update belongsTo relationship to reference correct property field
    - Ensure foreign key points to properties.property_id
    - _Requirements: 4.1_

  - [ ]* 3.2 Write property test for apartment creation with valid property references
    - **Property 2: Successful apartment creation with valid property references**
    - **Validates: Requirements 2.1, 2.3**

  - [ ] 3.3 Update Property model inverse relationship
    - Fix hasMany relationship for apartments
    - Ensure correct foreign key specification
    - _Requirements: 4.1_

- [ ] 4. Fix apartment creation validation and controllers
  - [ ] 4.1 Update ApartmentController validation logic
    - Modify property_id validation to check properties.property_id
    - Update error messages for invalid property references
    - _Requirements: 2.2, 2.4_

  - [ ]* 4.2 Write property test for property reference validation
    - **Property 3: Property reference validation consistency**
    - **Validates: Requirements 2.2, 4.5**

  - [ ] 4.3 Fix apartment creation form property dropdown
    - Update form to use properties.property_id as value field
    - Ensure dropdown shows correct property options
    - _Requirements: 4.3_

  - [ ]* 4.4 Write property test for invalid property reference rejection
    - **Property 4: Invalid property reference rejection**
    - **Validates: Requirements 2.4**

- [ ] 5. Fix apartment display on property pages
  - [ ] 5.1 Update PropertyController apartment queries
    - Fix apartment retrieval to use correct foreign key relationship
    - Ensure apartments are properly joined with properties
    - _Requirements: 3.1, 3.3, 4.2_

  - [ ]* 5.2 Write property test for apartment display completeness
    - **Property 5: Apartment display completeness**
    - **Validates: Requirements 3.1, 3.3**

  - [ ] 5.3 Update property show view apartment display
    - Ensure apartments are displayed correctly on property pages
    - Handle cases where no apartments exist
    - _Requirements: 3.1, 3.4_

  - [ ]* 5.4 Write property test for real-time apartment display updates
    - **Property 6: Real-time apartment display updates**
    - **Validates: Requirements 3.2**

- [ ] 6. Update all apartment-property queries throughout the application
  - [ ] 6.1 Audit and fix all database queries involving apartments and properties
    - Search for apartment-property joins in controllers and models
    - Update join conditions to use apartments.property_id = properties.property_id
    - _Requirements: 4.2, 4.4_

  - [ ]* 6.2 Write property test for query join correctness
    - **Property 8: Query join correctness**
    - **Validates: Requirements 4.2, 4.4**

  - [ ] 6.3 Update apartment display and management views
    - Fix any hardcoded references to properties.id
    - Ensure all apartment-property relationships use correct fields
    - _Requirements: 3.5, 4.4_

  - [ ]* 6.4 Write property test for apartment display state consistency
    - **Property 7: Apartment display state consistency**
    - **Validates: Requirements 3.5**

- [ ] 7. Checkpoint - Ensure all tests pass and foreign key relationships work correctly
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 8. Test and validate the complete fix
  - [ ] 8.1 Run migration on test database
    - Execute migration and verify foreign key constraint is correct
    - Test apartment creation with valid and invalid property_id values
    - _Requirements: 1.1, 1.2, 1.3_

  - [ ]* 8.2 Write integration tests for complete apartment workflow
    - Test apartment creation, storage, and display end-to-end
    - Verify foreign key constraint enforcement
    - _Requirements: 2.1, 2.3, 3.1, 3.2_

  - [ ] 8.3 Validate apartment display on property pages
    - Test that created apartments appear on property pages
    - Verify apartment updates and deletions are reflected
    - _Requirements: 3.1, 3.2, 3.5_

- [ ] 9. Final Checkpoint - Complete system validation
  - Ensure all tests pass, ask the user if questions arise.