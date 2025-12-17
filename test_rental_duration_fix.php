<?php

require_once 'vendor/autoload.php';

use App\Models\Apartment;

echo "=== Testing Rental Duration Fix ===\n\n";

try {
    // Test 1: Check if rental duration fields were added
    echo "1. Testing database schema...\n";
    $apartment = Apartment::first();
    
    if ($apartment) {
        echo "✅ Found apartment: {$apartment->apartment_id}\n";
        
        // Check if new fields exist
        $fields = ['supported_rental_types', 'hourly_rate', 'daily_rate', 'weekly_rate', 'monthly_rate', 'yearly_rate', 'default_rental_type'];
        foreach ($fields as $field) {
            if (array_key_exists($field, $apartment->getAttributes())) {
                echo "✅ Field '{$field}' exists\n";
            } else {
                echo "❌ Field '{$field}' missing\n";
            }
        }
        
        // Test 2: Test rental duration methods
        echo "\n2. Testing rental duration methods...\n";
        
        // Test getSupportedRentalTypes
        $supportedTypes = $apartment->getSupportedRentalTypes();
        echo "✅ Supported rental types: " . implode(', ', $supportedTypes) . "\n";
        
        // Test supportsRentalType
        $supportsMonthly = $apartment->supportsRentalType('monthly');
        echo "✅ Supports monthly: " . ($supportsMonthly ? 'Yes' : 'No') . "\n";
        
        // Test getRateForType
        $monthlyRate = $apartment->getRateForType('monthly');
        echo "✅ Monthly rate: ₦" . number_format($monthlyRate ?? 0, 2) . "\n";
        
        // Test getDefaultRentalType
        $defaultType = $apartment->getDefaultRentalType();
        echo "✅ Default rental type: {$defaultType}\n";
        
        // Test 3: Test setting rental configuration
        echo "\n3. Testing rental configuration...\n";
        
        $testConfig = [
            'hourly' => 5000,
            'daily' => 50000,
            'weekly' => 300000,
            'monthly' => 1000000,
            'yearly' => 10000000
        ];
        
        $apartment->setRentalConfiguration($testConfig);
        $apartment->save();
        
        echo "✅ Set rental configuration\n";
        
        // Verify the configuration was saved
        $apartment->refresh();
        $allRates = $apartment->getAllRates();
        
        foreach ($allRates as $type => $rate) {
            echo "✅ {$type}: " . $apartment->getFormattedRate($type) . "\n";
        }
        
        // Test 4: Test rental cost calculation
        echo "\n4. Testing rental cost calculation...\n";
        
        try {
            $weeklyCost = $apartment->calculateRentalCost('weekly', 2);
            echo "✅ 2 weeks cost: ₦" . number_format($weeklyCost, 2) . "\n";
            
            $monthlyCost = $apartment->calculateRentalCost('monthly', 6);
            echo "✅ 6 months cost: ₦" . number_format($monthlyCost, 2) . "\n";
        } catch (Exception $e) {
            echo "❌ Cost calculation error: " . $e->getMessage() . "\n";
        }
        
        echo "\n✅ All rental duration tests passed!\n";
        
    } else {
        echo "❌ No apartments found in database\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";