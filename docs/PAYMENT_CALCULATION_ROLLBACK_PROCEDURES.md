# Payment Calculation Service - Rollback Procedures

## Overview

This document provides comprehensive rollback procedures for the Payment Calculation Service in case of deployment issues, critical bugs, or system failures. These procedures ensure minimal downtime and data integrity during emergency situations.

## When to Rollback

### Critical Situations Requiring Immediate Rollback

1. **Calculation Accuracy Issues**
   - Payment totals are consistently incorrect
   - Proforma amounts don't match expected values
   - EasyRent invitations show wrong payment amounts

2. **System Performance Degradation**
   - Payment calculations taking excessive time (>5 seconds)
   - Database queries timing out
   - Memory usage spikes causing server instability

3. **Service Unavailability**
   - PaymentCalculationService cannot be instantiated
   - Critical calculation methods throwing exceptions
   - Database connectivity issues for payment tables

4. **Data Integrity Concerns**
   - Calculation results are inconsistent across requests
   - Audit logs show suspicious calculation patterns
   - Database corruption in payment-related tables

5. **Security Vulnerabilities**
   - Input validation bypassed
   - Unauthorized access to calculation endpoints
   - Potential data exposure through calculation logs

## Pre-Rollback Assessment

### 1. Immediate Impact Assessment
```bash
# Check current system status
php artisan payment-calculation:health-check

# Verify calculation accuracy
php artisan tinker --execute="
\$service = app('App\Services\Payment\PaymentCalculationServiceInterface');
\$testResults = [];
\$testCases = [
    ['price' => 100000, 'duration' => 12, 'type' => 'total', 'expected' => 100000],
    ['price' => 50000, 'duration' => 12, 'type' => 'monthly', 'expected' => 600000]
];

foreach (\$testCases as \$test) {
    \$result = \$service->calculatePaymentTotal(\$test['price'], \$test['duration'], \$test['type']);
    \$testResults[] = [
        'test' => \$test,
        'result' => \$result->totalAmount,
        'passed' => \$result->isValid && \$result->totalAmount == \$test['expected']
    ];
}

foreach (\$testResults as \$test) {
    echo (\$test['passed'] ? 'PASS' : 'FAIL') . ': ' . json_encode(\$test) . PHP_EOL;
}
"

# Check error rates
tail -n 100 storage/logs/laravel.log | grep -i "payment.*calculation.*error" | wc -l
```

### 2. Backup Verification
```bash
# List available backups
ls -la /var/backups/easyrent/payment_calculation/

# Verify backup integrity
BACKUP_PATH="/var/backups/easyrent/payment_calculation/backup_YYYYMMDD_HHMMSS"
if [ -f "$BACKUP_PATH/database_full.sql" ]; then
    echo "Database backup available"
else
    echo "WARNING: Database backup missing"
fi

if [ -d "$BACKUP_PATH/service_files" ]; then
    echo "Service files backup available"
else
    echo "WARNING: Service files backup missing"
fi
```

### 3. User Impact Assessment
```bash
# Check active user sessions
php artisan tinker --execute="
\$activeUsers = DB::table('sessions')->count();
echo 'Active user sessions: ' . \$activeUsers . PHP_EOL;
"

# Check recent payment activities
php artisan tinker --execute="
\$recentPayments = DB::table('payments')
    ->where('created_at', '>=', now()->subHours(1))
    ->count();
echo 'Recent payments (last hour): ' . \$recentPayments . PHP_EOL;
"
```

## Rollback Procedures

### Method 1: Automated Rollback (Recommended)

#### Step 1: Identify Backup Timestamp
```bash
# List recent backups
ls -lt /var/backups/easyrent/payment_calculation/ | head -10

# Choose the backup timestamp (format: YYYYMMDD_HHMMSS)
BACKUP_TIMESTAMP="20241216_143022"  # Example timestamp
```

#### Step 2: Execute Automated Rollback
```bash
# Navigate to application directory
cd /var/www/easyrent

# Execute rollback script
./scripts/deploy_payment_calculation_service.sh rollback $BACKUP_TIMESTAMP
```

#### Step 3: Verify Rollback Success
```bash
# Check service status
./scripts/deploy_payment_calculation_service.sh health-check

# Verify calculations are working
php artisan tinker --execute="
\$service = app('App\Services\Payment\PaymentCalculationServiceInterface');
\$result = \$service->calculatePaymentTotal(100000, 12, 'total');
echo 'Test calculation result: ' . \$result->totalAmount . PHP_EOL;
echo 'Is valid: ' . (\$result->isValid ? 'Yes' : 'No') . PHP_EOL;
"
```

### Method 2: Manual Rollback

#### Step 1: Enable Maintenance Mode
```bash
php artisan down --message="Emergency rollback in progress" --retry=30
```

