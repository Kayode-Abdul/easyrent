# Proforma Payment Calculation Fix - Implementation Plan

## Overview

This implementation plan converts the proforma payment calculation fix design into a series of incremental coding tasks. Each task builds upon previous work, ensuring a cohesive system that correctly handles apartment pricing calculations across proforma generation and EasyRent invitations.

## Implementation Tasks

- [x] 1. Create centralized payment calculation service
  - Implement PaymentCalculationServiceInterface and concrete service class
  - Add calculation methods for different pricing types (total vs monthly)
  - Implement input validation and error handling
  - Add comprehensive logging for audit purposes
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 4.1, 4.4_

- [x]* 1.1 Write property test for pricing calculation consistency
  - **Property 1: Total pricing calculation consistency**
  - **Validates: Requirements 1.1, 1.3**

- [x]* 1.2 Write property test for monthly pricing accuracy
  - **Property 2: Monthly pricing calculation accuracy**
  - **Validates: Requirements 1.4**

- [x]* 1.3 Write property test for calculation determinism
  - **Property 3: Calculation method consistency**
  - **Validates: Requirements 1.2, 1.5**

- [x] 2. Update apartment model with pricing configuration
  - Add pricing_type and price_configuration fields to Apartment model
  - Implement getPricingType() and getCalculatedPaymentTotal() methods
  - Add validation for pricing configuration data
  - Create database migration for new fields
  - _Requirements: 3.1, 4.2_

- [x]* 2.1 Write property test for pricing configuration validation
  - **Property 6: Pricing configuration validation**
  - **Validates: Requirements 3.1**

- [x] 3. Create database migration for pricing fields
  - Add pricing_type column to apartments table with default 'total'
  - Add price_configuration JSON column for complex pricing rules
  - Add calculation_method and calculation_log to proforma_receipts table
  - Create appropriate indexes for performance
  - _Requirements: 3.3, 4.2_

- [x] 4. Update ProformaController to use calculation service
  - Replace direct price calculations with PaymentCalculationService calls
  - Update all proforma generation methods to use centralized logic
  - Add calculation logging to proforma creation process
  - Ensure backward compatibility with existing proformas
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 3.2, 3.5_

- [ ]* 4.1 Write property test for calculation audit logging
  - **Property 7: Calculation audit logging**
  - **Validates: Requirements 3.2, 3.5**

- [x] 5. Update ApartmentInvitationController for consistent calculations
  - Integrate PaymentCalculationService into invitation payment previews
  - Ensure EasyRent invitation totals match proforma calculations
  - Update invitation display to show pricing structure information
  - Add validation before displaying payment amounts
  - _Requirements: 2.1, 2.2, 2.3, 2.5_

- [ ]* 5.1 Write property test for invitation calculation consistency
  - **Property 4: EasyRent invitation calculation consistency**
  - **Validates: Requirements 2.1, 2.2, 2.3**

- [ ]* 5.2 Write property test for payment preview accuracy
  - **Property 5: Payment preview accuracy**
  - **Validates: Requirements 2.5**

- [x] 6. Update PaymentController for calculation consistency
  - Ensure payment processing uses the same calculation service
  - Validate that final payment amounts match previewed amounts
  - Add error handling for calculation discrepancies
  - Update payment confirmation to include calculation details
  - _Requirements: 2.5, 3.4, 4.1_

- [ ]* 6.1 Write property test for error handling completeness
  - **Property 9: Error handling completeness**
  - **Validates: Requirements 3.4**

- [x] 7. Implement comprehensive error handling
  - Add validation for negative values and invalid pricing types
  - Implement overflow protection and precision handling
  - Create fallback logic for ambiguous configurations
  - Add comprehensive error logging and monitoring
  - _Requirements: 3.4, 4.4_

- [x] 8. Create service provider and dependency injection setup
  - Register PaymentCalculationService in service container
  - Configure interface binding for dependency injection
  - Set up service configuration and environment variables
  - Ensure proper service lifecycle management
  - _Requirements: 4.1, 4.3_

- [ ]* 8.1 Write property test for service centralization
  - **Property 10: Service centralization**
  - **Validates: Requirements 4.1**

