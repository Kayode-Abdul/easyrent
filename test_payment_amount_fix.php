<?php

require_once 'vendor/autoload.php';

use App\Models\ApartmentInvitation;
use App\Models\Apartment;
use App\Services\Payment\PaymentCalculationServiceInterface;

echo "=== Testing Payment Amount Calculation ===\n\n";

try {
    // Get a sample invitation
    $invitation = ApartmentInvitation::with(['apartment.property'])->first();
    
    if (!$invitation) {
        echo "❌ No apartment invitations found\n";
        exit;
    }
    
    echo "Testing invitation: {$invitation->invitation_token}\n";
    echo "Apartment ID: {$invitation->apartment_id}\n";
    echo "Apartment amount: ₦" . number_format($invitation->apartment->amount, 2) . "\n";
    echo "Lease duration: {$invitation->lease_duration} months\n";
    echo "Pricing type: {$invitation->apartment->getPricingType()}\n";
    echo "Current total_amount: ₦" . number_format($invitation->total_amount ?? 0, 2) . "\n\n";
    
    // Test calculation service
    $calculationService = app(PaymentCalculationServiceInterface::class);
    
    $result = $calculationService->calculatePaymentTotal(
        $invitation->apartment->amount,
        $invitation->lease_duration ?? 12,
        $invitation->apartment->getPricingType()
    );
    
    if ($result->isValid) {
        echo "✅ Calculation service result:\n";
        echo "   - Total amount: ₦" . number_format($result->totalAmount, 2) . "\n";
        echo "   - Pricing type: {$result->pricingType}\n";
        echo "   - Base amount: ₦" . number_format($result->baseAmount, 2) . "\n";
        echo "   - Duration: {$result->duration} months\n";
        
        // Check if amounts match
        if (abs($invitation->total_amount - $result->totalAmount) < 0.01) {
            echo "✅ Amounts match correctly\n";
        } else {
            echo "❌ Amount mismatch detected!\n";
            echo "   - Expected: ₦" . number_format($result->totalAmount, 2) . "\n";
            echo "   - Stored: ₦" . number_format($invitation->total_amount, 2) . "\n";
            echo "   - Difference: ₦" . number_format(abs($invitation->total_amount - $result->totalAmount), 2) . "\n";
            
            // Fix the amount
            echo "\n🔧 Fixing amount...\n";
            $invitation->total_amount = $result->totalAmount;
            $invitation->save();
            echo "✅ Amount updated successfully\n";
        }
        
        // Test Paystack amount conversion (NGN to kobo)
        $paystackAmount = $result->totalAmount * 100; // Convert to kobo
        echo "\n💳 Paystack integration:\n";
        echo "   - Amount in NGN: ₦" . number_format($result->totalAmount, 2) . "\n";
        echo "   - Amount in kobo: " . number_format($paystackAmount, 0) . "\n";
        
    } else {
        echo "❌ Calculation failed: {$result->errorMessage}\n";
    }
    
    // Test multiple invitations
    echo "\n=== Testing Multiple Invitations ===\n";
    $invitations = ApartmentInvitation::with(['apartment'])->take(5)->get();
    
    foreach ($invitations as $inv) {
        $calcResult = $calculationService->calculatePaymentTotal(
            $inv->apartment->amount,
            $inv->lease_duration ?? 12,
            $inv->apartment->getPricingType()
        );
        
        if ($calcResult->isValid) {
            $match = abs(($inv->total_amount ?? 0) - $calcResult->totalAmount) < 0.01;
            $status = $match ? "✅" : "❌";
            echo "{$status} Invitation {$inv->invitation_token}: ₦" . number_format($calcResult->totalAmount, 2) . 
                 " (stored: ₦" . number_format($inv->total_amount ?? 0, 2) . ")\n";
            
            if (!$match && $inv->total_amount != $calcResult->totalAmount) {
                $inv->total_amount = $calcResult->totalAmount;
                $inv->save();
                echo "   🔧 Fixed amount for invitation {$inv->invitation_token}\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";