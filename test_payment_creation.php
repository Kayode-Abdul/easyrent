<?php
/**
 * Test script to manually create a payment record
 * This will help identify if there are database issues
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payment;
use App\Models\User;
use App\Models\Apartment;

echo "=== Payment Creation Test ===\n\n";

try {
    // Get a test user
    $user = User::first();
    if (!$user) {
        echo "No users found in database. Creating test user...\n";
        $user = new User();
        $user->user_id = 999999;
        $user->first_name = 'Test';
        $user->last_name = 'User';
        $user->username = 'testuser999';
        $user->email = 'testuser999@example.com';
        $user->password = bcrypt('password');
        $user->role = 3; // Tenant
        $user->save();
        echo "Test user created with ID: {$user->user_id}\n";
    } else {
        echo "Using existing user: {$user->first_name} {$user->last_name} (ID: {$user->user_id})\n";
    }

    // Get a test apartment
    $apartment = Apartment::first();
    if (!$apartment) {
        echo "No apartments found in database. Cannot proceed with test.\n";
        exit(1);
    } else {
        echo "Using apartment ID: {$apartment->apartment_id}\n";
    }

    // Try to create a test payment
    echo "\nAttempting to create test payment...\n";
    
    $payment = new Payment();
    $payment->transaction_id = 'test_' . time();
    $payment->payment_reference = 'test_ref_' . time();
    $payment->amount = 50000; // 50,000 naira
    $payment->tenant_id = $user->user_id;
    $payment->landlord_id = $user->user_id; // Same user for simplicity
    $payment->apartment_id = $apartment->apartment_id;
    $payment->status = 'completed';
    $payment->payment_method = 'test';
    $payment->duration = 12;
    $payment->paid_at = now();
    
    echo "Payment data prepared:\n";
    echo "  Transaction ID: {$payment->transaction_id}\n";
    echo "  Amount: {$payment->amount}\n";
    echo "  Tenant ID: {$payment->tenant_id}\n";
    echo "  Landlord ID: {$payment->landlord_id}\n";
    echo "  Apartment ID: {$payment->apartment_id}\n";
    echo "  Status: {$payment->status}\n";
    
    $saved = $payment->save();
    
    if ($saved) {
        echo "\n✅ SUCCESS: Payment record created successfully!\n";
        echo "Payment ID: {$payment->id}\n";
        
        // Verify it was saved
        $savedPayment = Payment::find($payment->id);
        if ($savedPayment) {
            echo "✅ VERIFIED: Payment record exists in database\n";
            echo "Saved payment data: " . json_encode($savedPayment->toArray(), JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "❌ ERROR: Payment record not found after saving\n";
        }
    } else {
        echo "❌ ERROR: Payment save() returned false\n";
    }

} catch (\Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
?>