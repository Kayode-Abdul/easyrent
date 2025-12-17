# Multi-Channel Benefactor Invitations - Design Document

## Overview

This design enhances the existing EasyRent proforma "Invite Someone to Pay" functionality by adding WhatsApp and SMS delivery options alongside the current email-only system. The enhancement integrates seamlessly with the existing proforma interface, benefactor payment system, and PaymentInvitation model while adding new messaging services and delivery tracking capabilities.

## Architecture

### High-Level Architecture

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   Proforma      │    │   Multi-Channel  │    │   Messaging     │
│   Template      │───▶│   Invitation     │───▶│   Services      │
│   (Frontend)    │    │   Controller     │    │   (SMS/WhatsApp)│
└─────────────────┘    └──────────────────┘    └─────────────────┘
         │                        │                        │
         │                        ▼                        │
         │              ┌──────────────────┐               │
         │              │  PaymentInvitation│               │
         │              │     Model         │               │
         │              │   (Enhanced)      │               │
         │              └──────────────────┘               │
         │                        │                        │
         ▼                        ▼                        ▼
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   Delivery      │    │   Benefactor     │    │   Notification  │
│   Tracking      │    │   Payment        │    │   Queue         │
│   System        │    │   System         │    │   System        │
└─────────────────┘    └──────────────────┘    └─────────────────┘
```

### Integration Points

1. **Proforma Template Enhancement**: Modify the existing SweetAlert modal to include multi-channel options
2. **PaymentInvitation Model Extension**: Add delivery tracking fields and methods
3. **New Messaging Services**: Create SMS and WhatsApp service classes
4. **Delivery Tracking System**: Track delivery status across all channels
5. **Fallback Mechanism**: Automatic retry with alternative channels

## Components and Interfaces

### 1. Enhanced Proforma Interface

**File**: `resources/views/proforma/template.blade.php`

The existing "Invite Someone to Pay" button modal will be enhanced to include:
- Channel selection checkboxes (Email, WhatsApp, SMS)
- Conditional input fields (email address, phone number)
- Real-time validation for phone numbers and email addresses
- Message customization per channel
- Delivery status display

### 2. Multi-Channel Invitation Controller

**New File**: `app/Http/Controllers/MultiChannelInvitationController.php`

```php
class MultiChannelInvitationController extends Controller
{
    public function sendProformaInvitation(Request $request)
    {
        // Validate multi-channel input
        // Create PaymentInvitation with delivery preferences
        // Queue messages for selected channels
        // Return delivery status
    }
    
    public function getDeliveryStatus($invitationId)
    {
        // Return real-time delivery status for all channels
    }
    
    public function resendInvitation(Request $request, $invitationId)
    {
        // Resend via alternative channels
    }
}
```

### 3. Messaging Service Layer

**Interface**: `app/Services/Messaging/MessagingServiceInterface.php`

```php
interface MessagingServiceInterface
{
    public function send(string $recipient, string $message, array $options = []): DeliveryResult;
    public function validateRecipient(string $recipient): bool;
    public function getDeliveryStatus(string $messageId): DeliveryStatus;
    public function formatMessage(PaymentInvitation $invitation, array $customization = []): string;
}
```

**Implementations**:
- `app/Services/Messaging/EmailService.php` (existing, enhanced)
- `app/Services/Messaging/WhatsAppService.php` (new)
- `app/Services/Messaging/SmsService.php` (new)

### 4. Enhanced PaymentInvitation Model

**File**: `app/Models/PaymentInvitation.php` (enhanced)

New fields and methods:
```php
// New database fields
protected $fillable = [
    // ... existing fields
    'delivery_channels',      // JSON: ['email', 'whatsapp', 'sms']
    'phone_number',          // Benefactor phone number
    'delivery_attempts',     // JSON: delivery attempt history
    'delivery_status',       // JSON: current status per channel
    'custom_messages',       // JSON: custom messages per channel
];

// New methods
public function getDeliveryStatus(): array
public function markDeliveryAttempt(string $channel, DeliveryResult $result): void
public function hasSuccessfulDelivery(): bool
public function getFailedChannels(): array
```

## Data Models

### Database Schema Changes

**Migration**: `database/migrations/2025_12_07_120000_add_multi_channel_support_to_payment_invitations.php`

```sql
ALTER TABLE payment_invitations ADD COLUMN delivery_channels JSON;
ALTER TABLE payment_invitations ADD COLUMN phone_number VARCHAR(20);
ALTER TABLE payment_invitations ADD COLUMN delivery_attempts JSON;
ALTER TABLE payment_invitations ADD COLUMN delivery_status JSON;
ALTER TABLE payment_invitations ADD COLUMN custom_messages JSON;
ALTER TABLE payment_invitations ADD COLUMN created_from_proforma_id BIGINT UNSIGNED;
ALTER TABLE payment_invitations ADD INDEX idx_proforma_id (created_from_proforma_id);
```

### Configuration Schema

**File**: `config/messaging.php`

```php
return [
    'sms' => [
        'default' => env('SMS_PROVIDER', 'twilio'),
        'providers' => [
            'twilio' => [
                'sid' => env('TWILIO_SID'),
                'token' => env('TWILIO_TOKEN'),
                'from' => env('TWILIO_FROM'),
            ],
            'termii' => [
                'api_key' => env('TERMII_API_KEY'),
                'sender_id' => env('TERMII_SENDER_ID'),
            ],
        ],
    ],
    'whatsapp' => [
        'provider' => env('WHATSAPP_PROVIDER', 'twilio'),
        'twilio' => [
            'sid' => env('TWILIO_SID'),
            'token' => env('TWILIO_TOKEN'),
            'from' => env('TWILIO_WHATSAPP_FROM'),
        ],
    ],
    'fallback' => [
        'enabled' => true,
        'order' => ['whatsapp', 'sms', 'email'],
    ],
];
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property Reflection

