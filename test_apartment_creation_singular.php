<?php

/**
 * Test Apartment Creation with Singular Fields
 * 
 * This script tests the apartment creation endpoint with singular field names
 * to verify it works correctly after reverting from array-based creation.
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\Log;

echo "=== Testing Apartment Creation with Singular Fields ===\n\n";

try {
    // Find a test property
    $property = Property::with('owner')->first();
    
    if (!$property) {
        echo "❌ No properties found in database. Please create a property first.\n";
        exit(1);
    }
    
    echo "✓ Found test property:\n";
    echo "  - Property ID: {$property->property_id}\n";
    echo "  - Type: {$property->getPropertyTypeName()}\n";
    echo "  - Owner: {$property->owner->first_name} {$property->owner->last_name}\n\n";
    
    // Prepare test data with singular field names (matching the form)
    $testData = [
        'propertyId' => $property->property_id,
        'apartmentType' => '2-Bedroom',
        'tenantId' => null, // Vacant apartment
        'fromRange' => now()->format('Y-m-d'),
        'toRange' => now()->addYear()->format('Y-m-d'),
        'amount' => 500000,
        'rentalType' => 'monthly',
        'duration' => 12,
    ];
    
    echo "Test Data (Singular Fields):\n";
    foreach ($testData as $key => $value) {
        echo "  - {$key}: " . ($value ?? 'null') . "\n";
    }
    echo "\n";
    
    // Simulate the request
    echo "Simulating apartment creation request...\n";
    
    // Create a mock request
    $request = new \Illuminate\Http\Request();
    $request->merge($testData);
    
    // Validate using SingleApartmentRequest
    $validator = \Illuminate\Support\Facades\Validator::make($testData, [
        'propertyId' => 'required|exists:properties,property_id',
        'apartmentType' => 'nullable|string|max:100',
        'tenantId' => 'nullable|string',
        'duration' => 'required|numeric|min:0.01',
        'fromRange' => 'required|date',
        'toRange' => 'required|date|after:fromRange',
        'amount' => 'required|numeric|min:0',
        'rentalType' => 'required|in:hourly,daily,weekly,monthly,quarterly,semi_annually,yearly,bi_annually',
    ]);
    
    if ($validator->fails()) {
        echo "❌ Validation failed:\n";
        foreach ($validator->errors()->all() as $error) {
            echo "  - {$error}\n";
        }
        exit(1);
    }
    
    echo "✓ Validation passed\n\n";
    
    // Test the controller method directly
    echo "Testing PropertyController::addSingleApartment()...\n";
    
    // Authenticate as property owner
    auth()->login($property->owner);
    
    // Create the apartment
    $controller = new \App\Http\Controllers\PropertyController();
    $singleRequest = \App\Http\Requests\SingleApartmentRequest::create(
        '/apartment/single',
        'POST',
        $testData
    );
    
    // Set the authenticated user
    $singleRequest->setUserResolver(function () use ($property) {
        return $property->owner;
    });
    
    $response = $controller->addSingleApartment($singleRequest);
    $responseData = json_decode($response->getContent(), true);
    
    if ($responseData['success']) {
        echo "✓ Apartment created successfully!\n";
        echo "  - Apartment ID: {$responseData['data']['apartment_id']}\n";
        echo "  - Type: {$responseData['data']['apartment_type']}\n";
        echo "  - Amount: ₦" . number_format($responseData['data']['amount']) . "\n";
        echo "  - Rental Type: {$responseData['data']['default_rental_type']}\n";
        echo "  - Duration: {$responseData['data']['duration']} months\n";
        echo "  - Status: " . ($responseData['data']['occupied'] ? 'Occupied' : 'Vacant') . "\n\n";
        
        // Clean up - delete the test apartment
        $apartment = \App\Models\Apartment::where('apartment_id', $responseData['data']['apartment_id'])->first();
        if ($apartment) {
            $apartment->delete();
            echo "✓ Test apartment cleaned up\n";
        }
    } else {
        echo "❌ Apartment creation failed:\n";
        echo "  - Error: {$responseData['message']}\n";
        exit(1);
    }
    
    echo "\n=== All Tests Passed! ===\n";
    echo "The apartment creation with singular fields is working correctly.\n";
    
} catch (\Exception $e) {
    echo "❌ Test failed with exception:\n";
    echo "  - Message: {$e->getMessage()}\n";
    echo "  - File: {$e->getFile()}:{$e->getLine()}\n";
    echo "\nStack trace:\n{$e->getTraceAsString()}\n";
    exit(1);
}
