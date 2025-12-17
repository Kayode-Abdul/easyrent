<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ApartmentInvitation;
use App\Models\Payment;

echo "=== Testing Payment Records ===\n\n";

try {
    // Test 1: Check apartment invitations and their payment references
    echo "1. Checking apartment invitations and payment references:\n";
    $invitations = ApartmentInvitation::limit(5)->get();
    
    foreach ($invitations as $invitation) {
        echo "   Invitation Token: " . substr($invitation->invitation_token, 0, 20) . "...\n";
        echo "   - Expected payment reference: easyrent_{$invitation->invitation_token}\n";
        
        // Look for payment with this reference
        $payment = Payment::where('payment_reference', 'easyrent_' . $invitation->invitation_token)->first();
        if ($payment) {
            echo "   ✅ Payment found: ID {$payment->id}, Status: {$payment->status}\n";
        } else {
            echo "   ❌ No payment found with expected reference\n";
        }
        echo "\n";
    }

    // Test 2: Check all payments to see what references exist
    echo "2. Checking existing payment references:\n";
    $payments = Payment::limit(10)->get();
    
    foreach ($payments as $payment) {
        echo "   Payment ID: {$payment->id}\n";
        echo "   - Reference: {$payment->payment_reference}\n";
        echo "   - Status: {$payment->status}\n";
        echo "   - Amount: ₦" . number_format($payment->amount ?? 0) . "\n";
        echo "   - Apartment ID: {$payment->apartment_id}\n";
        echo "\n";
    }

    // Test 3: Check if the issue is that payments haven't been created yet
    echo "3. Analysis:\n";
    $totalInvitations = ApartmentInvitation::count();
    $totalPayments = Payment::count();
    $paymentsWithEasyrentRef = Payment::where('payment_reference', 'like', 'easyrent_%')->count();
    
    echo "   - Total invitations: {$totalInvitations}\n";
    echo "   - Total payments: {$totalPayments}\n";
    echo "   - Payments with 'easyrent_' reference: {$paymentsWithEasyrentRef}\n";
    
    if ($paymentsWithEasyrentRef == 0) {
        echo "\n   💡 The issue might be that no one has started the payment process yet.\n";
        echo "   Payment records are created when users apply for apartments, not when invitations are created.\n";
        echo "   To test the payment gateway, someone needs to:\n";
        echo "   1. Visit an apartment invitation link\n";
        echo "   2. Fill out the application form\n";
        echo "   3. Submit the application (this creates the payment record)\n";
        echo "   4. Then click the pay button (this should now work with our fix)\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}