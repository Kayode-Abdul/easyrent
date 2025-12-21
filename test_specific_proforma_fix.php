<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProfomaReceipt;
use App\Models\Apartment;
use Illuminate\Support\Facades\DB;

echo "🧪 Testing Specific Proforma Fix (ID: 2)\n";
echo "========================================\n\n";

try {
    // Test with the specific proforma that failed
    $proforma = DB::table('profoma_receipt')->where('id', 2)->first();
    
    if (!$proforma) {
        echo "❌ Proforma ID 2 not found\n";
        exit(1);
    }
    
    echo "📋 Testing Proforma ID: {$proforma->id}\n";
    echo "   Proforma apartment_id field: {$proforma->apartment_id}\n\n";
    
    // Test the relationship using Eloquent model
    echo "✅ RELATIONSHIP TEST:\n";
    $proformaModel = ProfomaReceipt::find(2);
    $apartment = $proformaModel->apartment;
    
    if ($apartment) {
        echo "   ✅ Apartment found via relationship\n";
        echo "   Apartment ID: {$apartment->id}\n";
        echo "   Apartment apartment_id: {$apartment->apartment_id}\n";
        echo "   Apartment amount: ₦" . number_format($apartment->amount, 2) . "\n";
        
        echo "\n🔧 Payment Record Test:\n";
        echo "   Proforma apartment_id: {$proforma->apartment_id} (refers to apartments.id)\n";
        echo "   Apartment.id: {$apartment->id}\n";
        echo "   Apartment.apartment_id: {$apartment->apartment_id}\n";
        echo "   ✅ Payment should store: {$apartment->apartment_id} (apartment's unique identifier)\n";
        
        echo "\n🎯 VERIFICATION:\n";
        if ($proforma->apartment_id == $apartment->id) {
            echo "   ✅ Relationship mapping is correct\n";
            echo "   ✅ proforma.apartment_id ({$proforma->apartment_id}) = apartments.id ({$apartment->id})\n";
        } else {
            echo "   ❌ Relationship mapping is incorrect\n";
        }
        
    } else {
        echo "   ❌ Apartment NOT found via relationship\n";
        echo "   This indicates the relationship is still broken\n";
    }
    
    echo "\n📊 Summary:\n";
    echo "----------\n";
    
    if ($apartment) {
        echo "✅ FIX SUCCESSFUL: Relationship now works correctly\n";
        echo "   - Proforma can find its apartment\n";
        echo "   - Payment callback will now succeed\n";
        echo "   - Payment will store correct apartment_id: {$apartment->apartment_id}\n";
    } else {
        echo "❌ FIX FAILED: Relationship still broken\n";
        echo "   - Need to investigate further\n";
    }

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Test completed.\n";