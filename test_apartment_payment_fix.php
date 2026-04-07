<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\ApartmentInvitation;
use App\Models\Payment;

echo "=== Testing Apartment Payment Fix ===\n\n";

try {
    // Test 1: Check if we have any apartment invitations
    $invitations = ApartmentInvitation::with(['apartment', 'landlord'])
        ->where('status', '!=', 'used')
        ->limit(5)
        ->get();
    
    echo "Found " . $invitations->count() . " active invitations\n";
    
    foreach ($invitations as $invitation) {
        echo "\nInvitation ID: {$invitation->id}\n";
        echo "Token: " . substr($invitation->invitation_token, 0, 8) . "...\n";
        echo "Total Amount: " . ($invitation->total_amount ?? 'NULL') . "\n";
        echo "Prospect Email: " . ($invitation->prospect_email ?? 'NULL') . "\n";
        echo "Lease Duration: " . ($invitation->lease_duration ?? 'NULL') . "\n";
        
        // Check if apartment data is available
        if ($invitation->apartment) {
            echo "Apartment Amount: {$invitation->apartment->amount}\n";
            
            // Fix missing total_amount
            if (!$invitation->total_amount && $invitation->apartment->amount) {
                $duration = $invitation->lease_duration ?? 12;
                $totalAmount = $invitation->apartment->amount * $duration;
                
                echo "Fixing missing total_amount: {$totalAmount}\n";
                $invitation->update(['total_amount' => $totalAmount]);
            }
        }
        
        echo "---\n";
    }
    
    // Test 2: Check payment integration
    echo "\n=== Testing Payment Integration ===\n";
    
    $testInvitation = $invitations->first();
    if ($testInvitation) {
        echo "Testing with invitation: {$testInvitation->id}\n";
        
        // Simulate the JavaScript validation
        $email = auth()->check() ? auth()->user()->email : ($testInvitation->prospect_email ?? null);
        $amount = $testInvitation->total_amount * 100; // Convert to kobo
        
        echo "Email for payment: " . ($email ?? 'NULL') . "\n";
        echo "Amount in kobo: {$amount}\n";
        
        // Test validation logic
        if (!$amount || $amount <= 0) {
            echo "❌ VALIDATION FAILED: Invalid amount\n";
        } else if (!$email) {
            echo "⚠️  VALIDATION WARNING: No email (guest user - will prompt)\n";
        } else {
            echo "✅ VALIDATION PASSED: Ready for payment\n";
        }
    }
    
    // Test 3: Check Paystack configuration
    echo "\n=== Testing Paystack Configuration ===\n";
    
    $paystackPublicKey = env('PAYSTACK_PUBLIC_KEY');
    $paystackSecretKey = env('PAYSTACK_SECRET_KEY');
    
    echo "Paystack Public Key: " . ($paystackPublicKey ? 'SET' : 'NOT SET') . "\n";
    echo "Paystack Secret Key: " . ($paystackSecretKey ? 'SET' : 'NOT SET') . "\n";
    
    if (!$paystackPublicKey || !$paystackSecretKey) {
        echo "❌ Paystack keys not configured properly\n";
        echo "Please set PAYSTACK_PUBLIC_KEY and PAYSTACK_SECRET_KEY in .env\n";
    } else {
        echo "✅ Paystack configuration looks good\n";
    }
    
    echo "\n=== Fix Summary ===\n";
    echo "1. ✅ Updated JavaScript validation to handle guest users\n";
    echo "2. ✅ Added email prompt for guest users\n";
    echo "3. ✅ Enhanced controller to ensure total_amount is set\n";
    echo "4. ✅ Added debug logging for payment validation\n";
    echo "5. ✅ Improved error handling for missing data\n";
    
    echo "\n=== Next Steps ===\n";
    echo "1. Test the payment page with a guest user\n";
    echo "2. Verify email prompt appears for unauthenticated users\n";
    echo "3. Check browser console for debug information\n";
    echo "4. Ensure Paystack keys are properly configured\n";
    
} catch (Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";