#### Step 2: Restore Database
```bash
# Set backup path
BACKUP_PATH="/var/backups/easyrent/payment_calculation/backup_YYYYMMDD_HHMMSS"

# Get database credentials
DB_NAME=$(php artisan tinker --execute="echo config('database.connections.mysql.database');")
DB_USER=$(php artisan tinker --execute="echo config('database.connections.mysql.username');")
DB_PASS=$(php artisan tinker --execute="echo config('database.connections.mysql.password');")

# Restore full database
mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$BACKUP_PATH/database_full.sql"

# Alternatively, restore specific tables only
mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$BACKUP_PATH/apartments_backup.sql"
mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$BACKUP_PATH/profoma_receipts_backup.sql"
```

#### Step 3: Restore Service Files
```bash
# Backup current files (in case rollback fails)
cp -r app/Services/Payment app/Services/Payment.rollback.$(date +%s)
cp config/payment_calculation.php config/payment_calculation.php.rollback.$(date +%s)

# Restore service files from backup
cp -r "$BACKUP_PATH/service_files/Payment" app/Services/
cp "$BACKUP_PATH/service_files/payment_calculation.php" config/
cp "$BACKUP_PATH/service_files/PaymentCalculationServiceProvider.php" app/Providers/
```

#### Step 4: Restore Controllers
```bash
# Backup current controllers
cp app/Http/Controllers/ProfomaController.php app/Http/Controllers/ProfomaController.php.rollback.$(date +%s)
cp app/Http/Controllers/ApartmentInvitationController.php app/Http/Controllers/ApartmentInvitationController.php.rollback.$(date +%s)
cp app/Http/Controllers/PaymentController.php app/Http/Controllers/PaymentController.php.rollback.$(date +%s)

# Restore controllers from backup
cp "$BACKUP_PATH/controllers/ProfomaController.php" app/Http/Controllers/
cp "$BACKUP_PATH/controllers/ApartmentInvitationController.php" app/Http/Controllers/
cp "$BACKUP_PATH/controllers/PaymentController.php" app/Http/Controllers/
```

#### Step 5: Clear Caches and Optimize
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Step 6: Disable Maintenance Mode
```bash
php artisan up
```

### Method 3: Partial Rollback (Service Only)

If only the service implementation needs to be rolled back:

#### Step 1: Restore Service Files Only
```bash
BACKUP_PATH="/var/backups/easyrent/payment_calculation/backup_YYYYMMDD_HHMMSS"

# Restore only the service implementation
cp -r "$BACKUP_PATH/service_files/Payment" app/Services/
cp "$BACKUP_PATH/service_files/payment_calculation.php" config/

# Clear service-related caches
php artisan cache:clear
php artisan config:clear
```

#### Step 2: Verify Service Functionality
```bash
php artisan tinker --execute="
try {
    \$service = app('App\Services\Payment\PaymentCalculationServiceInterface');
    \$result = \$service->calculatePaymentTotal(100000, 12, 'total');
    echo 'Service rollback successful: ' . \$result->totalAmount . PHP_EOL;
} catch (Exception \$e) {
    echo 'Service rollback failed: ' . \$e->getMessage() . PHP_EOL;
}
"
```

## Post-Rollback Verification

### 1. System Health Check
```bash
# Run comprehensive health check
./scripts/deploy_payment_calculation_service.sh health-check

# Check system logs for errors
tail -n 50 storage/logs/laravel.log | grep -i error

# Verify web server is responding
curl -I http://localhost/
```

### 2. Calculation Accuracy Verification
```bash
php artisan tinker --execute="
\$service = app('App\Services\Payment\PaymentCalculationServiceInterface');

// Test cases covering different scenarios
\$testCases = [
    ['price' => 500000, 'duration' => 12, 'type' => 'total', 'expected' => 500000],
    ['price' => 50000, 'duration' => 12, 'type' => 'monthly', 'expected' => 600000],
    ['price' => 100000, 'duration' => 6, 'type' => 'total', 'expected' => 100000],
    ['price' => 25000, 'duration' => 24, 'type' => 'monthly', 'expected' => 600000],
    ['price' => 0, 'duration' => 1, 'type' => 'total', 'expected' => 0]
];

\$allPassed = true;
foreach (\$testCases as \$test) {
    \$result = \$service->calculatePaymentTotal(\$test['price'], \$test['duration'], \$test['type']);
    \$passed = \$result->isValid && \$result->totalAmount == \$test['expected'];
    \$allPassed = \$allPassed && \$passed;
    
    echo (\$passed ? 'PASS' : 'FAIL') . ': ' . 
         \$test['price'] . ' (' . \$test['type'] . ') * ' . \$test['duration'] . 
         ' = ' . \$result->totalAmount . ' (expected: ' . \$test['expected'] . ')' . PHP_EOL;
}

echo PHP_EOL . 'Overall result: ' . (\$allPassed ? 'ALL TESTS PASSED' : 'SOME TESTS FAILED') . PHP_EOL;
"
```

