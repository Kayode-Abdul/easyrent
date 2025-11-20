# Live Server Deployment Guide - Benefactor System

## 🚀 Deployment Steps for Production

### Prerequisites
- SSH access to your live server
- Database backup completed
- Maintenance mode ready (optional but recommended)

---

## Step-by-Step Deployment

### 1. Backup Your Database (CRITICAL!)

```bash
# SSH into your live server
ssh user@your-server.com

# Navigate to your project
cd /path/to/your/project

# Backup database
php artisan db:backup
# OR manually:
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql
```

**⚠️ DO NOT SKIP THIS STEP!**

---

### 2. Enable Maintenance Mode (Recommended)

```bash
php artisan down --message="Upgrading benefactor payment system" --retry=60
```

This shows a maintenance page to users while you deploy.

---

### 3. Pull Latest Code

```bash
# If using Git
git pull origin main
# OR
git pull origin master

# Verify the new files are present
ls -la database/migrations/ | grep 2025_11_19
ls -la database/migrations/ | grep 2025_11_18
```

You should see:
- `2025_11_18_140304_add_phase1_features_to_benefactor_tables.php`
- `2025_11_18_163107_add_proforma_link_to_payment_invitations_and_payments.php`
- `2025_11_19_161940_fix_payment_invitations_foreign_keys.php`

---

### 4. Install/Update Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

---

### 5. Run Migrations

```bash
# Check which migrations need to run
php artisan migrate:status

# Run the migrations
php artisan migrate --force

# The --force flag is needed in production
```

**Expected Output:**
```
INFO  Running migrations.

2025_11_18_140304_add_phase1_features_to_benefactor_tables .... DONE
2025_11_18_163107_add_proforma_link_to_payment_invitations_and_payments .... DONE
2025_11_19_161940_fix_payment_invitations_foreign_keys .... DONE
```

---

### 6. Fix Existing Proforma Status (Important!)

This updates old proformas that were incorrectly marked as "confirmed":

```bash
php artisan tinker
```

Then paste this code:
```php
$updated = DB::table('profoma_receipt')
    ->where('status', 1)
    ->whereNotExists(function($query) {
        $query->select(DB::raw(1))
              ->from('payments')
              ->whereRaw('payments.transaction_id = profoma_receipt.transaction_id')
              ->where('status', 'completed');
    })
    ->update(['status' => 2]);

echo "Updated {$updated} proforma(s) from CONFIRMED to NEW status\n";
exit
```

---

### 7. Clear All Caches

```bash
# Clear application cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Rebuild optimized files
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### 8. Verify Installation

```bash
# Check routes are registered
php artisan route:list --name=benefactor

# Should show 14 routes including:
# - benefactor/payment/{token}/approve
# - benefactor/payment/{token}/decline
# - benefactor/payment/{payment}/pause
# - benefactor/payment/{payment}/resume
# - tenant/invite-benefactor
```

---

### 9. Test Database Structure

```bash
php artisan tinker
```

```php
// Verify foreign keys are correct
$fks = DB::select('SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = "payment_invitations" AND CONSTRAINT_TYPE = "FOREIGN KEY"');
foreach ($fks as $fk) {
    echo $fk->CONSTRAINT_NAME . "\n";
}
// Should NOT show: payment_invitations_tenant_id_foreign

// Verify indexes exist
$indexes = DB::select('SHOW INDEXES FROM payment_invitations WHERE Column_name = "tenant_id"');
foreach ($indexes as $index) {
    echo $index->Key_name . "\n";
}
// Should show: payment_invitations_tenant_id_index

exit
```

---

### 10. Disable Maintenance Mode

```bash
php artisan up
```

---

### 11. Test the System

#### Test 1: View Proforma
1. Login as a tenant
2. View a proforma
3. Verify you see: Accept, Reject, and "Invite Someone to Pay" buttons

#### Test 2: Invite Benefactor
1. Click "Invite Someone to Pay"
2. Enter an email address
3. Submit
4. Should succeed without 500 error

#### Test 3: Check Email
1. Verify benefactor receives invitation email
2. Click link in email
3. Should see approval page

---

## Rollback Plan (If Something Goes Wrong)

### Quick Rollback:

```bash
# 1. Enable maintenance mode
php artisan down

# 2. Restore database backup
mysql -u username -p database_name < backup_YYYYMMDD_HHMMSS.sql

# 3. Rollback migrations
php artisan migrate:rollback --step=3

# 4. Restore old code
git reset --hard HEAD~1  # or specific commit
composer install --no-dev

