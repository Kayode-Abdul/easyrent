# Payment Database Issue - Troubleshooting Guide

## 🚨 IMMEDIATE STEPS TO DIAGNOSE THE ISSUE

### Step 1: Run Diagnostic Script

```bash
php diagnose_payment_issue.php
```

This will check:

-   Database connectivity
-   Basic payment creation
-   Table structure
-   Recent error logs
-   Callback URL accessibility

### Step 2: Test Manual Payment Creation

Visit: `/create-test-payment`
This will attempt to create a payment record manually to test if the database constraints are working.

### Step 3: Check Laravel Logs

```bash
tail -f storage/logs/laravel.log
```

Look for any errors during payment processing.

### Step 4: Test Payment Creation Script

```bash
php test_payment_creation.php
```

This tests payment creation outside of the web context.

## 🔍 COMMON ISSUES AND SOLUTIONS

### Issue 1: Foreign Key Constraint Violations

**Symptoms**: Payment creation fails with foreign key errors
**Solution**:

-   Ensure `tenant_id` exists in `users` table
-   Ensure `landlord_id` exists in `users` table
-   Ensure `apartment_id` exists in `apartments` table

### Issue 2: Status Enum Constraint

**Symptoms**: Payment fails with invalid status value
**Solution**: Run the migration to update status enum:

```bash
php artisan migrate
```

### Issue 3: Callback Not Reaching Server

**Symptoms**: No logs showing callback received
**Solutions**:

-   Check Paystack webhook URL configuration
-   Verify server is accessible from external networks
-   Check firewall settings
-   Use webhook_logger.php to capture raw callbacks

### Issue 4: Database Transaction Rollback

**Symptoms**: Payment appears to be created but doesn't persist
**Solution**: Check for exceptions in the transaction block

### Issue 5: Data Type Mismatches

**Symptoms**: Errors about data types or field lengths
**Solution**: Verify field types match between model and database

## 🛠️ DEBUGGING TOOLS PROVIDED

1. **diagnose_payment_issue.php** - Quick diagnostic
2. **debug_payment_callback.php** - Comprehensive callback testing
3. **test_payment_creation.php** - Manual payment creation
4. **webhook_logger.php** - Raw webhook capture
5. **Enhanced logging** in PaymentController
6. **Test routes** for manual testing

## 🎯 STEP-BY-STEP DEBUGGING PROCESS

### Phase 1: Basic Checks

1. Run `diagnose_payment_issue.php`
2. Check if basic payment creation works
3. Verify database table structure

### Phase 2: Callback Testing

1. Make a test payment
2. Check Laravel logs for callback reception
3. Use webhook_logger.php if needed
4. Test callback URL accessibility

### Phase 3: Data Validation

1. Verify all foreign key relationships
2. Check data types and constraints
3. Test with minimal payment data

### Phase 4: Manual Testing

1. Use `/create-test-payment` route
2. Run `test_payment_creation.php`
3. Create payment records manually in database

## 🚀 QUICK FIXES TO TRY

### Fix 1: Update Status Enum

```sql
ALTER TABLE payments MODIFY COLUMN status ENUM('pending', 'completed', 'success', 'failed') DEFAULT 'pending';
```

### Fix 2: Check Required Fields

Ensure these fields are not null in your payment creation:

-   transaction_id
-   tenant_id
-   landlord_id
-   apartment_id
-   amount
-   status
-   duration

### Fix 3: Bypass Complex Logic

Use the fallback payment creation method that's now included in the PaymentController.

## 📞 SUPPORT INFORMATION

If the issue persists after trying these steps:

1. **Collect Debug Information**:

    - Output from `diagnose_payment_issue.php`
    - Laravel log entries during payment
    - Database table structure (`DESCRIBE payments`)
    - Sample payment data that's failing

2. **Check Paystack Dashboard**:

    - Webhook URL configuration
    - Recent webhook delivery attempts
    - Payment status in Paystack

3. **Database Investigation**:
    - Check if any payments exist: `SELECT COUNT(*) FROM payments`
    - Check recent proforma receipts: `SELECT * FROM profoma_receipt ORDER BY created_at DESC LIMIT 5`
    - Verify user and apartment data exists

## ⚡ EMERGENCY WORKAROUND

If payments need to work immediately, you can:

1. **Manual Payment Entry**: Use the `/create-test-payment` route to create payments manually
2. **Simplified Callback**: Temporarily simplify the callback logic to just create basic payment records
3. **Database Direct Insert**: Insert payment records directly into the database as a last resort

Remember to revert any emergency workarounds once the root issue is identified and fixed.
