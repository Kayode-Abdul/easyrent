# Multi-Channel Benefactor Invitations - Implementation Plan

- [ ] 1. Set up messaging service infrastructure and configuration
  - Create messaging service interface and base classes
  - Set up configuration files for SMS and WhatsApp providers
  - Create service provider registration in Laravel container
  - _Requirements: 6.1, 6.2, 6.3_

- [ ] 1.1 Create messaging service interface
  - Write MessagingServiceInterface with send, validate, and format methods
  - Define DeliveryResult and DeliveryStatus value objects
  - Create base MessagingService abstract class with common functionality
  - _Requirements: 6.1, 6.2_

- [ ]* 1.2 Write property test for messaging service interface
  - **Property 9: Service provider failover**
  - **Validates: Requirements 6.4, 6.5**

- [ ] 1.3 Create messaging configuration system
  - Create config/messaging.php with SMS and WhatsApp provider settings
  - Add environment variables for Twilio, Termii, and WhatsApp Business API
  - Implement configuration validation and connectivity testing
  - _Requirements: 6.1, 6.2, 6.3_

- [ ]* 1.4 Write property test for configuration validation
  - **Property 1: Multi-channel form validation**
  - **Validates: Requirements 1.2, 1.3, 1.5, 6.3**

- [ ] 2. Implement SMS and WhatsApp messaging services
  - Create concrete SMS service implementations for multiple providers
  - Implement WhatsApp Business API integration
  - Add message formatting and validation logic
  - _Requirements: 2.1, 2.2, 2.3, 3.1, 3.2, 3.3, 3.4_

- [ ] 2.1 Implement SMS service classes
  - Create TwilioSmsService with send and validation methods
  - Create TermiiSmsService with API integration
  - Implement SMS message formatting with character limit handling
  - Add phone number validation for SMS capability
  - _Requirements: 3.1, 3.2, 3.3, 3.4_

- [ ]* 2.2 Write property test for SMS message formatting
  - **Property 4: Message length constraints**
  - **Validates: Requirements 3.1, 3.2**

- [ ]* 2.3 Write property test for SMS content completeness
  - **Property 2: Message content completeness**
  - **Validates: Requirements 2.2, 2.3, 3.3, 8.5**

- [ ] 2.4 Implement WhatsApp service class
  - Create WhatsAppService using Twilio WhatsApp Business API
  - Implement WhatsApp message formatting with markdown support
  - Add WhatsApp number validation and capability checking
  - Create rich message templates with action buttons
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

- [ ]* 2.5 Write property test for WhatsApp message formatting
  - **Property 2: Message content completeness**
  - **Validates: Requirements 2.2, 2.3, 3.3, 8.5**

- [ ] 3. Enhance PaymentInvitation model for multi-channel support
  - Add database migration for new multi-channel fields
  - Extend PaymentInvitation model with delivery tracking methods
  - Implement delivery status management and history tracking
  - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5_

- [ ] 3.1 Create database migration for multi-channel fields
  - Add delivery_channels, phone_number, delivery_attempts columns
  - Add delivery_status, custom_messages, created_from_proforma_id columns
  - Create indexes for efficient querying of delivery status
  - _Requirements: 4.1, 4.4_

- [ ] 3.2 Extend PaymentInvitation model methods
  - Add getDeliveryStatus(), markDeliveryAttempt(), hasSuccessfulDelivery() methods
  - Implement getFailedChannels() and delivery history tracking
  - Add JSON casting for delivery_channels, delivery_attempts, delivery_status
  - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5_

- [ ]* 3.3 Write property test for delivery tracking
  - **Property 5: Delivery tracking consistency**
  - **Validates: Requirements 4.1, 4.4, 4.5**

- [ ] 4. Create multi-channel invitation controller
  - Implement controller for handling multi-channel invitation requests
  - Add validation for multi-channel input data
  - Create delivery status API endpoints for real-time updates
  - _Requirements: 1.1, 1.4, 4.1, 10.1_

- [ ] 4.1 Create MultiChannelInvitationController
  - Implement sendProformaInvitation method with multi-channel support
  - Add input validation for email, phone number, and channel selection
  - Create getDeliveryStatus endpoint for real-time status updates
  - Implement resendInvitation method for retry functionality
  - _Requirements: 1.1, 1.4, 4.1, 10.1_

- [ ]* 4.2 Write property test for invitation controller
  - **Property 3: Cross-channel link consistency**
  - **Validates: Requirements 2.4, 7.3, 10.2**

- [ ] 4.3 Add routes for multi-channel invitation endpoints
  - Create POST route for sending multi-channel invitations
  - Add GET route for delivery status checking
  - Create POST route for resending invitations
  - _Requirements: 1.1, 4.1, 10.1_

- [ ] 5. Implement delivery coordination and fallback system
  - Create delivery orchestration service to coordinate multi-channel sending
  - Implement automatic fallback mechanism for failed deliveries
  - Add queue-based retry system with exponential backoff
  - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5_

- [ ] 5.1 Create delivery orchestration service
  - Implement DeliveryOrchestrator to coordinate multi-channel sending
  - Add logic to send messages simultaneously across selected channels
  - Create delivery result aggregation and status tracking
  - _Requirements: 1.4, 4.1, 5.1_

- [ ]* 5.2 Write property test for delivery orchestration
  - **Property 6: Automatic fallback behavior**
  - **Validates: Requirements 5.1, 5.2, 5.3**

