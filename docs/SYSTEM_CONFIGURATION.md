# Super Marketer System Configuration Guide

This guide provides detailed configuration instructions for the Super Marketer System.

## Table of Contents

1. [Environment Configuration](#environment-configuration)
2. [Database Configuration](#database-configuration)
3. [Commission Rate Configuration](#commission-rate-configuration)
4. [Regional Settings](#regional-settings)
5. [Security Configuration](#security-configuration)
6. [Performance Configuration](#performance-configuration)
7. [Monitoring Configuration](#monitoring-configuration)
8. [Backup Configuration](#backup-configuration)

## Environment Configuration

### Required Environment Variables

Create or update your `.env` file with the following Super Marketer System specific configurations:

```env
# Super Marketer System Configuration
SUPER_MARKETER_ENABLED=true
SUPER_MARKETER_MAX_TIERS=3
SUPER_MARKETER_DEFAULT_RATE=0.008
MARKETER_DEFAULT_RATE=0.012
REGIONAL_MANAGER_DEFAULT_RATE=0.005
MAX_COMMISSION_PERCENTAGE=0.025

# Commission Calculation Settings
COMMISSION_CALCULATION_PRECISION=4
COMMISSION_ROUNDING_MODE=half_up
ENABLE_COMMISSION_AUDIT=true
COMMISSION_AUDIT_RETENTION_DAYS=365

# Fraud Detection Settings
FRAUD_DETECTION_ENABLED=true
CIRCULAR_REFERRAL_CHECK=true
SUSPICIOUS_PATTERN_THRESHOLD=5
FRAUD_ALERT_EMAIL=admin@easyrent.com

# Regional Rate Management
REGIONAL_RATES_ENABLED=true
DEFAULT_REGION=Lagos
RATE_CHANGE_NOTIFICATION=true
RATE_HISTORY_RETENTION_MONTHS=24

# Payment Processing
PAYMENT_PROCESSING_ENABLED=true
PAYMENT_BATCH_SIZE=100
PAYMENT_RETRY_ATTEMPTS=3
PAYMENT_FAILURE_NOTIFICATION=true

# Performance Monitoring
PERFORMANCE_MONITORING_ENABLED=true
SLOW_QUERY_THRESHOLD=1000
MEMORY_USAGE_ALERT_THRESHOLD=80
CPU_USAGE_ALERT_THRESHOLD=75

# Cache Configuration
COMMISSION_RATE_CACHE_TTL=3600
REFERRAL_CHAIN_CACHE_TTL=1800
PERFORMANCE_METRICS_CACHE_TTL=300
```

### Application-Specific Settings

Add to `config/app.php`:

```php
'super_marketer' => [
    'enabled' => env('SUPER_MARKETER_ENABLED', true),
    'max_tiers' => env('SUPER_MARKETER_MAX_TIERS', 3),
    'commission_precision' => env('COMMISSION_CALCULATION_PRECISION', 4),
    'max_commission' => env('MAX_COMMISSION_PERCENTAGE', 0.025),
],
```

## Database Configuration

### Required Tables

Ensure the following tables exist and are properly configured:

#### Commission Rates Table

```sql
CREATE TABLE commission_rates (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    region VARCHAR(100) NOT NULL,
    role_id INT NOT NULL,
    commission_percentage DECIMAL(5,4) NOT NULL,
    effective_from TIMESTAMP NOT NULL,
    effective_until TIMESTAMP NULL,
    created_by BIGINT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_region_role (region, role_id),
    INDEX idx_effective_dates (effective_from, effective_until),
    INDEX idx_active (is_active),
    FOREIGN KEY (created_by) REFERENCES users(user_id),
    FOREIGN KEY (role_id) REFERENCES roles(id)
);
```

#### Referral Chains Table

```sql
CREATE TABLE referral_chains (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    super_marketer_id BIGINT NULL,
    marketer_id BIGINT NOT NULL,
    landlord_id BIGINT NOT NULL,
    chain_hash VARCHAR(64) NOT NULL UNIQUE,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_super_marketer (super_marketer_id),
    INDEX idx_marketer (marketer_id),
    INDEX idx_landlord (landlord_id),
    INDEX idx_status (status),
    INDEX idx_chain_hash (chain_hash),
    FOREIGN KEY (super_marketer_id) REFERENCES users(user_id),
    FOREIGN KEY (marketer_id) REFERENCES users(user_id),
    FOREIGN KEY (landlord_id) REFERENCES users(user_id)
);
```

#### Extended Commission Payments Table

```sql
ALTER TABLE commission_payments ADD COLUMN (
    referral_chain_id BIGINT NULL,
    commission_tier ENUM('super_marketer', 'marketer', 'regional_manager') NOT NULL,
    parent_payment_id BIGINT NULL,
    regional_rate_applied DECIMAL(5,4) NOT NULL,
    calculation_metadata JSON NULL,
    
    INDEX idx_commission_tier (commission_tier),
    INDEX idx_referral_chain (referral_chain_id),
    INDEX idx_parent_payment (parent_payment_id),
    FOREIGN KEY (referral_chain_id) REFERENCES referral_chains(id),
    FOREIGN KEY (parent_payment_id) REFERENCES commission_payments(id)
);
```

### Database Indexes

Create additional indexes for performance:

```sql
-- Performance indexes for commission calculations
CREATE INDEX idx_users_region_role ON users(region, role_id);
CREATE INDEX idx_referrals_chain ON referrals(referrer_id, referred_id, status);
CREATE INDEX idx_payments_user_tier ON commission_payments(user_id, commission_tier, created_at);

-- Indexes for fraud detection
CREATE INDEX idx_referrals_fraud ON referrals(referrer_id, created_at, status);
CREATE INDEX idx_users_fraud_flags ON users(fraud_score, is_suspended, created_at);
```

## Commission Rate Configuration

### Default Regional Rates

Set up default commission rates for different regions:

```php
// Run this seeder to set up default rates
php artisan db:seed --class=DefaultCommissionRatesSeeder
```

### Rate Configuration Examples

#### Lagos Region Rates

```sql
INSERT INTO commission_rates (region, role_id, commission_percentage, effective_from, created_by, is_active) VALUES
('Lagos', 9, 0.008, NOW(), 1, TRUE),  -- Super Marketer: 0.8%
('Lagos', 7, 0.012, NOW(), 1, TRUE),  -- Marketer: 1.2%
('Lagos', 8, 0.005, NOW(), 1, TRUE);  -- Regional Manager: 0.5%
```

#### Abuja Region Rates

```sql
INSERT INTO commission_rates (region, role_id, commission_percentage, effective_from, created_by, is_active) VALUES
('Abuja', 9, 0.009, NOW(), 1, TRUE),  -- Super Marketer: 0.9%
('Abuja', 7, 0.011, NOW(), 1, TRUE),  -- Marketer: 1.1%
('Abuja', 8, 0.005, NOW(), 1, TRUE);  -- Regional Manager: 0.5%
```

### Rate Validation Rules

Configure validation rules in `config/commission.php`:

```php
return [
    'validation' => [
        'max_total_percentage' => 0.025, // 2.5%
        'min_rate' => 0.001, // 0.1%
        'max_rate' => 0.020, // 2.0%
        'required_roles' => [7, 8, 9], // Marketer, Regional Manager, Super Marketer
    ],
    
    'defaults' => [
        'super_marketer_rate' => 0.008,
        'marketer_rate' => 0.012,
        'regional_manager_rate' => 0.005,
    ],
];
```

## Regional Settings

### Supported Regions

Configure supported regions in `config/regions.php`:

```php
return [
    'supported_regions' => [
        'Lagos' => [
            'name' => 'Lagos State',
            'currency' => 'NGN',
            'timezone' => 'Africa/Lagos',
            'default_rates' => [
                9 => 0.008, // Super Marketer
                7 => 0.012, // Marketer
                8 => 0.005, // Regional Manager
            ],
        ],
        'Abuja' => [
            'name' => 'Federal Capital Territory',
            'currency' => 'NGN',
            'timezone' => 'Africa/Lagos',
            'default_rates' => [
                9 => 0.009,
                7 => 0.011,
                8 => 0.005,
            ],
        ],
        // Add more regions as needed
    ],
    
    'default_region' => 'Lagos',
    'fallback_rates' => [
        9 => 0.008,
        7 => 0.012,
        8 => 0.005,
    ],
];
```

## Security Configuration

### Role-Based Access Control

Configure permissions in `config/permissions.php`:

```php
return [
    'super_marketer' => [
        'dashboard.view',
        'referrals.create',
        'referrals.view',
        'marketers.view',
        'commissions.view',
        'analytics.view',
    ],
    
    'marketer' => [
        'dashboard.view',
        'referrals.create',
        'referrals.view',
        'commissions.view',
        'hierarchy.view',
    ],
    
    'regional_manager' => [
        'dashboard.view',
        'analytics.view',
        'analytics.regional',
        'commissions.view',
        'commissions.regional',
        'reports.generate',
    ],
    
    'admin' => [
        'commission_rates.manage',
        'users.manage',
        'system.configure',
        'audit.view',
        'fraud.manage',
    ],
];
```

### Fraud Detection Configuration

Configure fraud detection settings:

```php
// config/fraud.php
return [
    'detection' => [
        'enabled' => env('FRAUD_DETECTION_ENABLED', true),
        'circular_referral_check' => env('CIRCULAR_REFERRAL_CHECK', true),
        'suspicious_pattern_threshold' => env('SUSPICIOUS_PATTERN_THRESHOLD', 5),
        'max_referrals_per_day' => 10,
        'max_referrals_per_month' => 100,
    ],
    
    'alerts' => [
        'email' => env('FRAUD_ALERT_EMAIL', 'admin@easyrent.com'),
        'slack_webhook' => env('FRAUD_ALERT_SLACK_WEBHOOK'),
        'sms_enabled' => env('FRAUD_ALERT_SMS', false),
    ],
    
    'actions' => [
        'auto_suspend' => false,
        'require_manual_review' => true,
        'flag_for_investigation' => true,
    ],
];
```

## Performance Configuration

### Caching Configuration

Configure caching for optimal performance:

```php
// config/cache.php - Add custom cache stores
'stores' => [
    'commission_rates' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'prefix' => 'commission_rates',
    ],
    
    'referral_chains' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'prefix' => 'referral_chains',
    ],
],
```

### Queue Configuration

Set up queues for commission processing:

```php
// config/queue.php
'connections' => [
    'commission_processing' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'commission_processing',
        'retry_after' => 300,
        'block_for' => null,
    ],
    
    'payment_distribution' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'payment_distribution',
        'retry_after' => 600,
        'block_for' => null,
    ],
],
```

### Database Connection Optimization

Optimize database connections:

```php
// config/database.php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'unix_socket' => env('DB_SOCKET', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'prefix_indexes' => true,
    'strict' => true,
    'engine' => null,
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
        PDO::ATTR_PERSISTENT => true,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,
    ]) : [],
],
```

## Monitoring Configuration

### Application Monitoring

Configure monitoring in `config/monitoring.php`:

```php
return [
    'performance' => [
        'enabled' => env('PERFORMANCE_MONITORING_ENABLED', true),
        'slow_query_threshold' => env('SLOW_QUERY_THRESHOLD', 1000),
        'memory_threshold' => env('MEMORY_USAGE_ALERT_THRESHOLD', 80),
        'cpu_threshold' => env('CPU_USAGE_ALERT_THRESHOLD', 75),
    ],
    
    'commission_health' => [
        'check_interval' => 300, // 5 minutes
        'error_threshold' => 5,
        'alert_channels' => ['email', 'slack'],
    ],
    
    'fraud_monitoring' => [
        'real_time_alerts' => true,
        'batch_analysis_interval' => 3600, // 1 hour
        'retention_days' => 90,
    ],
];
```

### Logging Configuration

Configure comprehensive logging:

```php
// config/logging.php
'channels' => [
    'commission' => [
        'driver' => 'daily',
        'path' => storage_path('logs/commission.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 30,
    ],
    
    'fraud' => [
        'driver' => 'daily',
        'path' => storage_path('logs/fraud.log'),
        'level' => env('LOG_LEVEL', 'info'),
        'days' => 90,
    ],
    
    'performance' => [
        'driver' => 'daily',
        'path' => storage_path('logs/performance.log'),
        'level' => env('LOG_LEVEL', 'warning'),
        'days' => 14,
    ],
],
```

## Backup Configuration

### Database Backup

Configure automated database backups:

```bash
# Add to crontab
0 2 * * * /usr/local/bin/backup_database.sh

# backup_database.sh
#!/bin/bash
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/easyrent/database"
DB_NAME="easyrent"
DB_USER="backup_user"
DB_PASS="secure_password"

mkdir -p "$BACKUP_DIR"
mysqldump -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip > "$BACKUP_DIR/backup_$TIMESTAMP.sql.gz"

# Keep only last 30 days of backups
find "$BACKUP_DIR" -name "backup_*.sql.gz" -mtime +30 -delete
```

### Application Backup

Configure application file backups:

```bash
# Add to crontab
0 3 * * 0 /usr/local/bin/backup_application.sh

# backup_application.sh
#!/bin/bash
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/easyrent/application"
APP_DIR="/var/www/easyrent"

mkdir -p "$BACKUP_DIR"
tar -czf "$BACKUP_DIR/app_backup_$TIMESTAMP.tar.gz" \
    --exclude=node_modules \
    --exclude=vendor \
    --exclude=storage/logs \
    --exclude=storage/framework/cache \
    -C "$APP_DIR" .

# Keep only last 4 weekly backups
find "$BACKUP_DIR" -name "app_backup_*.tar.gz" -mtime +28 -delete
```

## Service Configuration

### Queue Workers

Configure queue workers for commission processing:

```bash
# /etc/supervisor/conf.d/easyrent-workers.conf
[program:easyrent-commission-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/easyrent/artisan queue:work redis --queue=commission_processing --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/easyrent/commission-worker.log
stopwaitsecs=3600

[program:easyrent-payment-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/easyrent/artisan queue:work redis --queue=payment_distribution --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/easyrent/payment-worker.log
stopwaitsecs=3600
```

### Scheduled Tasks

Configure Laravel scheduler:

```bash
# Add to crontab for www-data user
* * * * * cd /var/www/easyrent && php artisan schedule:run >> /dev/null 2>&1
```

## Testing Configuration

### Test Environment Setup

Configure test environment in `.env.testing`:

```env
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:

SUPER_MARKETER_ENABLED=true
FRAUD_DETECTION_ENABLED=false
PERFORMANCE_MONITORING_ENABLED=false
COMMISSION_AUDIT_RETENTION_DAYS=30

CACHE_DRIVER=array
QUEUE_CONNECTION=sync
SESSION_DRIVER=array
```

### Test Data Seeding

Create test data seeders:

```php
// database/seeders/TestSuperMarketerSeeder.php
class TestSuperMarketerSeeder extends Seeder
{
    public function run()
    {
        // Create test roles
        Role::create(['id' => 9, 'name' => 'Super Marketer']);
        Role::create(['id' => 7, 'name' => 'Marketer']);
        Role::create(['id' => 8, 'name' => 'Regional Manager']);
        
        // Create test commission rates
        CommissionRate::create([
            'region' => 'TestRegion',
            'role_id' => 9,
            'commission_percentage' => 0.008,
            'effective_from' => now(),
            'created_by' => 1,
            'is_active' => true
        ]);
        
        // Create test users and referral chains
        // ... additional test data
    }
}
```

## Deployment Configuration

### Production Environment

Ensure these settings for production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Security
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict

# Performance
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Monitoring
LOG_LEVEL=warning
PERFORMANCE_MONITORING_ENABLED=true
FRAUD_DETECTION_ENABLED=true
```

### Web Server Configuration

#### Nginx Configuration

```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    root /var/www/easyrent/public;
    index index.php;

    # SSL configuration
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # PHP-FPM configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    # Static file caching
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

---

**Configuration Checklist:**

- [ ] Environment variables configured
- [ ] Database tables created and indexed
- [ ] Commission rates set up for all regions
- [ ] Security permissions configured
- [ ] Caching and queues configured
- [ ] Monitoring and logging set up
- [ ] Backup procedures implemented
- [ ] Queue workers running
- [ ] Scheduled tasks configured
- [ ] Web server optimized
- [ ] SSL certificates installed
- [ ] Performance monitoring active

**Last Updated**: December 2024  
**Version**: 1.0  
**Maintained by**: EasyRent DevOps Team