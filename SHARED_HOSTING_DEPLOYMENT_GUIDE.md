# EasyRent - Shared Hosting Deployment Guide

## Pre-Deployment Checklist

### 1. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Set permissions
chmod -R 755 storage bootstrap/cache
chmod -R 777 storage bootstrap/cache
```

### 2. Database Preparation
```bash
# Export database
mysqldump -u username -p database_name > easyrent_backup.sql

# Create database on shared host
# Use cPanel/Plesk to create new database and user
```

### 3. Composer Dependencies
```bash
# Install dependencies locally first
composer install --no-dev --optimize-autoloader

# This creates vendor folder to upload
```

---

## Shared Hosting Optimization

### 1. Disable Unnecessary Services

**File: `config/app.php`**
```php
// Disable services not needed on shared hosting
'providers' => [
    // Remove or comment out:
    // App\Providers\PaymentCalculationServiceProvider::class,
    // App\Providers\EnhancedRentalCalculationServiceProvider::class,
],
```

### 2. Cache Configuration

**File: `.env`**
```env
# Use file cache (most compatible with shared hosting)
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Disable heavy features
TELESCOPE_ENABLED=false
DEBUGBAR_ENABLED=false
```

### 3. Database Optimization

**File: `config/database.php`**
```php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', 'localhost'),
    'port' => env('DB_PORT', 3306),
    'database' => env('DB_DATABASE'),
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
    'unix_socket' => env('DB_SOCKET', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'prefix_indexes' => true,
    'strict' => false,  // Important for shared hosting
    'engine' => null,
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
    ]) : [],
],
```

### 4. PHP Configuration

**File: `public/.htaccess`** (Create if doesn't exist)
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### 5. Disable Heavy Middleware

**File: `app/Http/Kernel.php`**
```php
protected $middleware = [
    // Remove or comment out heavy middleware
    // \App\Http\Middleware\EasyRentPerformanceMonitoring::class,
    // \App\Http\Middleware\PaymentCalculationPerformanceMiddleware::class,
];
```

---

## File Structure for Upload

```
public_html/
├── app/
├── bootstrap/
├── config/
├── database/
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env (configured)
├── .htaccess
├── artisan
├── composer.json
├── composer.lock
└── public/
    ├── index.php
    ├── .htaccess
    └── assets/
```

---

## Upload Process

### 1. Via FTP/SFTP
```
1. Connect to shared host via FTP
2. Upload all files to public_html/
3. Set permissions:
   - storage/ → 777
   - bootstrap/cache/ → 777
   - .env → 644
```

### 2. Via cPanel File Manager
```
1. Login to cPanel
2. File Manager → public_html
3. Upload files
4. Set permissions via right-click menu
```

### 3. Via SSH (if available)
```bash
# Upload via SCP
scp -r . user@host:/home/user/public_html/

# Set permissions
ssh user@host
chmod -R 755 public_html/
chmod -R 777 public_html/storage
chmod -R 777 public_html/bootstrap/cache
chmod 644 public_html/.env
```

---

## Post-Upload Configuration

### 1. Database Migration
```bash
# SSH into server
ssh user@host

# Navigate to app
cd public_html

# Run migrations
php artisan migrate --force

# Seed database (optional)
php artisan db:seed --force
```

### 2. Clear Cache
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Set Permissions
```bash
chmod -R 755 app bootstrap config database resources routes
chmod -R 777 storage bootstrap/cache
chmod 644 .env
chmod 755 artisan
```

---

## Environment Variables (.env)

```env
APP_NAME=EasyRent
APP_ENV=production
APP_KEY=base64:YOUR_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Cache & Session
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="EasyRent"

# Paystack
PAYSTACK_PUBLIC_KEY=your_public_key
PAYSTACK_SECRET_KEY=your_secret_key