- [ ] 5.3 Implement fallback mechanism
  - Create FallbackService to handle failed delivery scenarios
  - Implement cascading fallback: WhatsApp → SMS → Email
  - Add configuration for fallback order and retry limits
  - Create notification system for successful fallback deliveries
  - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5_

- [ ]* 5.4 Write property test for fallback system
  - **Property 6: Automatic fallback behavior**
  - **Validates: Requirements 5.1, 5.2, 5.3**

- [ ] 6. Enhance proforma template with multi-channel interface
  - Modify the existing "Invite Someone to Pay" modal for multi-channel selection
  - Add real-time delivery status display in proforma interface
  - Implement resend functionality with alternative channel options
  - _Requirements: 1.1, 1.2, 1.3, 1.5, 4.1, 10.1_

- [ ] 6.1 Update proforma template modal
  - Enhance SweetAlert modal to include channel selection checkboxes
  - Add conditional input fields for email and phone number
  - Implement real-time form validation for phone numbers and emails
  - Add message customization options for each channel
  - _Requirements: 1.1, 1.2, 1.3, 1.5_

- [ ] 6.2 Add delivery status display to proforma
  - Create delivery status section in proforma template
  - Implement real-time status updates using AJAX polling
  - Add visual indicators for sent, delivered, and opened status
  - Display timestamps and failure reasons for each channel
  - _Requirements: 4.1, 4.2, 4.4, 4.5_

- [ ] 6.3 Implement resend functionality in proforma interface
  - Add resend buttons for failed deliveries
  - Create alternative channel selection for resending
  - Implement manual sharing options (QR codes, shareable links)
  - Add prevention logic for already-responded invitations
  - _Requirements: 10.1, 10.3, 10.4, 10.5_

- [ ]* 6.4 Write property test for duplicate prevention
  - **Property 7: Duplicate prevention**
  - **Validates: Requirements 10.4**

- [ ] 7. Implement cost tracking and monitoring system
  - Add cost tracking for SMS and WhatsApp messages
  - Create admin dashboard for monitoring usage and costs
  - Implement budget controls and cost optimization features
  - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5_

- [ ] 7.1 Create cost tracking system
  - Add cost fields to delivery attempt records
  - Implement cost calculation for different providers and message types
  - Create CostTracker service to record and aggregate costs
  - _Requirements: 9.1, 9.3_

- [ ]* 7.2 Write property test for cost tracking
  - **Property 8: Cost tracking accuracy**
  - **Validates: Requirements 9.1, 9.3**

- [ ] 7.3 Implement usage monitoring and alerts
  - Create monitoring service to track usage approaching limits
  - Add alert system for administrators when budgets are exceeded
  - Implement automatic channel disabling for cost control
  - Create cost optimization recommendations
  - _Requirements: 9.2, 9.4, 9.5_

- [ ] 8. Add message customization and template system
  - Create customizable message templates for each channel
  - Implement message preview functionality
  - Add personalization with tenant and property information
  - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5_

- [ ] 8.1 Create message template system
  - Design template structure for Email, WhatsApp, and SMS
  - Implement template personalization with proforma data
  - Add character count validation for each channel type
  - Create default templates with intelligent formatting
  - _Requirements: 8.1, 8.2, 8.3, 8.5_

- [ ]* 8.2 Write property test for message personalization
  - **Property 10: Message personalization consistency**
  - **Validates: Requirements 8.1, 8.3, 8.4**

- [ ] 8.3 Implement message preview functionality
  - Create preview service to show formatted messages for each channel
  - Add real-time preview updates as user customizes messages
  - Implement character count display and validation feedback
  - _Requirements: 8.2, 8.4_

- [ ] 9. Create comprehensive testing suite
  - Write unit tests for all messaging services and controllers
  - Implement integration tests for end-to-end invitation flow
  - Add property-based tests for core correctness properties
  - _Requirements: All requirements validation_

- [ ]* 9.1 Write unit tests for messaging services
  - Test SMS service message formatting and validation
  - Test WhatsApp service API integration and formatting
  - Test email service enhancements
  - _Requirements: 2.1, 2.2, 2.3, 3.1, 3.2, 3.3_

- [ ]* 9.2 Write integration tests for multi-channel flow
  - Test complete proforma invitation flow with multiple channels
  - Test fallback mechanism activation and recovery
  - Test delivery status tracking and updates
  - _Requirements: 1.1, 4.1, 5.1, 10.1_

- [ ]* 9.3 Write property-based tests for correctness properties
  - Implement all 10 correctness properties as property-based tests
  - Configure tests to run minimum 100 iterations each
  - Add proper test tagging with property references
  - _Requirements: All requirements validation_

- [ ] 10. Checkpoint - Ensure all tests pass and system integration works
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 11. Create documentation and deployment guide
  - Write setup guide for SMS and WhatsApp provider configuration
  - Create user guide for multi-channel invitation features
  - Document cost management and monitoring procedures
  - _Requirements: 6.1, 6.2, 9.2_

- [ ] 11.1 Create provider setup documentation
  - Document Twilio SMS and WhatsApp Business API setup
  - Create Termii SMS provider configuration guide
  - Add troubleshooting guide for common provider issues
  - _Requirements: 6.1, 6.2_

- [ ] 11.2 Create user and admin documentation
  - Write user guide for multi-channel invitation features
  - Create admin guide for cost monitoring and budget management
  - Document fallback configuration and monitoring procedures
  - _Requirements: 9.2, 9.4_

- [ ] 12. Final Checkpoint - Complete system verification
  - Ensure all tests pass, ask the user if questions arise.