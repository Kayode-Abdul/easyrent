<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use App\Http\Controllers\ProfomaController;
use App\Models\Apartment;
use App\Models\User;
use App\Models\ProfomaReceipt;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PROFORMA SEND DIAGNOSTIC TEST ===\n\n";

try {
    // Test 1: Check if we have apartments with tenants
    echo "1. Checking apartments with tenants...\n";
    $apartmentsWithTenants = Apartment::whereNotNull('tenant_id')->with(['property', 'tenant'])->take(3)->get();
    
    if ($apartmentsWithTenants->isEmpty()) {
        echo "❌ No apartments found with assigned tenants\n";
        echo "   Creating test data...\n";
        
        // Find a user to act as landlord
        $landlord = User::where('admin', 0)->first();
        if (!$landlord) {
            echo "❌ No non-admin users found to act as landlord\n";
            exit(1);
        }
        
        // Find a user to act as tenant
        $tenant = User::where('admin', 0)->where('user_id', '!=', $landlord->user_id)->first();
        if (!$tenant) {
            echo "❌ No users found to act as tenant\n";
            exit(1);
        }
        
        echo "   Using landlord: {$landlord->username} (ID: {$landlord->user_id})\n";
        echo "   Using tenant: {$tenant->username} (ID: {$tenant->user_id})\n";
        
    } else {
        echo "✅ Found " . $apartmentsWithTenants->count() . " apartments with tenants\n";
        foreach ($apartmentsWithTenants as $apt) {
            echo "   - Apartment {$apt->apartment_id}: Tenant ID {$apt->tenant_id}\n";
        }
    }
    
    // Test 2: Check ProfomaController route accessibility
    echo "\n2. Testing ProfomaController route...\n";
    $testApartment = $apartmentsWithTenants->first();
    
    if (!$testApartment) {
        echo "❌ No test apartment available\n";
        exit(1);
    }
    
    echo "   Using apartment: {$testApartment->apartment_id}\n";
    echo "   Tenant ID: {$testApartment->tenant_id}\n";
    echo "   Property: " . ($testApartment->property->name ?? 'N/A') . "\n";
    
    // Test 3: Check if tenant exists
    echo "\n3. Verifying tenant exists...\n";
    $tenant = User::where('user_id', $testApartment->tenant_id)->first();
    if (!$tenant) {
        echo "❌ Tenant with ID {$testApartment->tenant_id} not found\n";
        exit(1);
    }
    echo "✅ Tenant found: {$tenant->first_name} {$tenant->last_name} ({$tenant->email})\n";
    
    // Test 4: Check landlord
    echo "\n4. Verifying landlord exists...\n";
    $landlord = User::where('user_id', $testApartment->property->user_id)->first();
    if (!$landlord) {
        echo "❌ Landlord not found\n";
        exit(1);
    }
    echo "✅ Landlord found: {$landlord->first_name} {$landlord->last_name} ({$landlord->email})\n";
    
    // Test 5: Simulate proforma send
    echo "\n5. Testing proforma send functionality...\n";
    
    // Mock authentication
    auth()->login($landlord);
    
    // Create request data
    $requestData = [
        'tenant_id' => $testApartment->tenant_id,
        'duration' => 12,
        'amount' => 50000,
        'security_deposit' => 25000,
        'water' => 5000,
        'internet' => 3000,
        'generator' => 2000,
        'other_charges_desc' => 'Service charge',
        'other_charges_amount' => 1000,
        'total' => 86000
    ];
    
    // Create mock request
    $request = new Request($requestData);
    
    // Test the controller
    $controller = new ProfomaController();
    
    try {
        $response = $controller->send($request, $testApartment->apartment_id);
        $responseData = json_decode($response->getContent(), true);
        
        if ($responseData['success']) {
            echo "✅ Proforma sent successfully!\n";
            echo "   Message: " . $responseData['message'] . "\n";
            
            // Check if proforma was created
            $proforma = ProfomaReceipt::where('apartment_id', $testApartment->id)->latest()->first();
            if ($proforma) {
                echo "✅ Proforma record created in database\n";
                echo "   Transaction ID: {$proforma->transaction_id}\n";
                echo "   Total: ₦" . number_format($proforma->total, 2) . "\n";
                echo "   Status: {$proforma->getStatusLabelAttribute()}\n";
            } else {
                echo "❌ Proforma record not found in database\n";
            }
            
        } else {
            echo "❌ Proforma send failed\n";
            echo "   Error: " . $responseData['message'] . "\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Exception occurred: " . $e->getMessage() . "\n";
        echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
    // Test 6: Check email configuration
    echo "\n6. Checking email configuration...\n";
    $mailConfig = config('mail');
    echo "   Mail driver: " . ($mailConfig['default'] ?? 'not set') . "\n";
    
    if (isset($mailConfig['mailers'][$mailConfig['default']])) {
        $mailer = $mailConfig['mailers'][$mailConfig['default']];
        echo "   Host: " . ($mailer['host'] ?? 'not set') . "\n";
        echo "   Port: " . ($mailer['port'] ?? 'not set') . "\n";
        echo "   Username: " . ($mailer['username'] ?? 'not set') . "\n";
    }
    
    // Test 7: Check recent proformas
    echo "\n7. Checking recent proforma records...\n";
    $recentProformas = ProfomaReceipt::with(['tenant', 'owner'])->latest()->take(5)->get();
    
    if ($recentProformas->isEmpty()) {
        echo "   No proforma records found\n";
    } else {
        echo "   Found " . $recentProformas->count() . " recent proformas:\n";
        foreach ($recentProformas as $proforma) {
            echo "   - ID {$proforma->id}: ₦" . number_format($proforma->total, 2) . 
                 " ({$proforma->getStatusLabelAttribute()}) - " . 
                 $proforma->created_at->format('Y-m-d H:i') . "\n";
        }
    }
    
    echo "\n=== DIAGNOSTIC COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}