<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProfomaReceipt;

echo "=== Testing Proforma Calculation Issue ===\n\n";

try {
    // Test proforma calculations
    $proformas = ProfomaReceipt::with(['apartment'])->limit(5)->get();
    
    foreach ($proformas as $proforma) {
        echo "Proforma ID: {$proforma->id}\n";
        echo "- amount (monthly rent): ₦" . number_format($proforma->amount ?? 0) . "\n";
        echo "- duration: {$proforma->duration} months\n";
        echo "- total (stored): ₦" . number_format($proforma->total ?? 0) . "\n";
        
        if ($proforma->apartment) {
            echo "- apartment->amount: ₦" . number_format($proforma->apartment->amount) . "\n";
            
            // What the view calculates
            $viewCalculation = $proforma->apartment->amount * $proforma->duration;
            echo "- View calculation (apartment->amount × duration): ₦" . number_format($viewCalculation) . "\n";
            
            // What the template shows for monthly rent
            $monthlyRentDisplay = $proforma->amount ?? ($proforma->total / $proforma->duration);
            echo "- Template monthly rent display: ₦" . number_format($monthlyRentDisplay) . "\n";
            
            // Check if there's a mismatch
            if ($proforma->amount != $proforma->apartment->amount) {
                echo "  ⚠️  MISMATCH: proforma->amount (₦" . number_format($proforma->amount) . ") != apartment->amount (₦" . number_format($proforma->apartment->amount) . ")\n";
            }
            
            // Check if total makes sense
            $expectedMonthlyTotal = $proforma->amount + ($proforma->security_deposit ?? 0) + ($proforma->water ?? 0) + ($proforma->internet ?? 0) + ($proforma->generator ?? 0) + ($proforma->other_charges_amount ?? 0);
            if (abs($proforma->total - $expectedMonthlyTotal) > 0.01) {
                echo "  ⚠️  TOTAL MISMATCH: Expected ₦" . number_format($expectedMonthlyTotal) . ", Got ₦" . number_format($proforma->total) . "\n";
            }
        }
        echo "\n";
    }

    echo "=== Analysis ===\n";
    echo "The issue is likely in the proforma template where it shows:\n";
    echo "{{ number_format((\$proforma->amount ?? \$proforma->total / \$proforma->duration), 2) }}\n\n";
    echo "If \$proforma->amount is null, it divides \$proforma->total by duration.\n";
    echo "But \$proforma->total is monthly rent + charges, NOT total lease amount.\n";
    echo "So this division would show an incorrect monthly rent.\n\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}