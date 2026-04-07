# EasyRent Shared Hosting Deployment

## Quick Start (5 Steps)

1. **Prepare**: `bash prepare-deployment.sh`
2. **Build**: `npm run build`
3. **Upload**: Via FTP/SFTP to `public_html/`
4. **Configure**: Update `.env` and run migrations
5. **Secure**: Delete `setup.php` and verify security

## Documentation Files

| File | Purpose |
|------|---------|
| `DEPLOYMENT_GUIDE.md` | Complete step-by-step guide |
| `DEPLOYMENT_CHECKLIST.md` | Verification checklist |
| `DEPLOYMENT_QUICK_REFERENCE.md` | Quick lookup reference |
| `SHARED_HOSTING_OPTIMIZATION.md` | Performance optimization |
| `DEPLOYMENT_FILES_SUMMARY.md` | Overview of all files |

## Key Files Created

- `.env.shared-hosting` - Production environment template
- `public/setup.php` - Web-based setup (delete after use)
- `prepare-deployment.sh` - Automated preparation script
- `public/.htaccess` - Enhanced Apache configuration

## Deployment Steps

### Local (30 min)
```bash
bash prepare-deployment.sh
npm run build
```

### Hosting (15 min)
- Create database
- Configure PHP
- Enable SSL

### Upload (10-30 min)
- Via FTP/SFTP or cPanel

### Configure (15 min)
```bash
chmod -R 777 storage bootstrap/cache
nano .env  # Update credentials
php artisan migrate --force
php artisan config:cache
```

### Test & Secure (10 min)
- Test all features
- Delete `setup.php`
- Verify security

## Common Commands

```bash
# Set permissions
chmod -R 755 app bootstrap config database resources routes
chmod -R 777 storage bootstrap/cache
chmod 644 .env

# Database
php artisan migrate --force
php artisan db:seed --force

# Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Logs
tail -f storage/logs/laravel.log
```

## Troubleshooting

| Issue | Fix |
|-------|-----|
| 500 Error | Check `storage/logs/laravel.log` |
| DB Error | Verify credentials in `.env` |
| Payment Error | Check Paystack keys |
| Permission Error | Run `chmod` commands |

## Security Checklist

- [ ] Delete `setup.php`
- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`
- [ ] Verify `.env` permissions (644)
- [ ] Enable SSL/HTTPS
- [ ] Setup backups

## Next Steps

1. Read `DEPLOYMENT_GUIDE.md` for complete instructions
2. Use `DEPLOYMENT_CHECKLIST.md` during deployment
3. Reference `DEPLOYMENT_QUICK_REFERENCE.md` for quick lookups
4. Check `SHARED_HOSTING_OPTIMIZATION.md` for performance tuning

## Support

- Laravel Docs: https://laravel.com/docs
- Paystack Docs: https://paystack.com/docs
- cPanel Docs: https://documentation.cpanel.net/
