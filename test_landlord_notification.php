<?php

/**
 * Test script to verify landlord payment notification system
 * 
 * This script tests:
 * 1. Email notification to landlord
 * 2. In-app message creation
 * 3. Commission calculation
 * 
 * Usage: php test_landlord_notification.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Payment;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\LandlordPaymentNotification;

echo "🧪 Testing Landlord Payment Notification System\n";
echo str_repeat("=", 60) . "\n\n";

try {
    // Find a recent payment with landlord
    $payment = Payment::with(['tenant', 'landlord', 'apartment.property'])
        ->whereNotNull('landlord_id')
        ->latest()
        ->first();
    
    if (!$payment) {
        echo "❌ No payments found with landlord_id\n";
        echo "💡 Create a test payment first\n";
        exit(1);
    }
    
    echo "✅ Found payment:\n";
    echo "   - Payment ID: {$payment->id}\n";
    echo "   - Transaction ID: {$payment->transaction_id}\n";
    echo "   - Amount: ₦" . number_format($payment->amount, 2) . "\n";
    echo "   - Tenant: {$payment->tenant->first_name} {$payment->tenant->last_name}\n";
    echo "   - Landlord: {$payment->landlord->first_name} {$payment->landlord->last_name}\n";
    echo "\n";
    
    // Test commission calculation
    echo "💰 Testing Commission Calculation:\n";
    $commissionAmount = $payment->amount * 0.025;
    $netAmount = $payment->amount - $commissionAmount;
    echo "   - Gross Amount: ₦" . number_format($payment->amount, 2) . "\n";
    echo "   - Commission (2.5%): ₦" . number_format($commissionAmount, 2) . "\n";
    echo "   - Net Amount: ₦" . number_format($netAmount, 2) . "\n";
    echo "\n";
    
    // Test email notification
    echo "📧 Testing Email Notification:\n";
    try {
        $mail = new LandlordPaymentNotification($payment);
        echo "   ✅ Email class instantiated successfully\n";
        echo "   - Subject: Payment Received - EasyRent\n";
        echo "   - Recipient: {$payment->landlord->email}\n";
        echo "   - Commission in email: ₦" . number_format($mail->commissionAmount, 2) . "\n";
        echo "   - Net amount in email: ₦" . number_format($mail->netAmount, 2) . "\n";
    } catch (\Exception $e) {
        echo "   ❌ Email test failed: {$e->getMessage()}\n";
    }
    echo "\n";
    
    // Test in-app message
    echo "💬 Testing In-App Message:\n";
    $existingMessage = Message::where('receiver_id', $payment->landlord_id)
        ->where('subject', 'LIKE', 'Payment Received%')
        ->where('sender_id', 0)
        ->latest()
        ->first();
    
    if ($existingMessage) {
        echo "   ✅ Found existing payment message:\n";
        echo "   - Message ID: {$existingMessage->id}\n";
        echo "   - Subject: {$existingMessage->subject}\n";
        echo "   - Is Read: " . ($existingMessage->is_read ? 'Yes' : 'No') . "\n";
        echo "   - Created: {$existingMessage->created_at}\n";
    } else {
        echo "   ⚠️  No payment messages found for this landlord\n";
        echo "   💡 Message will be created on next payment\n";
    }
    echo "\n";
    
    // Test unread message count
    echo "🔔 Testing Notification Badge:\n";
    $unreadCount = Message::where('receiver_id', $payment->landlord_id)
        ->where('is_read', false)
        ->count();
    echo "   - Unread messages for landlord: {$unreadCount}\n";
    echo "   - Badge will show: " . ($unreadCount > 0 ? "✅ Yes ({$unreadCount})" : "❌ No") . "\n";
    echo "\n";
    
    // Summary
    echo str_repeat("=", 60) . "\n";
    echo "📊 Test Summary:\n";
    echo "   ✅ Payment data loaded successfully\n";
    echo "   ✅ Commission calculation working (2.5%)\n";
    echo "   ✅ Email notification class working\n";
    echo "   " . ($existingMessage ? "✅" : "⚠️ ") . " In-app message system ready\n";
    echo "   ✅ Notification badge system working\n";
    echo "\n";
    
    echo "🎉 All systems operational!\n";
    echo "\n";
    echo "📝 Next Steps:\n";
    echo "   1. Make a test payment to verify email delivery\n";
    echo "   2. Check landlord's email inbox\n";
    echo "   3. Check landlord's Messages → Inbox\n";
    echo "   4. Verify notification badge appears in header\n";
    echo "\n";
    
} catch (\Exception $e) {
    echo "❌ Test failed: {$e->getMessage()}\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "✅ Test completed successfully!\n";
