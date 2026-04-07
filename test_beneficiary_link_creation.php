<?php

require_once 'vendor/autoload.php';

use App\Models\PaymentInvitation;
use App\Models\ProfomaReceipt;
use App\Models\User;

echo "=== Testing Beneficiary Link Creation ===\n\n";

try {
    // Test 1: Check if benefactor_email column is now nullable
    echo "1. Checking database schema...\n";
    
    $columns = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM payment_invitations WHERE Field = 'benefactor_email'");
    
    if (!empty($columns)) {
        $column = $columns[0];
        $isNullable = $column->Null === 'YES';
        echo "   - benefactor_email column exists: ✅\n";
        echo "   - benefactor_email is nullable: " . ($isNullable ? "✅" : "❌") . "\n";
        echo "   - Column details: {$column->Type}, Null: {$column->Null}, Default: {$column->Default}\n";
    } else {
        echo "   ❌ benefactor_email column not found\n";
        exit;
    }
    
    echo "\n2. Testing PaymentInvitation creation with null email...\n";
    
    // Get a test user (tenant)
    $tenant = User::first();
    if (!$tenant) {
        echo "   ❌ No users found in database\n";
        exit;
    }
    
    // Get a test proforma
    $proforma = ProfomaReceipt::first();
    if (!$proforma) {
        echo "   ❌ No proforma receipts found in database\n";
        exit;
    }
    
    echo "   - Using tenant: {$tenant->email} (ID: {$tenant->user_id})\n";
    echo "   - Using proforma: ID {$proforma->id}\n";
    
    // Test creating payment invitation with null email
    $testData = [
        'tenant_id' => $tenant->user_id,
        'benefactor_email' => null, // This should work now
        'proforma_id' => $proforma->id,
        'amount' => 1800000,
        'token' => \Illuminate\Support\Str::random(64),
        'expires_at' => now()->addDays(7),
        'invoice_details' => [
            'property_id' => $proforma->apartment->property_id ?? null,
            'apartment_id' => $proforma->apartment_id,
            'proforma_id' => $proforma->id,
            'tenant_name' => $tenant->first_name . ' ' . $tenant->last_name,
            'sharing_method' => 'link',
        ],
    ];
    
    echo "   - Creating payment invitation...\n";
    
    $invitation = PaymentInvitation::create($testData);
    
    echo "   ✅ Payment invitation created successfully!\n";
    echo "   - Invitation ID: {$invitation->id}\n";
    echo "   - Token: {$invitation->token}\n";
    echo "   - Benefactor Email: " . ($invitation->benefactor_email ?? 'NULL') . "\n";
    echo "   - Amount: ₦" . number_format($invitation->amount, 2) . "\n";
    echo "   - Expires At: {$invitation->expires_at}\n";
    
    echo "\n3. Testing payment link generation...\n";
    
    $paymentLink = route('benefactor.payment.show', $invitation->token);
    echo "   ✅ Payment link generated: {$paymentLink}\n";
    
    echo "\n4. Testing with email (should also work)...\n";
    
    $testDataWithEmail = [
        'tenant_id' => $tenant->user_id,
        'benefactor_email' => 'test@example.com', // This should also work
        'proforma_id' => $proforma->id,
        'amount' => 2000000,
        'token' => \Illuminate\Support\Str::random(64),
        'expires_at' => now()->addDays(7),
        'invoice_details' => [
            'property_id' => $proforma->apartment->property_id ?? null,
            'apartment_id' => $proforma->apartment_id,
            'proforma_id' => $proforma->id,
            'tenant_name' => $tenant->first_name . ' ' . $tenant->last_name,
            'sharing_method' => 'email',
        ],
    ];
    
    $invitationWithEmail = PaymentInvitation::create($testDataWithEmail);
    
    echo "   ✅ Payment invitation with email created successfully!\n";
    echo "   - Invitation ID: {$invitationWithEmail->id}\n";
    echo "   - Benefactor Email: {$invitationWithEmail->benefactor_email}\n";
    
    echo "\n5. Testing controller method simulation...\n";
    
    // Simulate the controller logic
    $controllerTestData = [
        'proforma_id' => $proforma->id,
        'amount' => 1500000,
    ];
    
    // Simulate what the controller does
    $controllerInvitation = PaymentInvitation::create([
        'tenant_id' => $tenant->user_id,
        'benefactor_email' => null, // No email for link sharing
        'proforma_id' => $controllerTestData['proforma_id'],
        'amount' => $controllerTestData['amount'],
        'token' => \Illuminate\Support\Str::random(64),
        'expires_at' => now()->addDays(7),
        'invoice_details' => [
            'property_id' => $proforma->apartment->property_id ?? null,
            'apartment_id' => $proforma->apartment_id,
            'proforma_id' => $controllerTestData['proforma_id'],
            'tenant_name' => $tenant->first_name . ' ' . $tenant->last_name,
            'sharing_method' => 'link',
        ],
    ]);
    
    $controllerPaymentLink = route('benefactor.payment.show', $controllerInvitation->token);
    
    echo "   ✅ Controller simulation successful!\n";
    echo "   - Payment Link: {$controllerPaymentLink}\n";
    echo "   - Response would be:\n";
    echo "     {\n";
    echo "       \"success\": true,\n";
    echo "       \"payment_link\": \"{$controllerPaymentLink}\",\n";
    echo "       \"invitation_token\": \"{$controllerInvitation->token}\"\n";
    echo "     }\n";
    
    echo "\n=== ALL TESTS PASSED! ===\n";
    echo "✅ The beneficiary link creation is now working correctly.\n";
    echo "✅ You can create payment invitations with or without email.\n";
    echo "✅ The 'invite someone to pay' button should work now.\n";
    
    // Clean up test data
    echo "\nCleaning up test data...\n";
    $invitation->delete();
    $invitationWithEmail->delete();
    $controllerInvitation->delete();
    echo "✅ Test data cleaned up.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";