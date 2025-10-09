# Marketer Referral System Implementation Plan

## Overview
This document outlines the comprehensive implementation plan for the EasyRent Marketer Referral System, designed to enable marketers to effectively promote the platform and earn commissions from successful landlord registrations.

## System Architecture

### 1. User Roles Enhancement
- **Role 5: Marketer** - New user type for referral marketers
- Enhanced user permissions and access levels
- Marketer-specific dashboard and features

### 2. Database Schema Enhancements

#### 2.1 Users Table Enhancements
```sql
-- Add marketer-specific fields
ALTER TABLE users ADD COLUMN marketer_status ENUM('pending', 'active', 'suspended', 'inactive') DEFAULT NULL;
ALTER TABLE users ADD COLUMN commission_rate DECIMAL(5,2) DEFAULT NULL;
ALTER TABLE users ADD COLUMN bank_account_name VARCHAR(255) DEFAULT NULL;
ALTER TABLE users ADD COLUMN bank_account_number VARCHAR(50) DEFAULT NULL;
ALTER TABLE users ADD COLUMN bank_name VARCHAR(255) DEFAULT NULL;
ALTER TABLE users ADD COLUMN bvn VARCHAR(11) DEFAULT NULL;
ALTER TABLE users ADD COLUMN referral_code VARCHAR(20) UNIQUE DEFAULT NULL;
```

#### 2.2 Enhanced Referrals Table
```sql
-- Add tracking and commission fields
ALTER TABLE referrals ADD COLUMN commission_amount DECIMAL(10,2) DEFAULT 0.00;
ALTER TABLE referrals ADD COLUMN commission_status ENUM('pending', 'approved', 'paid', 'cancelled') DEFAULT 'pending';
ALTER TABLE referrals ADD COLUMN conversion_date TIMESTAMP NULL;
ALTER TABLE referrals ADD COLUMN campaign_id VARCHAR(50) NULL;
ALTER TABLE referrals ADD COLUMN referral_source ENUM('link', 'qr_code', 'direct') DEFAULT 'link';
```

#### 2.3 New Tables

##### Marketer Profiles Table
```sql
CREATE TABLE marketer_profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    business_name VARCHAR(255),
    business_type VARCHAR(100),
    years_of_experience INT,
    preferred_commission_rate DECIMAL(5,2),
    marketing_channels TEXT,
    target_regions JSON,
    kyc_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    kyc_documents JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);
```

##### Referral Campaigns Table
```sql
CREATE TABLE referral_campaigns (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    marketer_id BIGINT UNSIGNED NOT NULL,
    campaign_name VARCHAR(255) NOT NULL,
    campaign_code VARCHAR(50) UNIQUE NOT NULL,
    qr_code_path VARCHAR(500),
    target_audience VARCHAR(255),
    start_date DATE,
    end_date DATE,
    status ENUM('active', 'paused', 'completed', 'cancelled') DEFAULT 'active',
    clicks_count INT DEFAULT 0,
    conversions_count INT DEFAULT 0,
    total_commission DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (marketer_id) REFERENCES users(user_id) ON DELETE CASCADE
);
```

##### Referral Rewards Table
```sql
CREATE TABLE referral_rewards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    marketer_id BIGINT UNSIGNED NOT NULL,
    referral_id BIGINT UNSIGNED NOT NULL,
    reward_type ENUM('commission', 'bonus', 'milestone') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    status ENUM('pending', 'approved', 'paid', 'cancelled') DEFAULT 'pending',
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (marketer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (referral_id) REFERENCES referrals(id) ON DELETE CASCADE
);
```

##### Commission Payments Table
```sql
CREATE TABLE commission_payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    marketer_id BIGINT UNSIGNED NOT NULL,
    payment_reference VARCHAR(100) UNIQUE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('bank_transfer', 'mobile_money', 'check') DEFAULT 'bank_transfer',
    payment_status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    payment_date TIMESTAMP NULL,
    processed_by BIGINT UNSIGNED NULL,
    referral_ids JSON,
    payment_details JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (marketer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(user_id) ON DELETE SET NULL
);
```

## 3. Feature Implementation Roadmap

### Phase 1: Core Infrastructure (Week 1-2)
1. **Database Migrations**
   - Create new tables and enhance existing ones
   - Add indexes for performance optimization
   - Set up foreign key constraints

2. **Model Updates**
   - Enhance User model with marketer relationships
   - Create new models: MarketerProfile, ReferralCampaign, ReferralReward, CommissionPayment
   - Add model relationships and scopes

3. **Authentication & Authorization**
   - Add marketer role to middleware
   - Create marketer-specific guards and policies
   - Implement role-based access control

