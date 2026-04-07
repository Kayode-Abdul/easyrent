# EasyRent Shared Hosting Optimization Guide

## Overview

This guide provides detailed optimization strategies for running EasyRent on shared hosting environments. Shared hosting has resource constraints, so optimization is critical for performance and reliability.

---

## 1. Memory Optimization

### Problem
Shared hosting typically limits PHP memory to 128MB, which may be insufficient for Laravel.

### Solutions

#### 1.1 Increase Memory Limit
**File: `public/index.php`** (Add before Laravel bootstrap)
```php
<?php
// Increase memory limit for shared hosting
if (php_sapi_name() !== 'cli') {
    ini_set('memory_limit', '256M');
}

// ... rest of index.php
```

#### 1.2 Disable Heavy Middleware
**File: `app/Http/Kernel.php`**
```php
protected $middleware = [
    // Remove these for shared hosting:
    // \App\Http\Middleware\EasyRentPerformanceMonitoring::class,
    // \App\Http\Middleware\PaymentCalculationPerformanceMiddleware::class,
];
```

#### 1.3 Lazy Load Service Providers
**File: `config/app.php`**
```php
'providers' => [
    // Only load essential providers
    // Comment out:
    // App\Providers\PaymentCalculationServiceProvider::class,
    // App\Providers\EnhancedRentalCalculationServiceProvider::class,
],
```

#### 1.4 Optimize Autoloader
```bash
composer install --no-dev --optimize-autoloader --classmap-authoritative
```

---

## 2. Database Optimization

### Problem
Shared hosting databases are often slow and have connection limits.

### Solutions

#### 2.1 Connection Pooling
**File: `config/database.php`**
```php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', 'localhost'),
    'port' => env('DB_PORT', 3306),
    'database' => env('DB_DATABASE'),
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => false,  // Important for shared hosting
    'engine' => null,
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
    ]) : [],
],
```

#### 2.2 Query Optimization
- Use eager loading: `with()` instead of lazy loading
- Use `select()` to fetch only needed columns
- Add database indexes on frequently queried columns
- Use pagination for large result sets

Example:
```php
// Bad - N+1 query problem
$apartments = Apartment::all();
foreach ($apartments as $apt) {
    echo $apt->property->name; // Extra query per apartment
}

// Good - Eager loading
$apartments = Apartment::with('property')->get();
foreach ($apartments as $apt) {
    echo $apt->property->name; // No extra queries
}
```

#### 2.3 Database Indexes
Ensure these columns are indexed:
```sql
-- Apartments table
ALTER TABLE apartments ADD INDEX idx_property_id (property_id);
ALTER TABLE apartments ADD INDEX idx_apartment_id (apartment_id);

-- Payments table
ALTER TABLE payments ADD INDEX idx_tenant_id (tenant_id);
ALTER TABLE payments ADD INDEX idx_apartment_id (apartment_id);
ALTER TABLE payments ADD INDEX idx_status (status);

-- Users table
ALTER TABLE users ADD INDEX idx_email (email);
ALTER TABLE users ADD INDEX idx_role_id (role_id);

-- Apartment Invitations table
ALTER TABLE apartment_invitations ADD INDEX idx_apartment_id (apartment_id);
ALTER TABLE apartment_invitations ADD INDEX idx_token (token);
```

#### 2.4 Disable Strict Mode
**File: `config/database.php`**
```php
'strict' => false,  // Shared hosting compatibility
```

---

## 3. Caching Strategy

### Problem
Shared hosting has limited resources, so caching is essential.

### Solutions

#### 3.1 File-Based Caching
**File: `.env`**
```env
CACHE_DRIVER=file
SESSION_DRIVER=file
```

#### 3.2 Cache Configuration
**File: `config/cache.php`**
```php
'default' => env('CACHE_DRIVER', 'file'),

'stores' => [
    'file' => [
        'driver' => 'file',
        'path' => storage_path('framework/cache/data'),
    ],
],

'ttl' => 60,  // Cache for 60 seconds
```

#### 3.3 Cache Warming
Pre-cache frequently accessed data:
```php
// In a scheduled command
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 3.4 Payment Calculation Caching
**File: `.env`**
```env
PAYMENT_CALC_ENABLE_CACHING=true
PAYMENT_CALC_CACHE_TTL=60
PAYMENT_CALC_ENABLE_BULK_CACHING=true
PAYMENT_CALC_BULK_CACHE_TTL=30
```

---

## 4. Logging Optimization

### Problem
Excessive logging consumes disk space and I/O.

### Solutions

#### 4.1 Single Log File
**File: `.env`**
```env
LOG_CHANNEL=single
LOG_LEVEL=error
```

#### 4.2 Log Rotation
**File: `config/logging.php`**
```php
'single' => [
    'driver' => 'single',
    'path' => storage_path('logs/laravel.log'),
    'level' => env('LOG_LEVEL', 'error'),
],
```

#### 4.3 Disable Verbose Logging
**File: `.env`**
```env
PAYMENT_CALC_ENABLE_LOGGING=false
PAYMENT_CALC_ENABLE_PERFORMANCE_MONITORING=false
CALC_LOG_SECURITY_EVENTS=false
CALC_LOG_RATE_LIMIT_VIOLATIONS=false
```

#### 4.4 Regular Log Cleanup
Add to cron jobs:
```bash
# Clear logs older than 30 days
find storage/logs -name "*.log" -mtime +30 -delete
```

---

## 5. Asset Optimization

### Problem
Large assets slow down page load times.

### Solutions

#### 5.1 Minify Assets
```bash
npm run build  # Builds minified assets
```

#### 5.2 Enable Gzip Compression
**File: `public/.htaccess`**
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

#### 5.3 Browser Caching
**File: `public/.htaccess`**
```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType text/javascript "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresDefault "access plus 2 days"
</IfModule>
```

#### 5.4 CDN for Static Assets (Optional)
Consider using a CDN for:
- CSS files
- JavaScript files
- Images
- Fonts

---

## 6. Queue Optimization

### Problem
Shared hosting doesn't support background workers.

### Solution
Use synchronous queue:
**File: `.env`**
```env
QUEUE_CONNECTION=sync
```

This processes jobs immediately instead of queuing them.

---

## 7. Session Optimization

### Problem
Session files can accumulate and consume disk space.

### Solutions

#### 7.1 File-Based Sessions
**File: `.env`**
```env
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

