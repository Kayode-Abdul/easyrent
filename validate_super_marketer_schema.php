<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Validating Super Marketer System Database Schema...\n\n";

// Check if commission_rates table exists and has correct structure
echo "1. Checking commission_rates table...\n";
if (Schema::hasTable('commission_rates')) {
    echo "   ✓ commission_rates table exists\n";
    
    $columns = ['region', 'role_id', 'commission_percentage', 'effective_from', 'effective_until', 'created_by', 'is_active'];
    foreach ($columns as $column) {
        if (Schema::hasColumn('commission_rates', $column)) {
            echo "   ✓ Column '$column' exists\n";
        } else {
            echo "   ✗ Column '$column' missing\n";
        }
    }
} else {
    echo "   ✗ commission_rates table does not exist\n";
}

// Check if Super Marketer role exists
echo "\n2. Checking Super Marketer role...\n";
try {
    $superMarketerRole = DB::table('roles')->where('id', 9)->first();
    if ($superMarketerRole) {
        echo "   ✓ Super Marketer role (ID: 9) exists\n";
        echo "   ✓ Name: {$superMarketerRole->name}\n";
        echo "   ✓ Display Name: {$superMarketerRole->display_name}\n";
    } else {
        echo "   ✗ Super Marketer role (ID: 9) does not exist\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error checking Super Marketer role: " . $e->getMessage() . "\n";
}

// Check referrals table extensions
echo "\n3. Checking referrals table extensions...\n";
if (Schema::hasTable('referrals')) {
    echo "   ✓ referrals table exists\n";
    
    $newColumns = ['referral_level', 'parent_referral_id', 'commission_tier', 'regional_rate_snapshot', 'referral_code', 'referral_status'];
    foreach ($newColumns as $column) {
        if (Schema::hasColumn('referrals', $column)) {
            echo "   ✓ Column '$column' exists\n";
        } else {
            echo "   ✗ Column '$column' missing\n";
        }
    }
} else {
    echo "   ✗ referrals table does not exist\n";
}

// Check referral_chains table
echo "\n4. Checking referral_chains table...\n";
if (Schema::hasTable('referral_chains')) {
    echo "   ✓ referral_chains table exists\n";
    
    $columns = ['super_marketer_id', 'marketer_id', 'landlord_id', 'chain_hash', 'status', 'commission_breakdown'];
    foreach ($columns as $column) {
        if (Schema::hasColumn('referral_chains', $column)) {
            echo "   ✓ Column '$column' exists\n";
        } else {
            echo "   ✗ Column '$column' missing\n";
        }
    }
} else {
    echo "   ✗ referral_chains table does not exist\n";
}

echo "\nSchema validation complete!\n";