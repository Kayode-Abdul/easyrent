# EasyRent Commission Flow Documentation

## 📋 **Overview**

The EasyRent platform implements a comprehensive commission system for **Marketers** and **Regional Managers** to incentivize user acquisition and property management. This document outlines the complete flow from referral to payment.

---

## 🎯 **Commission Structure**

### **Marketer Commission Rates**
- **Basic Tier**: 5% of first year rent
- **Premium Tier**: 7.5% of first year rent  
- **Elite Tier**: 10% of first year rent

### **Milestone Bonuses**
- **10 Referrals**: ₦50,000 bonus
- **25 Referrals**: ₦150,000 bonus
- **50 Referrals**: ₦350,000 bonus

### **Regional Manager Commission**
- **Property Approval Fee**: ₦5,000 per approved property
- **Monthly Management Fee**: 2% of regional revenue
- **Performance Bonus**: Based on regional growth metrics

---

## 🔄 **Complete Commission Flow**

### **Phase 1: Marketer Registration & Approval**

```
1. User Registration as Marketer (Role 5)
   ↓
2. Complete Marketer Profile Creation
   - Business details
   - KYC document upload
   - Preferred commission rate
   - Target regions
   ↓
3. Admin Review & Approval
   - KYC verification
   - Business validation
   - Commission rate assignment
   ↓
4. Marketer Activation
   - Status: 'active'
   - Referral code generation
   - Access to dashboard
```

### **Phase 2: Campaign Creation & Referral Generation**

```
1. Marketer Creates Campaign
   - Campaign name & description
   - Target audience
   - Date range (optional)
   ↓
2. System Generates Campaign Assets
   - Unique campaign code
   - Referral links
   - QR codes for offline marketing
   ↓
3. Marketer Promotes Campaign
   - Share referral links
   - Distribute QR codes
   - Track clicks & engagement
```

### **Phase 3: Referral Tracking & Conversion**

```
1. User Clicks Referral Link
   - Campaign click tracked
   - User session tagged with referrer
   ↓
2. User Registers as Landlord (Role 2)
   - Referral record created
   - Status: 'pending'
   - Commission calculation pending
   ↓
3. Landlord Lists First Property
   - Property creation triggers conversion
   - Referral status: 'converted'
   - Commission calculation initiated
```

### **Phase 4: Commission Calculation & Reward Creation**

```
1. System Calculates Commission
   - Base rate: Marketer's commission_rate
   - Amount: (Property rent × Duration × Rate)
   - Milestone bonus check
   ↓
2. ReferralReward Record Created
   - Marketer ID
   - Referral ID  
   - Reward type: 'commission'
   - Amount calculated
   - Status: 'pending'
   ↓
3. Admin Notification Sent
   - New commission pending approval
   - Marketer performance update
```

### **Phase 5: Admin Review & Approval**

```
1. Admin Reviews Commission
   - Validates referral authenticity
   - Checks property legitimacy
   - Verifies commission calculation
   ↓
2. Admin Decision
   ├── APPROVE
   │   - Status: 'approved'
   │   - Available for payment
   │   - Marketer notification
   └── REJECT
       - Status: 'cancelled'
       - Rejection reason logged
       - Marketer notification
```

### **Phase 6: Payment Request & Processing**

```
1. Marketer Requests Payment
   - Minimum: ₦1,000
   - Payment method selection
   - Bank details verification
   ↓
2. CommissionPayment Created
   - Payment reference generated
   - Status: 'pending'
   - Admin notification
   ↓
3. Admin Processes Payment
   - Status: 'processing'
   - External payment initiation
   ↓
4. Payment Completion
   - Status: 'completed'
   - Transaction ID recorded
   - Related rewards marked as 'paid'
   - Marketer notification
```

---

## 🏢 **Regional Manager Flow**

### **Property Approval Commission**

```
1. Property Submitted in Region
   - Regional Manager notification
   - Property status: 'pending'
   ↓
2. Regional Manager Reviews
   ├── APPROVE
   │   - Property status: 'approved'
   │   - RM earns ₦5,000 approval fee
   │   - ReferralReward created
   └── REJECT
       - Property status: 'rejected'
       - Rejection reason required
       - No commission earned
   ↓
3. Monthly Revenue Commission
   - Calculate 2% of regional revenue
   - Create monthly commission reward
   - Auto-approve if targets met
```

---

## 💾 **Database Schema Flow**

### **Key Tables & Relationships**

```sql
-- User registration with marketer role
users (user_id, role=5, marketer_status, commission_rate, referral_code)
  ↓
-- Marketer profile creation
marketer_profiles (user_id, kyc_status, preferred_commission_rate)
  ↓
-- Campaign creation
referral_campaigns (marketer_id, campaign_code, qr_code_path)
  ↓
-- Referral tracking
referrals (referrer_id, referred_id, campaign_id, commission_status)
  ↓
-- Commission calculation
referral_rewards (marketer_id, referral_id, amount, status)
  ↓
-- Payment processing
commission_payments (marketer_id, total_amount, payment_status)
```

