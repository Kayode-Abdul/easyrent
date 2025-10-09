# Super Marketer System Database Schema Documentation

## Overview

This document outlines the database schema changes implemented for the Super Marketer System, which introduces a 3-tier hierarchical commission structure with regional rate management capabilities.

## New Tables

### 1. commission_rates

Stores regional commission rates for different roles.

```sql
CREATE TABLE commission_rates (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    region VARCHAR(100) NOT NULL,
    role_id BIGINT NOT NULL,
    commission_percentage DECIMAL(5,4) NOT NULL,
    effective_from TIMESTAMP NOT NULL,
    effective_until TIMESTAMP NULL,
    created_by BIGINT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_region_role (region, role_id),
    INDEX idx_effective_dates (effective_from, effective_until),
    INDEX idx_active_rates (is_active, effective_from),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE CASCADE
);
```

**Purpose**: Manages commission rates by region and role, supporting historical rate tracking and bulk updates.

### 2. referral_chains

Tracks multi-tier referral relationships for commission distribution.

```sql
CREATE TABLE referral_chains (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    super_marketer_id BIGINT NULL,
    marketer_id BIGINT NULL,
    landlord_id BIGINT NOT NULL,
    chain_hash VARCHAR(64) UNIQUE NOT NULL,
    status ENUM('active', 'completed', 'broken', 'suspended') DEFAULT 'active',
    commission_breakdown JSON NULL,
    total_commission_percentage DECIMAL(5,4) NULL,
    region VARCHAR(100) NULL,
    activated_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_super_marketer (super_marketer_id),
    INDEX idx_marketer (marketer_id),
    INDEX idx_landlord (landlord_id),
    INDEX idx_chain_status (status),
    INDEX idx_chain_region (region),
    INDEX idx_active_chains (status, activated_at),
    UNIQUE KEY unique_referral_chain (super_marketer_id, marketer_id, landlord_id),
    FOREIGN KEY (super_marketer_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (marketer_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (landlord_id) REFERENCES users(user_id) ON DELETE CASCADE
);
```

**Purpose**: Maintains referral chain integrity and stores commission calculation results.

## Table Extensions

### 1. roles Table

Added Super Marketer role (ID: 9) with specific permissions:

```sql
INSERT INTO roles (id, name, display_name, description, is_active, permissions) VALUES (
    9,
    'super_marketer',
    'Super Marketer',
    'Top-tier marketer who can refer other marketers',
    TRUE,
    JSON_ARRAY('refer_marketers', 'view_referral_analytics', 'manage_referral_campaigns', 'view_commission_breakdown')
);
```

### 2. referrals Table Extensions

Added hierarchy tracking fields:

```sql
ALTER TABLE referrals ADD COLUMN (
    referral_level TINYINT DEFAULT 1,
    parent_referral_id BIGINT NULL,
    commission_tier ENUM('super_marketer', 'marketer', 'direct') DEFAULT 'direct',
    regional_rate_snapshot JSON NULL,
    referral_code VARCHAR(50) NULL,
    referral_status ENUM('pending', 'active', 'completed', 'cancelled') DEFAULT 'pending',
    
    INDEX idx_referral_level (referral_level),
    INDEX idx_parent_referral (parent_referral_id),
    INDEX idx_commission_tier (commission_tier),
    INDEX idx_referral_status (referral_status),
    INDEX idx_referral_code (referral_code),
    FOREIGN KEY (parent_referral_id) REFERENCES referrals(id) ON DELETE SET NULL
);
```

### 3. commission_payments Table Extensions

Added multi-tier payment tracking:

```sql
ALTER TABLE commission_payments ADD COLUMN (
    referral_chain_id BIGINT NULL,
    commission_tier ENUM('super_marketer', 'marketer', 'regional_manager') NOT NULL,
    parent_payment_id BIGINT NULL,
    regional_rate_applied DECIMAL(5,4) NOT NULL,
    region VARCHAR(100) NULL,
    
    INDEX idx_commission_tier (commission_tier),
    INDEX idx_referral_chain (referral_chain_id),
    INDEX idx_parent_payment (parent_payment_id),
    INDEX idx_payment_region (region),
    FOREIGN KEY (parent_payment_id) REFERENCES commission_payments(id) ON DELETE SET NULL,
    FOREIGN KEY (referral_chain_id) REFERENCES referral_chains(id) ON DELETE SET NULL
);
```

## Migration Files

The following migration files implement these schema changes:

1. `2025_11_09_000001_create_commission_rates_table.php`
2. `2025_11_09_000002_add_super_marketer_role.php`
3. `2025_11_09_000003_extend_referrals_table_for_hierarchy.php`
4. `2025_11_09_000004_create_referral_chains_table.php`
5. `2025_11_09_000005_extend_commission_payments_for_hierarchy.php`

## Seeders

### SuperMarketerSystemSeeder

Seeds initial commission rates for different regions:

- **Lagos**: Super Marketer (0.75%), Marketer (1.0%), Regional Manager (0.75%)
- **Abuja**: Super Marketer (0.8%), Marketer (0.9%), Regional Manager (0.8%)
- **Port Harcourt**: Super Marketer (0.7%), Marketer (1.1%), Regional Manager (0.7%)
- **Default**: Super Marketer (0.75%), Marketer (1.0%), Regional Manager (0.75%)

## Key Features

### 1. Regional Rate Management
- Commission rates can be configured by region and role
- Historical rate tracking with effective date ranges
- Validation ensures total rates don't exceed 2.5% threshold

### 2. Hierarchical Referral Tracking
- Support for 3-tier referral chains: Super Marketer → Marketer → Landlord
- Referral chain integrity validation
- Commission tier identification for proper distribution

### 3. Multi-Tier Commission Distribution
- Automatic commission calculation based on referral chains
- Regional rate application with snapshot storage
- Parent-child payment relationship tracking

### 4. Fraud Prevention
- Unique referral chain constraints
- Chain hash integrity verification
- Circular referral prevention through validation

## Validation

Use the `validate_super_marketer_schema.php` script to verify schema implementation:

```bash
php validate_super_marketer_schema.php
```

This script checks:
- Table existence and structure
- Column presence and types
- Role creation and permissions
- Index and constraint implementation

## Requirements Satisfied

This schema implementation satisfies the following requirements:

- **Requirement 2.1**: Regional commission rate configuration
- **Requirement 2.2**: Commission rate validation and management
- **Requirement 5.1**: Multi-tier commission calculation and distribution

The database schema provides the foundation for implementing the complete Super Marketer System with proper data integrity, performance optimization, and fraud prevention capabilities.