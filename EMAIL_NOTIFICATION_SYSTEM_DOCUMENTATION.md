# EasyRent Link Email Notification System

## Overview

The comprehensive email notification system for the EasyRent Link Authentication feature provides automated email communications throughout the apartment invitation, registration, and payment process. The system includes retry logic, delivery tracking, and comprehensive monitoring capabilities.

## Components

### 1. EmailNotificationInterface
- **Location**: `app/Services/Email/EmailNotificationInterface.php`
- **Purpose**: Defines the contract for email notification services
- **Methods**:
  - `sendApplicationNotification()` - Send application notifications to both parties
  - `sendPaymentConfirmation()` - Send payment confirmations
  - `sendWelcomeEmail()` - Send welcome emails for new registrations
  - `sendAssignmentConfirmation()` - Send apartment assignment confirmations
  - `sendWithRetry()` - Send emails with retry logic
  - `getDeliveryStats()` - Get delivery statistics

### 2. EmailNotificationService
- **Location**: `app/Services/Email/EmailNotificationService.php`
- **Purpose**: Main implementation of email notification functionality
- **Features**:
  - Automatic retry with exponential backoff
  - Comprehensive logging and tracking
  - Support for multiple email types
  - Integration with delivery tracker

### 3. EmailDeliveryTracker
- **Location**: `app/Services/Email/EmailDeliveryTracker.php`
- **Purpose**: Track email delivery statistics and health metrics
- **Features**:
  - Success/failure/retry tracking
  - Health score calculation
  - Privacy-compliant logging (masked emails)
  - Cache-based statistics storage

### 4. Enhanced Mail Classes

#### PaymentConfirmationMail
- **Location**: `app/Mail/PaymentConfirmationMail.php`
- **Templates**: 
  - `resources/views/emails/payment-confirmation-landlord.blade.php`
  - `resources/views/emails/payment-confirmation-tenant.blade.php`
- **Purpose**: Send payment confirmation emails to both landlord and tenant

#### Enhanced WelcomeToEasyRentMail
- **Location**: `app/Mail/WelcomeToEasyRentMail.php`
- **Template**: `resources/views/emails/welcome-to-easyrent.blade.php`
- **Purpose**: Send welcome emails with invitation context for new users

### 5. Console Commands

#### ProcessFailedEmails
- **Location**: `app/Console/Commands/ProcessFailedEmails.php`
- **Command**: `php artisan emails:process-failed`
- **Purpose**: Process failed email jobs with retry logic
- **Options**:
  - `--retry-limit=3` - Maximum retry attempts
  - `--batch-size=50` - Number of jobs to process at once
- **Schedule**: Runs every 30 minutes automatically

## Email Types and Templates

### 1. Application Notifications
- **Trigger**: When a tenant submits an apartment application
- **Recipients**: Both landlord and tenant
- **Templates**: 
  - `tenant-application-landlord.blade.php`
  - `tenant-application-tenant.blade.php`

### 2. Payment Confirmations
- **Trigger**: When payment is successfully processed
- **Recipients**: Both landlord and tenant
- **Templates**: 
  - `payment-confirmation-landlord.blade.php`
  - `payment-confirmation-tenant.blade.php`

### 3. Welcome Emails
- **Trigger**: When new user registers via invitation
- **Recipients**: New user
- **Template**: `welcome-to-easyrent.blade.php`
- **Features**: Contextual content based on invitation

### 4. Assignment Confirmations
- **Trigger**: When apartment is officially assigned after payment
- **Recipients**: Both landlord and tenant
- **Templates**: 
  - `apartment-assigned-landlord.blade.php`
  - `apartment-assigned-tenant.blade.php`

## Retry Logic and Error Handling

### Exponential Backoff
- **Initial Delay**: 2 seconds
- **Progression**: 2^attempt seconds (2s, 4s, 8s, 16s...)
- **Max Retries**: 3 attempts (configurable)
- **Timeout**: Jobs are removed after max retries

### Error Tracking
- All delivery attempts are logged with detailed context
- Failed emails are tracked with error messages
- Retry attempts are monitored and reported
- Health scores are calculated based on success rates

### Automatic Recovery
- Failed jobs are automatically retried via scheduled command
- Exponential backoff prevents overwhelming email services
- Permanent failures are logged and removed from queue

## Monitoring and Statistics

