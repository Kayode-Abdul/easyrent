# Complete Payment Flows Guide: EasyRent Link, Direct Payment, & Benefactor Link

## Overview
The system has three distinct payment link types, each serving different use cases:

1. **EasyRent Link (Apartment Invitation)** - For tenant apartment applications
2. **Direct Payment Link (Proforma)** - For landlord-initiated payment requests
3. **Benefactor Link** - For third-party payment assistance

---

## 1. EASYRENT LINK FLOW (Apartment Invitation)

### Purpose
Landlord shares a link to a specific apartment. Potential tenants can view details and apply without pre-registration.

### Route
```
GET /apartment/invite/{token}
```

### User Journey

#### **Step 1: Unauthenticated User Visits Link**
```
User clicks link → ApartmentInvitationController@show()
```
- System validates invitation token
- Checks security (rate limiting, suspicious activity)
- Loads apartment, property, and landlord details
- User sees apartment details WITHOUT login requirement

**What User Sees:**
- Property images carousel
- Apartment type, price, location
- Landlord contact info
- Amenities and features
- Payment calculator
- **Login/Signup buttons** (NOT payment button)

#### **Step 2: User Attempts to Apply**
```
User clicks "Create Account" or "Log In" button
```
- Application preferences saved to session
- User redirected to `/register` or `/login` with `invitation_token` parameter
- Session stores: duration, move-in date, notes, apartment ID

#### **Step 3: Authentication**
```
POST /register or POST /login
```
- User creates account or logs in
- System retrieves saved application data from session
- User auto-redirected back to apartment invite page

#### **Step 4: Authenticated User Applies**
```
POST /apartment/invite/{token}/apply
```
- User fills application form (pre-populated with saved data)
- System calculates payment using PaymentCalculationService
- Creates Payment record with status='pending'
- Redirects to payment page

#### **Step 5: Payment Processing**
```
GET /apartment/invite/{token}/payment/{payment_id}
```
- User sees payment form with calculated amount
- Clicks "Pay Now" button
- JavaScript submits to Paystack gateway

#### **Step 6: Payment Callback**
```
POST /payment/callback (from Paystack)
```
- Paystack sends payment verification
- PaymentController verifies transaction
- Updates Payment status to 'completed'
- Calls PaymentIntegrationService::processInvitationPayment()
- Apartment assigned to tenant
- Redirects to success page

#### **Step 7: Success**
```
GET /apartment/invite/{token}/success
```
- Shows confirmation message
- Apartment is now assigned to tenant
- Emails sent to both landlord and tenant

### Database Records Created
```
ApartmentInvitation:
  - invitation_token (unique)
  - apartment_id
  - landlord_id
  - status (viewed → payment_initiated → used)
  - lease_duration
  - move_in_date
  - total_amount

Payment:
  - transaction_id (from Paystack)
  - tenant_id (user_id of applicant)
  - landlord_id
  - apartment_id
  - amount
  - status (pending → completed)
  - payment_reference: 'easyrent_{token}'
```

### Key Features
- ✅ No pre-registration required to view
- ✅ Session-based context preservation
- ✅ Automatic payment calculation
- ✅ Secure token validation
- ✅ Rate limiting and fraud detection
- ✅ Mobile optimized

---

## 2. DIRECT PAYMENT LINK FLOW (Proforma)

### Purpose
Landlord creates a proforma (invoice) and sends payment link to tenant for rent/deposit payment.

### Route
```
GET /proforma/{id}/payment
```

### User Journey

#### **Step 1: Landlord Creates Proforma**
```
POST /dashboard/apartment/{id}/send-profoma
```
- Landlord selects apartment and payment details
- System calculates total amount
- Creates ProformaReceipt record
- Generates unique payment link

#### **Step 2: Tenant Receives Link**
- Landlord shares link via email/WhatsApp
- Link format: `yoursite.com/proforma/{id}/payment`

#### **Step 3: Tenant Visits Link**
```
GET /proforma/{id}/payment
```
- If authenticated: Shows payment form directly
- If not authenticated: Shows login/signup buttons
- Displays proforma details (amount, due date, etc.)

#### **Step 4: Authentication (if needed)**
```
POST /login or POST /register
```
- Tenant logs in or creates account
- Redirected back to proforma payment page

#### **Step 5: Payment Processing**
```
POST /pay
```
- Tenant submits payment form
- Redirects to Paystack gateway
- User completes payment on Paystack

#### **Step 6: Payment Callback**
```
POST /payment/callback (from Paystack)
```
- Paystack sends verification
- PaymentController processes callback
- Creates/updates Payment record
- Status set to 'completed'
- Redirects to success page: `/proforma/payment/success/{payment_id}`

#### **Step 7: Success**
```
GET /proforma/payment/success/{payment_id}
```
- Shows payment confirmation
- Emails sent to tenant and landlord
- Payment appears in tenant's billing page

### Database Records Created
```
ProformaReceipt:
  - apartment_id
  - tenant_id
  - amount
  - due_date
  - status

Payment:
  - transaction_id (from Paystack)
  - tenant_id
  - landlord_id
  - apartment_id
  - amount
  - status (pending → completed)
  - payment_reference: 'proforma_{id}'
```

