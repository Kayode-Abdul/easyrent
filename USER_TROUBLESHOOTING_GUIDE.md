# User Troubleshooting Guide

## Issue: "Payments not showing on billing page"

### Quick Checks
1. **Login Status**: Ensure you're logged in to your account
2. **Payment Status**: Only successful payments appear on billing page
3. **Wait Time**: Allow 24 hours after payment for processing

### Detailed Troubleshooting

#### For Tenants
1. Navigate to **Dashboard → Billing**
2. Check if you see "No Payment History" message
3. If empty, verify:
   - You have made payments through the system
   - Payments were completed successfully (not just initiated)
   - You're logged into the correct account

#### For Landlords
1. Navigate to **Dashboard → Billing**
2. You should see payments received from tenants
3. If empty, verify:
   - Tenants have made payments for your properties
   - Payments reached "completed" or "success" status

### Common Causes
- **Payment still processing**: Wait 24 hours
- **Payment failed**: Check with payment provider (Paystack)
- **Wrong account**: Ensure you're using the correct login
- **Status issue**: Payment may be "pending" instead of "completed"

---

## Issue: "Cannot create complaints"

### Requirements Check
1. **User Type**: Only tenants can create complaints
2. **Apartment Assignment**: You must be assigned to an apartment
3. **Login Status**: Must be logged in

### Step-by-Step Access
1. **Login** to your tenant account
2. Go to **Dashboard**
3. Look for **Complaints** section or menu
4. Click **Create New Complaint** or **Submit Complaint**

### If You Can't Find the Option
1. Check your user role (must be tenant)
2. Verify apartment assignment with your landlord
3. Try accessing directly: `/complaints/create`

### Form Requirements
- **Apartment**: Select from your assigned apartments
- **Category**: Choose issue type (plumbing, electrical, etc.)
- **Priority**: Select urgency level
- **Title**: Brief description (required)
- **Description**: Detailed explanation (minimum 10 characters)
- **Attachments**: Photos/documents (optional)

---

## Issue: "Payment amount is wrong (multiplied by months)"

### Understanding Pricing Types

#### Fixed Amount (Total Lease)
- **Use Case**: Entire lease amount upfront
- **Example**: ₦2,000,000 for 12-month lease
- **Result**: Tenant pays ₦2,000,000 regardless of duration
- **Setting**: Pricing Type = "Total"

#### Monthly Amount
- **Use Case**: Per-month rental amount
- **Example**: ₦500,000 per month for 6 months
- **Result**: Tenant pays ₦3,000,000 total (₦500K × 6)
- **Setting**: Pricing Type = "Monthly"

### For Landlords
When creating/editing apartments:
1. Set **Amount** to the base price
2. Choose **Pricing Type**:
   - **"Total"** for fixed lease amounts
   - **"Monthly"** for per-month amounts
3. Save changes

### For Tenants
The payment amount is calculated based on:
- Apartment's base amount
- Lease duration you selected
- Landlord's pricing type setting

If amount seems wrong, contact your landlord to verify pricing type.

---

## Issue: "Column 'property_name' not found"

### Status: FIXED ✅
This database error has been resolved. If you still see this error:
1. Clear browser cache
2. Refresh the page
3. Contact technical support if it persists

---

## General Navigation Tips

### Dashboard Access
- **URL**: `/dashboard` or `/dash`
- **Menu**: Look for "Dashboard" in main navigation
- **Mobile**: Use hamburger menu (☰) if on mobile

### Feature Locations
- **Billing**: Dashboard → Billing & Payments
- **Complaints**: Dashboard → Complaints
- **Properties**: Dashboard → My Properties (landlords)
- **Apartments**: Dashboard → My Apartments (tenants)

### Getting Help
1. **Documentation**: Check user guides in Help section
2. **Support**: Contact support through the platform
3. **Emergency**: For urgent property issues, contact landlord directly

---

## Contact Information

### For Technical Issues
- Platform bugs or errors
- Login problems
- Payment processing issues

### For Property Issues
- Maintenance requests
- Lease questions
- Apartment problems

### Emergency Contacts
- **Safety Issues**: Contact emergency services (911)
- **Security**: Contact local authorities
- **Urgent Repairs**: Contact landlord directly

---

## Frequently Asked Questions

### Q: Why don't I see the complaint creation option?
**A**: You must be logged in as a tenant and assigned to at least one apartment.

### Q: My payment was successful but doesn't show in billing
**A**: Wait 24 hours for processing. If still missing, contact support with your payment reference.

### Q: The payment amount seems doubled
**A**: Check with your landlord about the pricing type setting. It may be set to "monthly" when it should be "total".

### Q: I can't access certain features
**A**: Features are role-based. Tenants, landlords, and agents have different access levels.

### Q: How do I know if my complaint was submitted?
**A**: You'll receive a confirmation message with a complaint number and email notification.

---

*Last Updated: December 17, 2025*