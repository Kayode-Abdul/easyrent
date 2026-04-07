# Tenant Manager (Benefactor Payment System) - Implementation Documentation

## Overview
The Tenant Manager feature allows users to act as benefactors who pay rent on behalf of tenants. This is ideal for employers, sponsors, or family members who cover housing costs for others.

---

## Database Schema

### Tables Created

#### 1. `benefactors`
Stores information about users who pay rent for others.

**Columns:**
- `id` - Primary key
- `user_id` - Foreign key to users table (nullable for guests)
- `email` - Benefactor's email
- `full_name` - Benefactor's full name
- `phone` - Phone number (optional)
- `type` - Enum: 'registered' or 'guest'
- `is_active` - Boolean flag
- `timestamps`

#### 2. `benefactor_payments`
Records all payments made by benefactors.

**Columns:**
- `id` - Primary key
- `benefactor_id` - Foreign key to benefactors
- `tenant_id` - Foreign key to users (tenant)
- `property_id` - Foreign key to properties (optional)
- `apartment_id` - Foreign key to apartments (optional)
- `amount` - Payment amount (decimal)
- `payment_type` - Enum: 'one_time' or 'recurring'
- `status` - Enum: 'pending', 'completed', 'failed', 'cancelled'
- `frequency` - Enum: 'monthly', 'quarterly', 'annually' (for recurring)
- `next_payment_date` - Date of next payment (for recurring)
- `payment_reference` - Unique payment reference
- `transaction_id` - Payment gateway transaction ID
- `payment_metadata` - JSON field for additional data
- `paid_at` - Timestamp of payment completion
- `cancelled_at` - Timestamp of cancellation
- `timestamps`

#### 3. `payment_invitations`
Manages payment invitation links sent to benefactors.

**Columns:**
- `id` - Primary key
- `tenant_id` - Foreign key to users (tenant)
- `benefactor_email` - Email of invited benefactor
- `benefactor_id` - Foreign key to benefactors (after acceptance)
- `amount` - Requested amount
- `token` - Unique secure token for payment link
- `status` - Enum: 'pending', 'accepted', 'expired', 'cancelled'
- `expires_at` - Expiration timestamp (7 days default)
- `accepted_at` - Timestamp of acceptance
- `invoice_details` - JSON field for invoice data
- `timestamps`

---

## Models

### 1. Benefactor Model
**Location:** `app/Models/Benefactor.php`

**Key Methods:**
- `user()` - Relationship to User model
- `payments()` - All payments made
- `recurringPayments()` - Active recurring payments only
- `tenants()` - All tenants sponsored
- `isRegistered()` - Check if registered user
- `isGuest()` - Check if guest user

### 2. BenefactorPayment Model
**Location:** `app/Models/BenefactorPayment.php`

**Key Methods:**
- `benefactor()` - Relationship to Benefactor
- `tenant()` - Relationship to tenant User
- `property()` - Relationship to Property
- `apartment()` - Relationship to Apartment
- `isRecurring()` - Check if recurring payment
- `isOneTime()` - Check if one-time payment
- `markAsCompleted($transactionId)` - Mark payment as completed
- `setNextPaymentDate()` - Calculate next payment date
- `cancel()` - Cancel recurring payment

### 3. PaymentInvitation Model
**Location:** `app/Models/PaymentInvitation.php`

**Key Methods:**
- `tenant()` - Relationship to tenant User
- `benefactor()` - Relationship to Benefactor
- `isExpired()` - Check if invitation expired
- `isPending()` - Check if invitation pending
- `isAccepted()` - Check if invitation accepted
- `markAsAccepted($benefactorId)` - Mark as accepted
- `cancel()` - Cancel invitation
- `getPaymentLink()` - Generate payment URL

---

## Controllers

### 1. BenefactorPaymentController
**Location:** `app/Http/Controllers/BenefactorPaymentController.php`

**Routes:**
- `GET /benefactor/payment/{token}` - Show payment page
- `POST /benefactor/payment/{token}/process` - Process payment
- `GET /benefactor/payment/{payment}/gateway` - Payment gateway page
- `GET /benefactor/payment/callback` - Payment callback handler
- `GET /benefactor/payment/{payment}/success` - Success page
- `GET /benefactor/dashboard` - Benefactor dashboard (auth required)
- `POST /benefactor/payment/{payment}/cancel` - Cancel recurring payment

### 2. TenantBenefactorController
**Location:** `app/Http/Controllers/TenantBenefactorController.php`

**Routes:**
- `POST /tenant/invite-benefactor` - Send payment invitation
- `GET /tenant/benefactor-invitations` - View all invitations
- `POST /tenant/benefactor-invitation/{invitation}/cancel` - Cancel invitation

---

## Views

### Benefactor Views
**Location:** `resources/views/benefactor/`

1. **payment.blade.php** - Main payment page
   - Shows invoice details
   - Payment type selection (one-time/recurring)
   - Guest checkout or login option
   - Account creation for recurring payments

