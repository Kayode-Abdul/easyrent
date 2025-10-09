# Super Marketer System Deployment Guide

This guide provides comprehensive instructions for deploying the Super Marketer System to production environments.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Pre-Deployment Checklist](#pre-deployment-checklist)
3. [Deployment Process](#deployment-process)
4. [Post-Deployment Verification](#post-deployment-verification)
5. [Rollback Procedures](#rollback-procedures)
6. [Troubleshooting](#troubleshooting)
7. [Monitoring and Maintenance](#monitoring-and-maintenance)

## Prerequisites

### System Requirements

- **PHP**: 8.0 or higher
- **MySQL**: 5.7 or higher (8.0 recommended)
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Composer**: Latest version
- **Node.js**: 16+ (for asset compilation)
- **Memory**: Minimum 512MB, Recommended 1GB+
- **Storage**: Minimum 2GB free space

### Required PHP Extensions

```bash
php -m | grep -E "(pdo|mysql|mbstring|tokenizer|xml|ctype|json|bcmath|openssl|fileinfo|gd)"
```

Ensure all these extensions are installed and enabled.

### Environment Setup

1. **Create application user** (if not exists):
   ```bash
   sudo useradd -m -s /bin/bash easyrent
   sudo usermod -aG www-data easyrent
   ```

2. **Set up directory structure**:
   ```bash
   sudo mkdir -p /var/www/easyrent
   sudo mkdir -p /var/log/easyrent
   sudo mkdir -p /var/backups/easyrent
   sudo chown -R easyrent:www-data /var/www/easyrent
   sudo chown -R easyrent:easyrent /var/log/easyrent
   sudo chown -R easyrent:easyrent /var/backups/easyrent
   ```

## Pre-Deployment Checklist

### 1. Database Preparation

- [ ] Database server is running and accessible
- [ ] Database user has appropriate permissions
- [ ] Database backup is created
- [ ] Connection parameters are verified

### 2. Environment Configuration

- [ ] `.env` file is properly configured
- [ ] All required environment variables are set
- [ ] SSL certificates are installed (for HTTPS)
- [ ] Domain/subdomain is properly configured

### 3. Code Preparation

- [ ] Latest code is pulled from repository
- [ ] All tests pass in staging environment
- [ ] Dependencies are up to date
- [ ] Assets are compiled for production

### 4. Infrastructure

- [ ] Web server configuration is updated
- [ ] PHP-FPM/mod_php is properly configured
- [ ] Queue workers are set up (if using queues)
- [ ] Cron jobs are configured
- [ ] Log rotation is set up

## Deployment Process

### Automated Deployment

Use the provided deployment script for automated deployment:

```bash
# Navigate to application directory
cd /var/www/easyrent

# Run deployment script
./scripts/deploy_super_marketer_system.sh deploy
```

### Manual Deployment Steps

If you prefer manual deployment, follow these steps:

#### 1. Enable Maintenance Mode

```bash
php artisan down --message="System upgrade in progress" --retry=60
```

#### 2. Create Backup

```bash
# Database backup
mysqldump -u[username] -p[password] [database_name] > backup_$(date +%Y%m%d_%H%M%S).sql

# Application backup
tar -czf app_backup_$(date +%Y%m%d_%H%M%S).tar.gz \
    --exclude=node_modules \
    --exclude=vendor \
    --exclude=storage/logs \
    .
```

#### 3. Update Code

```bash
# Pull latest code
git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader

# Compile assets (if needed)
npm ci --production
npm run production
```

#### 4. Run Migrations

```bash
# Run database migrations
php artisan migrate --force

# Seed Super Marketer system data
php artisan db:seed --class=SuperMarketerSystemSeeder --force
```

#### 5. Optimize Application

```bash
# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 6. Set Permissions

```bash
chmod -R 775 storage bootstrap/cache
chgrp -R www-data storage bootstrap/cache
```

#### 7. Validate Deployment

```bash
php artisan system:validate
```

#### 8. Disable Maintenance Mode

```bash
php artisan up
```

## Post-Deployment Verification

### 1. System Health Check

Run the comprehensive system validation:

```bash
php artisan system:validate
```

### 2. Manual Testing

Test the following functionality:

#### Super Marketer Features
- [ ] Super Marketer dashboard loads correctly
- [ ] Can view referred marketers
- [ ] Commission analytics display properly
- [ ] Referral link generation works

#### Marketer Features
- [ ] Marketer dashboard shows hierarchy information
- [ ] Commission breakdown is visible
- [ ] Referral performance metrics display

#### Admin Features
- [ ] Regional commission rate management
- [ ] Bulk rate updates function
- [ ] System analytics dashboard

#### Regional Manager Features
- [ ] Multi-tier commission analytics
- [ ] Regional performance comparisons
- [ ] Chain effectiveness metrics

### 3. Performance Testing

```bash
# Test database performance
php artisan tinker --execute="
\$start = microtime(true);
DB::table('users')->count();
echo 'Query time: ' . (microtime(true) - \$start) . ' seconds';
"

# Test commission calculation performance
php artisan tinker --execute="
\$calculator = app(\App\Services\Commission\MultiTierCommissionCalculator::class);
\$start = microtime(true);
for (\$i = 0; \$i < 100; \$i++) {
    // Performance test code
}
echo 'Calculation time: ' . (microtime(true) - \$start) . ' seconds';
"
```

### 4. Log Monitoring

Monitor application logs for any errors:

```bash
tail -f storage/logs/laravel.log
tail -f /var/log/easyrent/deployment.log
```

## Rollback Procedures

### Automated Rollback

```bash
# List available backups
ls -la /var/backups/easyrent/

# Rollback to specific backup
./scripts/deploy_super_marketer_system.sh rollback 20231209_143022
```

### Manual Rollback

#### 1. Enable Maintenance Mode

```bash
php artisan down --message="System rollback in progress"
```

#### 2. Restore Database

```bash
mysql -u[username] -p[password] [database_name] < backup_20231209_143022.sql
```

#### 3. Restore Application Files

```bash
tar -xzf app_backup_20231209_143022.tar.gz
```

#### 4. Optimize and Validate

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan system:validate
```

#### 5. Disable Maintenance Mode

```bash
php artisan up
```

## Troubleshooting

### Common Issues

#### 1. Migration Failures

**Problem**: Database migration fails during deployment

**Solution**:
```bash
# Check migration status
php artisan migrate:status

# Rollback specific migration
php artisan migrate:rollback --step=1

# Re-run migrations
php artisan migrate --force
```

#### 2. Permission Issues

**Problem**: File permission errors

**Solution**:
```bash
# Fix storage permissions
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache

# Fix SELinux context (if applicable)
sudo setsebool -P httpd_can_network_connect 1
sudo setsebool -P httpd_unified 1
```

#### 3. Cache Issues

**Problem**: Application showing old data or errors

**Solution**:
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Clear OPcache (if enabled)
sudo systemctl reload php8.0-fpm
```

#### 4. Database Connection Issues

**Problem**: Cannot connect to database

**Solution**:
```bash
# Test database connection
php artisan tinker --execute="DB::connection()->getPdo();"

# Check database credentials in .env
grep -E "DB_|DATABASE_" .env

# Test MySQL connection directly
mysql -u[username] -p[password] -h[host] [database_name]
```

#### 5. Commission Calculation Errors

**Problem**: Commission calculations failing

**Solution**:
```bash
# Check commission rates
php artisan tinker --execute="
\App\Models\CommissionRate::where('is_active', true)->get();
"

# Validate regional rates
php artisan tinker --execute="
\$manager = app(\App\Services\Commission\RegionalRateManager::class);
\$manager->getActiveRate('Lagos', 9);
"
```

### Log Analysis

#### Application Logs

```bash
# View recent errors
tail -n 100 storage/logs/laravel.log | grep ERROR

# Monitor real-time logs
tail -f storage/logs/laravel.log
```

#### Web Server Logs

```bash
# Apache logs
sudo tail -f /var/log/apache2/error.log
sudo tail -f /var/log/apache2/access.log

# Nginx logs
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log
```

#### Database Logs

```bash
# MySQL error log
sudo tail -f /var/log/mysql/error.log

# MySQL slow query log
sudo tail -f /var/log/mysql/mysql-slow.log
```

## Monitoring and Maintenance

### Health Monitoring

Set up monitoring for:

- [ ] Application response time
- [ ] Database performance
- [ ] Commission calculation accuracy
- [ ] Payment processing success rate
- [ ] Fraud detection alerts

### Scheduled Tasks

Ensure these cron jobs are configured:

```bash
# Edit crontab
crontab -e

# Add Laravel scheduler
* * * * * cd /var/www/easyrent && php artisan schedule:run >> /dev/null 2>&1

# Add system validation (daily)
0 2 * * * cd /var/www/easyrent && php artisan system:validate >> /var/log/easyrent/validation.log 2>&1
```

### Regular Maintenance

#### Daily Tasks
- [ ] Monitor application logs
- [ ] Check system health
- [ ] Verify commission calculations
- [ ] Review fraud detection alerts

#### Weekly Tasks
- [ ] Database performance analysis
- [ ] Commission audit reports
- [ ] System backup verification
- [ ] Security updates check

#### Monthly Tasks
- [ ] Full system backup
- [ ] Performance optimization review
- [ ] Security audit
- [ ] Dependency updates

### Performance Optimization

#### Database Optimization

```sql
-- Analyze table performance
ANALYZE TABLE users, referrals, commission_payments, commission_rates;

-- Check for missing indexes
SHOW INDEX FROM referrals;
SHOW INDEX FROM commission_payments;

-- Optimize tables
OPTIMIZE TABLE users, referrals, commission_payments;
```

#### Application Optimization

```bash
# Enable OPcache
echo "opcache.enable=1" >> /etc/php/8.0/fpm/php.ini
echo "opcache.memory_consumption=256" >> /etc/php/8.0/fpm/php.ini

# Optimize Composer autoloader
composer dump-autoload --optimize --classmap-authoritative

# Use Redis for caching (if available)
php artisan config:cache
```

## Security Considerations

### SSL/TLS Configuration

Ensure HTTPS is properly configured:

```bash
# Test SSL configuration
openssl s_client -connect yourdomain.com:443 -servername yourdomain.com

# Check certificate expiration
openssl x509 -in /path/to/certificate.crt -text -noout | grep "Not After"
```

### File Permissions

Maintain secure file permissions:

```bash
# Application files
find /var/www/easyrent -type f -exec chmod 644 {} \;
find /var/www/easyrent -type d -exec chmod 755 {} \;

# Executable files
chmod +x /var/www/easyrent/artisan
chmod +x /var/www/easyrent/scripts/*.sh

# Sensitive files
chmod 600 /var/www/easyrent/.env
```

### Regular Security Updates

```bash
# Update system packages
sudo apt update && sudo apt upgrade

# Update PHP packages
sudo apt update && sudo apt upgrade php*

# Update Composer dependencies
composer update --with-dependencies
```

## Support and Documentation

### Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Super Marketer System Requirements](./REQUIREMENTS.md)
- [API Documentation](./API_DOCUMENTATION.md)
- [User Training Materials](./USER_TRAINING.md)

### Getting Help

For deployment issues or questions:

1. Check the troubleshooting section above
2. Review application logs
3. Consult the system validation output
4. Contact the development team with specific error messages and logs

### Emergency Contacts

- **Development Team**: dev-team@easyrent.com
- **System Administrator**: sysadmin@easyrent.com
- **Database Administrator**: dba@easyrent.com

---

**Last Updated**: December 2024  
**Version**: 1.0  
**Maintained by**: EasyRent Development Team