<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== PM Toggle Debug ===\n\n";

try {
    // Check current PM users and their properties
    $pms = DB::table('users')->whereIn('role', [6, 8])->get();
    
    foreach ($pms as $pm) {
        echo "PM: {$pm->first_name} {$pm->last_name} (ID: {$pm->user_id}, Role: {$pm->role})\n";
        
        // Check owned properties
        $ownedProperties = DB::table('properties')->where('user_id', $pm->user_id)->count();
        echo "  - Owned Properties: {$ownedProperties}\n";
        
        // Check managed properties
        $managedProperties = DB::table('properties')->where('agent_id', $pm->user_id)->count();
        echo "  - Managed Properties: {$managedProperties}\n";
        
        // Check apartments as tenant
        $apartments = DB::table('apartments')->where('tenant_id', $pm->user_id)->count();
        echo "  - Rented Apartments: {$apartments}\n";
        
        echo "\n";
    }
    
    echo "=== Debugging Steps ===\n";
    echo "1. Login as a PM user\n";
    echo "2. Visit /dashboard/myproperty directly\n";
    echo "3. Check if toggle appears at top-right\n";
    echo "4. If no toggle, check browser console for errors\n";
    echo "5. Try clearing browser cache\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>