### **Status Transitions**

#### **Marketer Status Flow**
```
pending → active → suspended → active
        ↘ rejected
```

#### **Referral Status Flow**
```
pending → converted → commission_calculated
                   ↓
approved → paid
↓
cancelled
```

#### **Reward Status Flow**
```
pending → approved → paid
        ↘ cancelled
```

#### **Payment Status Flow**
```
pending → processing → completed
                    ↘ failed
```

---

## 🔧 **Technical Implementation**

### **Commission Calculation Logic**

```php
// Basic commission calculation
$commissionAmount = ($propertyRent * $leaseDuration * $marketerRate) / 100;

// Milestone bonus check
$totalReferrals = $marketer->referrals()->count();
$milestoneBonus = 0;

if ($totalReferrals == 10) $milestoneBonus = 50000;
if ($totalReferrals == 25) $milestoneBonus = 150000;  
if ($totalReferrals == 50) $milestoneBonus = 350000;

$totalReward = $commissionAmount + $milestoneBonus;
```

### **Automated Triggers**

1. **Property Creation Trigger**
   ```php
   // When landlord creates first property
   Property::created(function ($property) {
       $this->processReferralConversion($property->user_id);
   });
   ```

2. **Payment Completion Trigger**
   ```php
   // When tenant makes first payment
   Payment::created(function ($payment) {
       $this->calculateFinalCommission($payment);
   });
   ```

### **Notification System**

```php
// Commission approval notification
NotificationController::sendPushNotification(
    $marketer->user_id,
    'Commission Approved',
    "₦{$amount} commission approved for referral #{$referralId}",
    ['type' => 'commission_approved', 'amount' => $amount]
);
```

---

## 📊 **Analytics & Reporting**

### **Marketer Dashboard Metrics**
- Total referrals count
- Successful conversions
- Total commission earned
- Pending commission amount
- Conversion rate percentage
- Campaign performance data

### **Admin Analytics**
- Total marketers by status
- Commission payout trends
- Top performing marketers
- Regional performance comparison
- ROI analysis per marketer

### **Regional Manager Metrics**
- Properties approved/rejected
- Regional revenue growth
- Marketer performance in region
- Commission earnings breakdown

---

## 🔒 **Security & Fraud Prevention**

### **Validation Checks**
1. **Referral Authenticity**
   - IP address tracking
   - User agent validation
   - Time-based conversion limits

2. **Commission Validation**
   - Property legitimacy verification
   - Rent amount validation
   - Duplicate referral prevention

3. **Payment Security**
   - Bank account verification
   - Transaction ID validation
   - Payment reconciliation

### **Audit Trail**
- All commission calculations logged
- Status changes tracked with timestamps
- Admin actions recorded with user IDs
- Payment transactions fully auditable

---

## 🚀 **API Endpoints**

### **Marketer APIs**
```
GET /marketer/dashboard - Dashboard data
GET /marketer/referrals - Referral list with filters
GET /marketer/campaigns - Campaign management
POST /marketer/payments/request - Request payment
GET /marketer/analytics - Performance analytics
```

### **Admin APIs**
```
GET /admin/marketers - Marketer management
POST /admin/marketers/{id}/approve - Approve marketer
POST /admin/rewards/{id}/approve - Approve commission
GET /admin/payments - Payment management
POST /admin/payments/{id}/process - Process payment
```

---

## 📈 **Performance Optimization**

### **Caching Strategy**
- Marketer statistics cached for 5 minutes
- Commission calculations cached until status change
- Dashboard metrics cached with Redis

### **Database Indexing**
```sql
-- Performance indexes
CREATE INDEX idx_referrals_marketer_status ON referrals(referrer_id, commission_status);
CREATE INDEX idx_rewards_marketer_status ON referral_rewards(marketer_id, status);
CREATE INDEX idx_payments_status_date ON commission_payments(payment_status, created_at);
```

---

## 🎯 **Success Metrics**

### **Platform KPIs**
- **Marketer Acquisition**: 100+ active marketers
- **Referral Conversion**: 15%+ conversion rate
- **Revenue Growth**: 25%+ increase via referrals
- **Payment Processing**: <24 hour payment processing
- **Marketer Satisfaction**: 85%+ satisfaction score

### **Commission Efficiency**
- **Approval Time**: <48 hours for commission approval
- **Payment Time**: <7 days from request to completion
- **Error Rate**: <1% commission calculation errors
- **Fraud Rate**: <0.1% fraudulent referrals

---

This comprehensive commission system ensures fair compensation for marketers and regional managers while maintaining platform integrity and growth objectives.