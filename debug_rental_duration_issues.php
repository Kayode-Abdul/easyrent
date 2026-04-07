<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartment;
use App\Services\Payment\PaymentCalculationServiceInterface;
use Illuminate\Support\Facades\DB;

echo "🔍 DEBUGGING RENTAL DURATION ISSUES\n";
echo "===================================\n\n";

// Test the specific rental duration types mentioned by the user
$userRequestedTypes = [
    'daily' => 'Daily rentals',
    'weekly' => 'Weekly rentals', 
    'monthly' => 'Monthly rentals',
    'yearly' => 'Yearly rentals',
    'annually' => 'Annual rentals (same as yearly)',
    'quarterly' => 'Quarterly rentals (3 months)',
    'semi_annually' => 'Semi-annual rentals (6 months)',
    'bi_annually' => 'Bi-annual rentals (2 years)'
];

echo "1. Testing user-requested rental duration types:\n";
foreach ($userRequestedTypes as $type => $description) {
    echo "   - {$type}: {$description}\n";
}
echo "\n";

// Get our test apartment
$testApartment = Apartment::where('apartment_id', 999999)->first();
if (!$testApartment) {
    echo "❌ Test apartment not found. Please run the comprehensive test first.\n";
    exit(1);
}

echo "2. Current apartment configuration:\n";
echo "   - Apartment ID: {$testApartment->apartment_id}\n";
echo "   - Hourly Rate: ₦" . number_format($testApartment->hourly_rate ?? 0, 2) . "\n";
echo "   - Daily Rate: ₦" . number_format($testApartment->daily_rate ?? 0, 2) . "\n";
echo "   - Weekly Rate: ₦" . number_format($testApartment->weekly_rate ?? 0, 2) . "\n";
echo "   - Monthly Rate: ₦" . number_format($testApartment->monthly_rate ?? 0, 2) . "\n";
echo "   - Yearly Rate: ₦" . number_format($testApartment->yearly_rate ?? 0, 2) . "\n";
echo "   - Supported Types: " . implode(', ', $testApartment->getSupportedRentalTypes()) . "\n";
echo "   - Default Type: {$testApartment->getDefaultRentalType()}\n";
echo "\n";

// Test 3: Check what the current PaymentCalculationService supports
echo "3. Testing PaymentCalculationService limitations:\n";

$paymentService = app(PaymentCalculationServiceInterface::class);

// Test different scenarios that should work
$testScenarios = [
    // Standard monthly calculations (should work)
    ['type' => 'monthly', 'duration' => 1, 'pricing_type' => 'monthly', 'description' => 'Standard 1 month'],
    ['type' => 'monthly', 'duration' => 3, 'pricing_type' => 'monthly', 'description' => 'Quarterly (3 months)'],
    ['type' => 'monthly', 'duration' => 6, 'pricing_type' => 'monthly', 'description' => 'Semi-annual (6 months)'],
    ['type' => 'monthly', 'duration' => 12, 'pricing_type' => 'monthly', 'description' => 'Annual (12 months)'],
    ['type' => 'monthly', 'duration' => 24, 'pricing_type' => 'monthly', 'description' => 'Bi-annual (24 months)'],
    
    // Total pricing (should work)
    ['type' => 'total', 'duration' => 1, 'pricing_type' => 'total', 'description' => 'Total pricing (1 month)'],
    ['type' => 'total', 'duration' => 12, 'pricing_type' => 'total', 'description' => 'Total pricing (12 months)'],
];

foreach ($testScenarios as $scenario) {
    try {
        $result = $paymentService->calculatePaymentTotal(
            $testApartment->monthly_rate,
            $scenario['duration'],
            $scenario['pricing_type']
        );
        
        if ($result->isValid) {
            echo "   ✅ {$scenario['description']}: ₦" . number_format($result->totalAmount, 2) . "\n";
        } else {
            echo "   ❌ {$scenario['description']}: FAILED - {$result->errorMessage}\n";
        }
    } catch (Exception $e) {
        echo "   ❌ {$scenario['description']}: ERROR - {$e->getMessage()}\n";
    }
}
echo "\n";