- [x] 9. Update ProfomaReceipt model for calculation tracking
  - Add calculation_method and calculation_log fields
  - Implement methods for storing and retrieving calculation details
  - Add relationships for audit trail functionality
  - Create accessors for calculation breakdown display
  - _Requirements: 3.2, 3.5_

- [x] 10. Implement data migration for existing records
  - Create migration script to set default pricing_type for existing apartments
  - Analyze existing proforma calculations for consistency
  - Preserve existing calculation results without modification
  - Add data validation and integrity checks
  - _Requirements: 3.3, 4.2_

- [ ]* 10.1 Write property test for configuration change isolation
  - **Property 8: Configuration change isolation**
  - **Validates: Requirements 3.3, 4.2**

- [x] 11. Add input validation and security measures
  - Implement comprehensive input sanitization
  - Add rate limiting for calculation API endpoints
  - Validate pricing configuration JSON structure
  - Implement access control for pricing configuration changes
  - _Requirements: 3.1, 3.4, 4.4_

- [x]* 11.1 Write property test for input validation consistency
  - **Property 11: Input validation consistency**
  - **Validates: Requirements 4.4**

- [x] 12. Create admin interface for pricing configuration
  - Build admin views for managing apartment pricing types
  - Add forms for configuring complex pricing rules
  - Implement validation and preview functionality
  - Add audit trail for configuration changes
  - _Requirements: 3.1, 3.5_

- [x] 13. Implement monitoring and observability
  - Add metrics for calculation performance and accuracy
  - Create alerts for calculation errors or inconsistencies
  - Build dashboard for monitoring pricing configuration usage
  - Implement comprehensive audit logging
  - _Requirements: 3.2, 3.5_

- [x] 14. Update API endpoints for mobile integration
  - Ensure mobile API uses centralized calculation service
  - Add calculation details to API responses
  - Implement proper error handling for mobile clients
  - Update API documentation with pricing structure information
  - _Requirements: 2.1, 2.3, 4.1_

- [x] 15. Create comprehensive test suite
  - Implement unit tests for PaymentCalculationService
  - Add integration tests for complete calculation flows
  - Create performance tests for calculation service
  - Add end-to-end tests for proforma and invitation consistency
  - _Requirements: All requirements_

- [ ]* 15.1 Write integration tests for calculation flows
  - Test complete proforma generation with corrected calculations
  - Test EasyRent invitation preview accuracy
  - Test cross-system consistency validation
  - _Requirements: All requirements_

- [x] 16. Update frontend views and templates
  - Modify proforma templates to show calculation breakdown
  - Update invitation views to display pricing structure
  - Add error handling and user feedback for calculation issues
  - Ensure mobile-responsive design for new features
  - _Requirements: 2.4, 3.4_

- [x] 17. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 18. Performance optimization and caching
  - Implement caching for frequently used calculations
  - Optimize database queries for pricing configuration
  - Add performance monitoring and metrics collection
  - Tune calculation service for high-volume usage
  - _Requirements: Performance and scalability_

- [ ]* 18.1 Write performance tests for calculation service
  - Test calculation service performance with large datasets
  - Test concurrent calculation requests
  - Verify memory usage with complex configurations
  - _Requirements: Performance and scalability_

- [x] 19. Documentation and deployment preparation
  - Create technical documentation for calculation service
  - Write user guides for pricing configuration
  - Prepare deployment scripts and configuration
  - Create rollback procedures for emergency situations
  - _Requirements: All requirements_

- [x] 20. Complete remaining property-based tests
  - Implement missing property tests for EasyRent invitation consistency
  - Add property tests for payment preview accuracy
  - Complete property tests for error handling and audit logging
  - Ensure all 11 correctness properties from design are tested
  - _Requirements: 2.1, 2.2, 2.3, 2.5, 3.2, 3.4, 3.5, 4.1_

- [x] 21. Final integration testing and validation
  - Perform end-to-end testing of complete payment flows
  - Validate calculation accuracy across all scenarios
  - Test error handling and recovery mechanisms
  - Verify audit logging and monitoring functionality
  - _Requirements: All requirements_

- [ ] 22. Final Checkpoint - Complete system verification
  - Ensure all tests pass, ask the user if questions arise.