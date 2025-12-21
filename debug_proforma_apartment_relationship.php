<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProfomaReceipt;
use App\Models\Apartment;
use Illuminate\Support\Facades\DB;

echo "🔍 Debugging Proforma-Apartment Relationship\n";
echo "============================================\n\n";

try {
    // Get the specific proforma that failed
    echo "📋 Analyzing Failed Proforma (ID: 2):\n";
    echo str_repeat("-", 40) . "\n";
    
    // Use raw SQL to avoid relationship issues
    $proforma = DB::table('profoma_receipt')->where('id', 2)->first();
    
    if ($proforma) {
        echo "Proforma ID: {$proforma->id}\n";
        echo "Proforma apartment_id field: {$proforma->apartment_id}\n";
        echo "Proforma tenant_id: {$proforma->tenant_id}\n";
        echo "Proforma user_id (landlord): {$proforma->user_id}\n";
        echo "Proforma amount: ₦" . number_format($proforma->amount, 2) . "\n\n";
        
        // Check what apartments exist with this ID
        echo "🏠 Apartment Lookup Tests:\n";
        echo str_repeat("-", 26) . "\n";
        
        // Test 1: Look for apartment with apartment_id = proforma.apartment_id
        $apartmentByApartmentId = DB::table('apartments')->where('apartment_id', $proforma->apartment_id)->first();
        if ($apartmentByApartmentId) {
            echo "✅ Found apartment by apartment_id = {$proforma->apartment_id}\n";
            echo "   Apartment.id: {$apartmentByApartmentId->id}\n";
            echo "   Apartment.apartment_id: {$apartmentByApartmentId->apartment_id}\n";
            echo "   Apartment.amount: ₦" . number_format($apartmentByApartmentId->amount, 2) . "\n";
        } else {
            echo "❌ No apartment found with apartment_id = {$proforma->apartment_id}\n";
        }
        
        // Test 2: Look for apartment with id = proforma.apartment_id
        $apartmentById = DB::table('apartments')->where('id', $proforma->apartment_id)->first();
        if ($apartmentById) {
            echo "✅ Found apartment by id = {$proforma->apartment_id}\n";
            echo "   Apartment.id: {$apartmentById->id}\n";
            echo "   Apartment.apartment_id: {$apartmentById->apartment_id}\n";
            echo "   Apartment.amount: ₦" . number_format($apartmentById->amount, 2) . "\n";
        } else {
            echo "❌ No apartment found with id = {$proforma->apartment_id}\n";
        }
        
        echo "\n📊 Database Analysis:\n";
        echo str_repeat("-", 20) . "\n";
        
        // Show some sample apartments to understand the structure
        $sampleApartments = DB::table('apartments')->take(5)->get(['id', 'apartment_id', 'amount']);
        echo "Sample apartments in database:\n";
        foreach ($sampleApartments as $apt) {
            echo "  ID: {$apt->id}, apartment_id: {$apt->apartment_id}, amount: ₦" . number_format($apt->amount, 2) . "\n";
        }
        
        echo "\n🔧 Relationship Analysis:\n";
        echo str_repeat("-", 25) . "\n";
        
        if ($apartmentByApartmentId && !$apartmentById) {
            echo "✅ CORRECT: proforma.apartment_id refers to apartments.apartment_id\n";
            echo "   Relationship should be: belongsTo(Apartment::class, 'apartment_id', 'apartment_id')\n";
        } elseif ($apartmentById && !$apartmentByApartmentId) {
            echo "✅ CORRECT: proforma.apartment_id refers to apartments.id\n";
            echo "   Relationship should be: belongsTo(Apartment::class, 'apartment_id', 'id')\n";
        } elseif ($apartmentById && $apartmentByApartmentId) {
            echo "⚠️  AMBIGUOUS: Both lookups work (IDs happen to match)\n";
            echo "   Need to check which is the intended relationship\n";
        } else {
            echo "❌ BROKEN: Neither lookup works - apartment doesn't exist\n";
            echo "   This proforma references a non-existent apartment\n";
        }
        
        // Check if there are apartments that might match
        echo "\n🔍 Finding Possible Matches:\n";
        echo str_repeat("-", 28) . "\n";
        
        $possibleMatches = DB::table('apartments')
            ->where('user_id', $proforma->user_id)
            ->get(['id', 'apartment_id', 'amount', 'apartment_type']);
            
        if ($possibleMatches->count() > 0) {
            echo "Apartments owned by landlord {$proforma->user_id}:\n";
            foreach ($possibleMatches as $match) {
                echo "  ID: {$match->id}, apartment_id: {$match->apartment_id}, type: {$match->apartment_type}, amount: ₦" . number_format($match->amount, 2) . "\n";
            }
        } else {
            echo "❌ No apartments found for landlord {$proforma->user_id}\n";
        }
        
    } else {
        echo "❌ Proforma with ID 2 not found\n";
    }

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Analysis complete.\n";