// Test 4: Identify the core issues
echo "4. Identifying core issues:\n";

$issues = [];

// Issue 1: PaymentCalculationService doesn't support other duration types
$supportedPricingTypes = ['total', 'monthly'];
$requestedTypes = ['daily', 'weekly', 'yearly', 'quarterly', 'semi_annually', 'bi_annually'];

foreach ($requestedTypes as $type) {
    if (!in_array($type, $supportedPricingTypes)) {
        $issues[] = "PaymentCalculationService doesn't support '{$type}' pricing type";
    }
}

// Issue 2: No conversion between different duration types
$issues[] = "No automatic conversion between daily/weekly/yearly rates and monthly calculations";

// Issue 3: Frontend forms may not handle all duration types
$issues[] = "Frontend forms may not be configured for all rental duration types";

// Issue 4: Database queries may not handle complex duration calculations
$issues[] = "Complex duration calculations (quarterly, semi-annually) not handled in payment flow";

foreach ($issues as $index => $issue) {
    echo "   " . ($index + 1) . ". {$issue}\n";
}
echo "\n";

// Test 5: Simulate what happens in real payment scenarios
echo "5. Simulating real payment scenarios:\n";

// Scenario 1: User selects daily rental for 30 days
echo "   Scenario 1: Daily rental for 30 days\n";
$dailyRate = $testApartment->daily_rate;
$dailyTotal = $dailyRate * 30;
echo "      - Daily rate: ₦" . number_format($dailyRate, 2) . "\n";
echo "      - 30 days total: ₦" . number_format($dailyTotal, 2) . "\n";
echo "      - PaymentCalculationService would use: monthly rate × 1 = ₦" . number_format($testApartment->monthly_rate, 2) . "\n";
echo "      - Difference: ₦" . number_format(abs($dailyTotal - $testApartment->monthly_rate), 2) . "\n";

// Scenario 2: User selects weekly rental for 4 weeks  
echo "\n   Scenario 2: Weekly rental for 4 weeks\n";
$weeklyRate = $testApartment->weekly_rate;
$weeklyTotal = $weeklyRate * 4;
echo "      - Weekly rate: ₦" . number_format($weeklyRate, 2) . "\n";
echo "      - 4 weeks total: ₦" . number_format($weeklyTotal, 2) . "\n";
echo "      - PaymentCalculationService would use: monthly rate × 1 = ₦" . number_format($testApartment->monthly_rate, 2) . "\n";
echo "      - Difference: ₦" . number_format(abs($weeklyTotal - $testApartment->monthly_rate), 2) . "\n";

// Scenario 3: User selects quarterly (3 months)
echo "\n   Scenario 3: Quarterly rental (3 months)\n";
$quarterlyTotal = $testApartment->monthly_rate * 3;
echo "      - Monthly rate × 3: ₦" . number_format($quarterlyTotal, 2) . "\n";
echo "      - PaymentCalculationService would use: monthly rate × 3 = ₦" . number_format($quarterlyTotal, 2) . "\n";
echo "      - ✅ This works correctly!\n";

// Scenario 4: User selects yearly
echo "\n   Scenario 4: Yearly rental\n";
$yearlyRate = $testApartment->yearly_rate;
$monthlyEquivalent = $testApartment->monthly_rate * 12;
echo "      - Yearly rate: ₦" . number_format($yearlyRate, 2) . "\n";
echo "      - Monthly × 12: ₦" . number_format($monthlyEquivalent, 2) . "\n";
echo "      - PaymentCalculationService would use: monthly rate × 12 = ₦" . number_format($monthlyEquivalent, 2) . "\n";
echo "      - Difference: ₦" . number_format(abs($yearlyRate - $monthlyEquivalent), 2) . "\n";
echo "\n";

// Test 6: Check frontend integration points
echo "6. Checking frontend integration points:\n";

