# EasyRent Complete Deployment Guide

## Quick Start

For a quick deployment, follow these steps:

1. **Prepare locally**: `bash prepare-deployment.sh`
2. **Build assets**: `npm run build`
3. **Upload to host**: Via FTP/SFTP/cPanel
4. **Configure on host**: Use `setup.php` or SSH
5. **Test**: Verify all features work
6. **Secure**: Delete `setup.php` and verify security

---

## Table of Contents

1. [Pre-Deployment](#pre-deployment)
2. [Local Preparation](#local-preparation)
3. [Hosting Setup](#hosting-setup)
4. [Upload Process](#upload-process)
5. [Post-Upload Configuration](#post-upload-configuration)
6. [Testing](#testing)
7. [Security](#security)
8. [Monitoring](#monitoring)
9. [Troubleshooting](#troubleshooting)
10. [Rollback](#rollback)

---

## Pre-Deployment

### 1. Requirements Check

Verify you have:
- [ ] Shared hosting account with cPanel/Plesk
- [ ] PHP 8.0+ support
- [ ] MySQL 5.7+ support
- [ ] FTP/SFTP or SSH access
- [ ] Composer installed locally
- [ ] Node.js installed locally
- [ ] Git installed locally

### 2. Credentials Preparation

Gather and securely store:
- [ ] Hosting control panel credentials
- [ ] FTP/SFTP credentials
- [ ] Database credentials
- [ ] Paystack API keys (production)
- [ ] Email SMTP credentials
- [ ] Social auth credentials (Google, Facebook, GitHub)

### 3. Domain Setup

- [ ] Domain registered
- [ ] DNS configured to point to hosting
- [ ] SSL certificate ordered (Let's Encrypt recommended)
- [ ] Email configured (if needed)

---

## Local Preparation

### Step 1: Clone/Download Repository

```bash
# If using git
git clone https://github.com/yourusername/easyrent.git
cd easyrent

# Or download and extract zip
unzip easyrent.zip
cd easyrent
```

### Step 2: Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### Step 3: Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Update .env with your local database
# DB_DATABASE=easyrent_local
# DB_USERNAME=root
# DB_PASSWORD=
```

### Step 4: Setup Local Database

```bash
# Create database
php artisan migrate

# Seed database (optional)
php artisan db:seed
```

### Step 5: Test Locally

```bash
# Start development server
php artisan serve

# In another terminal, start frontend build watcher
npm run dev

# Visit http://localhost:8000
# Test login, apartment creation, payment flow
```

### Step 6: Prepare for Deployment

```bash
# Run preparation script
bash prepare-deployment.sh

# This will:
# - Remove test files
# - Remove development directories
# - Optimize composer
# - Create backup
```

### Step 7: Build Production Assets

```bash
# Build minified assets
npm run build

# Verify build output
ls -la public/build/
```

### Step 8: Create Deployment Package

```bash
# Create archive (excluding unnecessary files)
tar --exclude='.git' \
    --exclude='node_modules' \
    --exclude='tests' \
    --exclude='.kiro' \
    --exclude='*.md' \
    -czf easyrent-deployment.tar.gz .

# Or use zip
zip -r easyrent-deployment.zip . \
    -x ".git/*" "node_modules/*" "tests/*" ".kiro/*" "*.md"
```

---

## Hosting Setup

### Step 1: Create Hosting Account

1. Purchase shared hosting plan
2. Receive hosting credentials
3. Login to control panel (cPanel/Plesk)
4. Note down:
   - FTP/SFTP credentials
   - Database credentials
   - PHP version
   - Disk space available

### Step 2: Create Database

**Via cPanel:**
1. Go to MySQL Databases
2. Create new database (e.g., `easyrent_prod`)
3. Create database user (e.g., `easyrent_user`)
4. Assign user to database with all privileges
5. Note credentials

**Via Plesk:**
1. Go to Databases
2. Create new database
3. Create user and assign to database

### Step 3: Configure PHP

**Via cPanel:**
1. Go to PHP Configuration
2. Set PHP version to 8.0 or higher
3. Set memory limit to 256M
4. Set max execution time to 300 seconds
5. Set upload max filesize to 100M

**Via Plesk:**
1. Go to PHP Settings
2. Configure same settings

### Step 4: Enable Required Extensions

Verify these are enabled:
- [ ] OpenSSL
- [ ] PDO MySQL
- [ ] Mbstring
- [ ] JSON
- [ ] Curl
- [ ] GD (for images)
- [ ] Zip

---

## Upload Process

### Option 1: FTP/SFTP Upload

**Using FileZilla:**
1. Open FileZilla
2. File → Site Manager → New Site
3. Enter FTP credentials
4. Connect
5. Navigate to `public_html/`
6. Drag and drop files from local to remote
7. Wait for upload to complete

**Using Command Line:**
```bash
# Using lftp
lftp -u username,password ftp.yourdomain.com
mirror -R . /public_html/
quit
```

### Option 2: cPanel File Manager

1. Login to cPanel
2. File Manager
3. Navigate to `public_html/`
4. Upload → Select Files
5. Choose deployment package
6. Extract if uploaded as archive

### Option 3: SSH (if available)

```bash
# Connect via SSH
ssh user@yourdomain.com

# Navigate to public_html
cd public_html

# Download deployment package
wget https://your-server.com/easyrent-deployment.tar.gz

# Extract
tar -xzf easyrent-deployment.tar.gz

# Clean up
rm easyrent-deployment.tar.gz
```

### Verify Upload

```bash
# Check file count
find . -type f | wc -l

# Check directory structure
ls -la

# Verify key files exist
ls -la artisan
ls -la public/index.php
ls -la app/
ls -la config/
```

---

## Post-Upload Configuration

### Step 1: Set File Permissions

**Via SSH:**
```bash
# Navigate to app directory
cd /home/user/public_html

# Set directory permissions
chmod -R 755 app bootstrap config database resources routes

# Set writable directories
chmod -R 777 storage bootstrap/cache

# Set .env permissions
chmod 644 .env

# Set artisan executable
chmod 755 artisan
```

**Via cPanel File Manager:**
1. Right-click on directory
2. Change Permissions
3. Set to 755 for directories, 644 for files

### Step 2: Configure .env

**Via SSH:**
```bash
nano .env
```

**Via cPanel File Manager:**
1. Right-click `.env`
2. Edit
3. Update values

Update these values:
```env
APP_NAME=EasyRent
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=easyrent_prod
DB_USERNAME=easyrent_user
DB_PASSWORD=your_password

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=465
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls

PAYSTACK_PUBLIC_KEY=pk_live_your_key
PAYSTACK_SECRET_KEY=sk_live_your_key
```

### Step 3: Run Database Migrations

**Option A: Using setup.php (Recommended for shared hosting)**

1. Generate a random secret key:
   ```bash
   openssl rand -hex 32
   ```

2. Edit `public/setup.php` and change:
   ```php
   define('SETUP_KEY', 'your_random_key_here');
   ```

3. Visit in browser:
   ```
   https://yourdomain.com/setup.php?key=your_random_key_here&cmd=migrate
   ```

4. Wait for completion

**Option B: Using SSH**

```bash
php artisan migrate --force
php artisan db:seed --force
```

### Step 4: Cache Configuration

**Using setup.php:**
```
https://yourdomain.com/setup.php?key=your_key&cmd=config-cache
https://yourdomain.com/setup.php?key=your_key&cmd=route-cache
https://yourdomain.com/setup.php?key=your_key&cmd=view-cache
```

**Using SSH:**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 5: Create Storage Link (if needed)

**Using setup.php:**
```
https://yourdomain.com/setup.php?key=your_key&cmd=storage-link
```

**Using SSH:**
```bash
php artisan storage:link
```

### Step 6: Enable SSL

**Via cPanel:**
1. Go to AutoSSL or Let's Encrypt
2. Install certificate
3. Force HTTPS redirect

**Via Plesk:**
1. Go to SSL/TLS Certificates
2. Install Let's Encrypt certificate
3. Enable HTTPS redirect

---

## Testing

### Step 1: Basic Functionality

- [ ] Homepage loads
- [ ] CSS/JS loads correctly
- [ ] No 404 errors
- [ ] No 500 errors
- [ ] Mobile responsive

### Step 2: Authentication

- [ ] User registration works
- [ ] Email verification works
- [ ] Login works
- [ ] Logout works
- [ ] Password reset works

### Step 3: Core Features

- [ ] Create apartment works
- [ ] Edit apartment works
- [ ] Delete apartment works
- [ ] Generate EasyRent link works
- [ ] View apartment invitation works

### Step 4: Payment Flow

- [ ] Payment page loads
- [ ] Paystack integration works
- [ ] Test payment succeeds
- [ ] Payment appears in billing
- [ ] Email notifications sent

### Step 5: Performance

- [ ] Homepage loads in < 2 seconds
- [ ] Payment page loads in < 3 seconds
- [ ] No timeout errors
- [ ] Database queries optimized

### Step 6: Error Handling

- [ ] 404 page displays correctly
- [ ] 500 page displays correctly
- [ ] Error logs are created
- [ ] No sensitive info in errors

---

## Security

### Step 1: Delete Setup Script

```bash
rm public/setup.php
```

### Step 2: Verify .env Security

- [ ] .env not accessible via web
- [ ] .env permissions set to 644
- [ ] .env not in git repository

### Step 3: Disable Debug Mode

Verify in `.env`:
```env
APP_DEBUG=false
APP_ENV=production
```

### Step 4: Update Security Headers

Verify in `public/.htaccess`:
```apache
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
```

### Step 5: Database Security

- [ ] Database user has limited privileges
- [ ] Database password is strong
- [ ] Database backups enabled

### Step 6: File Permissions

- [ ] No world-writable files except storage/
- [ ] No executable files in public/
- [ ] .env not readable by web server

---

## Monitoring

### Step 1: Set Up Error Logging

```bash
# Check error logs
tail -f storage/logs/laravel.log

# Or via cPanel
# Logs → Error Log
```

### Step 2: Database Backups

**Via cPanel:**
1. Go to Backups
2. Download Full Backup
3. Store safely

**Via SSH:**
```bash
mysqldump -u user -p database > backup.sql
```

### Step 3: Cron Jobs

**Via cPanel:**
1. Go to Cron Jobs
2. Add new cron job:
   ```
   * * * * * /usr/bin/php /home/user/public_html/artisan schedule:run >> /dev/null 2>&1
   ```

### Step 4: Uptime Monitoring

Use services like:
- Uptime Robot
- Pingdom
- StatusCake

### Step 5: Performance Monitoring

Monitor:
- Page load times
- Database query times
- Error rates
- Disk usage
- Memory usage

---

## Troubleshooting

### 500 Internal Server Error

**Check error logs:**
```bash
tail -f storage/logs/laravel.log
```

**Common causes:**
1. .env file missing or unreadable
2. Database connection failed
3. File permissions incorrect
4. PHP memory limit too low
5. Missing PHP extensions

**Solutions:**
```bash
# Verify .env exists
ls -la .env

# Check permissions
ls -la storage/
ls -la bootstrap/cache/

# Increase memory limit in .htaccess
php_value memory_limit 256M

# Clear cache
rm -rf bootstrap/cache/*
```

### Database Connection Error

**Check credentials:**
```bash
# Verify in .env
cat .env | grep DB_

# Test connection via SSH
mysql -h localhost -u user -p database
```

**Common causes:**
1. Wrong database credentials
2. Database doesn't exist
3. MySQL not running
4. Host not localhost

### Payment Not Working

**Check Paystack keys:**
```bash
cat .env | grep PAYSTACK
```

**Verify callback URL:**
1. Login to Paystack dashboard
2. Go to Settings → API Keys & Webhooks
3. Verify callback URL matches your domain

**Check logs:**
```bash
tail -f storage/logs/laravel.log | grep -i payment
```

### Email Not Sending

**Check SMTP settings:**
```bash
cat .env | grep MAIL_
```

**Test SMTP connection:**
```bash
telnet smtp.mailtrap.io 465
```

**Check logs:**
```bash
tail -f storage/logs/laravel.log | grep -i mail
```

---

## Rollback

If deployment fails:

### Step 1: Restore from Backup

```bash
# Download backup from cPanel
# Or restore via SSH
mysql -u user -p database < backup.sql
```

### Step 2: Revert Files

```bash
# If you have backup directory
cp -r backups/deployment-YYYYMMDD-HHMMSS/* .
```

### Step 3: Verify Functionality

- [ ] Homepage loads
- [ ] Login works
- [ ] Payment works
- [ ] No errors in logs

### Step 4: Investigate Issue

1. Check error logs
2. Review recent changes
3. Test locally
4. Identify root cause

### Step 5: Fix and Redeploy

1. Fix issue locally
2. Test thoroughly
3. Deploy again
4. Verify functionality

---

## Post-Deployment

### Step 1: Documentation

- [ ] Document database credentials (securely)
- [ ] Document FTP credentials (securely)
- [ ] Document Paystack keys (securely)
- [ ] Create runbook for common tasks

### Step 2: Team Communication

- [ ] Notify team of live deployment
- [ ] Share access credentials securely
- [ ] Document support procedures
- [ ] Set up incident response plan

### Step 3: Monitoring Setup

- [ ] Set up uptime monitoring
- [ ] Set up error alerts
- [ ] Set up performance monitoring
- [ ] Set up security monitoring

### Step 4: Backup Strategy

- [ ] Automated daily backups enabled
- [ ] Backup retention policy set
- [ ] Test backup restoration
- [ ] Document backup procedures

---

## Maintenance Schedule

### Daily
- Monitor error logs
- Check disk space

### Weekly
- Clear cache: `php artisan cache:clear`
- Review slow queries

### Monthly
- Clear old logs
- Update dependencies
- Review security logs

### Quarterly
- Full security audit
- Performance audit
- Update all packages

---

## Support Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Shared Hosting Best Practices](https://laravel.com/docs/deployment#shared-hosting)
- [Paystack Documentation](https://paystack.com/docs)
- [cPanel Documentation](https://documentation.cpanel.net/)

---

## Deployment Checklist

Use `DEPLOYMENT_CHECKLIST.md` for a comprehensive checklist to follow during deployment.

---

## Optimization Guide

For performance optimization tips, see `SHARED_HOSTING_OPTIMIZATION.md`.