# Disable heavy features
TELESCOPE_ENABLED=false
DEBUGBAR_ENABLED=false
```

---

## Performance Optimization

### 1. Disable Unused Features
```php
// config/app.php - Comment out unused providers
'providers' => [
    // Illuminate providers...
    
    // Remove these for shared hosting:
    // App\Providers\PaymentCalculationServiceProvider::class,
],
```

### 2. Optimize Autoloader
```bash
composer install --no-dev --optimize-autoloader --classmap-authoritative
```

### 3. Remove Test Files
```bash
# Delete test directories
rm -rf tests/
rm -rf .kiro/
rm -rf .git/
```

### 4. Remove Debug Files
```bash
# Delete all test_*.php files
find . -name "test_*.php" -delete
find . -name "debug_*.php" -delete
find . -name "diagnose_*.php" -delete

# Delete documentation files (optional)
rm -rf *.md
```

---

## Shared Hosting Limitations & Solutions

### Issue 1: Memory Limit
**Problem**: Shared hosting has low PHP memory limit (usually 128MB)

**Solution**:
```php
// Add to public/index.php before Laravel bootstrap
ini_set('memory_limit', '256M');
```

Or in `.htaccess`:
```apache
php_value memory_limit 256M
php_value max_execution_time 300
```

### Issue 2: No SSH/Artisan Access
**Problem**: Can't run `php artisan` commands

**Solution**: Create `setup.php` in public folder
```php
<?php
// public/setup.php
// Access via: https://yourdomain.com/setup.php

if ($_GET['key'] !== 'your_secret_key') {
    die('Unauthorized');
}

$commands = [
    'migrate' => 'php artisan migrate --force',
    'seed' => 'php artisan db:seed --force',
    'cache' => 'php artisan config:cache',
];

$cmd = $_GET['cmd'] ?? 'migrate';
echo "<pre>";
echo shell_exec($commands[$cmd] ?? 'echo "Invalid command"');
echo "</pre>";
?>
```

### Issue 3: Cron Jobs
**Problem**: Need to run scheduled tasks

**Solution**: Use cPanel Cron Jobs
```
# Add to cPanel Cron Jobs
* * * * * /usr/bin/php /home/user/public_html/artisan schedule:run >> /dev/null 2>&1
```

### Issue 4: Large File Uploads
**Problem**: Shared hosting limits file uploads

**Solution**: Update `.htaccess`
```apache
php_value upload_max_filesize 100M
php_value post_max_size 100M
```

---

## Testing Before Going Live

### 1. Test Payment Flow
```
1. Create test account
2. Create test apartment
3. Generate EasyRent link
4. Test payment with Paystack test keys
5. Verify payment appears in billing
```

### 2. Test Email Notifications
```
1. Create account
2. Verify welcome email received
3. Create apartment
4. Verify landlord notification
5. Test payment notification
```

### 3. Test Database
```
1. Verify all tables created
2. Test CRUD operations
3. Verify relationships work
4. Test complex queries
```

### 4. Performance Test
```
1. Load homepage
2. Login/register
3. Create apartment
4. Generate link
5. Check response times
```

---

## Monitoring & Maintenance

### 1. Error Logs
```
# Check error logs
tail -f storage/logs/laravel.log

# Or via cPanel
# Error Log in cPanel → Logs
```

### 2. Database Backups
```
# Via cPanel
1. Go to Backups
2. Download Full Backup
3. Store safely

# Or via command
mysqldump -u user -p database > backup.sql
```

### 3. Regular Maintenance
```bash
# Weekly
php artisan cache:clear
php artisan view:clear

# Monthly
php artisan optimize:clear
php artisan db:prune
```

---

## Troubleshooting

### 500 Internal Server Error
```
1. Check storage/logs/laravel.log
2. Verify .env file exists and readable
3. Check file permissions (755/777)
4. Verify database connection
5. Check PHP version compatibility
```

### Database Connection Error
```
1. Verify DB_HOST (usually localhost)
2. Check DB_USERNAME and DB_PASSWORD
3. Verify database exists
4. Check MySQL is running
5. Test connection via cPanel
```

### Payment Not Working
```
1. Verify Paystack keys in .env
2. Check callback URL in Paystack dashboard
3. Verify HTTPS is enabled
4. Check firewall allows Paystack IPs
5. Review payment logs
```

### Email Not Sending
```
1. Verify MAIL_* settings in .env
2. Check SMTP credentials
3. Verify sender email is allowed
4. Check spam folder
5. Review mail logs
```

