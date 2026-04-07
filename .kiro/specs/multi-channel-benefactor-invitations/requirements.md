# Multi-Channel Benefactor Invitations - Requirements Document

## Introduction

This feature enhances the EasyRent proforma "Invite Someone to Pay" functionality by allowing tenants to send benefactor payment invitations through multiple communication channels: Email, WhatsApp, and SMS. Currently, tenants can only send email invitations when they click the "Invite Someone to Pay" button on a proforma. This enhancement will provide multiple delivery options in the same interface, improving accessibility and user experience by letting tenants choose the most convenient method to reach their benefactors.

## Glossary

- **Benefactor**: A person who pays rent on behalf of a tenant (employer, family member, sponsor, etc.)
- **Tenant**: The person requesting payment assistance from a benefactor
- **Payment Invitation**: A secure link sent to benefactors to initiate payment process
- **Multi-Channel Delivery**: The ability to send invitations via Email, WhatsApp, and SMS
- **WhatsApp Business API**: Official WhatsApp API for business communications
- **SMS Gateway**: Third-party service for sending SMS messages (e.g., Twilio, Termii)
- **Delivery Status**: Real-time tracking of message delivery across all channels
- **Fallback Mechanism**: Automatic retry using alternative channels if primary delivery fails

## Requirements

### Requirement 1

**User Story:** As a tenant viewing a proforma, I want to choose how to send payment invitations when I click "Invite Someone to Pay", so that I can use the most convenient communication method for my benefactor.

#### Acceptance Criteria

1. WHEN a tenant clicks "Invite Someone to Pay" on a proforma, THE system SHALL display a modal with options for Email, WhatsApp, and SMS delivery
2. WHEN a tenant selects WhatsApp delivery, THE system SHALL show a phone number input field and validate the format with country code
3. WHEN a tenant selects SMS delivery, THE system SHALL show a phone number input field and validate the format for SMS capability
4. WHEN a tenant selects multiple channels, THE system SHALL allow entering both email and phone number for simultaneous delivery
5. WHERE a tenant provides both email and phone number, THE system SHALL show checkboxes to select any combination of Email, WhatsApp, and SMS delivery methods

### Requirement 2

**User Story:** As a tenant, I want to send WhatsApp invitations with rich formatting and easy-to-tap links from the proforma interface, so that my benefactor has a smooth mobile experience.

#### Acceptance Criteria

1. WHEN sending via WhatsApp from a proforma, THE system SHALL format messages with proper WhatsApp markdown for readability
2. WHEN a WhatsApp message is sent, THE system SHALL include the proforma details (amount, property, tenant name) and a shortened payment link
3. WHEN formatting WhatsApp messages, THE system SHALL include the proforma total amount, property address, and tenant name in a structured format
4. WHEN a benefactor receives a WhatsApp invitation, THE system SHALL ensure the payment link opens the same benefactor payment interface as email invitations
5. WHERE WhatsApp Business API is available, THE system SHALL use rich message templates with action buttons for quick payment access

### Requirement 3

**User Story:** As a tenant, I want to send SMS invitations with concise, clear information from the proforma, so that my benefactor can quickly understand and act on the payment request.

#### Acceptance Criteria

1. WHEN sending via SMS from a proforma, THE system SHALL format messages within standard SMS character limits (160 characters for single SMS)
2. WHEN SMS content exceeds single message limits, THE system SHALL use concatenated SMS or provide shortened content prioritizing essential information
3. WHEN an SMS is sent, THE system SHALL include essential information: tenant name, proforma amount, and secure payment link
4. WHEN formatting SMS messages, THE system SHALL use URL shortening to maximize space for proforma details and payment link
5. WHERE SMS delivery fails, THE system SHALL show error messages in the proforma interface and suggest alternative delivery methods

### Requirement 4

**User Story:** As a tenant, I want to track the delivery status of my proforma invitations across all channels, so that I know whether my benefactor received the payment request.

#### Acceptance Criteria

1. WHEN an invitation is sent through any channel from a proforma, THE system SHALL track and display delivery status in the proforma interface
2. WHEN a delivery fails on any channel, THE system SHALL show the failure reason in the proforma and provide retry options
3. WHEN a benefactor opens the invitation link, THE system SHALL track the engagement and show this status on the proforma
4. WHEN displaying delivery status on the proforma, THE system SHALL show timestamps for sent, delivered, and opened events for each channel
5. WHERE multiple channels are used, THE system SHALL provide consolidated status in the proforma showing success/failure for each delivery method

