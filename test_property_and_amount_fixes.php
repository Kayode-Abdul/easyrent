<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Property;
use App\Models\Apartment;
use App\Models\ApartmentInvitation;

echo "=== Testing Property Type and Amount Calculation Fixes ===\n\n";

try {
    // Test 1: Property Type Display Fix
    echo "1. Testing Property Type Display Fix:\n";
    $property = Property::first();
    if ($property) {
        echo "   Property ID: {$property->property_id}\n";
        echo "   - Raw prop_type: {$property->getRawOriginal('prop_type')}\n";
        echo "   - prop_type (should still be number): {$property->prop_type}\n";
        echo "   - getPropertyTypeName(): {$property->getPropertyTypeName()}\n";
        echo "   - View should now show: {$property->getPropertyTypeName()}\n";
    }
    echo "\n";

    // Test 2: Amount Calculation Investigation
    echo "2. Investigating Amount Calculations:\n";
    $invitation = ApartmentInvitation::with(['apartment'])->whereNotNull('total_amount')->first();
    if ($invitation && $invitation->apartment) {
        echo "   Invitation ID: {$invitation->id}\n";
        echo "   - Monthly rent: ₦" . number_format($invitation->apartment->amount) . "\n";
        echo "   - Lease duration: {$invitation->lease_duration} months\n";
        echo "   - Stored total_amount: ₦" . number_format($invitation->total_amount) . "\n";
        echo "   - Calculated (monthly × duration): ₦" . number_format($invitation->apartment->amount * $invitation->lease_duration) . "\n";
        
        $expected = $invitation->apartment->amount * $invitation->lease_duration;
        $actual = $invitation->total_amount;
        
        if ($expected == $actual) {
            echo "   ✅ Calculation is correct\n";
        } else {
            echo "   ❌ CALCULATION MISMATCH!\n";
            echo "   - Expected: ₦" . number_format($expected) . "\n";
            echo "   - Actual: ₦" . number_format($actual) . "\n";
            echo "   - Difference: ₦" . number_format(abs($expected - $actual)) . "\n";
            
            if ($actual == ($expected * 2)) {
                echo "   🔍 Issue: Amount is being DOUBLED!\n";
            } elseif ($actual == ($expected * $invitation->lease_duration)) {
                echo "   🔍 Issue: Duration is being applied TWICE!\n";
            }
        }
    }
    echo "\n";

    // Test 3: Check multiple invitations for patterns
    echo "3. Checking Multiple Invitations for Patterns:\n";
    $invitations = ApartmentInvitation::with(['apartment'])
        ->whereNotNull('total_amount')
        ->whereNotNull('lease_duration')
        ->limit(5)
        ->get();
    
    foreach ($invitations as $inv) {
        if ($inv->apartment) {
            $expected = $inv->apartment->amount * $inv->lease_duration;
            $actual = $inv->total_amount;
            $status = ($expected == $actual) ? '✅' : '❌';
            
            echo "   ID {$inv->id}: {$status} ₦" . number_format($inv->apartment->amount) . " × {$inv->lease_duration} = ₦" . number_format($expected) . " (stored: ₦" . number_format($actual) . ")\n";
        }
    }
    echo "\n";

    // Test 4: Check if there are any invitations with incorrect calculations
    echo "4. Finding Invitations with Calculation Errors:\n";
    $incorrectInvitations = ApartmentInvitation::with(['apartment'])
        ->whereNotNull('total_amount')
        ->whereNotNull('lease_duration')
        ->get()
        ->filter(function($invitation) {
            if (!$invitation->apartment) return false;
            $expected = $invitation->apartment->amount * $invitation->lease_duration;
            return $expected != $invitation->total_amount;
        });
    
    if ($incorrectInvitations->count() > 0) {
        echo "   Found {$incorrectInvitations->count()} invitations with incorrect calculations:\n";
        foreach ($incorrectInvitations as $inv) {
            $expected = $inv->apartment->amount * $inv->lease_duration;
            echo "   - ID {$inv->id}: Expected ₦" . number_format($expected) . ", Got ₦" . number_format($inv->total_amount) . "\n";
        }
    } else {
        echo "   ✅ All invitations have correct calculations\n";
    }
    echo "\n";

    echo "=== Test completed ===\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}