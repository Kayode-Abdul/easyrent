<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Property;
use App\Models\PropertyType;

echo "=== Testing Property Type Display ===\n\n";

try {
    // Test 1: Check property types table
    echo "1. Checking property types in database:\n";
    $propertyTypes = PropertyType::all();
    foreach ($propertyTypes as $type) {
        echo "   ID: {$type->id} - Name: {$type->name}\n";
    }
    echo "\n";

    // Test 2: Check properties and their types
    echo "2. Testing properties with prop_type:\n";
    $properties = Property::limit(5)->get();
    
    foreach ($properties as $property) {
        echo "   Property ID: {$property->property_id}\n";
        echo "   - prop_type (raw): {$property->prop_type}\n";
        echo "   - getPropertyTypeName(): {$property->getPropertyTypeName()}\n";
        
        // Test with relationship
        $propertyWithType = Property::with('propertyType')->find($property->id);
        if ($propertyWithType && $propertyWithType->propertyType) {
            echo "   - propertyType->name: {$propertyWithType->propertyType->name}\n";
        } else {
            echo "   - propertyType->name: No relationship found\n";
        }
        echo "\n";
    }

    // Test 3: Check if we need to create an accessor
    echo "3. Testing property type display in views:\n";
    $property = Property::first();
    if ($property) {
        echo "   Current display: prop_type = {$property->prop_type}\n";
        echo "   Should display: {$property->getPropertyTypeName()}\n";
        echo "   Problem: Views showing '{$property->prop_type}' instead of '{$property->getPropertyTypeName()}'\n";
    }
    echo "\n";

    echo "=== Test completed ===\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}