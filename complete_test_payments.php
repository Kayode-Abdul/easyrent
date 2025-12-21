<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payment;

echo "🔄 Completing Test Payments\n";
echo "===========================\n\n";

try {
    // Update all pending payments to completed for testing
    $pendingPayments = Payment::where('status', 'pending')->get();
    
    echo "Found {$pendingPayments->count()} pending payments\n\n";
    
    foreach ($pendingPayments as $payment) {
        echo "Completing Payment ID: {$payment->id}\n";
        echo "Amount: ₦" . number_format($payment->amount, 2) . "\n";
        
        $payment->status = 'completed';
        $payment->paid_at = now();
        $payment->save();
        
        echo "✅ Status updated to completed\n";
        echo str_repeat("-", 30) . "\n";
    }
    
    echo "\n🎯 Summary:\n";
    echo "Updated {$pendingPayments->count()} payments to completed status\n";
    echo "These should now appear in the billing page\n";
    
    // Show current payment status distribution
    echo "\nCurrent payment statuses:\n";
    $statuses = Payment::select('status')->distinct()->get();
    foreach ($statuses as $status) {
        $count = Payment::where('status', $status->status)->count();
        echo "- {$status->status}: {$count} payments\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n✨ Done! Check your billing page now.\n";