### Phase 2: Marketer Management (Week 3-4)
1. **Marketer Registration**
   - Enhanced registration form for marketers
   - KYC document upload system
   - Profile verification workflow

2. **Marketer Dashboard**
   - Overview of referral performance
   - Commission tracking
   - Campaign management interface

3. **Admin Panel Enhancements**
   - Marketer approval system
   - Commission rate management
   - Performance monitoring tools

### Phase 3: Referral System Enhancement (Week 5-6)
1. **Advanced Referral Links**
   - Custom referral codes
   - Campaign-specific tracking
   - Analytics integration

2. **QR Code System**
   - QR code generation for campaigns
   - Mobile scanner integration
   - Offline tracking capabilities

3. **Commission Engine**
   - Automated commission calculation
   - Tiered commission structures
   - Bonus and milestone rewards

### Phase 4: Payment & Analytics (Week 7-8)
1. **Payment Processing**
   - Commission payment automation
   - Multiple payment methods
   - Payment history and receipts

2. **Analytics & Reporting**
   - Marketer performance dashboards
   - Conversion tracking
   - ROI analysis tools

3. **Mobile App Integration**
   - QR code scanner
   - Mobile-friendly marketer dashboard
   - Push notifications for conversions

## 4. Technical Specifications

### 4.1 Commission Structure
```php
// Default commission rates
const COMMISSION_RATES = [
    'basic' => 5.00,      // 5% of first year rent
    'premium' => 7.50,    // 7.5% of first year rent
    'elite' => 10.00,     // 10% of first year rent
];

// Milestone bonuses
const MILESTONE_BONUSES = [
    '10_referrals' => 50000,   // ₦50,000 for 10 successful referrals
    '25_referrals' => 150000,  // ₦150,000 for 25 successful referrals
    '50_referrals' => 350000,  // ₦350,000 for 50 successful referrals
];
```

### 4.2 QR Code Integration
- Generate unique QR codes for each campaign
- Include campaign tracking parameters
- Mobile-responsive landing pages
- Offline-to-online conversion tracking

### 4.3 Analytics Tracking
- Click-through rates
- Conversion rates
- Geographic performance
- Time-based analytics
- ROI calculations

## 5. Security Considerations

### 5.1 Fraud Prevention
- Implement referral validation checks
- Prevent self-referrals
- Monitor suspicious activity patterns
- Rate limiting on referral submissions

### 5.2 Data Protection
- Encrypt sensitive marketer information
- Secure payment processing
- GDPR compliance for user data
- Audit trails for all transactions

### 5.3 Access Control
- Role-based permissions
- API rate limiting
- Secure file uploads for KYC
- Two-factor authentication for marketers

## 6. Integration Points

### 6.1 Payment Gateways
- Flutterwave for bank transfers
- Paystack for mobile money
- Manual bank transfer processing
- Payment reconciliation system

### 6.2 Communication Systems
- Email notifications for referrals
- SMS alerts for conversions
- In-app notifications
- WhatsApp integration for updates

### 6.3 Analytics Platforms
- Google Analytics integration
- Custom analytics dashboard
- Export capabilities for reports
- Real-time performance monitoring

## 7. Testing Strategy

### 7.1 Unit Testing
- Model relationships and methods
- Commission calculation logic
- QR code generation and validation
- Payment processing workflows

### 7.2 Integration Testing
- End-to-end referral flow
- Payment gateway integration
- Analytics tracking verification
- Mobile app compatibility

### 7.3 User Acceptance Testing
- Marketer registration process
- Referral link functionality
- Commission payment verification
- Admin management tools

## 8. Deployment Plan

### 8.1 Database Migration
- Backup existing data
- Run migrations in staging environment
- Validate data integrity
- Deploy to production with rollback plan

### 8.2 Feature Rollout
- Soft launch with selected marketers
- Gradual feature enablement
- Monitor system performance
- Full rollout after validation

### 8.3 Monitoring & Maintenance
- Real-time error monitoring
- Performance optimization
- Regular security audits
- Feature usage analytics

---

## Implementation Timeline: 8 Weeks

**Week 1-2:** Core Infrastructure & Database
**Week 3-4:** Marketer Management & Registration
**Week 5-6:** Enhanced Referral System & QR Codes
**Week 7-8:** Payment Processing & Analytics

## Success Metrics

1. **Marketer Acquisition:** 100+ active marketers in first 3 months
2. **Referral Conversion:** 15%+ conversion rate from referrals
3. **Revenue Growth:** 25%+ increase in landlord registrations
4. **Marketer Satisfaction:** 85%+ satisfaction score
5. **System Performance:** 99.9% uptime and <2s response times

---

*This implementation plan provides a comprehensive roadmap for building a world-class marketer referral system that will drive significant growth for the EasyRent platform.*
