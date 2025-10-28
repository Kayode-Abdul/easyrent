<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Dual Toggle Debug ===\n\n";

try {
    // Check different user types
    $users = DB::table('users')->whereIn('role', [1, 2, 3, 6, 8])->get();
    
    echo "User Types and Expected Toggle Behavior:\n\n";
    
    foreach ($users as $user) {
        $roleName = match($user->role) {
            1 => 'Admin',
            2 => 'Landlord', 
            3 => 'Tenant',
            6 => 'Property Manager',
            8 => 'Verified Property Manager',
            default => 'Unknown'
        };
        
        echo "User: {$user->first_name} {$user->last_name} (Role: {$roleName})\n";
        
        // Check what they should see
        if (in_array($user->role, [6, 8])) {
            echo "  ✓ Should see PM Toggle: Personal ↔ Property Manager\n";
            echo "  ✓ Should see L/T Toggle: Landlord ↔ Tenant\n";
        } else {
            echo "  ✗ Should NOT see PM Toggle\n";
            echo "  ✓ Should see L/T Toggle: Landlord ↔ Tenant\n";
        }
        
        // Check their properties/apartments
        $ownedProperties = DB::table('properties')->where('user_id', $user->user_id)->count();
        $managedProperties = DB::table('properties')->where('agent_id', $user->user_id)->count();
        $rentedApartments = DB::table('apartments')->where('tenant_id', $user->user_id)->count();
        
        echo "  - Owned Properties: {$ownedProperties}\n";
        echo "  - Managed Properties: {$managedProperties}\n";
        echo "  - Rented Apartments: {$rentedApartments}\n";
        echo "\n";
    }
    
    echo "=== Expected Toggle Layout ===\n";
    echo "For Property Managers:\n";
    echo "  [Personal ↔ Property Manager]    [Landlord ↔ Tenant]\n\n";
    
    echo "For Everyone Else:\n";
    echo "                                   [Landlord ↔ Tenant]\n\n";
    
    echo "=== Testing Steps ===\n";
    echo "1. Login as different user types\n";
    echo "2. Visit /dashboard/myproperty\n";
    echo "3. Check toggle visibility:\n";
    echo "   - PMs should see 2 toggles\n";
    echo "   - Others should see 1 toggle (Landlord/Tenant)\n";
    echo "4. Test toggle functionality:\n";
    echo "   - Landlord/Tenant toggle should work for everyone\n";
    echo "   - PM toggle should only work for PMs\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>