### Delivery Statistics
```php
$stats = $emailService->getDeliveryStats();
// Returns: ['sent' => 0, 'failed' => 0, 'retries' => 0]

$trackerStats = $tracker->getStats();
// Returns detailed stats per email type

$healthScore = $tracker->getHealthScore();
// Returns: 0-100 health score
```

### Health Monitoring
- **Health Score**: Calculated as (successful / total) * 100
- **Tracking**: Per-email-type statistics
- **Caching**: Statistics cached for 1 hour
- **Privacy**: Email addresses are masked in logs

## Configuration

### Service Registration
The email notification system is registered in `AppServiceProvider`:

```php
// Register Email Delivery Tracker
$this->app->singleton(\App\Services\Email\EmailDeliveryTracker::class);

// Bind EmailNotificationInterface to EmailNotificationService
$this->app->bind(
    \App\Services\Email\EmailNotificationInterface::class,
    \App\Services\Email\EmailNotificationService::class
);
```

### Scheduled Tasks
The system includes automatic processing of failed emails:

```php
// Process failed emails every 30 minutes
$schedule->command('emails:process-failed --retry-limit=3 --batch-size=50')
        ->everyThirtyMinutes()
        ->timezone('Africa/Lagos');
```

## Usage Examples

### Basic Usage
```php
use App\Services\Email\EmailNotificationInterface;

$emailService = app(EmailNotificationInterface::class);

// Send application notification
$emailService->sendApplicationNotification($invitation, $payment);

// Send payment confirmation
$emailService->sendPaymentConfirmation($invitation, $payment);

// Send welcome email
$emailService->sendWelcomeEmail($user, $invitation);

// Send assignment confirmation
$emailService->sendAssignmentConfirmation($invitation, $payment);
```

### Manual Retry
```php
// Send with custom retry settings
$success = $emailService->sendWithRetry(
    TenantApplicationMail::class,
    ['invitation' => $invitation, 'payment' => $payment, 'recipient' => 'landlord'],
    'landlord@example.com',
    5 // max retries
);
```

### Statistics Monitoring
```php
use App\Services\Email\EmailDeliveryTracker;

$tracker = app(EmailDeliveryTracker::class);

// Get overall stats
$stats = $tracker->getStats();

// Get stats for specific type
$applicationStats = $tracker->getStats('application');

// Get health score
$health = $tracker->getHealthScore();

// Reset statistics
$tracker->resetStats('application'); // Reset specific type
$tracker->resetStats(); // Reset all types
```

## Integration Points

### With ApartmentInvitationController
The email notification service is integrated into the apartment invitation flow:
- Application notifications sent when payment is initiated
- Payment confirmations sent when payment succeeds
- Assignment confirmations sent when apartment is assigned

### With Authentication Controllers
- Welcome emails sent when users register via invitation links
- Context preserved throughout registration process

### With Payment System
- Payment confirmations triggered by successful payments
- Error handling for payment-related email failures

## Security and Privacy

### Email Masking
All email addresses in logs are automatically masked for privacy:
- `john.doe@example.com` becomes `jo****@example.com`
- Preserves domain for debugging while protecting user privacy

### Error Handling
- Sensitive information is not logged in error messages
- Failed email content is not stored in logs
- Only metadata and error types are tracked

### Rate Limiting
- Automatic retry delays prevent email service abuse
- Exponential backoff reduces server load
- Failed jobs are eventually removed to prevent queue bloat

## Maintenance

### Regular Tasks
1. **Monitor Health Score**: Check `$tracker->getHealthScore()` regularly
2. **Review Failed Jobs**: Use `emails:process-failed` command to handle failures
3. **Clear Statistics**: Reset stats periodically with `$tracker->resetStats()`
4. **Log Analysis**: Review email delivery logs for patterns

### Troubleshooting
1. **High Failure Rate**: Check email service configuration and credentials
2. **Queue Buildup**: Increase batch size or frequency of failed job processing
3. **Memory Issues**: Reduce batch size for failed job processing
4. **Performance**: Monitor cache usage and consider increasing TTL

## Requirements Validation

This implementation satisfies all requirements from the EasyRent Link Authentication System:

- ✅ **5.1**: Application notifications sent to both parties
- ✅ **5.2**: Payment confirmation emails implemented
- ✅ **5.3**: Assignment confirmation emails created
- ✅ **5.4**: Welcome emails for invitation-based registrations
- ✅ **5.5**: Email delivery failure handling with retry logic

The system provides comprehensive email notification capabilities with robust error handling, monitoring, and automatic recovery mechanisms.