<?php

require_once 'vendor/autoload.php';

use App\Models\Apartment;
use App\Models\ApartmentInvitation;
use App\Models\Payment;
use App\Services\Payment\PaymentCalculationServiceInterface;

echo "=== Fixing Payment Calculation Issues ===\n\n";

try {
    $calculationService = app(PaymentCalculationServiceInterface::class);
    
    // Step 1: Fix apartment pricing types
    echo "1. Checking apartment pricing types...\n";
    $apartments = Apartment::whereNull('pricing_type')->orWhere('pricing_type', '')->get();
    
    foreach ($apartments as $apartment) {
        // Set default pricing type to 'total' for backward compatibility
        $apartment->pricing_type = 'total';
        $apartment->save();
        echo "   ✅ Set apartment {$apartment->apartment_id} pricing_type to 'total'\n";
    }
    
    echo "   Fixed " . $apartments->count() . " apartments with missing pricing types\n\n";
    
    // Step 2: Fix apartment invitations with incorrect total_amount
    echo "2. Checking apartment invitation calculations...\n";
    $invitations = ApartmentInvitation::with('apartment')->get();
    $fixedCount = 0;
    
    foreach ($invitations as $invitation) {
        if (!$invitation->apartment) {
            echo "   ❌ Invitation {$invitation->id} has no apartment (apartment_id: {$invitation->apartment_id})\n";
            continue;
        }
        
        $duration = $invitation->lease_duration ?? 12;
        $pricingType = $invitation->apartment->getPricingType();
        
        $result = $calculationService->calculatePaymentTotal(
            $invitation->apartment->amount,
            $duration,
            $pricingType
        );
        
        if ($result->isValid) {
            $expectedAmount = $result->totalAmount;
            $currentAmount = $invitation->total_amount ?? 0;
            
            if (abs($currentAmount - $expectedAmount) > 0.01) {
                echo "   🔧 Fixing invitation {$invitation->id}:\n";
                echo "      - Current: ₦" . number_format($currentAmount, 2) . "\n";
                echo "      - Expected: ₦" . number_format($expectedAmount, 2) . "\n";
                echo "      - Pricing type: {$pricingType}\n";
                echo "      - Duration: {$duration} months\n";
                
                $invitation->total_amount = $expectedAmount;
                $invitation->save();
                $fixedCount++;
                echo "      ✅ Fixed!\n";
            }
        }
    }
    
    echo "   Fixed {$fixedCount} invitations with incorrect amounts\n\n";
    
    // Step 3: Check payment records
    echo "3. Checking payment records...\n";
    $payments = Payment::whereNotNull('amount')->take(10)->get();
    
    foreach ($payments as $payment) {
        echo "   Payment {$payment->id}: ₦" . number_format($payment->amount, 2) . 
             " (Status: {$payment->status})\n";
    }
    
    // Step 4: Create test scenarios
    echo "\n4. Testing calculation scenarios...\n";
    
    // Test fixed amount (total pricing)
    $totalResult = $calculationService->calculatePaymentTotal(1000000, 6, 'total');
    echo "   Fixed amount test: ₦1,000,000 for 6 months = ₦" . 
         number_format($totalResult->totalAmount, 2) . " (should be ₦1,000,000)\n";
    
    // Test monthly amount
    $monthlyResult = $calculationService->calculatePaymentTotal(500000, 6, 'monthly');
    echo "   Monthly amount test: ₦500,000 × 6 months = ₦" . 
         number_format($monthlyResult->totalAmount, 2) . " (should be ₦3,000,000)\n";
    
    // Step 5: Recommendations
    echo "\n5. Recommendations for landlords:\n";
    echo "   - For FIXED rental amounts (e.g., ₦2M for entire lease): Set pricing_type = 'total'\n";
    echo "   - For MONTHLY rental amounts (e.g., ₦500K per month): Set pricing_type = 'monthly'\n";
    echo "   - The payment page will automatically calculate correctly based on pricing_type\n";
    
    echo "\n✅ Payment calculation fix completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Fix Complete ===\n";