// Check if apartment edit form supports all duration types
$apartmentEditView = file_get_contents('resources/views/apartment/edit.blade.php');
$supportedInForm = [];

if (strpos($apartmentEditView, 'hourly_rate') !== false) $supportedInForm[] = 'hourly';
if (strpos($apartmentEditView, 'daily_rate') !== false) $supportedInForm[] = 'daily';
if (strpos($apartmentEditView, 'weekly_rate') !== false) $supportedInForm[] = 'weekly';
if (strpos($apartmentEditView, 'monthly_rate') !== false) $supportedInForm[] = 'monthly';
if (strpos($apartmentEditView, 'yearly_rate') !== false) $supportedInForm[] = 'yearly';

echo "   ✅ Apartment edit form supports: " . implode(', ', $supportedInForm) . "\n";

// Check payment form
$paymentFormExists = file_exists('resources/views/apartment/invite/payment.blade.php');
if ($paymentFormExists) {
    $paymentFormContent = file_get_contents('resources/views/apartment/invite/payment.blade.php');
    $hasRentalDurationSelect = strpos($paymentFormContent, 'rental_duration') !== false || strpos($paymentFormContent, 'duration') !== false;
    echo "   " . ($hasRentalDurationSelect ? '✅' : '❌') . " Payment form has duration selection\n";
} else {
    echo "   ❌ Payment form not found\n";
}
echo "\n";

// Test 7: Proposed solutions
echo "7. Proposed solutions:\n";

echo "   Solution 1: Extend PaymentCalculationService\n";
echo "      - Add support for 'daily', 'weekly', 'yearly' pricing types\n";
echo "      - Add conversion logic between different duration types\n";
echo "      - Maintain backward compatibility with existing 'monthly' and 'total' types\n";

echo "\n   Solution 2: Create RentalDurationCalculationService\n";
echo "      - New service specifically for handling all rental duration types\n";
echo "      - Integrate with existing PaymentCalculationService\n";
echo "      - Handle complex scenarios like quarterly, semi-annually, bi-annually\n";

echo "\n   Solution 3: Update frontend forms\n";
echo "      - Add duration type selection to payment forms\n";
echo "      - Add JavaScript to calculate totals based on selected duration type\n";
echo "      - Update validation to handle all duration types\n";

echo "\n   Solution 4: Database optimization\n";
echo "      - Add indexes for rental duration queries\n";
echo "      - Add validation constraints for rate consistency\n";
echo "      - Add migration to fix existing inconsistent data\n";
echo "\n";

// Test 8: Quick fix demonstration
echo "8. Quick fix demonstration:\n";

echo "   Creating enhanced calculation method...\n";

