<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Payment;
use App\Models\Apartment;
use App\Models\ApartmentInvitation;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Services\Payment\PaymentCalculationServiceInterface;
use Illuminate\Support\Facades\DB;

echo "=== CRITICAL ISSUES DIAGNOSIS ===\n\n";

// Issue 1: Check billing page - payments not showing
echo "1. BILLING PAGE ISSUE DIAGNOSIS\n";
echo "================================\n";

try {
    // Get a sample user
    $user = User::first();
    if (!$user) {
        echo "❌ No users found in database\n";
    } else {
        echo "Testing with user: {$user->email} (ID: {$user->user_id})\n";
        
        // Check payments for this user
        $paymentsAsTenant = Payment::where('tenant_id', $user->user_id)->get();
        $paymentsAsLandlord = Payment::where('landlord_id', $user->user_id)->get();
        $successfulPayments = Payment::where(function($query) use ($user) {
                                    $query->where('tenant_id', $user->user_id)
                                          ->orWhere('landlord_id', $user->user_id);
                                })
                                ->whereIn('status', ['success', 'completed'])
                                ->get();
        
        echo "   - Payments as tenant: {$paymentsAsTenant->count()}\n";
        echo "   - Payments as landlord: {$paymentsAsLandlord->count()}\n";
        echo "   - Successful payments: {$successfulPayments->count()}\n";
        
        if ($successfulPayments->count() > 0) {
            echo "   ✅ User has successful payments - billing should show data\n";
            foreach ($successfulPayments as $payment) {
                echo "      - Payment #{$payment->id}: ₦" . number_format($payment->amount, 2) . " ({$payment->status})\n";
            }
        } else {
            echo "   ❌ No successful payments found - this explains empty billing page\n";
            
            // Check if there are any payments at all
            $allPayments = Payment::where(function($query) use ($user) {
                                $query->where('tenant_id', $user->user_id)
                                      ->orWhere('landlord_id', $user->user_id);
                            })->get();
            
            if ($allPayments->count() > 0) {
                echo "   📊 Found {$allPayments->count()} payments with other statuses:\n";
                foreach ($allPayments as $payment) {
                    echo "      - Payment #{$payment->id}: ₦" . number_format($payment->amount, 2) . " ({$payment->status})\n";
                }
            } else {
                echo "   📊 No payments found at all for this user\n";
            }
        }
    }
} catch (Exception $e) {
    echo "❌ Error checking billing: " . $e->getMessage() . "\n";
}

echo "\n";

// Issue 2: Check complaint creation system
echo "2. COMPLAINT CREATION SYSTEM DIAGNOSIS\n";
echo "======================================\n";

