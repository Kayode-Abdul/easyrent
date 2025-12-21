<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payment;
use App\Models\ProfomaReceipt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "🚨 CRITICAL: Diagnosing Payment Database Issue\n";
echo "==============================================\n\n";

try {
    // Check recent payment activity
    echo "📊 Recent Payment Activity Analysis:\n";
    echo str_repeat("-", 40) . "\n";
    
    // Check payments created in last 24 hours
    $recentPayments = Payment::where('created_at', '>=', now()->subDay())->get();
    echo "Payments created in last 24 hours: {$recentPayments->count()}\n";
    
    // Check payments created in last hour
    $veryRecentPayments = Payment::where('created_at', '>=', now()->subHour())->get();
    echo "Payments created in last hour: {$veryRecentPayments->count()}\n";
    
    // Check payments created in last 10 minutes
    $justNowPayments = Payment::where('created_at', '>=', now()->subMinutes(10))->get();
    echo "Payments created in last 10 minutes: {$justNowPayments->count()}\n\n";
    
    if ($justNowPayments->count() > 0) {
        echo "✅ Recent payments found - system appears to be working\n";
        foreach ($justNowPayments as $payment) {
            echo "  - Payment ID: {$payment->id}, Amount: ₦" . number_format($payment->amount, 2) . ", Status: {$payment->status}\n";
        }
    } else {
        echo "⚠️  No payments created in last 10 minutes\n";
    }
    
    echo "\n📋 Database Connection Test:\n";
    echo str_repeat("-", 30) . "\n";
    
    // Test database connection
    try {
        $dbTest = DB::select('SELECT COUNT(*) as count FROM payments');
        echo "✅ Database connection: OK\n";
        echo "Total payments in database: {$dbTest[0]->count}\n";
    } catch (Exception $e) {
        echo "❌ Database connection: FAILED\n";
        echo "Error: {$e->getMessage()}\n";
    }
    
    echo "\n🔍 Payment Callback System Check:\n";
    echo str_repeat("-", 35) . "\n";
    
    // Check if there are any recent proforma receipts
    $recentProformas = ProfomaReceipt::where('created_at', '>=', now()->subDay())->get();
    echo "Proforma receipts created in last 24 hours: {$recentProformas->count()}\n";
    
    if ($recentProformas->count() > 0) {
        echo "Recent proformas:\n";
        foreach ($recentProformas->take(5) as $proforma) {
            echo "  - Proforma ID: {$proforma->id}, Amount: ₦" . number_format($proforma->amount, 2) . ", Status: {$proforma->status}\n";
            
            // Check if this proforma has a corresponding payment
            $correspondingPayment = Payment::where('transaction_id', $proforma->transaction_id)->first();
            if ($correspondingPayment) {
                echo "    ✅ Has corresponding payment (ID: {$correspondingPayment->id})\n";
            } else {
                echo "    ❌ NO corresponding payment found\n";
            }
        }
    }
    
    echo "\n🔧 System Configuration Check:\n";
    echo str_repeat("-", 32) . "\n";
    
    // Check Paystack configuration
    $paystackPublicKey = config('paystack.publicKey');
    $paystackSecretKey = config('paystack.secretKey');
    
    echo "Paystack Public Key: " . (empty($paystackPublicKey) ? "❌ NOT SET" : "✅ SET") . "\n";
    echo "Paystack Secret Key: " . (empty($paystackSecretKey) ? "❌ NOT SET" : "✅ SET") . "\n";
    
    // Check payment URL configuration
    $paymentUrl = config('paystack.paymentUrl');
    echo "Paystack Payment URL: " . ($paymentUrl ?: "❌ NOT SET") . "\n";
    
    echo "\n📝 Recent Laravel Logs Check:\n";
    echo str_repeat("-", 28) . "\n";
    
    // Check for recent payment-related log entries
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $logContent = file_get_contents($logFile);
        $recentLogs = array_slice(explode("\n", $logContent), -50); // Last 50 lines
        
        $paymentLogs = array_filter($recentLogs, function($line) {
            return stripos($line, 'payment') !== false || 
                   stripos($line, 'paystack') !== false ||
                   stripos($line, 'callback') !== false;
        });
        
        if (count($paymentLogs) > 0) {
            echo "Recent payment-related log entries found:\n";
            foreach (array_slice($paymentLogs, -5) as $log) {
                echo "  " . trim($log) . "\n";
            }
        } else {
            echo "⚠️  No recent payment-related log entries found\n";
        }
    } else {
        echo "❌ Laravel log file not found\n";
    }
    
    echo "\n🧪 Test Payment Creation:\n";
    echo str_repeat("-", 25) . "\n";
    
    // Try to create a test payment to see if database writes work
    try {
        $testPayment = new Payment();
        $testPayment->transaction_id = 'TEST_' . time();
        $testPayment->amount = 1000;
        $testPayment->tenant_id = 1;
        $testPayment->landlord_id = 1;
        $testPayment->apartment_id = 1;
        $testPayment->status = 'pending';
        $testPayment->payment_method = 'card';
        $testPayment->duration = 1;
        
        $saved = $testPayment->save();
        
        if ($saved) {
            echo "✅ Test payment creation: SUCCESS\n";
            echo "Test payment ID: {$testPayment->id}\n";
            
            // Clean up test payment
            $testPayment->delete();
            echo "✅ Test payment cleanup: SUCCESS\n";
        } else {
            echo "❌ Test payment creation: FAILED (save returned false)\n";
        }
    } catch (Exception $e) {
        echo "❌ Test payment creation: FAILED\n";
        echo "Error: {$e->getMessage()}\n";
    }
    
    echo "\n🔍 Potential Issues Analysis:\n";
    echo str_repeat("-", 30) . "\n";
    
    $issues = [];
    
    // Check for common issues
    if ($justNowPayments->count() === 0) {
        $issues[] = "No recent payments created - callback system may not be working";
    }
    
    if (empty($paystackSecretKey)) {
        $issues[] = "Paystack secret key not configured";
    }
    
    if ($recentProformas->count() > 0) {
        $orphanedProformas = 0;
        foreach ($recentProformas as $proforma) {
            if (!Payment::where('transaction_id', $proforma->transaction_id)->exists()) {
                $orphanedProformas++;
            }
        }
        if ($orphanedProformas > 0) {
            $issues[] = "{$orphanedProformas} proforma receipts without corresponding payments";
        }
    }
    
    if (count($issues) > 0) {
        echo "🚨 ISSUES DETECTED:\n";
        foreach ($issues as $issue) {
            echo "  ❌ {$issue}\n";
        }
    } else {
        echo "✅ No obvious issues detected\n";
    }
    
    echo "\n💡 Recommended Actions:\n";
    echo str_repeat("-", 22) . "\n";
    
    if ($justNowPayments->count() === 0) {
        echo "1. Check if payment callback URL is accessible\n";
        echo "2. Verify Paystack webhook configuration\n";
        echo "3. Check server logs for callback errors\n";
        echo "4. Test payment flow end-to-end\n";
    }
    
    if (empty($paystackSecretKey)) {
        echo "5. Configure Paystack secret key in .env file\n";
    }
    
    echo "6. Monitor Laravel logs during payment attempts\n";
    echo "7. Check network connectivity to Paystack\n";
    
    echo "\n🔧 Quick Fix Commands:\n";
    echo str_repeat("-", 20) . "\n";
    echo "# Check Laravel logs:\n";
    echo "tail -f storage/logs/laravel.log | grep -i payment\n\n";
    echo "# Test callback URL:\n";
    echo "curl -X POST " . url('/payment/callback') . "\n\n";
    echo "# Check database permissions:\n";
    echo "php artisan migrate:status\n";

} catch (Exception $e) {
    echo "❌ CRITICAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🚨 URGENT: If payments are not being saved, this is a\n";
echo "   CRITICAL ISSUE that needs immediate attention!\n";
echo str_repeat("=", 50) . "\n";