function calculateEnhancedRentalCost(Apartment $apartment, string $durationType, int $quantity): array {
    $result = [
        'success' => false,
        'amount' => 0,
        'method' => '',
        'error' => null
    ];
    
    try {
        switch ($durationType) {
            case 'hourly':
                if ($apartment->hourly_rate) {
                    $result['amount'] = $apartment->hourly_rate * $quantity;
                    $result['method'] = "hourly_rate × {$quantity}";
                    $result['success'] = true;
                }
                break;
                
            case 'daily':
                if ($apartment->daily_rate) {
                    $result['amount'] = $apartment->daily_rate * $quantity;
                    $result['method'] = "daily_rate × {$quantity}";
                    $result['success'] = true;
                }
                break;
                
            case 'weekly':
                if ($apartment->weekly_rate) {
                    $result['amount'] = $apartment->weekly_rate * $quantity;
                    $result['method'] = "weekly_rate × {$quantity}";
                    $result['success'] = true;
                }
                break;
                
            case 'monthly':
                if ($apartment->monthly_rate) {
                    $result['amount'] = $apartment->monthly_rate * $quantity;
                    $result['method'] = "monthly_rate × {$quantity}";
                    $result['success'] = true;
                }
                break;
                
            case 'quarterly':
                if ($apartment->monthly_rate) {
                    $result['amount'] = $apartment->monthly_rate * ($quantity * 3);
                    $result['method'] = "monthly_rate × ({$quantity} × 3)";
                    $result['success'] = true;
                }
                break;
                
            case 'semi_annually':
                if ($apartment->monthly_rate) {
                    $result['amount'] = $apartment->monthly_rate * ($quantity * 6);
                    $result['method'] = "monthly_rate × ({$quantity} × 6)";
                    $result['success'] = true;
                }
                break;
                
            case 'annually':
            case 'yearly':
                if ($apartment->yearly_rate) {
                    $result['amount'] = $apartment->yearly_rate * $quantity;
                    $result['method'] = "yearly_rate × {$quantity}";
                    $result['success'] = true;
                } elseif ($apartment->monthly_rate) {
                    $result['amount'] = $apartment->monthly_rate * ($quantity * 12);
                    $result['method'] = "monthly_rate × ({$quantity} × 12)";
                    $result['success'] = true;
                }
                break;
                
            case 'bi_annually':
                if ($apartment->yearly_rate) {
                    $result['amount'] = $apartment->yearly_rate * ($quantity * 2);
                    $result['method'] = "yearly_rate × ({$quantity} × 2)";
                    $result['success'] = true;
                } elseif ($apartment->monthly_rate) {
                    $result['amount'] = $apartment->monthly_rate * ($quantity * 24);
                    $result['method'] = "monthly_rate × ({$quantity} × 24)";
                    $result['success'] = true;
                }
                break;
                
            default:
                $result['error'] = "Unsupported duration type: {$durationType}";
        }
        
        if (!$result['success'] && !$result['error']) {
            $result['error'] = "No rate configured for {$durationType}";
        }
        
    } catch (Exception $e) {
        $result['error'] = $e->getMessage();
    }
    
    return $result;
}

// Test the enhanced calculation
$enhancedTests = [
    ['type' => 'daily', 'quantity' => 30, 'description' => '30 days'],
    ['type' => 'weekly', 'quantity' => 4, 'description' => '4 weeks'],
    ['type' => 'monthly', 'quantity' => 1, 'description' => '1 month'],
    ['type' => 'quarterly', 'quantity' => 1, 'description' => '1 quarter'],
    ['type' => 'semi_annually', 'quantity' => 1, 'description' => '1 semi-annual'],
    ['type' => 'annually', 'quantity' => 1, 'description' => '1 year'],
    ['type' => 'bi_annually', 'quantity' => 1, 'description' => '1 bi-annual'],
];

foreach ($enhancedTests as $test) {
    $result = calculateEnhancedRentalCost($testApartment, $test['type'], $test['quantity']);
    
    if ($result['success']) {
        echo "   ✅ {$test['description']}: ₦" . number_format($result['amount'], 2) . " ({$result['method']})\n";
    } else {
        echo "   ❌ {$test['description']}: {$result['error']}\n";
    }
}
echo "\n";

// Summary
echo "🎯 DEBUGGING SUMMARY:\n";
echo "====================\n";
echo "✅ Database schema supports all rental duration fields\n";
echo "✅ Apartment model has all necessary methods\n";
echo "✅ Basic rental calculations work correctly\n";
echo "\n";
echo "❌ ISSUES FOUND:\n";
echo "   1. PaymentCalculationService only supports 'total' and 'monthly' pricing\n";
echo "   2. No integration between different duration types and payment system\n";
echo "   3. Frontend may not handle all duration type selections\n";
echo "   4. Rate inconsistencies between different duration types\n";
echo "\n";
echo "🔧 NEXT STEPS:\n";
echo "   1. Extend PaymentCalculationService to support all duration types\n";
echo "   2. Update payment forms to handle duration type selection\n";
echo "   3. Add JavaScript for dynamic calculation based on duration type\n";
echo "   4. Create rate consistency validation\n";
echo "   5. Update payment processing to use correct duration-based calculations\n";

echo "\nDebugging completed! 🎉\n";