<?php

/**
 * Quick Test Script for New Property Types
 * Run this from command line: php test_new_property_types.php
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Property;
use App\Models\User;

echo "🧪 Testing New Property Types Implementation\n";
echo str_repeat("=", 60) . "\n\n";

// Test 1: Check Property Model Constants
echo "✓ Test 1: Property Type Constants\n";
$types = [
    Property::TYPE_MANSION => 'Mansion',
    Property::TYPE_DUPLEX => 'Duplex',
    Property::TYPE_FLAT => 'Flat',
    Property::TYPE_TERRACE => 'Terrace',
    Property::TYPE_WAREHOUSE => 'Warehouse',
    Property::TYPE_LAND => 'Land',
    Property::TYPE_FARM => 'Farm',
    Property::TYPE_STORE => 'Store',
    Property::TYPE_SHOP => 'Shop',
];

foreach ($types as $id => $name) {
    echo "  - Type $id: $name ✓\n";
}
echo "\n";

// Test 2: Check Helper Methods
echo "✓ Test 2: Property Model Helper Methods\n";
$methods = [
    'getPropertyTypeName',
    'getPropertyTypes',
    'isCommercial',
    'isLand',
    'isResidential',
    'getPropertyAttribute',
    'setPropertyAttribute',
    'getFormattedSize'
];

foreach ($methods as $method) {
    if (method_exists(Property::class, $method)) {
        echo "  - $method() exists ✓\n";
    } else {
        echo "  - $method() MISSING ✗\n";
    }
}
echo "\n";

// Test 3: Check Database Tables
echo "✓ Test 3: Database Tables\n";
try {
    $hasPropertiesTable = \Schema::hasTable('properties');
    $hasAttributesTable = \Schema::hasTable('property_attributes');
    
    echo "  - properties table: " . ($hasPropertiesTable ? "✓" : "✗") . "\n";
    echo "  - property_attributes table: " . ($hasAttributesTable ? "✓" : "✗") . "\n";
    
    if ($hasPropertiesTable) {
        $hasSizeValue = \Schema::hasColumn('properties', 'size_value');
        $hasSizeUnit = \Schema::hasColumn('properties', 'size_unit');
        echo "  - size_value column: " . ($hasSizeValue ? "✓" : "✗") . "\n";
        echo "  - size_unit column: " . ($hasSizeUnit ? "✓" : "✗") . "\n";
    }
} catch (Exception $e) {
    echo "  - Error checking database: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 4: Test Property Type Methods
echo "✓ Test 4: Property Type Classification\n";
$testTypes = [
    ['type' => 5, 'method' => 'isCommercial', 'expected' => true],
    ['type' => 6, 'method' => 'isLand', 'expected' => true],
    ['type' => 1, 'method' => 'isResidential', 'expected' => true],
];

foreach ($testTypes as $test) {
    $property = new Property(['prop_type' => $test['type']]);
    $result = $property->{$test['method']}();
    $status = $result === $test['expected'] ? "✓" : "✗";
    echo "  - Type {$test['type']} {$test['method']}(): " . ($result ? 'true' : 'false') . " $status\n";
}
echo "\n";

// Test 5: Check Property Count by Type
echo "✓ Test 5: Property Statistics\n";
try {
    $totalProperties = Property::count();
    echo "  - Total properties: $totalProperties\n";
    
    foreach ($types as $typeId => $typeName) {
        $count = Property::where('prop_type', $typeId)->count();
        if ($count > 0) {
            echo "  - $typeName: $count\n";
        }
    }
} catch (Exception $e) {
    echo "  - Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 6: Check PropertyAttribute Model
echo "✓ Test 6: PropertyAttribute Model\n";
if (class_exists('App\Models\PropertyAttribute')) {
    echo "  - PropertyAttribute model exists ✓\n";
    
    try {
        $attributeCount = \App\Models\PropertyAttribute::count();
        echo "  - Total property attributes: $attributeCount\n";
    } catch (Exception $e) {
        echo "  - Error counting attributes: " . $e->getMessage() . "\n";
    }
} else {
    echo "  - PropertyAttribute model MISSING ✗\n";
}
echo "\n";

// Summary
echo str_repeat("=", 60) . "\n";
echo "🎉 Test Complete!\n\n";
echo "Next Steps:\n";
echo "1. Visit /listing to test the form\n";
echo "2. Create a new property with one of the new types\n";
echo "3. View the property to see the new details\n";
echo "\n";
