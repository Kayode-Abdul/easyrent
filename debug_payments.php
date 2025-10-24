<?php
/**
 * Debug script to check payment records
 * Run this to see what's in the payments table
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payment;
use App\Models\User;

echo "=== Payment Debug Script ===\n\n";

// Check total payments
$totalPayments = Payment::count();
echo "Total payments in database: $totalPayments\n\n";

// Check payments by status
$statusCounts = Payment::select('status', \DB::raw('count(*) as count'))
    ->groupBy('status')
    ->get();

echo "Payments by status:\n";
foreach ($statusCounts as $status) {
    echo "  {$status->status}: {$status->count}\n";
}
echo "\n";

// Check recent payments
echo "Recent 5 payments:\n";
$recentPayments = Payment::orderBy('created_at', 'desc')->limit(5)->get();
foreach ($recentPayments as $payment) {
    echo "  ID: {$payment->id}, Status: {$payment->status}, Amount: {$payment->amount}, Tenant ID: {$payment->tenant_id}, Created: {$payment->created_at}\n";
}
echo "\n";

// Check if there are any users
$totalUsers = User::count();
echo "Total users in database: $totalUsers\n\n";

// Check a specific user's payments (if any users exist)
if ($totalUsers > 0) {
    $firstUser = User::first();
    echo "Checking payments for first user (ID: {$firstUser->user_id}):\n";
    
    $userPayments = Payment::where('tenant_id', $firstUser->user_id)->get();
    echo "  Total payments for this user: " . $userPayments->count() . "\n";
    
    $successfulPayments = Payment::where('tenant_id', $firstUser->user_id)
        ->whereIn('status', ['success', 'completed'])
        ->get();
    echo "  Successful payments for this user: " . $successfulPayments->count() . "\n";
    
    foreach ($userPayments as $payment) {
        echo "    Payment ID: {$payment->id}, Status: {$payment->status}, Amount: {$payment->amount}\n";
    }
}

echo "\n=== Debug Complete ===\n";
?>