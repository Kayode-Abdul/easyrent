# Benefactor Payment System - Phase 1 Setup Guide

## Quick Start

### Step 1: Run Migrations
```bash
php artisan migrate
```

This will add Phase 1 features to your database:
- Relationship type field
- Approval/consent fields
- Payment scheduling field
- Pause/resume fields
- Guest-to-registered migration support

### Step 2: Clear Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Step 3: Verify Email Configuration
Check your `.env` file has proper email settings:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io  # or your SMTP host
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Step 4: Test the System

#### Test Approval Flow:
1. Login as a tenant
2. Navigate to "Invite Benefactor" (you may need to create this link in your tenant dashboard)
3. Enter benefactor email and amount
4. Check benefactor's email for invitation
5. Click link and test approve/decline

#### Test Payment Scheduling:
1. As benefactor, choose "Recurring Payment"
2. Select payment day of month (e.g., 15th)
3. Complete payment
4. Verify next payment date is set correctly

#### Test Pause/Resume:
1. Login as benefactor (or use existing benefactor account)
2. Go to benefactor dashboard
3. Find active recurring payment
4. Click "Pause" and provide reason
5. Verify tenant receives email
6. Click "Resume" to restart
7. Verify tenant receives resume email

#### Test Guest Migration:
1. Make payment as guest with email: test@example.com
2. Register new account with same email
3. Login and go to benefactor dashboard
4. Verify welcome message shows previous payments

---

## Phase 1 Features Summary

### ✅ 1. Approval/Consent System
- Benefactors must approve payment requests before proceeding
- Can decline with optional reason
- Tenant receives notification of decision

### ✅ 2. Relationship Type
- Captures benefactor's relationship to tenant
- Options: Employer, Parent, Guardian, Sponsor, Organization, Other
- Required field during payment

### ✅ 3. Payment Scheduling
- Choose specific day of month for recurring charges
- Aligns with payday or preferred date
- Handles edge cases (e.g., 31st in February)

### ✅ 4. Emergency Pause
- Pause recurring payments temporarily
- Resume anytime with recalculated dates
- Cancel permanently if needed
- Tenant notified of all actions

### ✅ 5. Guest-to-Registered Migration
- Guest payments automatically linked when registering
- Complete history preserved
- Seamless transition with welcome message

---

## New Routes Added

```php
// Approval routes
POST /benefactor/payment/{token}/approve
POST /benefactor/payment/{token}/decline

// Payment management routes
POST /benefactor/payment/{payment}/pause
POST /benefactor/payment/{payment}/resume
POST /benefactor/payment/{payment}/cancel (updated)
```

---

## New Views Created

```
resources/views/benefactor/approval.blade.php    - Approval page
resources/views/benefactor/declined.blade.php    - Declined message
resources/views/emails/payment-declined.blade.php
resources/views/emails/payment-paused.blade.php
resources/views/emails/payment-resumed.blade.php
resources/views/emails/payment-cancelled.blade.php
```

---

## Database Changes

### New Fields in `benefactors`:
- `relationship_type` - ENUM
- `is_registered` - BOOLEAN

### New Fields in `payment_invitations`:
- `approval_status` - ENUM
- `approved_at` - TIMESTAMP
- `declined_at` - TIMESTAMP
- `decline_reason` - TEXT

### New Fields in `benefactor_payments`:
- `is_paused` - BOOLEAN
- `paused_at` - TIMESTAMP
- `pause_reason` - TEXT
- `payment_day_of_month` - INTEGER

---

## Troubleshooting

### Emails Not Sending
```bash
# Test email configuration
php artisan tinker
>>> Mail::raw('Test email', function($msg) { $msg->to('your@email.com')->subject('Test'); });
```

### Migration Errors
```bash
# Check migration status
php artisan migrate:status

# Rollback if needed
php artisan migrate:rollback --step=1

# Re-run
php artisan migrate
```

### Route Not Found
```bash
# Clear and cache routes
php artisan route:clear
php artisan route:cache
php artisan route:list | grep benefactor
```

### View Not Found
```bash
# Clear view cache
php artisan view:clear
```

---

## Next Steps

1. **Add Tenant UI**: Create "Invite Benefactor" button in tenant dashboard
2. **Test Thoroughly**: Go through all flows with test data
3. **Configure Queue**: Set up queue workers for email sending
4. **Monitor Logs**: Watch `storage/logs/laravel.log` for issues
5. **User Training**: Prepare documentation for end users

---

## Production Checklist

- [ ] Migrations run successfully
- [ ] Email configuration tested
- [ ] Queue workers configured
- [ ] All routes accessible
- [ ] Views rendering correctly
- [ ] Email notifications working
- [ ] Guest migration tested
- [ ] Pause/resume functionality verified
- [ ] Payment scheduling tested
- [ ] Approval flow working
- [ ] Error logging configured
- [ ] Backup system in place

---

## Support

For issues or questions:
1. Check `storage/logs/laravel.log`
2. Review this documentation
3. Test in development environment first
4. Contact development team

---

**Phase 1 Complete! 🎉**

You now have a production-ready benefactor payment system with:
- Consent-based payments
- Flexible scheduling
- Emergency controls
- Seamless user experience
- Complete email notifications