### Key Features
- ✅ Landlord-initiated payment requests
- ✅ Works for authenticated users
- ✅ Flexible payment amounts
- ✅ Payment tracking and history
- ✅ Automatic notifications

---

## 3. BENEFACTOR LINK FLOW (Third-Party Payment)

### Purpose
Tenant invites a third party (parent, sponsor, employer) to help pay rent/deposit.

### Route
```
GET /benefactor/payment/{token}
```

### User Journey

#### **Step 1: Tenant Invites Benefactor**
```
POST /tenant/invite-benefactor
```
- Tenant selects apartment/proforma
- Enters benefactor email
- System creates PaymentInvitation record
- Sends invitation email to benefactor

#### **Step 2: Benefactor Receives Email**
- Email contains payment link
- Link format: `yoursite.com/benefactor/payment/{token}`

#### **Step 3: Benefactor Visits Link**
```
GET /benefactor/payment/{token}
```
- Shows approval page (if pending approval)
- Displays payment details
- Shows relationship type options (parent, employer, etc.)

#### **Step 4: Benefactor Approves Payment**
```
POST /benefactor/payment/{token}/approve
```
- Benefactor reviews details
- Clicks "Approve" button
- Redirected to payment page

#### **Step 5: Benefactor Information Collection**
```
POST /benefactor/payment/{token}/process
```
- Benefactor enters:
  - Full name
  - Email
  - Phone
  - Relationship type
  - Payment type (one-time or recurring)
  - If recurring: frequency and payment day

**Guest vs Registered:**
- **Guest**: Completes payment without account
- **Registered**: Can create account for future payments
- **Existing**: If email exists, updates benefactor record

#### **Step 6: Payment Processing**
```
GET /benefactor/gateway/{payment_id}
```
- Shows payment form
- User clicks "Pay Now"
- Redirects to Paystack

#### **Step 7: Payment Callback**
```
GET /benefactor/payment/callback?reference={ref}
```
- Paystack sends verification
- BenefactorPaymentController processes callback
- Updates BenefactorPayment status to 'completed'
- Sends notifications to tenant and benefactor

#### **Step 8: Success**
```
GET /benefactor/payment/success/{payment_id}
```
- Shows confirmation
- Emails sent to all parties
- Payment recorded in system

### Database Records Created
```
PaymentInvitation:
  - token (unique)
  - tenant_id
  - proforma_id
  - benefactor_email
  - amount
  - status (pending → approved → accepted)

Benefactor:
  - email
  - full_name
  - phone
  - user_id (if registered)
  - type (guest or registered)
  - relationship_type

BenefactorPayment:
  - benefactor_id
  - tenant_id
  - proforma_id
  - amount
  - payment_type (one_time or recurring)
  - frequency (if recurring)
  - status (pending → completed)
```

### Key Features
- ✅ Guest checkout available
- ✅ Optional account creation
- ✅ Recurring payment support
- ✅ Pause/resume/cancel options
- ✅ Relationship tracking
- ✅ Multi-party notifications

---

## Comparison Table

| Feature | EasyRent Link | Direct Payment | Benefactor Link |
|---------|---------------|-----------------|-----------------|
| **Initiator** | Landlord | Landlord | Tenant |
| **Purpose** | Apartment application | Rent/deposit payment | Third-party assistance |
| **Guest Checkout** | No (must register) | Yes | Yes |
| **Pre-registration** | Not required to view | Required to pay | Not required |
| **Recurring** | No | No | Yes |
| **Apartment Assignment** | Yes | No | No |
| **Payment Type** | One-time | One-time | One-time or recurring |
| **Relationship Tracking** | N/A | N/A | Yes |

---

## Payment Status Flow (All Types)

```
pending → completed → (optional: paused/resumed/cancelled)
```

### Status Meanings
- **pending**: Payment created, awaiting Paystack callback
- **completed**: Payment verified and successful
- **paused**: (Benefactor only) Recurring payment temporarily stopped
- **cancelled**: (Benefactor only) Recurring payment terminated

---

## Security Measures

### Token Security
- Cryptographically secure tokens
- Automatic expiration (30 days default)
- Rate limiting per IP
- Suspicious activity detection

### Session Security
- IP address tracking
- User agent validation
- Automatic cleanup
- CSRF protection

### Payment Security
- Paystack integration
- Transaction verification
- Secure callback handling
- PCI compliance

---

## Error Handling

### Common Scenarios
- **Expired Link**: Clear message with contact info
- **Invalid Token**: Security-focused error page
- **Rate Limited**: Temporary block with retry info
- **Payment Failed**: State preserved for retry
- **Session Timeout**: Fresh start option

---

## Mobile Optimization

All three flows are fully optimized for mobile:
- Responsive design
- Touch-friendly buttons
- Fast loading
- Offline resilience
- Minimal data usage

---

## Integration Points

### External Services
- **Paystack**: Payment gateway
- **Email**: Notifications
- **Session**: Context preservation
- **Cache**: Performance optimization

### Internal Services
- **PaymentCalculationService**: Amount calculation
- **PaymentIntegrationService**: Payment processing
- **SessionManager**: Context management
- **EasyRentLogger**: Audit logging