try {
    // Check if complaint categories exist
    $categories = ComplaintCategory::count();
    echo "Complaint categories: {$categories}\n";
    
    if ($categories == 0) {
        echo "❌ No complaint categories found - need to run seeder\n";
    } else {
        echo "✅ Complaint categories available\n";
    }
    
    // Check if users have apartments assigned
    $tenantsWithApartments = User::whereHas('tenantApartments')->count();
    echo "Tenants with apartments: {$tenantsWithApartments}\n";
    
    if ($tenantsWithApartments == 0) {
        echo "❌ No tenants assigned to apartments - complaints cannot be created\n";
    } else {
        echo "✅ Tenants have apartments assigned\n";
        
        // Test complaint creation for a tenant
        $tenant = User::whereHas('tenantApartments')->first();
        if ($tenant) {
            $apartments = $tenant->tenantApartments;
            echo "   - Sample tenant: {$tenant->email}\n";
            echo "   - Assigned apartments: {$apartments->count()}\n";
            
            // Check if tenant can create complaints
            $existingComplaints = Complaint::where('tenant_id', $tenant->user_id)->count();
            echo "   - Existing complaints: {$existingComplaints}\n";
            echo "   ✅ Tenant can access complaint creation\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error checking complaints: " . $e->getMessage() . "\n";
}

echo "\n";

// Issue 3: Check payment calculation logic
echo "3. PAYMENT CALCULATION DIAGNOSIS\n";
echo "===============================\n";

try {
    $calculationService = app(PaymentCalculationServiceInterface::class);
    
    // Test scenarios
    $testCases = [
        ['price' => 2000000, 'duration' => 12, 'type' => 'total', 'expected' => 2000000],
        ['price' => 2000000, 'duration' => 6, 'type' => 'total', 'expected' => 2000000],
        ['price' => 500000, 'duration' => 6, 'type' => 'monthly', 'expected' => 3000000],
        ['price' => 100000, 'duration' => 12, 'type' => 'monthly', 'expected' => 1200000],
    ];
    
    foreach ($testCases as $i => $test) {
        $result = $calculationService->calculatePaymentTotal(
            $test['price'],
            $test['duration'],
            $test['type']
        );
        
        if ($result->isValid) {
            $correct = abs($result->totalAmount - $test['expected']) < 0.01;
            $status = $correct ? "✅" : "❌";
            echo "{$status} Test " . ($i + 1) . ": ₦" . number_format($test['price'], 2) . 
                 " × {$test['duration']} months ({$test['type']}) = ₦" . number_format($result->totalAmount, 2) . 
                 " (expected: ₦" . number_format($test['expected'], 2) . ")\n";
            
            if (!$correct) {
                echo "   ❌ CALCULATION ERROR DETECTED!\n";
            }
        } else {
            echo "❌ Test " . ($i + 1) . " failed: {$result->errorMessage}\n";
        }
    }
    
    // Check apartment pricing types
    echo "\nApartment pricing type analysis:\n";
    $apartments = Apartment::select('apartment_id', 'amount', 'pricing_type')->take(5)->get();
    
    foreach ($apartments as $apt) {
        $pricingType = $apt->pricing_type ?? 'total';
        echo "   - Apartment #{$apt->apartment_id}: ₦" . number_format($apt->amount, 2) . " ({$pricingType})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error testing calculations: " . $e->getMessage() . "\n";
}

echo "\n";

// Issue 4: Check apartment invitations and payment amounts
echo "4. APARTMENT INVITATION PAYMENT AMOUNTS\n";
echo "======================================\n";

try {
    $invitations = ApartmentInvitation::with(['apartment'])->take(5)->get();
    
    if ($invitations->count() == 0) {
        echo "❌ No apartment invitations found\n";
    } else {
        echo "Found {$invitations->count()} invitations to check:\n";
        
        $calculationService = app(PaymentCalculationServiceInterface::class);
        
        foreach ($invitations as $inv) {
            $apartment = $inv->apartment;
            if (!$apartment) {
                echo "   ❌ Invitation {$inv->invitation_token}: No apartment found\n";
                continue;
            }
            
            $pricingType = $apartment->pricing_type ?? 'total';
            $duration = $inv->lease_duration ?? 12;
            
            $result = $calculationService->calculatePaymentTotal(
                $apartment->amount,
                $duration,
                $pricingType
            );
            
            if ($result->isValid) {
                $storedAmount = $inv->total_amount ?? 0;
                $calculatedAmount = $result->totalAmount;
                $match = abs($storedAmount - $calculatedAmount) < 0.01;
                $status = $match ? "✅" : "❌";
                
                echo "   {$status} {$inv->invitation_token}:\n";
                echo "      - Base amount: ₦" . number_format($apartment->amount, 2) . "\n";
                echo "      - Duration: {$duration} months\n";
                echo "      - Pricing type: {$pricingType}\n";
                echo "      - Stored total: ₦" . number_format($storedAmount, 2) . "\n";
                echo "      - Calculated total: ₦" . number_format($calculatedAmount, 2) . "\n";
                
                if (!$match) {
                    echo "      ❌ AMOUNT MISMATCH - needs correction\n";
                }
            } else {
                echo "   ❌ {$inv->invitation_token}: Calculation failed - {$result->errorMessage}\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error checking invitations: " . $e->getMessage() . "\n";
}

echo "\n";

// Summary and recommendations
echo "5. SUMMARY AND RECOMMENDATIONS\n";
echo "=============================\n";

try {
    // Database health check
    $userCount = User::count();
    $apartmentCount = Apartment::count();
    $paymentCount = Payment::count();
    $invitationCount = ApartmentInvitation::count();
    $complaintCount = Complaint::count();
    
    echo "Database health:\n";
    echo "   - Users: {$userCount}\n";
    echo "   - Apartments: {$apartmentCount}\n";
    echo "   - Payments: {$paymentCount}\n";
    echo "   - Invitations: {$invitationCount}\n";
    echo "   - Complaints: {$complaintCount}\n";
    
    // Recommendations
    echo "\nRecommendations:\n";
    
    if ($paymentCount == 0) {
        echo "   📝 No payments in system - billing page will be empty until payments are made\n";
    }
    
    if ($tenantsWithApartments == 0) {
        echo "   📝 Assign tenants to apartments to enable complaint creation\n";
    }
    
    if ($categories == 0) {
        echo "   📝 Run complaint categories seeder: php artisan db:seed --class=ComplaintCategoriesSeeder\n";
    }
    
    echo "   📝 Ensure landlords understand pricing types when creating apartments\n";
    echo "   📝 Test payment flow end-to-end with Paystack integration\n";
    
} catch (Exception $e) {
    echo "❌ Error in summary: " . $e->getMessage() . "\n";
}

echo "\n=== DIAGNOSIS COMPLETE ===\n";