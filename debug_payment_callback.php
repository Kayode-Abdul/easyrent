<?php
/**
 * Debug script to test payment callback process
 * This simulates the payment callback to identify issues
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
use App\Models\Property;

echo "=== Payment Callback Debug ===\n\n";

// Step 1: Check database connectivity
echo "1. Testing database connectivity...\n";
try {
    $userCount = User::count();
    echo "   ✅ Database connected. Users count: $userCount\n";
} catch (\Exception $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 2: Check if we have required data
echo "\n2. Checking required data...\n";
$user = User::first();
$apartment = Apartment::first();
$property = Property::first();

if (!$user) {
    echo "   ❌ No users found\n";
} else {
    echo "   ✅ User found: {$user->first_name} {$user->last_name} (ID: {$user->user_id})\n";
}

if (!$apartment) {
    echo "   ❌ No apartments found\n";
} else {
    echo "   ✅ Apartment found: ID {$apartment->apartment_id}\n";
}

if (!$property) {
    echo "   ❌ No properties found\n";
} else {
    echo "   ✅ Property found: ID {$property->prop_id}\n";
}

// Step 3: Create test proforma receipt
echo "\n3. Creating test proforma receipt...\n";
try {
    $testReference = 'test_debug_' . time();
    
    $proforma = new ProfomaReceipt();
    $proforma->transaction_id = $testReference;
    $proforma->tenant_id = $user->user_id;
    $proforma->user_id = $user->user_id; // Same user as landlord for simplicity
    $proforma->apartment_id = $apartment->apartment_id;
    $proforma->amount = 50000;
    $proforma->duration = 12;
    $proforma->status = ProfomaReceipt::STATUS_NEW;
    $proforma->save();
    
    echo "   ✅ Proforma created: ID {$proforma->id}\n";
} catch (\Exception $e) {
    echo "   ❌ Proforma creation failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 4: Test payment creation
echo "\n4. Testing payment creation...\n";
try {
    // Check if payment already exists
    $existingPayment = Payment::where('transaction_id', $testReference)->first();
    if ($existingPayment) {
        echo "   ⚠️  Payment already exists, deleting for test...\n";
        $existingPayment->delete();
    }
    
    $payment = new Payment();
    $payment->transaction_id = $testReference;
    $payment->payment_reference = $testReference;
    $payment->amount = 50000;
    $payment->tenant_id = $proforma->tenant_id;
    $payment->landlord_id = $proforma->user_id;
    $payment->apartment_id = $proforma->apartment_id;
    $payment->status = 'completed';
    $payment->payment_method = 'test';
    $payment->duration = $proforma->duration;
    $payment->paid_at = now();
    
    echo "   Attempting to save payment...\n";
    $saved = $payment->save();
    
    if ($saved) {
        echo "   ✅ Payment created successfully: ID {$payment->id}\n";
        
        // Verify it exists
        $verifyPayment = Payment::find($payment->id);
        if ($verifyPayment) {
            echo "   ✅ Payment verified in database\n";
        } else {
            echo "   ❌ Payment not found after creation\n";
        }
    } else {
        echo "   ❌ Payment save() returned false\n";
    }
    
} catch (\Exception $e) {
    echo "   ❌ Payment creation failed: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

// Step 5: Test foreign key constraints
echo "\n5. Testing foreign key constraints...\n";
try {
    // Check if tenant_id exists in users table
    $tenantExists = User::where('user_id', $proforma->tenant_id)->exists();
    echo "   Tenant ID {$proforma->tenant_id} exists: " . ($tenantExists ? '✅ Yes' : '❌ No') . "\n";
    
    // Check if landlord_id exists in users table
    $landlordExists = User::where('user_id', $proforma->user_id)->exists();
    echo "   Landlord ID {$proforma->user_id} exists: " . ($landlordExists ? '✅ Yes' : '❌ No') . "\n";
    
    // Check if apartment_id exists in apartments table
    $apartmentExists = Apartment::where('apartment_id', $proforma->apartment_id)->exists();
    echo "   Apartment ID {$proforma->apartment_id} exists: " . ($apartmentExists ? '✅ Yes' : '❌ No') . "\n";
    
} catch (\Exception $e) {
    echo "   ❌ Foreign key check failed: " . $e->getMessage() . "\n";
}

// Step 6: Test with minimal data
echo "\n6. Testing with minimal payment data...\n";
try {
    $minimalPayment = new Payment();
    $minimalPayment->transaction_id = 'minimal_test_' . time();
    $minimalPayment->amount = 1000;
    $minimalPayment->tenant_id = $user->user_id;
    $minimalPayment->landlord_id = $user->user_id;
    $minimalPayment->apartment_id = $apartment->apartment_id;
    $minimalPayment->status = 'pending';
    $minimalPayment->duration = 1;
    
    $saved = $minimalPayment->save();
    
    if ($saved) {
        echo "   ✅ Minimal payment created: ID {$minimalPayment->id}\n";
    } else {
        echo "   ❌ Minimal payment creation failed\n";
    }
    
} catch (\Exception $e) {
    echo "   ❌ Minimal payment failed: " . $e->getMessage() . "\n";
}

// Step 7: Check payment table structure
echo "\n7. Checking payment table structure...\n";
try {
    $columns = \DB::select("DESCRIBE payments");
    echo "   Payment table columns:\n";
    foreach ($columns as $column) {
        echo "     - {$column->Field}: {$column->Type} " . ($column->Null === 'YES' ? '(nullable)' : '(required)') . "\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Table structure check failed: " . $e->getMessage() . "\n";
}

echo "\n=== Debug Complete ===\n";
echo "If payments are still not being created, check:\n";
echo "1. Database permissions\n";
echo "2. Foreign key constraints\n";
echo "3. Required vs nullable fields\n";
echo "4. Enum values for status field\n";
echo "5. Laravel logs for detailed errors\n";
?>