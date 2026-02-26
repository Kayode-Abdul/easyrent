# Implementation Plan - Two Tasks

## TASK 1: Complaints Dashboard - Multi-Type Search

### Search Types to Implement
1. **Tenant Name** - Search by tenant first/last name
2. **Complaint Title** - Search by complaint title/description
3. **Status Filter** - Filter by open/in_progress/resolved/closed
4. **Priority Filter** - Filter by low/medium/high/urgent
5. **Category Filter** - Filter by complaint category
6. **Date Range** - Filter by created date range
7. **Property** - Filter by property name

### Implementation
- Add search form to landlord-dashboard.blade.php
- Update ComplaintController to handle multiple filters
- Add query builder for flexible filtering

---

## TASK 2: Benefactor Link Flow Redesign

### Current Flow (Guest Allowed)
```
User Views Link
    ↓
If Not Logged In: Shows Login/Signup + Guest Checkout
    ↓
Guest Can Pay Without Account
```

### New Flow (No Guest)
```
User Views Link with Proforma Details
    ↓
If Not Logged In: Shows Login/Signup Buttons ONLY
    ↓
User Must Login or Signup
    ↓
After Auth: Redirected Back to Link with Details
    ↓
Shows "Pay Now" Button
    ↓
Proceeds to Payment
```

### Changes Required
1. **benefactor/payment.blade.php**
   - Remove guest checkout section
   - Remove "Continue as Guest" option
   - Keep login/signup buttons prominent
   - Show proforma details clearly

2. **BenefactorPaymentController.php**
   - Require authentication
   - Redirect unauthenticated users to login with return URL
   - Keep link details in session after auth

3. **routes/web.php**
   - Add middleware to require auth for payment processing
   - Keep show route public (for viewing details)

---

## Files to Modify

### Task 1: Complaints Search
- `resources/views/complaints/landlord-dashboard.blade.php`
- `app/Http/Controllers/ComplaintController.php`

### Task 2: Benefactor Link
- `resources/views/benefactor/payment.blade.php`
- `app/Http/Controllers/BenefactorPaymentController.php`
- `routes/web.php`