2. **success.blade.php** - Payment success page
   - Payment confirmation
   - Receipt details
   - Option to create account (for guests)
   - Link to dashboard (for registered users)

3. **dashboard.blade.php** - Benefactor dashboard
   - Summary statistics
   - Active recurring payments
   - Payment history
   - Manage recurring payments

4. **expired.blade.php** - Expired invitation page
5. **already-paid.blade.php** - Already paid notification

### Tenant Views
**Location:** `resources/views/tenant/`

1. **invite-benefactor.blade.php** - Form to invite benefactor
   - Benefactor email input
   - Amount specification
   - Optional property selection
   - Personal message

### Email Template
**Location:** `resources/views/emails/benefactor-invitation.blade.php`
- Professional email template
- Payment details
- Secure payment link
- Expiration notice

---

## User Flows

### Flow 1: Registered Benefactor, Registered Tenant
1. Tenant creates payment invitation
2. Benefactor receives email with payment link
3. Benefactor clicks link → Redirects to login
4. Benefactor logs in → Views invoice
5. Benefactor selects payment type
6. Benefactor completes payment
7. Both receive confirmation

### Flow 2: Guest Benefactor, One-Time Payment
1. Tenant creates payment invitation
2. Benefactor receives email
3. Benefactor clicks link → Guest payment page
4. Benefactor enters basic info
5. Benefactor selects "One-time payment"
6. Benefactor completes payment as guest
7. Option to create account after payment

### Flow 3: Guest Benefactor, Recurring Payment
1. Tenant creates payment invitation
2. Benefactor receives email
3. Benefactor clicks link → Guest payment page
4. Benefactor selects "Recurring payment"
5. System prompts for account creation
6. Benefactor creates account (quick registration)
7. Benefactor sets up recurring payment
8. Automatic payments on schedule

---

## Features

### For Benefactors
✅ Guest checkout for one-time payments
✅ Account creation for recurring payments
✅ Dashboard to manage all payments
✅ View payment history
✅ Manage multiple tenants
✅ Cancel recurring payments anytime
✅ Email notifications

### For Tenants
✅ Send payment invitations via email
✅ Track invitation status
✅ View payment history
✅ Cancel pending invitations
✅ Add personal messages

### Security Features
✅ Unique, time-limited payment tokens
✅ Email verification for new accounts
✅ Secure payment processing
✅ Encrypted sensitive data
✅ Payment confirmation emails

---

## Configuration

### Email Setup
Configure your mail settings in `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@easyrent.africa
MAIL_FROM_NAME="EasyRent"
```

### Payment Gateway Integration
The system is designed to integrate with payment gateways like Paystack or Flutterwave. Update the `paymentCallback` method in `BenefactorPaymentController` with your gateway's verification logic.

---

## Testing

### Manual Testing Steps

1. **Create Payment Invitation:**
   ```
   POST /tenant/invite-benefactor
   Data: {
     benefactor_email: "benefactor@example.com",
     amount: 150000,
     message: "Please pay my rent"
   }
   ```

2. **Access Payment Link:**
   - Check email for invitation
   - Click payment link
   - Verify page loads correctly

3. **Test Guest Checkout:**
   - Select "One-time payment"
   - Enter guest information
   - Complete payment

4. **Test Recurring Payment:**
   - Select "Recurring payment"
   - Create account
   - Set up recurring payment

5. **Test Dashboard:**
   - Login as benefactor
   - View payment history
   - Cancel recurring payment

---

## Future Enhancements

### Planned Features
- [ ] SMS notifications
- [ ] Multiple payment methods
- [ ] Payment reminders
- [ ] Bulk payment invitations
- [ ] Payment analytics
- [ ] Export payment reports
- [ ] Mobile app integration
- [ ] WhatsApp notifications
- [ ] Payment splitting (multiple benefactors)
- [ ] Scheduled payments

---

## API Endpoints (Future)

For mobile app or third-party integrations:

```
POST /api/benefactor/invitations - Create invitation
GET /api/benefactor/payments - List payments
POST /api/benefactor/payments/{id}/cancel - Cancel payment
GET /api/benefactor/dashboard - Dashboard data
```

---

## Troubleshooting

### Common Issues

**Issue:** Email not sending
**Solution:** Check mail configuration in `.env` and ensure mail service is running

**Issue:** Payment link expired
**Solution:** Tenant needs to create a new invitation

**Issue:** Recurring payment not processing
**Solution:** Check cron job is running for scheduled payments

**Issue:** Guest cannot complete payment
**Solution:** Verify all required fields are filled

---

## Support

For questions or issues:
- Email: support@easyrent.africa
- Documentation: [Link to docs]
- GitHub Issues: [Link to repo]

---

## Changelog

### Version 1.0.0 (November 17, 2025)
- Initial implementation
- Guest checkout support
- Recurring payment support
- Email notifications
- Benefactor dashboard
- Payment history tracking

---

## Credits

Developed by: EasyRent Development Team
Date: November 17, 2025
