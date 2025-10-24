<?php
/**
 * Quick diagnostic tool for payment database issues
 * Run this to identify the most likely problems
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payment;
use App\Models\User;
use App\Models\Apartment;
use App\Models\ProfomaReceipt;

echo "🔍 PAYMENT ISSUE DIAGNOSTIC TOOL\n";
echo "================================\n\n";

// Test 1: Basic database connectivity
echo "1️⃣  Testing database connectivity...\n";
try {
    $count = \DB::table('payments')->count();
    echo "   ✅ Database connected. Current payments count: $count\n";
} catch (\Exception $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Check if we can create a simple payment
echo "\n2️⃣  Testing basic payment creation...\n";
try {
    // Get first user and apartment for testing
    $user = User::first();
    $apartment = Apartment::first();
    
    if (!$user || !$apartment) {
        echo "   ⚠️  Missing test data (user or apartment)\n";
        if (!$user) echo "      - No users found\n";
        if (!$apartment) echo "      - No apartments found\n";
    } else {
        // Try to create a minimal payment
        $testPayment = new Payment([
            'transaction_id' => 'diagnostic_' . time(),
            'amount' => 1000,
            'tenant_id' => $user->user_id,
            'landlord_id' => $user->user_id,
            'apartment_id' => $apartment->apartment_id,
            'status' => 'completed',
            'duration' => 1
        ]);
        
        $saved = $testPayment->save();
        
        if ($saved) {
            echo "   ✅ Basic payment creation works! Payment ID: {$testPayment->id}\n";
            // Clean up test payment
            $testPayment->delete();
        } else {
            echo "   ❌ Basic payment creation failed\n";
        }
    }
} catch (\Exception $e) {
    echo "   ❌ Payment creation error: " . $e->getMessage() . "\n";
    echo "   📝 Full error: " . $e->getTraceAsString() . "\n";
}

// Test 3: Check payment table structure
echo "\n3️⃣  Checking payment table structure...\n";
try {
    $columns = \DB::select("SHOW COLUMNS FROM payments");
    $requiredFields = ['transaction_id', 'tenant_id', 'landlord_id', 'apartment_id', 'amount', 'status', 'duration'];
    
    $existingFields = array_column($columns, 'Field');
    $missingFields = array_diff($requiredFields, $existingFields);
    
    if (empty($missingFields)) {
        echo "   ✅ All required fields exist\n";
    } else {
        echo "   ❌ Missing required fields: " . implode(', ', $missingFields) . "\n";
    }
    
    // Check status enum values
    $statusColumn = collect($columns)->firstWhere('Field', 'status');
    if ($statusColumn) {
        echo "   📋 Status field type: {$statusColumn->Type}\n";
        if (strpos($statusColumn->Type, 'completed') === false) {
            echo "   ⚠️  Status enum might not include 'completed'\n";
        }
    }
    
} catch (\Exception $e) {
    echo "   ❌ Table structure check failed: " . $e->getMessage() . "\n";
}

// Test 4: Check recent Laravel logs for payment errors
echo "\n4️⃣  Checking recent Laravel logs...\n";
try {
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $logContent = file_get_contents($logFile);
        $recentLogs = array_slice(explode("\n", $logContent), -50); // Last 50 lines
        
        $paymentErrors = array_filter($recentLogs, function($line) {
            return stripos($line, 'payment') !== false && 
                   (stripos($line, 'error') !== false || stripos($line, 'failed') !== false);
        });
        
        if (empty($paymentErrors)) {
            echo "   ✅ No recent payment errors in logs\n";
        } else {
            echo "   ⚠️  Found payment-related errors:\n";
            foreach (array_slice($paymentErrors, -3) as $error) {
                echo "      " . trim($error) . "\n";
            }
        }
    } else {
        echo "   ⚠️  Laravel log file not found\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Log check failed: " . $e->getMessage() . "\n";
}

// Test 5: Check if callback URL is accessible
echo "\n5️⃣  Testing callback URL accessibility...\n";
try {
    $callbackUrl = url('/payment/callback');
    echo "   📍 Callback URL: $callbackUrl\n";
    
    // Try to make a simple request to the callback URL
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 5,
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($callbackUrl, false, $context);
    if ($response !== false) {
        echo "   ✅ Callback URL is accessible\n";
    } else {
        echo "   ⚠️  Callback URL might not be accessible externally\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Callback URL test failed: " . $e->getMessage() . "\n";
}

// Test 6: Check ProfomaReceipt records
echo "\n6️⃣  Checking ProfomaReceipt records...\n";
try {
    $proformaCount = ProfomaReceipt::count();
    $recentProforma = ProfomaReceipt::latest()->first();
    
    echo "   📊 Total proforma receipts: $proformaCount\n";
    
    if ($recentProforma) {
        echo "   📋 Most recent proforma:\n";
        echo "      - ID: {$recentProforma->id}\n";
        echo "      - Transaction ID: " . ($recentProforma->transaction_id ?? 'NULL') . "\n";
        echo "      - Tenant ID: " . ($recentProforma->tenant_id ?? 'NULL') . "\n";
        echo "      - Amount: " . ($recentProforma->amount ?? 'NULL') . "\n";
        echo "      - Status: " . ($recentProforma->status ?? 'NULL') . "\n";
    } else {
        echo "   ⚠️  No proforma receipts found\n";
    }
} catch (\Exception $e) {
    echo "   ❌ ProfomaReceipt check failed: " . $e->getMessage() . "\n";
}

echo "\n🎯 RECOMMENDATIONS:\n";
echo "==================\n";

if (!isset($user) || !isset($apartment)) {
    echo "❗ CRITICAL: Missing basic data (users/apartments). Create test data first.\n";
}

echo "1. Check Laravel logs: tail -f storage/logs/laravel.log\n";
echo "2. Test payment creation manually using test_payment_creation.php\n";
echo "3. Verify Paystack webhook URL in Paystack dashboard\n";
echo "4. Run the debug_payment_callback.php script\n";
echo "5. Check if migrations are up to date: php artisan migrate:status\n";

echo "\n✅ Diagnostic complete!\n";
?>