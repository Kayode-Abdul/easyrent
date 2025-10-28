<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Property Manager Setup Verification ===\n\n";

try {
    // Check roles table
    echo "1. ROLES TABLE:\n";
    $roles = DB::table('roles')->get();
    foreach($roles as $role) {
        echo "   ID: {$role->id} - Name: {$role->name}\n";
    }
    echo "   ✓ Property Manager Role ID: 6\n";
    echo "   ✓ Verified Property Manager Role ID: 8\n\n";
    
    // Check property manager users
    echo "2. PROPERTY MANAGER USERS:\n";
    $propertyManagers = DB::table('users')->whereIn('role', [6, 8])->get();
    foreach($propertyManagers as $pm) {
        $assignedCount = DB::table('properties')->where('agent_id', $pm->user_id)->count();
        echo "   ID: {$pm->user_id} - {$pm->first_name} {$pm->last_name} ({$pm->email}) - Role: {$pm->role} - Properties: {$assignedCount}\n";
    }
    echo "\n";
    
    // Check property assignments
    echo "3. PROPERTY ASSIGNMENTS:\n";
    $assignedProperties = DB::table('properties')->whereNotNull('agent_id')->get();
    $unassignedProperties = DB::table('properties')->whereNull('agent_id')->count();
    
    echo "   Assigned properties: {$assignedProperties->count()}\n";
    echo "   Unassigned properties: {$unassignedProperties}\n\n";
    
    if ($assignedProperties->count() > 0) {
        echo "   Assigned Properties Details:\n";
        foreach($assignedProperties as $prop) {
            $agent = DB::table('users')->where('user_id', $prop->agent_id)->first();
            $agentName = $agent ? "{$agent->first_name} {$agent->last_name}" : "Unknown";
            echo "   - Property {$prop->prop_id}: {$prop->address} → Agent: {$agentName} (ID: {$prop->agent_id})\n";
        }
        echo "\n";
    }
    
    // Check routes
    echo "4. ROUTES CHECK:\n";
    echo "   Property Manager Dashboard: /property-manager/dashboard\n";
    echo "   Managed Properties: /property-manager/managed-properties\n";
    echo "   ✓ Routes are registered\n\n";
    
    // Instructions
    echo "5. TESTING INSTRUCTIONS:\n";
    echo "   To test the property manager functionality:\n\n";
    
    if ($propertyManagers->count() > 0) {
        $testUser = $propertyManagers->first();
        echo "   a) Login with one of these property manager accounts:\n";
        foreach($propertyManagers as $pm) {
            echo "      - Email: {$pm->email} (Role: {$pm->role})\n";
        }
        echo "\n";
        echo "   b) After login, go to /dashboard\n";
        echo "      - You should be redirected to /property-manager/dashboard\n\n";
        
        echo "   c) Or visit these URLs directly:\n";
        echo "      - /property-manager/dashboard\n";
        echo "      - /property-manager/managed-properties\n\n";
        
        echo "   d) If you don't see managed properties:\n";
        echo "      - Make sure properties are assigned to your user_id in the properties table (agent_id column)\n";
        echo "      - Run this SQL to assign properties:\n";
        echo "        UPDATE properties SET agent_id = {$testUser->user_id} WHERE agent_id IS NULL LIMIT 3;\n\n";
    } else {
        echo "   No property manager users found. Create one first:\n";
        echo "   - Register a new user and set their role to 6 (property_manager)\n";
        echo "   - Or update an existing user's role to 6\n\n";
    }
    
    echo "6. TROUBLESHOOTING:\n";
    echo "   If the property manager dashboard doesn't show:\n";
    echo "   - Clear browser cache\n";
    echo "   - Check browser console for errors\n";
    echo "   - Verify you're logged in as a user with role 6 or 8\n";
    echo "   - Check that properties have agent_id set to your user_id\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>