# 5. Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# 6. Bring site back up
php artisan up
```

---

## Alternative: Manual Database Updates (If Migrations Fail)

If migrations fail, you can run these SQL commands directly:

### 1. Add Phase 1 Fields:

```sql
-- Add to benefactors table
ALTER TABLE benefactors 
ADD COLUMN relationship_type ENUM('employer','parent','guardian','sponsor','organization','other') DEFAULT 'other' AFTER phone,
ADD COLUMN is_registered TINYINT(1) DEFAULT 0 AFTER type;

-- Add to payment_invitations table
ALTER TABLE payment_invitations
ADD COLUMN approval_status ENUM('pending_approval','approved','declined') DEFAULT 'pending_approval' AFTER status,
ADD COLUMN approved_at TIMESTAMP NULL AFTER accepted_at,
ADD COLUMN declined_at TIMESTAMP NULL AFTER approved_at,
ADD COLUMN decline_reason TEXT NULL AFTER declined_at,
ADD COLUMN proforma_id BIGINT UNSIGNED NULL AFTER benefactor_id,
ADD INDEX payment_invitations_proforma_id_index (proforma_id);

-- Add to benefactor_payments table
ALTER TABLE benefactor_payments
ADD COLUMN is_paused TINYINT(1) DEFAULT 0 AFTER status,
ADD COLUMN paused_at TIMESTAMP NULL AFTER cancelled_at,
ADD COLUMN pause_reason TEXT NULL AFTER paused_at,
ADD COLUMN payment_day_of_month INT NULL AFTER next_payment_date,
ADD COLUMN proforma_id BIGINT UNSIGNED NULL AFTER apartment_id,
ADD INDEX benefactor_payments_proforma_id_index (proforma_id);
```

### 2. Fix Foreign Keys:

```sql
-- Drop incorrect foreign keys (if they exist)
ALTER TABLE payment_invitations DROP FOREIGN KEY IF EXISTS payment_invitations_tenant_id_foreign;
ALTER TABLE benefactor_payments DROP FOREIGN KEY IF EXISTS benefactor_payments_tenant_id_foreign;

-- Add indexes (without foreign keys)
ALTER TABLE payment_invitations ADD INDEX IF NOT EXISTS payment_invitations_tenant_id_index (tenant_id);
ALTER TABLE benefactor_payments ADD INDEX IF NOT EXISTS benefactor_payments_tenant_id_index (tenant_id);
```

### 3. Fix Proforma Status:

```sql
UPDATE profoma_receipt 
SET status = 2 
WHERE status = 1 
AND NOT EXISTS (
    SELECT 1 FROM payments 
    WHERE payments.transaction_id = profoma_receipt.transaction_id 
    AND status = 'completed'
);
```

---

## Monitoring After Deployment

### Check Logs:
```bash
# Watch for errors
tail -f storage/logs/laravel.log

# Check for 500 errors
grep "500" storage/logs/laravel.log | tail -20
```

### Monitor Key Metrics:
- [ ] Users can view proformas
- [ ] Action buttons appear correctly
- [ ] Invitations send successfully
- [ ] No 500 errors in logs
- [ ] Email notifications working

---

## Common Issues & Solutions

### Issue 1: Migration Fails - Foreign Key Error
**Solution**: Use the manual SQL commands above

### Issue 2: Routes Not Found
**Solution**: 
```bash
php artisan route:clear
php artisan route:cache
```

### Issue 3: Views Not Updating
**Solution**:
```bash
php artisan view:clear
php artisan cache:clear
```

### Issue 4: Still Getting 500 Errors
**Solution**:
```bash
# Check the actual error
tail -50 storage/logs/laravel.log

# Verify database structure
php artisan tinker
>>> DB::select('DESCRIBE payment_invitations');
```

---

## Post-Deployment Checklist

- [ ] Database backup completed
- [ ] Migrations run successfully
- [ ] Proforma status updated
- [ ] Caches cleared
- [ ] Routes verified
- [ ] Test invitation sent successfully
- [ ] No errors in logs
- [ ] Maintenance mode disabled
- [ ] Users notified (if needed)
- [ ] Documentation updated

---

## Support

If you encounter issues:

1. **Check logs first**: `storage/logs/laravel.log`
2. **Verify database**: Run the verification commands above
3. **Test locally**: Ensure it works on dev before production
4. **Rollback if needed**: Use the rollback plan above

---

## Summary

**What Changed:**
1. Added Phase 1 benefactor features (approval, relationship, scheduling, pause)
2. Added proforma integration
3. Fixed foreign key constraints to work with `user_id`
4. Fixed proforma status display issue

**Database Changes:**
- 15 new fields across 3 tables
- Removed 2 foreign key constraints
- Added 2 indexes
- Updated proforma status values

**Zero Downtime:** If you follow these steps carefully, users will experience minimal disruption.

---

**Deployment Time Estimate:** 10-15 minutes  
**Risk Level:** Low (with proper backup)  
**Rollback Time:** 5 minutes

Good luck with your deployment! 🚀
