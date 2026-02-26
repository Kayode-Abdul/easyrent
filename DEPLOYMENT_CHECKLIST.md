# EasyRent Shared Hosting Deployment Checklist

## Pre-Deployment (Local Environment)

### 1. Code Cleanup
- [ ] Remove all test files: `find . -maxdepth 1 -name "test_*.php" -delete`
- [ ] Remove all debug files: `find . -maxdepth 1 -name "debug_*.php" -delete`
- [ ] Remove all diagnostic files: `find . -maxdepth 1 -name "diagnose_*.php" -delete`
- [ ] Remove documentation files (optional): `rm -rf *.md` (keep only README.md)
- [ ] Remove git history: `rm -rf .git/`
- [ ] Remove development folders: `rm -rf .kiro/ tests/`

### 2. Composer Optimization
```bash
# Install production dependencies only
composer install --no-dev --optimize-autoloader --classmap-authoritative

# Verify vendor folder is created
ls -la vendor/
```
- [ ] Composer dependencies installed
- [ ] No dev dependencies included
- [ ] Autoloader optimized

### 3. Environment Configuration
- [ ] Copy `.env.shared-hosting` to `.env`
- [ ] Update database credentials in `.env`
- [ ] Update Paystack keys (use production keys)
- [ ] Update email configuration
- [ ] Update social auth credentials
- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`
- [ ] Generate new `APP_KEY`: `php artisan key:generate`

### 4. Build Assets
```bash
# Build frontend assets
npm run build