### 3. Database Integrity Check
```bash
php artisan tinker --execute="
// Check apartment pricing configurations
\$apartmentsWithPricing = DB::table('apartments')
    ->whereNotNull('pricing_type')
    ->count();
echo 'Apartments with pricing configuration: ' . \$apartmentsWithPricing . PHP_EOL;

// Check proforma receipts
\$proformaCount = DB::table('profoma_receipts')->count();
echo 'Total proforma receipts: ' . \$proformaCount . PHP_EOL;

// Check for any null or invalid pricing types
\$invalidPricingTypes = DB::table('apartments')
    ->whereNotIn('pricing_type', ['total', 'monthly'])
    ->whereNotNull('pricing_type')
    ->count();
echo 'Invalid pricing types: ' . \$invalidPricingTypes . PHP_EOL;
"
```

### 4. User Interface Testing
```bash
# Test proforma generation (manual verification required)
echo "Manual testing required:"
echo "1. Log in to the system"
echo "2. Navigate to proforma generation"
echo "3. Generate a test proforma"
echo "4. Verify calculation accuracy"
echo "5. Test EasyRent invitation links"
```

## Emergency Contacts and Escalation

### Immediate Response Team
- **Technical Lead**: [Name] - [Phone] - [Email]
- **Database Administrator**: [Name] - [Phone] - [Email]
- **DevOps Engineer**: [Name] - [Phone] - [Email]

### Escalation Procedures

#### Level 1: Technical Team Response (0-15 minutes)
- Execute automated rollback procedures
- Verify system functionality
- Document issues and resolution steps

#### Level 2: Management Notification (15-30 minutes)
- Notify technical management
- Assess business impact
- Coordinate with customer support team

#### Level 3: Executive Escalation (30+ minutes)
- Notify executive team if issues persist
- Consider external vendor support
- Prepare customer communication

## Communication Templates

### Internal Team Notification
```
Subject: URGENT - Payment Calculation Service Rollback Initiated

Team,

A rollback of the Payment Calculation Service has been initiated due to [REASON].

Status: [IN PROGRESS/COMPLETED]
Rollback Method: [AUTOMATED/MANUAL]
Backup Timestamp: [YYYYMMDD_HHMMSS]
Expected Resolution: [TIME]

Current Impact:
- [DESCRIBE IMPACT]

Actions Taken:
- [LIST ACTIONS]

Next Steps:
- [LIST NEXT STEPS]

Point of Contact: [NAME] - [CONTACT INFO]
```

### Customer Communication (if needed)
```
Subject: Temporary Service Maintenance - Payment Processing

Dear Valued Customers,

We are currently performing emergency maintenance on our payment calculation system to ensure accuracy and reliability.

During this time:
- Property listings remain accessible
- New proforma generation may be temporarily unavailable
- Existing payment processes are not affected

We expect to complete this maintenance within [TIME FRAME].

We apologize for any inconvenience and appreciate your patience.

Best regards,
EasyRent Support Team
```

## Prevention Measures

### 1. Enhanced Testing
- Implement comprehensive pre-deployment testing
- Add automated calculation accuracy tests
- Perform load testing before production deployment

### 2. Monitoring Improvements
- Set up real-time calculation accuracy monitoring
- Implement automated alerts for calculation errors
- Add performance monitoring for calculation response times

### 3. Deployment Safeguards
- Implement blue-green deployment strategy
- Add automatic rollback triggers for critical failures
- Require manual approval for production deployments

### 4. Backup Enhancements
- Increase backup frequency during deployment periods
- Implement automated backup verification
- Store backups in multiple locations

## Rollback Testing

### Regular Rollback Drills
Perform monthly rollback drills to ensure procedures work correctly:

```bash
# Create test backup
./scripts/deploy_payment_calculation_service.sh deploy

# Wait 5 minutes, then test rollback
BACKUP_TIMESTAMP=$(ls -t /var/backups/easyrent/payment_calculation/ | head -1 | cut -d'_' -f2-3)
./scripts/deploy_payment_calculation_service.sh rollback $BACKUP_TIMESTAMP

# Verify rollback success
./scripts/deploy_payment_calculation_service.sh health-check
```

### Rollback Performance Metrics
Track rollback performance:
- Time to complete rollback: Target < 10 minutes
- System availability during rollback: Target > 95%
- Data integrity verification: Target 100% success
- User impact duration: Target < 15 minutes

## Documentation Updates

After each rollback incident:

1. **Update Procedures**: Refine rollback procedures based on lessons learned
2. **Document Issues**: Record root causes and resolution steps
3. **Improve Monitoring**: Add monitoring for newly discovered failure modes
4. **Train Team**: Conduct post-incident training sessions

---

**Emergency Hotline**: [PHONE NUMBER]  
**Last Updated**: December 2024  
**Version**: 1.0  
**Review Schedule**: Monthly  
**Maintained by**: EasyRent DevOps Team