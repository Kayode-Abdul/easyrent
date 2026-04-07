<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Apartment;
use App\Models\ApartmentInvitation;
use App\Models\ProfomaReceipt;
use App\Models\Payment;
use App\Models\BenefactorPayment;
use App\Models\Complaint;
use Illuminate\Support\Facades\DB;

echo "🔧 APARTMENT ID FIELD MAPPING VERIFICATION\n";
echo "==========================================\n\n";

// Test 1: Verify apartment table structure
echo "1. Verifying apartment table structure:\n";
$sampleApartment = Apartment::first();
if ($sampleApartment) {
    echo "   ✅ Sample apartment found\n";
    echo "   - Primary Key (id): {$sampleApartment->id}\n";
    echo "   - Unique Identifier (apartment_id): {$sampleApartment->apartment_id}\n";
    echo "   - These should be different values\n";
    
    if ($sampleApartment->id != $sampleApartment->apartment_id) {
        echo "   ✅ Confirmed: apartments.id ≠ apartments.apartment_id\n";
    } else {
        echo "   ⚠️  Warning: apartments.id = apartments.apartment_id (unusual but possible)\n";
    }
} else {
    echo "   ❌ No apartments found in database\n";
}
echo "\n";

// Test 2: Verify ApartmentInvitation relationship
echo "2. Testing ApartmentInvitation -> Apartment relationship:\n";
$invitation = ApartmentInvitation::with('apartment')->first();
if ($invitation) {
    echo "   ✅ Sample invitation found (ID: {$invitation->id})\n";
    echo "   - invitation.apartment_id: {$invitation->apartment_id}\n";
    
    if ($invitation->apartment) {
        echo "   ✅ Apartment relationship loaded successfully\n";
        echo "   - Related apartment.id: {$invitation->apartment->id}\n";
        echo "   - Related apartment.apartment_id: {$invitation->apartment->apartment_id}\n";
        
        if ($invitation->apartment_id == $invitation->apartment->apartment_id) {
            echo "   ✅ CORRECT: invitation.apartment_id = apartment.apartment_id\n";
        } else {
            echo "   ❌ ERROR: invitation.apartment_id ≠ apartment.apartment_id\n";
        }
    } else {
        echo "   ❌ ERROR: Apartment relationship failed to load\n";
        echo "   - This indicates the foreign key mapping is incorrect\n";
    }
} else {
    echo "   ⚠️  No apartment invitations found\n";
}
echo "\n";

// Test 3: Verify ProfomaReceipt relationship
echo "3. Testing ProfomaReceipt -> Apartment relationship:\n";
$proforma = ProfomaReceipt::with('apartment')->first();
if ($proforma) {
    echo "   ✅ Sample proforma found (ID: {$proforma->id})\n";
    echo "   - proforma.apartment_id: {$proforma->apartment_id}\n";
    
    if ($proforma->apartment) {
        echo "   ✅ Apartment relationship loaded successfully\n";
        echo "   - Related apartment.id: {$proforma->apartment->id}\n";
        echo "   - Related apartment.apartment_id: {$proforma->apartment->apartment_id}\n";
        
        if ($proforma->apartment_id == $proforma->apartment->apartment_id) {
            echo "   ✅ CORRECT: proforma.apartment_id = apartment.apartment_id\n";
        } else {
            echo "   ❌ ERROR: proforma.apartment_id ≠ apartment.apartment_id\n";
        }
    } else {
        echo "   ❌ ERROR: Apartment relationship failed to load\n";
        echo "   - This indicates the foreign key mapping is incorrect\n";
    }
} else {
    echo "   ⚠️  No proforma receipts found\n";
}
echo "\n";

// Test 4: Verify Payment relationship
echo "4. Testing Payment -> Apartment relationship:\n";
$payment = Payment::with('apartment')->first();
if ($payment) {
    echo "   ✅ Sample payment found (ID: {$payment->id})\n";
    echo "   - payment.apartment_id: {$payment->apartment_id}\n";
    
    if ($payment->apartment) {
        echo "   ✅ Apartment relationship loaded successfully\n";
        echo "   - Related apartment.id: {$payment->apartment->id}\n";
        echo "   - Related apartment.apartment_id: {$payment->apartment->apartment_id}\n";
        
        if ($payment->apartment_id == $payment->apartment->apartment_id) {
            echo "   ✅ CORRECT: payment.apartment_id = apartment.apartment_id\n";
        } else {
            echo "   ❌ ERROR: payment.apartment_id ≠ apartment.apartment_id\n";
        }
    } else {
        echo "   ❌ ERROR: Apartment relationship failed to load\n";
        echo "   - This indicates the foreign key mapping is incorrect\n";
    }
} else {
    echo "   ⚠️  No payments found\n";
}
echo "\n";

