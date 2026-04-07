# EasyRent Deployment - Quick Reference Card

## Pre-Deployment (5 minutes)

```bash
# 1. Prepare codebase
bash prepare-deployment.sh

# 2. Build assets
npm run build

# 3. Verify build
ls -la public/build/
```

## Upload (10-30 minutes)

**Choose one method:**

### FTP/SFTP
```bash
lftp -u username,password ftp.yourdomain.com
mirror -R . /public_html/
quit
```

### cPanel File Manager
1. Login to cPanel
2. File Manager → public_html/
3. Upload files

### SSH
```bash
scp -r . user@host:/home/user/public_html/
```

## Post-Upload (10 minutes)

### 1. Set Permissions
```bash
ssh user@host
cd public_html
chmod -R 755 app bootstrap config database resources routes
chmod -R 777 storage bootstrap/cache
chmod 644 .env
chmod 755 artisan
```

### 2. Configure .env
```bash
nano .env
# Update: DB_*, PAYSTACK_*, MAIL_*, APP_URL
```

### 3. Run Migrations
```bash
# Option A: SSH
php artisan migrate --force
php artisan db:seed --force

# Option B: Web (setup.php)
# https://yourdomain.com/setup.php?key=YOUR_KEY&cmd=migrate
# https://yourdomain.com/setup.php?key=YOUR_KEY&cmd=seed
```

### 4. Cache Configuration
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Testing (5 minutes)

- [ ] Homepage loads
- [ ] Login works
- [ ] Create apartment works
- [ ] Payment works
- [ ] No errors in logs

## Security (2 minutes)

```bash
# Delete setup script
rm public/setup.php

# Verify .env is secure
chmod 644 .env

# Verify debug is off
grep APP_DEBUG .env  # Should be false
```

## Monitoring (ongoing)

```bash
# Check error logs
tail -f storage/logs/laravel.log

# Check disk usage
du -sh storage/

# Check database
mysql -u user -p database
```

---

## Common Commands

### Database
```bash
php artisan migrate --force          # Run migrations
php artisan migrate:rollback --force # Rollback
php artisan db:seed --force          # Seed database
```

### Cache
```bash
php artisan cache:clear              # Clear cache
php artisan config:cache             # Cache config
php artisan route:cache              # Cache routes
php artisan view:cache               # Cache views
```

### Logs
```bash
tail -f storage/logs/laravel.log     # View logs
rm storage/logs/laravel.log          # Clear logs
```

### Permissions
```bash
chmod -R 755 app bootstrap config    # Read-only
chmod -R 777 storage bootstrap/cache # Writable
chmod 644 .env                       # Secure .env
```

---

## Troubleshooting

### 500 Error
```bash
# Check logs
tail -f storage/logs/laravel.log

# Check permissions
ls -la storage/
ls -la bootstrap/cache/

# Check .env
cat .env | head -20
```

### Database Error
```bash
# Test connection
mysql -h localhost -u user -p database

# Check credentials
cat .env | grep DB_
```

### Payment Error
```bash
# Check Paystack keys
cat .env | grep PAYSTACK

# Check logs
tail -f storage/logs/laravel.log | grep -i payment
```

### Email Error
```bash
# Check SMTP settings
cat .env | grep MAIL_

# Test connection
telnet smtp.mailtrap.io 465
```

---

## File Permissions Reference

```
755 = rwxr-xr-x (directories, executable files)
644 = rw-r--r-- (regular files)
777 = rwxrwxrwx (writable directories)
```

**Correct permissions:**
- `app/`, `bootstrap/`, `config/`, `database/`, `resources/`, `routes/` → 755
- `storage/`, `bootstrap/cache/` → 777
- `.env` → 644
- `artisan` → 755

---

## Environment Variables Checklist

```env
✓ APP_NAME=EasyRent
✓ APP_ENV=production
✓ APP_DEBUG=false
✓ APP_URL=https://yourdomain.com
✓ DB_HOST=localhost
✓ DB_DATABASE=easyrent_prod
✓ DB_USERNAME=easyrent_user
✓ DB_PASSWORD=strong_password
✓ CACHE_DRIVER=file
✓ SESSION_DRIVER=file
✓ QUEUE_CONNECTION=sync
✓ MAIL_MAILER=smtp
✓ MAIL_HOST=your_smtp_host
✓ MAIL_PORT=465
✓ PAYSTACK_PUBLIC_KEY=pk_live_...
✓ PAYSTACK_SECRET_KEY=sk_live_...
```

---

## Performance Targets

- Homepage load: < 2 seconds
- Payment page: < 3 seconds
- Database query: < 100ms
- Memory per request: < 128MB
- Error rate: < 0.1%

---

## Backup & Restore

### Backup
```bash
# Via cPanel: Backups → Download Full Backup
# Via SSH:
mysqldump -u user -p database > backup.sql
tar -czf easyrent-backup.tar.gz .
```

### Restore
```bash
# Via SSH:
mysql -u user -p database < backup.sql
tar -xzf easyrent-backup.tar.gz
```

---

## Cron Job Setup

**Via cPanel → Cron Jobs:**
```
* * * * * /usr/bin/php /home/user/public_html/artisan schedule:run >> /dev/null 2>&1
```

---

## SSL Certificate

**Via cPanel:**
1. Go to AutoSSL or Let's Encrypt
2. Install certificate
3. Force HTTPS redirect

**Verify:**
```bash
# Check certificate
openssl s_client -connect yourdomain.com:443
```

---

## Support Contacts

- **Hosting Support**: [Your hosting provider]
- **Paystack Support**: support@paystack.com
- **Laravel Docs**: https://laravel.com/docs
- **cPanel Docs**: https://documentation.cpanel.net/

---

## Emergency Contacts

- **Hosting Admin**: [Your contact]
- **Database Admin**: [Your contact]
- **Security Officer**: [Your contact]

---

## Deployment Sign-Off

- [ ] All files uploaded
- [ ] Permissions set correctly
- [ ] Database migrated
- [ ] .env configured
- [ ] Cache cleared
- [ ] SSL enabled
- [ ] Tests passed
- [ ] Monitoring active
- [ ] Backups enabled
- [ ] setup.php deleted
- [ ] Team notified

**Deployed by:** ________________
**Date:** ________________
**Time:** ________________

