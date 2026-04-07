# Visual Payment Flow Diagrams

## 1. EASYRENT LINK FLOW (Apartment Invitation)

```
┌─────────────────────────────────────────────────────────────────┐
│                    EASYRENT LINK FLOW                           │
└─────────────────────────────────────────────────────────────────┘

1. UNAUTHENTICATED USER VISITS LINK
   ┌──────────────────────────────────────────┐
   │ User clicks: /apartment/invite/{token}   │
   └──────────────────────────────────────────┘
                      ↓
   ┌──────────────────────────────────────────┐
   │ ApartmentInvitationController@show()     │
   │ - Validate token                         │
   │ - Check security                         │
   │ - Load apartment details                 │
   └──────────────────────────────────────────┘
                      ↓
   ┌──────────────────────────────────────────┐
   │ Display Apartment Details Page           │
   │ - Images, price, location                │
   │ - Landlord info                          │
   │ - [Login] [Sign Up] buttons              │
   │ - NO payment button yet                  │
   └──────────────────────────────────────────┘

2. USER CLICKS LOGIN/SIGNUP
   ┌──────────────────────────────────────────┐
   │ Save to Session:                         │
   │ - apartment_id                           │
   │ - duration (if selected)                 │
   │ - move_in_date (if selected)             │
   │ - invitation_token                       │
   └──────────────────────────────────────────┘
                      ↓
   ┌──────────────────────────────────────────┐
   │ Redirect to:                             │
   │ /login?invitation_token={token}          │
   │ or                                       │
   │ /register?invitation_token={token}       │
   └──────────────────────────────────────────┘

3. USER AUTHENTICATES
   ┌──────────────────────────────────────────┐
   │ POST /login or POST /register            │
   │ - Create/verify user account             │
   │ - Retrieve session data                  │
   │ - Auto-login user                        │
   └──────────────────────────────────────────┘
                      ↓
   ┌──────────────────────────────────────────┐
   │ Redirect back to:                        │
   │ /apartment/invite/{token}                │
   │ (with pre-filled form data)              │
   └──────────────────────────────────────────┘

4. AUTHENTICATED USER APPLIES
   ┌──────────────────────────────────────────┐
   │ POST /apartment/invite/{token}/apply     │
   │ - Validate form data                     │
   │ - Calculate payment amount               │
   │ - Create Payment record (pending)        │
   │ - Update ApartmentInvitation             │
   └──────────────────────────────────────────┘
                      ↓
   ┌──────────────────────────────────────────┐
   │ Redirect to:                             │
   │ /apartment/invite/{token}/payment        │
   └──────────────────────────────────────────┘

5. PAYMENT PAGE
   ┌──────────────────────────────────────────┐
   │ Display Payment Form                     │
   │ - Amount: ₦{calculated_total}            │
   │ - Duration: {selected_duration}          │
   │ - [Pay Now] button                       │
   └──────────────────────────────────────────┘
                      ↓
   ┌──────────────────────────────────────────┐
   │ User clicks [Pay Now]                    │
   │ - JavaScript submits to Paystack         │
   │ - Redirects to Paystack gateway          │
   └──────────────────────────────────────────┘

6. PAYSTACK PAYMENT
   ┌──────────────────────────────────────────┐
   │ User enters card details on Paystack     │
   │ - Card number                            │
   │ - Expiry date                            │
   │ - CVV                                    │
   │ - OTP verification                       │
   └──────────────────────────────────────────┘
                      ↓
   ┌──────────────────────────────────────────┐
   │ Paystack processes payment               │
   │ - Charges card                           │
   │ - Generates transaction reference        │
   │ - Sends callback to server               │
   └──────────────────────────────────────────┘

7. PAYMENT CALLBACK
   ┌──────────────────────────────────────────┐
   │ POST /payment/callback                   │
   │ (from Paystack)                          │
   │ - Verify transaction                     │
   │ - Update Payment status → completed      │
   │ - Call PaymentIntegrationService         │
   │ - Assign apartment to tenant             │
   │ - Send emails                            │
   └──────────────────────────────────────────┘
                      ↓
   ┌──────────────────────────────────────────┐
   │ Redirect to:                             │
   │ /apartment/invite/{token}/success        │
   └──────────────────────────────────────────┘

8. SUCCESS PAGE
   ┌──────────────────────────────────────────┐
   │ Show Confirmation                        │
   │ - Payment successful                     │
   │ - Apartment assigned                     │
   │ - Emails sent to landlord & tenant       │
   │ - Next steps information                 │
   └──────────────────────────────────────────┘
```

---

## 2. DIRECT PAYMENT LINK FLOW (Proforma)

