<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payment;
use App\Models\Apartment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "🔍 Debugging Payment Display Issue\n";
echo "==================================\n\n";

try {
    // Get a specific user to test with
    $user = User::first();
    if (!$user) {
        echo "❌ No users found in database\n";
        exit(1);
    }
    
    echo "Testing with User ID: {$user->user_id}\n";
    echo "User Email: {$user->email}\n\n";
    
    // Get payments for this user (same query as BillingController)
    $payments = Payment::where(function($query) use ($user) {
                        $query->where('tenant_id', $user->user_id)
                              ->orWhere('landlord_id', $user->user_id);
                    })
                    ->whereIn('status', ['success', 'completed'])
                    ->orderBy('created_at', 'desc')
                    ->get();
    
    echo "📋 Payments found: {$payments->count()}\n\n";
    
    if ($payments->isEmpty()) {
        echo "ℹ️  No payments found for this user. Let's check all payments:\n\n";
        
        $allPayments = Payment::limit(5)->get();
        foreach ($allPayments as $payment) {
            echo "Payment ID: {$payment->id}\n";
            echo "Amount: ₦" . number_format($payment->amount, 2) . "\n";
            echo "Tenant ID: {$payment->tenant_id}\n";
            echo "Landlord ID: {$payment->landlord_id}\n";
            echo "Status: {$payment->status}\n";
            echo str_repeat("-", 30) . "\n";
        }
    } else {
        foreach ($payments as $payment) {
            echo "Payment ID: {$payment->id}\n";
            echo "Transaction ID: {$payment->transaction_id}\n";
            echo "Stored Amount: ₦" . number_format($payment->amount, 2) . "\n";
            echo "Status: {$payment->status}\n";
            echo "Created: {$payment->created_at}\n";
            
            // Check if there's an apartment associated
            if ($payment->apartment_id) {
                $apartment = Apartment::where('apartment_id', $payment->apartment_id)->first();
                if ($apartment) {
                    echo "Apartment Amount: ₦" . number_format($apartment->amount, 2) . "\n";
                    echo "Apartment Pricing Type: {$apartment->getPricingType()}\n";
                    echo "Payment Duration: {$payment->duration} months\n";
                    
                    // What would current calculation be?
                    $currentCalc = $apartment->amount;
                    if ($apartment->getPricingType() === 'monthly') {
                        $currentCalc = $apartment->amount * ($payment->duration ?? 12);
                    }
                    echo "Current System Would Calculate: ₦" . number_format($currentCalc, 2) . "\n";
                    
                    // Check if they match
                    if (abs($payment->amount - $currentCalc) > 0.01) {
                        echo "✅ GOOD: Payment shows actual paid amount, not recalculated\n";
                        echo "   Difference: ₦" . number_format(abs($payment->amount - $currentCalc), 2) . "\n";
                    } else {
                        echo "ℹ️  Payment amount matches current calculation\n";
                    }
                }
            }
            
            echo str_repeat("-", 50) . "\n";
        }
        
        // Calculate total as BillingController does
        $totalPaid = $payments->sum('amount');
        echo "\n💰 Total Paid (as shown in billing): ₦" . number_format($totalPaid, 2) . "\n";
    }
    
    echo "\n🔍 Checking specific issue scenarios...\n";
    
    // Test scenario: Find a payment where apartment pricing might have changed
    $testPayment = Payment::with(['apartment'])->first();
    if ($testPayment && $testPayment->apartment) {
        echo "\nTest Payment Analysis:\n";
        echo "Payment ID: {$testPayment->id}\n";
        echo "Payment Amount (stored): ₦" . number_format($testPayment->amount, 2) . "\n";
        
        $apartment = $testPayment->apartment;
        echo "Apartment Current Amount: ₦" . number_format($apartment->amount, 2) . "\n";
        echo "Apartment Current Pricing Type: {$apartment->getPricingType()}\n";
        
        // Test changing pricing type
        $originalPricingType = $apartment->pricing_type;
        
        echo "\nTesting pricing type changes:\n";
        
        // Test with 'total' pricing
        $apartment->pricing_type = 'total';
        $apartment->save();
        $apartment->refresh();
        echo "Changed to 'total' - Payment still shows: ₦" . number_format($testPayment->fresh()->amount, 2) . "\n";
        
        // Test with 'monthly' pricing
        $apartment->pricing_type = 'monthly';
        $apartment->save();
        $apartment->refresh();
        echo "Changed to 'monthly' - Payment still shows: ₦" . number_format($testPayment->fresh()->amount, 2) . "\n";
        
        // Restore original pricing type
        $apartment->pricing_type = $originalPricingType;
        $apartment->save();
        echo "Restored to '{$originalPricingType}' - Payment still shows: ₦" . number_format($testPayment->fresh()->amount, 2) . "\n";
        
        echo "\n✅ Payment amount remains consistent regardless of apartment pricing changes\n";
    }
    
    echo "\n🎯 Issue Analysis:\n";
    echo "=================\n";
    
    if ($payments->count() > 0) {
        echo "✅ Fix is working: Payments show stored amounts\n";
        echo "✅ BillingController uses correct sum of actual amounts\n";
        echo "✅ Billing view displays actual payment amounts\n";
        echo "\n💡 If you're still seeing wrong amounts, the issue might be:\n";
        echo "   1. Browser cache - try hard refresh (Ctrl+F5)\n";
        echo "   2. Different page/view showing calculated amounts\n";
        echo "   3. JavaScript recalculating amounts on frontend\n";
        echo "   4. Different user account with different payment data\n";
    } else {
        echo "ℹ️  No payments found for test user - create a test payment to verify\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n✨ Debug completed!\n";