### Requirement 5

**User Story:** As a tenant, I want automatic fallback delivery options, so that my invitation reaches the benefactor even if the primary method fails.

#### Acceptance Criteria

1. WHEN a primary delivery method fails, THE system SHALL automatically attempt delivery via configured fallback channels
2. WHEN WhatsApp delivery fails, THE system SHALL fall back to SMS if a phone number is available
3. WHEN both WhatsApp and SMS fail, THE system SHALL fall back to email if an email address is provided
4. WHEN all delivery methods fail, THE system SHALL notify the tenant and provide manual sharing options
5. WHERE fallback delivery succeeds, THE system SHALL notify the tenant of the successful alternative delivery

### Requirement 6

**User Story:** As a system administrator, I want to configure SMS and WhatsApp service providers, so that the system can integrate with reliable messaging services.

#### Acceptance Criteria

1. WHEN configuring SMS services, THE system SHALL support multiple providers (Twilio, Termii, Nexmo/Vonage)
2. WHEN configuring WhatsApp services, THE system SHALL support WhatsApp Business API integration
3. WHEN service credentials are updated, THE system SHALL validate connectivity before saving configuration
4. WHEN a messaging service is unavailable, THE system SHALL automatically switch to backup providers
5. WHERE rate limits are reached, THE system SHALL queue messages and retry within service limits

### Requirement 7

**User Story:** As a benefactor, I want to receive invitations through my preferred communication channel, so that I can respond quickly and conveniently.

#### Acceptance Criteria

1. WHEN receiving a WhatsApp invitation, THE benefactor SHALL see a properly formatted message with clear payment instructions
2. WHEN receiving an SMS invitation, THE benefactor SHALL get concise information with a working payment link
3. WHEN clicking any invitation link, THE benefactor SHALL be directed to the same secure payment interface
4. WHEN accessing the payment page from mobile, THE benefactor SHALL have an optimized mobile experience
5. WHERE the benefactor receives multiple invitations via different channels, THE system SHALL recognize and consolidate them as the same request

### Requirement 8

**User Story:** As a tenant, I want to customize invitation messages for different channels, so that I can provide appropriate context for each communication method.

#### Acceptance Criteria

1. WHEN creating an invitation, THE system SHALL allow custom messages for each selected delivery channel
2. WHEN customizing messages, THE system SHALL provide character count limits appropriate for each channel
3. WHEN no custom message is provided, THE system SHALL use intelligent default templates for each channel
4. WHEN previewing messages, THE system SHALL show how the invitation will appear in each selected channel
5. WHERE message templates are used, THE system SHALL personalize them with tenant and property information

### Requirement 9

**User Story:** As a system administrator, I want to monitor messaging costs and usage, so that I can manage service expenses and optimize delivery methods.

#### Acceptance Criteria

1. WHEN messages are sent through paid services, THE system SHALL track costs per message and per channel
2. WHEN usage approaches service limits, THE system SHALL alert administrators and suggest cost optimization
3. WHEN generating reports, THE system SHALL provide delivery success rates and cost analysis by channel
4. WHEN costs exceed budgets, THE system SHALL provide options to disable expensive channels temporarily
5. WHERE free alternatives exist, THE system SHALL prioritize cost-effective delivery methods while maintaining reliability

### Requirement 10

**User Story:** As a tenant viewing a proforma, I want to resend invitations through different channels if needed, so that I can ensure my benefactor receives the payment request.

#### Acceptance Criteria

1. WHEN an invitation delivery fails from a proforma, THE system SHALL provide options in the proforma interface to resend via alternative channels
2. WHEN resending invitations from the same proforma, THE system SHALL use the same secure payment token to prevent duplicate payment requests
3. WHEN multiple resend attempts are made, THE system SHALL track all delivery attempts and show their outcomes in the proforma interface
4. WHEN a benefactor has already responded to a proforma invitation, THE system SHALL prevent unnecessary resending and show the payment status
5. WHERE manual resending is needed, THE system SHALL provide shareable links and QR codes in the proforma interface for easy distribution