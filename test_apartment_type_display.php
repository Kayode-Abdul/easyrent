<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartment;
use App\Models\ApartmentType;

echo "=== Testing Apartment Type Display Fix ===\n\n";

try {
    // Test 1: Check apartment types table
    echo "1. Checking apartment types in database:\n";
    $apartmentTypes = ApartmentType::all();
    foreach ($apartmentTypes as $type) {
        echo "   ID: {$type->id} - Name: {$type->name}\n";
    }
    echo "\n";

    // Test 2: Check apartments with apartment_type_id
    echo "2. Testing apartments with apartment_type_id:\n";
    $apartments = Apartment::whereNotNull('apartment_type_id')->limit(5)->get();
    
    foreach ($apartments as $apartment) {
        echo "   Apartment ID: {$apartment->id}\n";
        echo "   - apartment_type_id: {$apartment->apartment_type_id}\n";
        echo "   - apartment_type (accessor): {$apartment->apartment_type}\n";
        echo "   - Raw apartment_type field: " . $apartment->getRawOriginal('apartment_type') . "\n";
        
        // Test with relationship loaded
        $apartmentWithType = Apartment::with('apartmentType')->find($apartment->id);
        echo "   - With relationship loaded: {$apartmentWithType->apartment_type}\n";
        echo "\n";
    }

    // Test 3: Check apartments without apartment_type_id (fallback)
    echo "3. Testing apartments without apartment_type_id (fallback):\n";
    $apartmentsWithoutTypeId = Apartment::whereNull('apartment_type_id')->limit(3)->get();
    
    foreach ($apartmentsWithoutTypeId as $apartment) {
        echo "   Apartment ID: {$apartment->id}\n";
        echo "   - apartment_type_id: " . ($apartment->apartment_type_id ?? 'NULL') . "\n";
        echo "   - apartment_type (accessor): {$apartment->apartment_type}\n";
        echo "   - Raw apartment_type field: " . $apartment->getRawOriginal('apartment_type') . "\n";
        echo "\n";
    }

    // Test 4: Test specific apartment from payment context
    echo "4. Testing apartment from payment/invitation context:\n";
    $apartmentInvitation = \App\Models\ApartmentInvitation::with(['apartment.apartmentType'])->first();
    
    if ($apartmentInvitation) {
        $apartment = $apartmentInvitation->apartment;
        echo "   Invitation Token: {$apartmentInvitation->invitation_token}\n";
        echo "   Apartment ID: {$apartment->id}\n";
        echo "   - apartment_type_id: {$apartment->apartment_type_id}\n";
        echo "   - apartment_type (accessor): {$apartment->apartment_type}\n";
        echo "   - Relationship loaded: " . ($apartment->relationLoaded('apartmentType') ? 'YES' : 'NO') . "\n";
        
        if ($apartment->relationLoaded('apartmentType') && $apartment->getRelation('apartmentType')) {
            echo "   - ApartmentType name: {$apartment->getRelation('apartmentType')->name}\n";
        }
        echo "\n";
    }

    echo "=== Test completed successfully! ===\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}