After reviewing all properties identified in the prework analysis, I've identified several areas where properties can be consolidated:

**Redundancy Elimination:**
- Properties 2.2, 2.3, 3.3 all test message content requirements - these can be combined into a comprehensive "message content completeness" property
- Properties 4.1, 4.4, 4.5 all test delivery status tracking - these can be consolidated into a single "delivery tracking consistency" property  
- Properties 7.1, 7.2, 7.3 all test recipient experience consistency - these can be combined into a "cross-channel consistency" property
- Properties 8.1, 8.3, 8.5 all test message customization - these can be consolidated into a "message personalization" property

**Consolidated Properties:**

Property 1: Multi-channel form validation
*For any* combination of selected delivery channels, the form should validate required inputs (email for email channel, phone for WhatsApp/SMS channels) and reject invalid formats
**Validates: Requirements 1.2, 1.3, 1.5, 6.3**

Property 2: Message content completeness  
*For any* proforma invitation sent through any channel, the message should contain tenant name, proforma amount, payment link, and channel-appropriate formatting
**Validates: Requirements 2.2, 2.3, 3.3, 8.5**

Property 3: Cross-channel link consistency
*For any* payment invitation, all delivery channels should generate links that lead to the same secure payment interface with the same token
**Validates: Requirements 2.4, 7.3, 10.2**

Property 4: Message length constraints
*For any* SMS message, the content should either fit within 160 characters or be properly split/shortened while preserving essential information
**Validates: Requirements 3.1, 3.2**

Property 5: Delivery tracking consistency
*For any* invitation sent through multiple channels, the system should track delivery status, timestamps, and outcomes for each channel independently and display consolidated results
**Validates: Requirements 4.1, 4.4, 4.5**

Property 6: Automatic fallback behavior
*For any* failed delivery attempt, if fallback is enabled and alternative channels are available, the system should automatically attempt delivery via the next configured channel
**Validates: Requirements 5.1, 5.2, 5.3**

Property 7: Duplicate prevention
*For any* payment invitation that has already been responded to, resend attempts should be blocked and current payment status should be displayed instead
**Validates: Requirements 10.4**

Property 8: Cost tracking accuracy
*For any* message sent through paid services, the system should accurately record the cost and associate it with the correct channel and invitation
**Validates: Requirements 9.1, 9.3**

Property 9: Service provider failover
*For any* messaging service that becomes unavailable, the system should automatically switch to configured backup providers without losing messages
**Validates: Requirements 6.4, 6.5**

Property 10: Message personalization consistency
*For any* invitation with custom messages, each channel should use its specified custom message, and when no custom message is provided, appropriate default templates should be used with proper personalization
**Validates: Requirements 8.1, 8.3, 8.4**

## Error Handling

### Delivery Failure Scenarios

1. **SMS Provider Failures**
   - Invalid phone numbers
   - Service rate limits
   - Provider downtime
   - Insufficient account balance

2. **WhatsApp API Failures**
   - Invalid WhatsApp numbers
   - Business API rate limits
   - Template approval issues
   - Recipient opt-out status

3. **Network and Infrastructure**
   - Internet connectivity issues
   - DNS resolution failures
   - SSL certificate problems
   - Database connection failures

### Error Recovery Strategies

1. **Automatic Retry Logic**
   - Exponential backoff for temporary failures
   - Circuit breaker pattern for provider failures
   - Queue-based retry system with dead letter handling

2. **Fallback Mechanisms**
   - Channel-specific fallback order
   - Provider-level fallback within channels
   - Manual sharing options as final fallback

3. **User Notification**
   - Real-time delivery status updates in proforma interface
   - Clear error messages with suggested actions
   - Alternative sharing methods (QR codes, shareable links)

## Testing Strategy

### Unit Testing Approach

Unit tests will focus on:
- Message formatting functions for each channel
- Phone number and email validation logic
- Delivery status tracking and updates
- Cost calculation and tracking
- Configuration validation

### Property-Based Testing Requirements

The system will use **PHPUnit with Pest** for property-based testing, configured to run a minimum of 100 iterations per property test.

Each property-based test will be tagged with comments referencing the design document property:
- Format: `**Feature: multi-channel-benefactor-invitations, Property {number}: {property_text}**`
- Each correctness property will be implemented by a single property-based test
- Tests will generate random proforma data, contact information, and delivery scenarios

**Property Test Examples:**

```php
/**
 * **Feature: multi-channel-benefactor-invitations, Property 1: Multi-channel form validation**
 */
test('form validation works for any channel combination', function () {
    // Generate random channel combinations and contact info
    // Verify validation rules are applied correctly
});

/**
 * **Feature: multi-channel-benefactor-invitations, Property 3: Cross-channel link consistency**  
 */
test('all channels generate consistent payment links', function () {
    // Generate random proforma and send via multiple channels
    // Verify all links use same token and lead to same interface
});
```

### Integration Testing

Integration tests will verify:
- End-to-end proforma invitation flow
- Multi-channel delivery coordination
- Fallback mechanism activation
- Provider failover scenarios
- Database transaction consistency

### Manual Testing Scenarios

1. **Proforma Interface Testing**
   - Modal display and interaction
   - Form validation feedback
   - Delivery status updates
   - Resend functionality

2. **Cross-Device Testing**
   - Mobile WhatsApp message display
   - SMS link functionality on various devices
   - Email rendering across clients
   - Payment interface mobile optimization

3. **Provider Integration Testing**
   - SMS delivery via different providers
   - WhatsApp Business API integration
   - Rate limit handling
   - Cost tracking accuracy