```
┌─────────────────────────────────────────────────────────────────┐
│                  DIRECT PAYMENT FLOW                            │
└─────────────────────────────────────────────────────────────────┘

1. LANDLORD CREATES PROFORMA
   ┌──────────────────────────────────────────┐
   │ POST /dashboard/apartment/{id}/send-     │
   │ profoma                                  │
   │ - Select apartment                       │
   │ - Enter payment details                  │
   │ - Calculate amount                       │
   └──────────────────────────────────────────┘
                      ↓
   ┌──────────────────────────────────────────┐
   │ Create ProformaReceipt                   │
   │ - Generate unique ID                     │
   │ - Store payment details                  │
   │ - Create payment link                    │
   └──────────────────────────────────────────┘
                      ↓
   ┌──────────────────────────────────────────┐
   │ Landlord shares link:                    │
   │ /proforma/{id}/payment                   │
   │ (via email, WhatsApp, SMS)               │
   └──────────────────────────────────────────┘

2. TENANT RECEIVES LINK
   ┌──────────────────────────────────────────┐
   │ Tenant clicks link                       │
   │ GET /proforma/{id}/payment               │
   └──────────────────────────────────────────┘
                      ↓
   ┌──────────────────────────────────────────┐
   │ Check Authentication                     │
   │ ├─ If authenticated:                     │
   │ │  Show payment form directly            │
   │ └─ If not authenticated:                 │
   │    Show login/signup buttons             │
   └──────────────────────────────────────────┘

3. AUTHENTICATION (if needed)
   ┌──────────────────────────────────────────┐
   │ POST /login or POST /register            │
   │ - Create/verify account                  │
   │ - Redirect back to proforma              │
   └──────────────────────────────────────────┘

4. PAYMENT FORM
   ┌──────────────────────────────────────────┐
   │ Display Payment Form                     │
   │ - Amount: ₦{proforma_amount}             │
   │ - Due date: {due_date}                   │
   │ - Description: {details}                 │
   │ - [Pay Now] button                       │
   └──────────────────────────────────────────┘
                      ↓
   ┌──────────────────────────────────────────┐
   │ User clicks [Pay Now]                    │
   │ POST /pay                                │
   │ - Submit payment form                    │
   │ - Redirect to Paystack                   │
   └──────────────────────────────────────────┘

5. PAYSTACK PAYMENT
   ┌──────────────────────────────────────────┐
   │ User completes payment on Paystack       │
   │ - Enter card details                     │
   │ - Verify OTP                             │
   │ - Paystack processes transaction         │
   └──────────────────────────────────────────┘

6. PAYMENT CALLBACK
   ┌──────────────────────────────────────────┐
   │ POST /payment/callback                   │
   │ - Verify transaction                     │
   │ - Create/update Payment record           │
   │ - Status → completed                     │
   │ - Send notifications                     │
   └──────────────────────────────────────────┘
                      ↓
   ┌──────────────────────────────────────────┐
   │ Redirect to:                             │
   │ /proforma/payment/success/{payment_id}   │
   └──────────────────────────────────────────┘

7. SUCCESS PAGE
   ┌──────────────────────────────────────────┐
   │ Show Confirmation                        │
   │ - Payment successful                     │
   │ - Receipt details                        │
   │ - Emails sent                            │
   │ - Payment appears in billing             │
   └──────────────────────────────────────────┘
```

---

## 3. BENEFACTOR LINK FLOW (Third-Party Payment)

