<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProfomaReceipt;
use App\Models\Payment;
use App\Models\Apartment;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

echo "🧪 Testing Proforma Payment Flow\n";
echo "================================\n\n";

try {
    // Check for recent proforma receipts
    echo "📋 Checking Recent Proforma Receipts:\n";
    echo str_repeat("-", 40) . "\n";
    
    $recentProformas = ProfomaReceipt::orderBy('created_at', 'desc')->take(5)->get();
    
    if ($recentProformas->count() === 0) {
        echo "❌ No proforma receipts found in database\n";
        echo "Creating a test proforma receipt...\n\n";
        
        // Create test data
        $testUser = User::first();
        $testApartment = Apartment::first();
        
        if (!$testUser || !$testApartment) {
            echo "❌ No users or apartments found to create test proforma\n";
            exit(1);
        }
        
        $testProforma = new ProfomaReceipt();
        $testProforma->user_id = $testUser->user_id;
        $testProforma->tenant_id = $testUser->user_id;
        $testProforma->apartment_id = $testApartment->apartment_id;
        $testProforma->amount = 1000000; // 1M naira
        $testProforma->duration = 12;
        $testProforma->status = ProfomaReceipt::STATUS_NEW;
        $testProforma->transaction_id = 'TEST_PROFORMA_' . time();
        $testProforma->save();
        
        echo "✅ Test proforma created with ID: {$testProforma->id}\n";
        $recentProformas = collect([$testProforma]);
    }
    
    foreach ($recentProformas as $proforma) {
        echo "Proforma ID: {$proforma->id}\n";
        echo "  Transaction ID: {$proforma->transaction_id}\n";
        echo "  Amount: ₦" . number_format($proforma->amount, 2) . "\n";
        echo "  Duration: {$proforma->duration} months\n";
        echo "  Status: {$proforma->status}\n";
        echo "  Apartment ID: {$proforma->apartment_id}\n";
        
        // Check if apartment exists
        $apartment = $proforma->apartment;
        if ($apartment) {
            echo "  ✅ Apartment found: {$apartment->apartment_id}\n";
        } else {
            echo "  ❌ Apartment NOT found\n";
        }
        
        // Check for existing payments
        $existingPayments = Payment::where('transaction_id', $proforma->transaction_id)->get();
        echo "  Existing payments: {$existingPayments->count()}\n";
        
        echo "\n";
    }
    
    // Test the payment callback simulation
    echo "🔄 Simulating Payment Callback:\n";
    echo str_repeat("-", 32) . "\n";
    
    $testProforma = $recentProformas->first();
    
    // Simulate Paystack payment data
    $mockPaymentDetails = [
        'status' => true,
        'data' => [
            'status' => 'success',
            'reference' => $testProforma->transaction_id,
            'amount' => $testProforma->amount * 100, // Convert to kobo
            'channel' => 'card',
            'gateway_response' => 'Successful',
            'paid_at' => now()->toISOString(),
            'metadata' => [
                'proforma_id' => $testProforma->id
            ]
        ]
    ];
    
    echo "Mock payment data:\n";
    echo "  Reference: {$mockPaymentDetails['data']['reference']}\n";
    echo "  Amount: ₦" . number_format($mockPaymentDetails['data']['amount'] / 100, 2) . "\n";
    echo "  Proforma ID: {$testProforma->id}\n\n";
    
    // Test apartment lookup
    echo "🔍 Testing Apartment Lookup:\n";
    echo str_repeat("-", 28) . "\n";
    
    $apartment = $testProforma->apartment;
    if ($apartment) {
        echo "✅ Apartment found via relationship\n";
        echo "  Apartment ID: {$apartment->id}\n";
        echo "  Apartment apartment_id: {$apartment->apartment_id}\n";
        echo "  Amount: ₦" . number_format($apartment->amount, 2) . "\n";
        echo "  Pricing Type: {$apartment->getPricingType()}\n";
    } else {
        echo "❌ Apartment NOT found via relationship\n";
        
        // Try direct lookup
        $apartmentDirect = Apartment::where('apartment_id', $testProforma->apartment_id)->first();
        if ($apartmentDirect) {
            echo "✅ Apartment found via direct lookup\n";
            echo "  This indicates a relationship issue\n";
        } else {
            echo "❌ Apartment NOT found via direct lookup either\n";
            echo "  This indicates the apartment doesn't exist\n";
        }
    }
    
    // Test payment creation
    echo "\n💾 Testing Payment Creation:\n";
    echo str_repeat("-", 28) . "\n";
    
    if (!$apartment) {
        echo "❌ Cannot test payment creation - apartment not found\n";
        exit(1);
    }
    
    // Check if payment already exists
    $existingPayment = Payment::where('transaction_id', $testProforma->transaction_id)->first();
    if ($existingPayment) {
        echo "⚠️  Payment already exists for this transaction\n";
        echo "  Payment ID: {$existingPayment->id}\n";
        echo "  Amount: ₦" . number_format($existingPayment->amount, 2) . "\n";
        echo "  Status: {$existingPayment->status}\n";
    } else {
        echo "✅ No existing payment found - can create new one\n";
        
        // Test payment creation
        try {
            DB::beginTransaction();
            
            $payment = new Payment();
            $payment->transaction_id = $testProforma->transaction_id;
            $payment->payment_reference = $testProforma->transaction_id;
            $payment->amount = $mockPaymentDetails['data']['amount'] / 100;
            $payment->tenant_id = $testProforma->tenant_id;
            $payment->landlord_id = $testProforma->user_id;
            $payment->apartment_id = $testProforma->apartment_id;
            $payment->status = 'completed';
            $payment->payment_method = 'card';
            $payment->duration = $testProforma->duration;
            $payment->paid_at = now();
            
            $paymentMeta = [
                'test_payment' => true,
                'actual_paid_amount' => $payment->amount,
                'paystack_amount_kobo' => $mockPaymentDetails['data']['amount'],
                'payment_source' => 'test_simulation'
            ];
            
            $payment->payment_meta = json_encode($paymentMeta);
            
            echo "Attempting to save payment...\n";
            $saved = $payment->save();
            
            if ($saved) {
                echo "✅ Payment saved successfully!\n";
                echo "  Payment ID: {$payment->id}\n";
                echo "  Amount: ₦" . number_format($payment->amount, 2) . "\n";
                
                // Update proforma status
                $testProforma->status = ProfomaReceipt::STATUS_CONFIRMED;
                $testProforma->save();
                
                DB::commit();
                echo "✅ Transaction committed successfully\n";
            } else {
                echo "❌ Payment save failed - save() returned false\n";
                DB::rollBack();
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            echo "❌ Payment creation failed: " . $e->getMessage() . "\n";
            echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
        }
    }
    
    echo "\n📊 Final Database State:\n";
    echo str_repeat("-", 24) . "\n";
    
    $totalPayments = Payment::count();
    $recentPayments = Payment::where('created_at', '>=', now()->subHour())->count();
    
    echo "Total payments in database: {$totalPayments}\n";
    echo "Payments created in last hour: {$recentPayments}\n";
    
    if ($recentPayments > 0) {
        echo "\nRecent payments:\n";
        $recent = Payment::where('created_at', '>=', now()->subHour())->get();
        foreach ($recent as $payment) {
            echo "  - ID: {$payment->id}, Amount: ₦" . number_format($payment->amount, 2) . ", Status: {$payment->status}\n";
        }
    }

} catch (Exception $e) {
    echo "❌ CRITICAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Test completed.\n";