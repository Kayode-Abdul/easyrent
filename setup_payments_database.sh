#!/bin/bash

echo "🚀 Setting up Payments Database"
echo "==============================="

# Clear configuration cache
echo "1. Clearing configuration cache..."
php artisan config:clear

# Clear route cache
echo "2. Clearing route cache..."
php artisan route:clear

# Run migrations
echo "3. Running database migrations..."
php artisan migrate --force

# Check if payments table exists
echo "4. Verifying payments table..."
php artisan tinker --execute="echo 'Payments table exists: ' . (Schema::hasTable('payments') ? 'YES' : 'NO') . PHP_EOL;"

# Test payment creation
echo "5. Testing payment creation..."
php fix_payment_database_issue.php

echo ""
echo "✅ Setup complete!"
echo "Your payment system should now work correctly."