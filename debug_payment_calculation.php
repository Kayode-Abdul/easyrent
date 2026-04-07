<?php

require_once 'vendor/autoload.php';

use App\Models\ApartmentInvitation;
use App\Models\Apartment;
use App\Models\Payment;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Payment Calculation Debug ===\n\n";

// Get a recent apartment invitation
$invitation = ApartmentInvitation::with(['apartment', 'apartment.property'])
    ->whereNotNull('lease_duration')
    ->whereNotNull('total_amount')
    ->latest()
    ->first();

if (!$invitation) {
    echo "No apartment invitation found with lease_duration and total_amount.\n";
    echo "Let's check all invitations:\n\n";
    
    $allInvitations = ApartmentInvitation::with(['apartment', 'apartment.property'])
        ->latest()
        ->take(5)
        ->get();
    
    foreach ($allInvitations as $inv) {
        echo "Invitation ID: {$inv->id}\n";
        echo "Apartment ID: {$inv->apartment_id}\n";
        echo "Lease Duration: " . ($inv->lease_duration ?? 'NULL') . "\n";
        echo "Total Amount: " . ($inv->total_amount ?? 'NULL') . "\n";
        echo "Apartment Amount: " . ($inv->apartment ? $inv->apartment->amount : 'NULL') . "\n";
        echo "Property Name: " . ($inv->apartment && $inv->apartment->property ? $inv->apartment->property->prop_name : 'NULL') . "\n";
        echo "---\n";
    }
    exit;
}

echo "Found Invitation:\n";
echo "ID: {$invitation->id}\n";
echo "Token: " . substr($invitation->invitation_token, 0, 8) . "...\n";
echo "Apartment ID: {$invitation->apartment_id}\n";
echo "Lease Duration: {$invitation->lease_duration} months\n";
echo "Total Amount: ₦" . number_format($invitation->total_amount) . "\n";

if ($invitation->apartment) {
    echo "Apartment Monthly Amount: ₦" . number_format($invitation->apartment->amount) . "\n";
    echo "Calculated Total (Duration × Monthly): ₦" . number_format($invitation->apartment->amount * $invitation->lease_duration) . "\n";
    
    $calculatedTotal = $invitation->apartment->amount * $invitation->lease_duration;
    $storedTotal = $invitation->total_amount;
    
    if ($calculatedTotal != $storedTotal) {
        echo "\n⚠️  MISMATCH DETECTED!\n";
        echo "Stored Total: ₦" . number_format($storedTotal) . "\n";
        echo "Calculated Total: ₦" . number_format($calculatedTotal) . "\n";
        echo "Difference: ₦" . number_format(abs($calculatedTotal - $storedTotal)) . "\n";
        
        // Fix the mismatch
        echo "\nFixing the mismatch...\n";
        $invitation->update(['total_amount' => $calculatedTotal]);
        echo "✅ Updated total_amount to ₦" . number_format($calculatedTotal) . "\n";
    } else {
        echo "\n✅ Calculation matches stored value.\n";
    }
    
    if ($invitation->apartment->property) {
        echo "Property: {$invitation->apartment->property->prop_name}\n";
    }
} else {
    echo "❌ No apartment found for this invitation!\n";
}

// Check for any payments related to this invitation
$payment = Payment::where('payment_reference', 'easyrent_' . $invitation->invitation_token)->first();

if ($payment) {
    echo "\nRelated Payment:\n";
    echo "Payment ID: {$payment->id}\n";
    echo "Payment Amount: ₦" . number_format($payment->amount) . "\n";
    echo "Payment Status: {$payment->status}\n";
    
    if ($payment->amount != $invitation->total_amount) {
        echo "\n⚠️  PAYMENT AMOUNT MISMATCH!\n";
        echo "Invitation Total: ₦" . number_format($invitation->total_amount) . "\n";
        echo "Payment Amount: ₦" . number_format($payment->amount) . "\n";
        
        // Fix payment amount
        echo "\nFixing payment amount...\n";
        $payment->update(['amount' => $invitation->total_amount]);
        echo "✅ Updated payment amount to ₦" . number_format($invitation->total_amount) . "\n";
    }
} else {
    echo "\n❌ No payment found for this invitation.\n";
}

// Check for any invitations with NULL values
echo "\n=== Checking for NULL Values ===\n";
$nullDurationCount = ApartmentInvitation::whereNull('lease_duration')->count();
$nullAmountCount = ApartmentInvitation::whereNull('total_amount')->count();

echo "Invitations with NULL lease_duration: {$nullDurationCount}\n";
echo "Invitations with NULL total_amount: {$nullAmountCount}\n";

if ($nullDurationCount > 0 || $nullAmountCount > 0) {
    echo "\nInvitations with NULL values:\n";
    $nullInvitations = ApartmentInvitation::with(['apartment'])
        ->where(function($query) {
            $query->whereNull('lease_duration')
                  ->orWhereNull('total_amount');
        })
        ->take(10)
        ->get();
    
    foreach ($nullInvitations as $inv) {
        echo "ID: {$inv->id}, Duration: " . ($inv->lease_duration ?? 'NULL') . 
             ", Amount: " . ($inv->total_amount ?? 'NULL') . 
             ", Apt Amount: " . ($inv->apartment ? $inv->apartment->amount : 'NULL') . "\n";
    }
}

echo "\n=== Debug Complete ===\n";