#### 7.2 Session Cleanup
Add to cron jobs:
```bash
# Clean up old session files
find storage/framework/sessions -type f -mtime +7 -delete
```

#### 7.3 Reduce Session Lifetime
```env
SESSION_LIFETIME=120  # 2 hours instead of default
```

---

## 8. Email Optimization

### Problem
Shared hosting may have email sending restrictions.

### Solutions

#### 8.1 Use SMTP
**File: `.env`**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
```

#### 8.2 Queue Emails (if possible)
```php
// In mailable class
public function queue()
{
    return $this->queue(new SendEmailJob($this));
}
```

#### 8.3 Batch Email Sending
Send emails in batches instead of all at once:
```php
$users = User::chunk(100, function ($users) {
    foreach ($users as $user) {
        Mail::to($user)->send(new WelcomeMail());
    }
});
```

---

## 9. Security Hardening

### Problem
Shared hosting environments are more vulnerable.

### Solutions

#### 9.1 Disable Debug Mode
**File: `.env`**
```env
APP_DEBUG=false
APP_ENV=production
```

#### 9.2 Secure .env File
**File: `public/.htaccess`**
```apache
<FilesMatch "^\.env">
    Order allow,deny
    Deny from all
</FilesMatch>
```

#### 9.3 Disable Directory Listing
**File: `public/.htaccess`**
```apache
Options -Indexes
```

#### 9.4 Add Security Headers
**File: `public/.htaccess`**
```apache
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>
```

---

## 10. Performance Monitoring

### Problem
Need to identify performance bottlenecks.

### Solutions

#### 10.1 Monitor Error Logs
```bash
tail -f storage/logs/laravel.log
```

#### 10.2 Check Database Performance
```sql
-- Find slow queries
SELECT * FROM mysql.slow_log;
```

#### 10.3 Monitor Disk Usage
```bash
du -sh storage/
du -sh bootstrap/cache/
```

#### 10.4 Monitor Memory Usage
```bash
free -h
```

---

## 11. Deployment Checklist

Before deploying to shared hosting:

- [ ] Run `composer install --no-dev --optimize-autoloader`
- [ ] Run `npm run build`
- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`
- [ ] Configure `.env` with production values
- [ ] Set file permissions (755/777)
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Clear cache: `php artisan config:cache`
- [ ] Cache routes: `php artisan route:cache`
- [ ] Cache views: `php artisan view:cache`
- [ ] Test payment flow
- [ ] Test email notifications
- [ ] Monitor error logs
- [ ] Set up backups
- [ ] Set up monitoring

---

## 12. Troubleshooting

### High Memory Usage
```bash
# Check memory limit
php -r "echo ini_get('memory_limit');"

# Increase in .htaccess
php_value memory_limit 256M
```

### Slow Database Queries
```sql
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;

-- Check slow queries
SELECT * FROM mysql.slow_log;
```

### Disk Space Issues
```bash
# Find large files
find . -type f -size +10M

# Clean up logs
rm storage/logs/*.log

# Clean up cache
rm -rf bootstrap/cache/*
```

### Email Not Sending
```bash
# Check mail logs
tail -f /var/log/mail.log

# Test SMTP connection
telnet smtp.mailtrap.io 465
```

---

## 13. Performance Targets

Aim for these metrics on shared hosting:

- **Homepage Load Time**: < 2 seconds
- **Payment Page Load Time**: < 3 seconds
- **Database Query Time**: < 100ms
- **Memory Usage**: < 128MB per request
- **Disk Usage**: < 500MB (excluding backups)
- **Error Rate**: < 0.1%

---

## 14. Regular Maintenance

### Daily
- Monitor error logs
- Check disk space

### Weekly
- Clear cache: `php artisan cache:clear`
- Review slow queries

### Monthly
- Clear old logs: `find storage/logs -mtime +30 -delete`
- Update dependencies: `composer update`
- Review security logs

### Quarterly
- Full backup
- Performance audit
- Security audit

---

## 15. Resources

- [Laravel Deployment Guide](https://laravel.com/docs/deployment)
- [Shared Hosting Best Practices](https://laravel.com/docs/deployment#shared-hosting)
- [Database Optimization](https://laravel.com/docs/queries#debugging-queries)
- [Caching Guide](https://laravel.com/docs/cache)