# Verify build output
ls -la public/build/
```
- [ ] Frontend assets built
- [ ] CSS/JS files minified
- [ ] No build errors

### 5. Local Testing
```bash
# Test with production configuration
APP_ENV=production APP_DEBUG=false php artisan serve
```
- [ ] Homepage loads
- [ ] Login/Register works
- [ ] Payment flow works
- [ ] Email notifications send
- [ ] No errors in logs

---

## Shared Hosting Preparation

### 1. Create Hosting Account
- [ ] Purchase shared hosting plan
- [ ] Create cPanel/Plesk account
- [ ] Note hosting credentials
- [ ] Note FTP/SFTP credentials
- [ ] Note database credentials

### 2. Create Database
Via cPanel:
- [ ] Create MySQL database
- [ ] Create database user
- [ ] Assign user to database
- [ ] Note database name, user, password

### 3. Configure PHP
Via cPanel → PHP Configuration:
- [ ] PHP version: 8.0 or higher
- [ ] Memory limit: 256M or higher
- [ ] Max execution time: 300 seconds
- [ ] Upload max filesize: 100M
- [ ] Post max size: 100M

### 4. Enable Required Extensions
- [ ] OpenSSL
- [ ] PDO MySQL
- [ ] Mbstring
- [ ] JSON
- [ ] Curl
- [ ] GD (for images)

---

## Upload to Shared Host

### Option 1: FTP/SFTP Upload
```bash
# Using lftp
lftp -u username,password ftp.yourdomain.com
mirror -R . /public_html/
```

- [ ] Connected to FTP
- [ ] All files uploaded to public_html/
- [ ] Verified file count matches local

### Option 2: cPanel File Manager
- [ ] Login to cPanel
- [ ] Navigate to File Manager
- [ ] Upload all files to public_html/
- [ ] Verify upload complete

### Option 3: SSH (if available)
```bash
scp -r . user@host:/home/user/public_html/
```
- [ ] Files uploaded via SCP
- [ ] Verified on server

---

## Post-Upload Configuration

### 1. Set File Permissions
Via SSH or cPanel File Manager:
```bash
chmod -R 755 app bootstrap config database resources routes
chmod -R 777 storage bootstrap/cache
chmod 644 .env
chmod 755 artisan
```

- [ ] Permissions set correctly
- [ ] storage/ is writable
- [ ] bootstrap/cache/ is writable

### 2. Database Setup
Via cPanel → phpMyAdmin or SSH:
```bash
# SSH method
php artisan migrate --force
php artisan db:seed --force
```

Or use setup.php:
```
https://yourdomain.com/setup.php?key=YOUR_SECRET_KEY&cmd=migrate
https://yourdomain.com/setup.php?key=YOUR_SECRET_KEY&cmd=seed
```

- [ ] Database migrations completed
- [ ] Database seeded with initial data
- [ ] Tables created successfully

### 3. Cache Configuration
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Or use setup.php:
```
https://yourdomain.com/setup.php?key=YOUR_SECRET_KEY&cmd=config-cache
https://yourdomain.com/setup.php?key=YOUR_SECRET_KEY&cmd=route-cache
https://yourdomain.com/setup.php?key=YOUR_SECRET_KEY&cmd=view-cache
```

- [ ] Configuration cached
- [ ] Routes cached
- [ ] Views cached

### 4. Storage Link (if needed)
```bash
php artisan storage:link
```

Or use setup.php:
```
https://yourdomain.com/setup.php?key=YOUR_SECRET_KEY&cmd=storage-link
```

- [ ] Storage symlink created

### 5. SSL Certificate
Via cPanel → AutoSSL or Let's Encrypt:
- [ ] SSL certificate installed
- [ ] HTTPS enabled
- [ ] Redirect HTTP to HTTPS

---

## Testing on Live Server

### 1. Basic Functionality
- [ ] Homepage loads
- [ ] CSS/JS loads correctly
- [ ] No 404 errors
- [ ] No 500 errors

### 2. Authentication
- [ ] User registration works
- [ ] Email verification works
- [ ] Login works
- [ ] Logout works
- [ ] Password reset works

### 3. Core Features
- [ ] Create apartment works
- [ ] Generate EasyRent link works
- [ ] Payment flow works
- [ ] Billing page shows payments
- [ ] Complaints system works

### 4. Payment Integration
- [ ] Paystack integration works
- [ ] Test payment succeeds
- [ ] Payment appears in billing
- [ ] Email notifications sent
- [ ] Callback URL working

### 5. Email Notifications
- [ ] Welcome email received
- [ ] Payment confirmation received
- [ ] Landlord notifications received
- [ ] No emails in spam folder

### 6. Performance
- [ ] Homepage loads in < 2 seconds
- [ ] Payment page loads in < 3 seconds
- [ ] No timeout errors
- [ ] Database queries optimized

---

## Security Hardening

### 1. Remove Setup Script
```bash
rm public/setup.php
```
- [ ] setup.php deleted
- [ ] No web-accessible setup scripts remain

### 2. Secure .env File
- [ ] .env file not accessible via web
- [ ] .env permissions set to 644
- [ ] .env not in git repository

### 3. Disable Debug Mode
- [ ] APP_DEBUG=false in .env
- [ ] No stack traces visible on errors
- [ ] Error logs only in storage/logs/

### 4. Update Security Headers
- [ ] HTTPS enforced
- [ ] HSTS header enabled
- [ ] X-Frame-Options set
- [ ] X-Content-Type-Options set

### 5. Database Security
- [ ] Database user has limited privileges
- [ ] Database backups enabled
- [ ] Database password is strong

### 6. File Permissions
- [ ] No world-writable files except storage/
- [ ] No executable files in public/
- [ ] .env not readable by web server

---

## Monitoring & Maintenance

### 1. Set Up Error Logging
- [ ] Check storage/logs/laravel.log regularly
- [ ] Set up log rotation
- [ ] Monitor error rates

### 2. Database Backups
Via cPanel:
- [ ] Enable automatic backups
- [ ] Set backup frequency (daily)
- [ ] Download backup copies
- [ ] Store backups securely

### 3. Cron Jobs
Via cPanel → Cron Jobs:
```
* * * * * /usr/bin/php /home/user/public_html/artisan schedule:run >> /dev/null 2>&1
```
- [ ] Cron job added
- [ ] Cron job runs every minute

### 4. Monitoring
- [ ] Set up uptime monitoring
- [ ] Set up error alerts
- [ ] Monitor disk space
- [ ] Monitor database size

### 5. Regular Maintenance
- [ ] Clear cache weekly: `php artisan cache:clear`
- [ ] Clear logs monthly: `php artisan logs:clear`
- [ ] Update dependencies quarterly
- [ ] Review security logs monthly

---

## Troubleshooting

### 500 Internal Server Error
- [ ] Check storage/logs/laravel.log
- [ ] Verify .env file exists
- [ ] Check file permissions
- [ ] Verify database connection
- [ ] Check PHP version compatibility

### Database Connection Error
- [ ] Verify DB_HOST (usually localhost)
- [ ] Verify DB_USERNAME and DB_PASSWORD
- [ ] Verify database exists
- [ ] Test connection via phpMyAdmin
- [ ] Check MySQL is running

### Payment Not Working
- [ ] Verify Paystack keys in .env
- [ ] Check callback URL in Paystack dashboard
- [ ] Verify HTTPS is enabled
- [ ] Check firewall allows Paystack IPs
- [ ] Review payment logs

### Email Not Sending
- [ ] Verify MAIL_* settings in .env
- [ ] Check SMTP credentials
- [ ] Verify sender email is allowed
- [ ] Check spam folder
- [ ] Review mail logs

### High Memory Usage
- [ ] Disable heavy middleware
- [ ] Optimize database queries
- [ ] Clear cache
- [ ] Reduce log verbosity
- [ ] Contact hosting provider

---

## Post-Deployment

### 1. Documentation
- [ ] Document database credentials (securely)
- [ ] Document FTP credentials (securely)
- [ ] Document Paystack keys (securely)
- [ ] Create runbook for common tasks

### 2. Team Communication
- [ ] Notify team of live deployment
- [ ] Share access credentials securely
- [ ] Document support procedures
- [ ] Set up incident response plan

### 3. Monitoring Setup
- [ ] Set up uptime monitoring
- [ ] Set up error alerts
- [ ] Set up performance monitoring
- [ ] Set up security monitoring

### 4. Backup Strategy
- [ ] Automated daily backups enabled
- [ ] Backup retention policy set
- [ ] Test backup restoration
- [ ] Document backup procedures

---

## Rollback Plan

If deployment fails:

### 1. Immediate Actions
- [ ] Revert DNS if needed
- [ ] Restore from backup
- [ ] Notify users
- [ ] Document issue

### 2. Investigation
- [ ] Check error logs
- [ ] Review recent changes
- [ ] Test locally
- [ ] Identify root cause

### 3. Fix & Redeploy
- [ ] Fix issue locally
- [ ] Test thoroughly
- [ ] Deploy again
- [ ] Verify functionality

---

## Sign-Off

- [ ] All checklist items completed
- [ ] All tests passed
- [ ] Security review completed
- [ ] Performance acceptable
- [ ] Monitoring active
- [ ] Team notified
- [ ] Documentation complete

**Deployment Date:** _______________

**Deployed By:** _______________

**Verified By:** _______________

**Notes:** _______________________________________________

