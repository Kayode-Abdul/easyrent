<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "=== Property Manager Setup Script ===\n\n";

try {
    // Check if there are any existing property managers
    $existingPMs = DB::table('users')->where('role', 7)->get();
    
    if ($existingPMs->count() > 0) {
        echo "Existing Property Managers:\n";
        foreach ($existingPMs as $pm) {
            echo "- ID: {$pm->user_id}, Name: {$pm->first_name} {$pm->last_name}, Email: {$pm->email}\n";
        }
        echo "\n";
    } else {
        echo "No existing property managers found.\n\n";
        
        // Create a test property manager
        echo "Creating a test property manager...\n";
        
        $userId = DB::table('users')->insertGetId([
            'first_name' => 'Test',
            'last_name' => 'PropertyManager',
            'email' => 'propertymanager@test.com',
            'password' => Hash::make('password123'),
            'role' => 7,
            'phone' => '08012345678',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "✓ Created property manager with ID: {$userId}\n";
        echo "  Email: propertymanager@test.com\n";
        echo "  Password: password123\n\n";
    }
    
    // Check properties that can be assigned
    $unassignedProperties = DB::table('properties')->whereNull('agent_id')->get();
    $assignedProperties = DB::table('properties')->whereNotNull('agent_id')->get();
    
    echo "Property Assignment Status:\n";
    echo "- Unassigned properties: {$unassignedProperties->count()}\n";
    echo "- Assigned properties: {$assignedProperties->count()}\n\n";
    
    if ($unassignedProperties->count() > 0) {
        echo "Sample unassigned properties:\n";
        foreach ($unassignedProperties->take(5) as $prop) {
            echo "- Property {$prop->prop_id}: {$prop->address}\n";
        }
        echo "\n";
    }
    
    // Get the first property manager to assign properties to
    $propertyManager = DB::table('users')->where('role', 7)->first();
    
    if ($propertyManager && $unassignedProperties->count() > 0) {
        echo "Assigning first 3 properties to property manager {$propertyManager->first_name} {$propertyManager->last_name}...\n";
        
        $propertiesToAssign = $unassignedProperties->take(3);
        foreach ($propertiesToAssign as $prop) {
            DB::table('properties')
                ->where('prop_id', $prop->prop_id)
                ->update(['agent_id' => $propertyManager->user_id]);
            
            echo "✓ Assigned property {$prop->prop_id} ({$prop->address})\n";
        }
        echo "\n";
    }
    
    // Final summary
    $finalPMs = DB::table('users')->where('role', 7)->get();
    echo "=== Setup Complete ===\n";
    echo "Property Managers in system: {$finalPMs->count()}\n";
    
    foreach ($finalPMs as $pm) {
        $managedCount = DB::table('properties')->where('agent_id', $pm->user_id)->count();
        echo "- {$pm->first_name} {$pm->last_name} (ID: {$pm->user_id}): {$managedCount} properties\n";
    }
    
    echo "\nNext steps:\n";
    echo "1. Login with email: propertymanager@test.com, password: password123\n";
    echo "2. Go to /dashboard - you should be redirected to property manager dashboard\n";
    echo "3. Or visit /property-manager/dashboard directly\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>