```
┌─────────────────────────────────────────────────────────────────┐
│                  BENEFACTOR LINK FLOW                           │
└─────────────────────────────────────────────────────────────────┘

1. TENANT INVITES BENEFACTOR
   ┌──────────────────────────────────────────┐
   │ POST /tenant/invite-benefactor           │
   │ - Select apartment/proforma              │
   │ - Enter benefactor email                 │
   │ - Select relationship type               │
   └──────────────────────────────────────────┘
                      ↓
   ┌──────────────────────────────────────────┐
   │ Create PaymentInvitation                 │
   │ - Generate unique token                  │
   │ - Store payment details                  │
   │ - Send invitation email                  │
   └──────────────────────────────────────────┘

2. BENEFACTOR RECEIVES EMAIL
   ┌──────────────────────────────────────────┐
   │ Email contains link:                     │
   │ /benefactor/payment/{token}              │
   │ - Benefactor clicks link                 │
   └──────────────────────────────────────────┘

3. APPROVAL PAGE
   ┌──────────────────────────────────────────┐
   │ GET /benefactor/payment/{token}          │
   │ - Show payment details                   │
   │ - Show tenant information                │
   │ - [Approve] [Decline] buttons            │
   └──────────────────────────────────────────┘
                      ↓
   ┌──────────────────────────────────────────┐
   │ Benefactor clicks [Approve]              │
   │ POST /benefactor/payment/{token}/approve │
   │ - Update invitation status               │
   │ - Redirect to payment page               │
   └──────────────────────────────────────────┘

4. BENEFACTOR INFORMATION
   ┌──────────────────────────────────────────┐
   │ POST /benefactor/payment/{token}/process │
   │ - Full name                              │
   │ - Email                                  │
   │ - Phone                                  │
   │ - Relationship type                      │
   │ - Payment type (one-time/recurring)      │
   │ - If recurring: frequency & day          │
   └──────────────────────────────────────────┘
                      ↓
   ┌──────────────────────────────────────────┐
   │ Check if Guest or Registered             │
   │ ├─ Guest: Continue to payment            │
   │ ├─ Create Account: Register & continue   │
   │ └─ Existing: Update benefactor record    │
   └──────────────────────────────────────────┘

5. PAYMENT GATEWAY
   ┌──────────────────────────────────────────┐
   │ GET /benefactor/gateway/{payment_id}     │
   │ - Show payment form                      │
   │ - Amount: ₦{amount}                      │
   │ - [Pay Now] button                       │
   └──────────────────────────────────────────┘
                      ↓
   ┌──────────────────────────────────────────┐
   │ User clicks [Pay Now]                    │
   │ - Redirect to Paystack                   │
   └──────────────────────────────────────────┘

6. PAYSTACK PAYMENT
   ┌──────────────────────────────────────────┐
   │ User completes payment on Paystack       │
   │ - Enter card details                     │
   │ - Verify OTP                             │
   │ - Paystack processes transaction         │
   └──────────────────────────────────────────┘

7. PAYMENT CALLBACK
   ┌──────────────────────────────────────────┐
   │ GET /benefactor/payment/callback         │
   │ - Verify transaction with Paystack       │
   │ - Update BenefactorPayment status        │
   │ - Create Benefactor record (if new)      │
   │ - Send notifications                     │
   └──────────────────────────────────────────┘
                      ↓
   ┌──────────────────────────────────────────┐
   │ Redirect to:                             │
   │ /benefactor/payment/success/{payment_id} │
   └──────────────────────────────────────────┘

8. SUCCESS PAGE
   ┌──────────────────────────────────────────┐
   │ Show Confirmation                        │
   │ - Payment successful                     │
   │ - Benefactor info                        │
   │ - Emails sent to all parties             │
   │ - Recurring payment info (if applicable) │
   └──────────────────────────────────────────┘

9. RECURRING PAYMENT OPTIONS (if applicable)
   ┌──────────────────────────────────────────┐
   │ Benefactor can later:                    │
   │ - Pause recurring payment                │
   │ - Resume paused payment                  │
   │ - Cancel recurring payment               │
   │ - View payment history                   │
   │ (via /benefactor/dashboard)              │
   └──────────────────────────────────────────┘
```

---

## Database Relationships

```
EASYRENT LINK:
  ApartmentInvitation
    ├─ apartment_id → Apartment
    ├─ landlord_id → User
    └─ tenant_user_id → User (after payment)
  
  Payment
    ├─ apartment_id → Apartment
    ├─ tenant_id → User
    └─ landlord_id → User

DIRECT PAYMENT:
  ProformaReceipt
    ├─ apartment_id → Apartment
    └─ tenant_id → User
  
  Payment
    ├─ apartment_id → Apartment
    ├─ tenant_id → User
    └─ landlord_id → User

BENEFACTOR LINK:
  PaymentInvitation
    ├─ tenant_id → User
    ├─ proforma_id → ProformaReceipt
    └─ benefactor_id → Benefactor (after approval)
  
  Benefactor
    ├─ user_id → User (if registered)
    └─ email (unique)
  
  BenefactorPayment
    ├─ benefactor_id → Benefactor
    ├─ tenant_id → User
    └─ proforma_id → ProformaReceipt
```

---

## Key Differences Summary

| Aspect | EasyRent | Direct | Benefactor |
|--------|----------|--------|-----------|
| **Initiator** | Landlord | Landlord | Tenant |
| **Link Type** | Apartment | Proforma | Payment Invitation |
| **Guest Access** | View only | Can pay | Can pay |
| **Apartment Assignment** | Yes | No | No |
| **Recurring** | No | No | Yes |
| **Approval Step** | No | No | Yes |
| **Relationship Tracking** | No | No | Yes |

