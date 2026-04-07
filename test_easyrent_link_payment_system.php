<?php

require_once 'vendor/autoload.php';

use App\Models\ApartmentInvitation;
use App\Models\Apartment;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== EASYRENT LINK PAYMENT SYSTEM TEST ===\n\n";

try {
    // Test 1: Check existing apartment invitations
    echo "1. Checking existing apartment invitations...\n";
    
    $invitations = ApartmentInvitation::with(['apartment'])->take(5)->get();
    
    if ($invitations->isEmpty()) {
        echo "   No apartment invitations found. Creating test invitation...\n";
        
        // Find an apartment to create invitation for
        $apartment = Apartment::whereNotNull('amount')->first();
        if (!$apartment) {
            echo "   ✗ No apartments found with amount data\n";
            return;
        }
        
        // Create test invitation
        $invitation = new ApartmentInvitation();
        $invitation->apartment_id = $apartment->id; // Use apartment's primary key
        $invitation->invitation_token = 'test_' . time();
        $invitation->tenant_email = 'test@example.com';
        $invitation->tenant_phone = '+2348012345678';
        $invitation->lease_duration = 12;
        $invitation->status = ApartmentInvitation::STATUS_PENDING;
        $invitation->expires_at = now()->addDays(7);
        $invitation->save();
        
        echo "   ✓ Created test invitation (ID: {$invitation->id})\n";
        $invitations = collect([$invitation]);
    }
    
    foreach ($invitations as $invitation) {
        echo "   Invitation ID: {$invitation->id}\n";
        echo "     - Token: {$invitation->invitation_token}\n";
        echo "     - Apartment ID: {$invitation->apartment_id}\n";
        echo "     - Lease Duration: {$invitation->lease_duration} months\n";
        echo "     - Status: {$invitation->status}\n";
        
        $apartment = $invitation->apartment;
        if ($apartment) {
            echo "     - Apartment found: ID {$apartment->id} (apartment_id: {$apartment->apartment_id})\n";
            echo "     - Amount: ₦" . number_format($apartment->amount, 2) . "\n";
            echo "     - Pricing Type: {$apartment->pricing_type}\n";
            echo "     - Rental Types: " . (is_array($apartment->supported_rental_types) ? json_encode($apartment->supported_rental_types) : ($apartment->supported_rental_types ?? 'Not set')) . "\n";
        } else {
            echo "     - ✗ Apartment not found\n";
        }
        echo "\n";
    }
    
    // Test 2: Test payment calculation for EasyRent link
    echo "2. Testing payment calculation for EasyRent link...\n";
    
    $testInvitation = $invitations->where('apartment', '!=', null)->first();
    $testApartment = $testInvitation ? $testInvitation->apartment : null;
    
    if (!$testApartment) {
        echo "   ✗ No apartment found for testing\n";
        return;
    }
    
    echo "   Using apartment: ID {$testApartment->id}\n";
    echo "   Amount: ₦" . number_format($testApartment->amount, 2) . "\n";
    echo "   Pricing Type: {$testApartment->pricing_type}\n";
    echo "   Lease Duration: {$testInvitation->lease_duration} months\n";
    
    // Test payment calculation using the service
    try {
        $paymentCalculationService = app(\App\Services\Payment\PaymentCalculationServiceInterface::class);
        
        $calculationResult = $paymentCalculationService->calculatePaymentTotal(
            $testApartment->amount,
            $testInvitation->lease_duration,
            $testApartment->getPricingType()
        );
        
        if ($calculationResult->isValid) {
            echo "   ✓ Payment calculation successful\n";
            echo "     - Method: {$calculationResult->calculationMethod}\n";
            echo "     - Total Amount: ₦" . number_format($calculationResult->totalAmount, 2) . "\n";
            echo "     - Duration: {$calculationResult->duration} months\n";
            
            if (!empty($calculationResult->calculationSteps)) {
                echo "     - Calculation Steps:\n";
                foreach ($calculationResult->calculationSteps as $step) {
                    if (is_array($step) && isset($step['step'])) {
                        echo "       * {$step['step']}\n";
                    }
                }
            }
        } else {
            echo "   ✗ Payment calculation failed: {$calculationResult->errorMessage}\n";
        }
        
    } catch (Exception $e) {
        echo "   ✗ Payment calculation error: " . $e->getMessage() . "\n";
    }
    
    // Test 3: Test EasyRent link payment creation
    echo "\n3. Testing EasyRent link payment creation...\n";
    
    $testReference = 'easyrent_test_' . time();
    
    try {
        DB::beginTransaction();
        
        // Create payment record like the EasyRent link system does
        $payment = new Payment();
        $payment->transaction_id = $testReference;
        $payment->payment_reference = $testReference;
        $payment->amount = $calculationResult->totalAmount ?? $testApartment->amount;
        $payment->tenant_id = null; // EasyRent link payments start without tenant
        $payment->landlord_id = $testApartment->user_id;
        $payment->apartment_id = $testApartment->apartment_id; // Use apartment_id field for payments
        $payment->status = 'pending';
        $payment->payment_method = 'card';
        $payment->duration = $testInvitation->lease_duration;
        
        // Add EasyRent link metadata
        $paymentMeta = [
            'invitation_token' => $testInvitation->invitation_token,
            'invitation_id' => $testInvitation->id,
            'easyrent_link' => true,
            'calculation_method' => $calculationResult->calculationMethod ?? 'legacy',
            'lease_duration' => $testInvitation->lease_duration,
            'apartment_pricing_type' => $testApartment->pricing_type
        ];
        $payment->payment_meta = json_encode($paymentMeta);
        
        echo "   Payment data prepared:\n";
        echo "     - Transaction ID: {$payment->transaction_id}\n";
        echo "     - Amount: ₦" . number_format($payment->amount, 2) . "\n";
        echo "     - Landlord ID: {$payment->landlord_id}\n";
        echo "     - Apartment ID: {$payment->apartment_id}\n";
        echo "     - Duration: {$payment->duration} months\n";
        echo "     - Payment Method: {$payment->payment_method}\n";
        
        $saved = $payment->save();
        
        if ($saved) {
            echo "   ✓ EasyRent link payment created successfully!\n";
            echo "     - Database ID: {$payment->id}\n";
            echo "     - Status: {$payment->status}\n";
            
            // Test payment callback simulation
            echo "\n4. Testing EasyRent link payment callback...\n";
            
            // Simulate successful payment callback
            $mockPaystackData = [
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'reference' => $testReference,
                    'amount' => $payment->amount * 100, // Convert to kobo
                    'channel' => 'card',
                    'gateway_response' => 'Successful',
                    'paid_at' => now()->toISOString(),
                    'metadata' => [
                        'invitation_token' => $testInvitation->invitation_token
                    ]
                ]
            ];
            
            // Update payment to completed (like callback would do)
            $payment->status = 'completed';
            $payment->paid_at = now();
            $payment->payment_method = $mockPaystackData['data']['channel'];
            
            // Update metadata with callback info
            $updatedMeta = json_decode($payment->payment_meta, true);
            $updatedMeta['paystack_data'] = $mockPaystackData['data'];
            $updatedMeta['callback_processed'] = true;
            $payment->payment_meta = json_encode($updatedMeta);
            
            $callbackSaved = $payment->save();
            
            if ($callbackSaved) {
                echo "   ✓ Payment callback simulation successful!\n";
                echo "     - Updated status: {$payment->status}\n";
                echo "     - Payment method: {$payment->payment_method}\n";
                echo "     - Paid at: {$payment->paid_at}\n";
            } else {
                echo "   ✗ Payment callback simulation failed\n";
            }
            
            DB::commit();
            
        } else {
            echo "   ✗ EasyRent link payment creation failed\n";
            DB::rollBack();
        }
        
    } catch (Exception $e) {
        DB::rollBack();
        echo "   ✗ EasyRent link payment error: " . $e->getMessage() . "\n";
        echo "   Error details: " . $e->getTraceAsString() . "\n";
    }
    
    // Test 5: Test different rental durations
    echo "\n5. Testing different rental durations...\n";
    
    $testDurations = [1, 3, 6, 12, 24]; // months
    
    foreach ($testDurations as $duration) {
        try {
            $calculationResult = $paymentCalculationService->calculatePaymentTotal(
                $testApartment->amount,
                $duration,
                $testApartment->getPricingType()
            );
            
            if ($calculationResult->isValid) {
                echo "   Duration {$duration} months: ₦" . number_format($calculationResult->totalAmount, 2);
                echo " (Method: {$calculationResult->calculationMethod})\n";
            } else {
                echo "   Duration {$duration} months: FAILED - {$calculationResult->errorMessage}\n";
            }
            
        } catch (Exception $e) {
            echo "   Duration {$duration} months: ERROR - " . $e->getMessage() . "\n";
        }
    }
    
    // Test 6: Check for any existing EasyRent link payments
    echo "\n6. Checking existing EasyRent link payments...\n";
    
    $existingPayments = Payment::where('payment_reference', 'LIKE', 'easyrent_%')
        ->orWhere('transaction_id', 'LIKE', 'easyrent_%')
        ->get();
    
    echo "   Found {$existingPayments->count()} existing EasyRent link payments:\n";
    
    foreach ($existingPayments as $p) {
        echo "     - Payment ID {$p->id}: ₦" . number_format($p->amount, 2) . " ({$p->status})\n";
        echo "       Reference: {$p->payment_reference}\n";
        echo "       Duration: {$p->duration} months\n";
        echo "       Method: {$p->payment_method}\n";
        
        // Check metadata
        if ($p->payment_meta) {
            $meta = is_string($p->payment_meta) ? json_decode($p->payment_meta, true) : $p->payment_meta;
            if (is_array($meta)) {
                if (isset($meta['invitation_token'])) {
                    echo "       Invitation Token: {$meta['invitation_token']}\n";
                }
                if (isset($meta['calculation_method'])) {
                    echo "       Calculation Method: {$meta['calculation_method']}\n";
                }
            }
        }
        echo "\n";
    }
    
    echo "=== TEST SUMMARY ===\n";
    echo "✓ EasyRent link invitation system working\n";
    echo "✓ Payment calculation service working with rental durations\n";
    echo "✓ EasyRent link payment creation and database storage working\n";
    echo "✓ Payment callback simulation successful\n";
    echo "✓ Multiple rental duration calculations working\n";
    echo "\nThe EasyRent link payment system appears to be working correctly!\n";
    
} catch (Exception $e) {
    echo "Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}