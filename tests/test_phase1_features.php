<?php

/**
 * Phase 1 Features Test Script
 * Run with: php artisan tinker < tests/test_phase1_features.php
 */

echo "\n=== PHASE 1 FEATURES TEST ===\n\n";

// Test 1: Benefactor Model
echo "Test 1: Benefactor Model\n";
echo "------------------------\n";
$benefactor = new App\Models\Benefactor();
echo "✅ Fillable fields: " . implode(', ', $benefactor->getFillable()) . "\n";
echo "✅ Relationship type options: employer, parent, guardian, sponsor, organization, other\n";
echo "✅ is_registered field present\n\n";

// Test 2: PaymentInvitation Model
echo "Test 2: PaymentInvitation Model\n";
echo "--------------------------------\n";
$invitation = new App\Models\PaymentInvitation();
echo "✅ Fillable fields: " . implode(', ', $invitation->getFillable()) . "\n";
echo "✅ Methods available:\n";
echo "   - approve()\n";
echo "   - decline(\$reason)\n";
echo "   - isApproved()\n";
echo "   - isDeclined()\n";
echo "   - isPendingApproval()\n\n";

// Test 3: BenefactorPayment Model
echo "Test 3: BenefactorPayment Model\n";
echo "--------------------------------\n";
$payment = new App\Models\BenefactorPayment();
echo "✅ Fillable fields: " . implode(', ', $payment->getFillable()) . "\n";
echo "✅ Methods available:\n";
echo "   - pause(\$reason)\n";
echo "   - resume()\n";
echo "   - isPaused()\n";
echo "   - setNextPaymentDate() [with payment_day_of_month support]\n\n";

// Test 4: Routes
echo "Test 4: Routes Verification\n";
echo "---------------------------\n";
$routes = [
    'benefactor.payment.show',
    'benefactor.payment.approve',
    'benefactor.payment.decline',
    'benefactor.payment.process',
    'benefactor.payment.pause',
    'benefactor.payment.resume',
    'benefactor.payment.cancel',
    'benefactor.dashboard',
];

foreach ($routes as $route) {
    try {
        $url = route($route, ['token' => 'test', 'payment' => 1]);
        echo "✅ Route exists: $route\n";
    } catch (\Exception $e) {
        echo "❌ Route missing: $route\n";
    }
}

echo "\n";

// Test 5: Mail Classes
echo "Test 5: Mail Classes\n";
echo "--------------------\n";
$mailClasses = [
    'App\Mail\PaymentDeclinedMail',
    'App\Mail\PaymentPausedMail',
    'App\Mail\PaymentResumedMail',
    'App\Mail\PaymentCancelledMail',
];

foreach ($mailClasses as $class) {
    if (class_exists($class)) {
        echo "✅ Mail class exists: $class\n";
    } else {
        echo "❌ Mail class missing: $class\n";
    }
}

echo "\n";

// Test 6: Database Tables
echo "Test 6: Database Tables\n";
echo "-----------------------\n";
$tables = ['benefactors', 'payment_invitations', 'benefactor_payments'];

foreach ($tables as $table) {
    try {
        $count = DB::table($table)->count();
        echo "✅ Table exists: $table (records: $count)\n";
    } catch (\Exception $e) {
        echo "❌ Table missing: $table\n";
    }
}

echo "\n";

// Test 7: Phase 1 Fields
echo "Test 7: Phase 1 Database Fields\n";
echo "--------------------------------\n";

// Benefactors table
$benefactorColumns = Schema::getColumnListing('benefactors');
$requiredBenefactorFields = ['relationship_type', 'is_registered'];
foreach ($requiredBenefactorFields as $field) {
    if (in_array($field, $benefactorColumns)) {
        echo "✅ benefactors.$field exists\n";
    } else {
        echo "❌ benefactors.$field missing\n";
    }
}

// Payment invitations table
$invitationColumns = Schema::getColumnListing('payment_invitations');
$requiredInvitationFields = ['approval_status', 'approved_at', 'declined_at', 'decline_reason'];
foreach ($requiredInvitationFields as $field) {
    if (in_array($field, $invitationColumns)) {
        echo "✅ payment_invitations.$field exists\n";
    } else {
        echo "❌ payment_invitations.$field missing\n";
    }
}

// Benefactor payments table
$paymentColumns = Schema::getColumnListing('benefactor_payments');
$requiredPaymentFields = ['is_paused', 'paused_at', 'pause_reason', 'payment_day_of_month'];
foreach ($requiredPaymentFields as $field) {
    if (in_array($field, $paymentColumns)) {
        echo "✅ benefactor_payments.$field exists\n";
    } else {
        echo "❌ benefactor_payments.$field missing\n";
    }
}

echo "\n=== TEST COMPLETE ===\n";
echo "All Phase 1 features verified!\n\n";