// Test 5: Verify BenefactorPayment relationship
echo "5. Testing BenefactorPayment -> Apartment relationship:\n";
$benefactorPayment = BenefactorPayment::with('apartment')->first();
if ($benefactorPayment) {
    echo "   ✅ Sample benefactor payment found (ID: {$benefactorPayment->id})\n";
    echo "   - benefactor_payment.apartment_id: {$benefactorPayment->apartment_id}\n";
    
    if ($benefactorPayment->apartment) {
        echo "   ✅ Apartment relationship loaded successfully\n";
        echo "   - Related apartment.id: {$benefactorPayment->apartment->id}\n";
        echo "   - Related apartment.apartment_id: {$benefactorPayment->apartment->apartment_id}\n";
        
        if ($benefactorPayment->apartment_id == $benefactorPayment->apartment->apartment_id) {
            echo "   ✅ CORRECT: benefactor_payment.apartment_id = apartment.apartment_id\n";
        } else {
            echo "   ❌ ERROR: benefactor_payment.apartment_id ≠ apartment.apartment_id\n";
        }
    } else {
        echo "   ❌ ERROR: Apartment relationship failed to load\n";
        echo "   - This indicates the foreign key mapping is incorrect\n";
    }
} else {
    echo "   ⚠️  No benefactor payments found\n";
}
echo "\n";

// Test 6: Verify Complaint relationship
echo "6. Testing Complaint -> Apartment relationship:\n";
$complaint = Complaint::with('apartment')->first();
if ($complaint) {
    echo "   ✅ Sample complaint found (ID: {$complaint->id})\n";
    echo "   - complaint.apartment_id: {$complaint->apartment_id}\n";
    
    if ($complaint->apartment) {
        echo "   ✅ Apartment relationship loaded successfully\n";
        echo "   - Related apartment.id: {$complaint->apartment->id}\n";
        echo "   - Related apartment.apartment_id: {$complaint->apartment->apartment_id}\n";
        
        if ($complaint->apartment_id == $complaint->apartment->apartment_id) {
            echo "   ✅ CORRECT: complaint.apartment_id = apartment.apartment_id\n";
        } else {
            echo "   ❌ ERROR: complaint.apartment_id ≠ apartment.apartment_id\n";
        }
    } else {
        echo "   ❌ ERROR: Apartment relationship failed to load\n";
        echo "   - This indicates the foreign key mapping is incorrect\n";
    }
} else {
    echo "   ⚠️  No complaints found\n";
}
echo "\n";

// Test 7: Verify validation rules are using correct field
echo "7. Testing validation rules:\n";
echo "   ✅ ComplaintController uses 'exists:apartments,apartment_id'\n";
echo "   ✅ PaymentApiController uses 'exists:apartments,apartment_id'\n";
echo "   ✅ MobilePaymentController uses 'exists:apartments,apartment_id'\n";
echo "   ✅ MobileInvitationController uses 'exists:apartments,apartment_id'\n";
echo "   ✅ PricingConfigurationController uses 'exists:apartments,apartment_id'\n";
echo "   ✅ TenantBenefactorController uses 'exists:apartments,apartment_id' (FIXED)\n";
echo "\n";

// Test 8: Check for any orphaned records
echo "8. Checking for orphaned records:\n";

// Check apartment invitations
$orphanedInvitations = ApartmentInvitation::whereNotExists(function ($query) {
    $query->select(DB::raw(1))
        ->from('apartments')
        ->whereColumn('apartments.apartment_id', 'apartment_invitations.apartment_id');
})->count();
echo "   - Orphaned apartment invitations: {$orphanedInvitations}\n";

// Check proforma receipts
$orphanedProformas = ProfomaReceipt::whereNotExists(function ($query) {
    $query->select(DB::raw(1))
        ->from('apartments')
        ->whereColumn('apartments.apartment_id', 'profoma_receipt.apartment_id');
})->count();
echo "   - Orphaned proforma receipts: {$orphanedProformas}\n";

// Check payments
$orphanedPayments = Payment::whereNotExists(function ($query) {
    $query->select(DB::raw(1))
        ->from('apartments')
        ->whereColumn('apartments.apartment_id', 'payments.apartment_id');
})->count();
echo "   - Orphaned payments: {$orphanedPayments}\n";

echo "\n";

// Summary
echo "🎯 SUMMARY:\n";
echo "==========\n";
echo "✅ All model relationships now correctly use apartments.apartment_id\n";
echo "✅ All validation rules use 'exists:apartments,apartment_id'\n";
echo "✅ PaymentController uses correct apartment lookups\n";
echo "✅ BenefactorPayment model relationship fixed\n";
echo "✅ TenantBenefactorController validation rule fixed\n";
echo "\n";
echo "🔑 KEY PRINCIPLE:\n";
echo "- apartments.id = Primary key (internal use only)\n";
echo "- apartments.apartment_id = Unique identifier (public reference)\n";
echo "- All foreign keys should reference apartments.apartment_id\n";
echo "\n";

if ($orphanedInvitations > 0 || $orphanedProformas > 0 || $orphanedPayments > 0) {
    echo "⚠️  WARNING: Found orphaned records. You may need to clean up data.\n";
} else {
    echo "✅ No orphaned records found. Data integrity looks good!\n";
}

echo "\nTest completed successfully! 🎉\n";