<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProfomaReceipt;
use App\Models\Apartment;
use Illuminate\Support\Facades\Log;

echo "🔧 Testing Apartment Lookup Fix\n";
echo "===============================\n\n";

try {
    // Get a sample proforma receipt
    $proforma = ProfomaReceipt::first();
    
    if (!$proforma) {
        echo "❌ No proforma receipts found in database\n";
        exit(1);
    }
    
    echo "📋 Testing with Proforma ID: {$proforma->id}\n";
    echo "   Proforma apartment_id field: {$proforma->apartment_id}\n\n";
    
    // Test the OLD way (incorrect - this was causing the bug)
    echo "🚫 OLD METHOD (Incorrect):\n";
    $apartmentOldWay = Apartment::where('apartment_id', $proforma->apartment_id)->first();
    if ($apartmentOldWay) {
        echo "   ✅ Found apartment using old method\n";
        echo "   Apartment ID: {$apartmentOldWay->id}\n";
        echo "   Apartment apartment_id: {$apartmentOldWay->apartment_id}\n";
    } else {
        echo "   ❌ No apartment found using old method (this was the bug!)\n";
    }
    
    echo "\n✅ NEW METHOD (Correct):\n";
    // Test the NEW way (correct - using relationship)
    $apartmentNewWay = $proforma->apartment;
    if ($apartmentNewWay) {
        echo "   ✅ Found apartment using relationship\n";
        echo "   Apartment ID: {$apartmentNewWay->id}\n";
        echo "   Apartment apartment_id: {$apartmentNewWay->apartment_id}\n";
    } else {
        echo "   ❌ No apartment found using relationship\n";
    }
    
    // Test the alternative correct way
    echo "\n✅ ALTERNATIVE METHOD (Also Correct):\n";
    $apartmentAltWay = Apartment::where('id', $proforma->apartment_id)->first();
    if ($apartmentAltWay) {
        echo "   ✅ Found apartment using apartments.id lookup\n";
        echo "   Apartment ID: {$apartmentAltWay->id}\n";
        echo "   Apartment apartment_id: {$apartmentAltWay->apartment_id}\n";
    } else {
        echo "   ❌ No apartment found using apartments.id lookup\n";
    }
    
    echo "\n📊 Summary:\n";
    echo "----------\n";
    
    if (!$apartmentOldWay && $apartmentNewWay) {
        echo "✅ FIX CONFIRMED: Old method fails, new method works!\n";
        echo "   This confirms the apartment lookup bug has been identified and fixed.\n";
    } elseif ($apartmentOldWay && $apartmentNewWay) {
        echo "⚠️  Both methods work - this proforma might have matching IDs\n";
        echo "   The fix is still correct for cases where IDs don't match.\n";
    } else {
        echo "❌ Unexpected result - need further investigation\n";
    }
    
    // Test payment creation with correct apartment_id
    echo "\n🧪 Testing Payment Creation:\n";
    echo "----------------------------\n";
    
    if ($apartmentNewWay) {
        echo "Proforma apartment_id field: {$proforma->apartment_id} (refers to apartments.id)\n";
        echo "Actual apartment.id: {$apartmentNewWay->id}\n";
        echo "Actual apartment.apartment_id: {$apartmentNewWay->apartment_id}\n";
        echo "\n";
        echo "✅ For payment record, we should use: {$apartmentNewWay->apartment_id}\n";
        echo "   (This is the apartment's unique identifier field)\n";
    }
    
    echo "\n🎯 CONCLUSION:\n";
    echo "=============\n";
    echo "The payment callback was failing because:\n";
    echo "1. proforma_receipt.apartment_id refers to apartments.id (primary key)\n";
    echo "2. PaymentController was looking up apartments.apartment_id (unique identifier)\n";
    echo "3. These are different fields with different values\n";
    echo "4. Fixed by using the relationship: \$proforma->apartment\n";
    echo "5. Payment records should store apartment.apartment_id for consistency\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}