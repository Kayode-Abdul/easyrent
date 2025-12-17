<?php

require_once 'vendor/autoload.php';

use App\Models\ApartmentInvitation;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fixing Existing Invitations ===\n\n";

// Get all invitations with NULL lease_duration or total_amount
$invitations = ApartmentInvitation::with(['apartment'])
    ->where(function($query) {
        $query->whereNull('lease_duration')
              ->orWhereNull('total_amount');
    })
    ->get();

echo "Found {$invitations->count()} invitations to fix.\n\n";

$fixed = 0;
$errors = 0;

foreach ($invitations as $invitation) {
    try {
        if (!$invitation->apartment) {
            echo "❌ Invitation {$invitation->id}: No apartment found\n";
            $errors++;
            continue;
        }

        $defaultDuration = 12; // Default to 12 months
        $defaultMoveInDate = now()->addDays(7)->format('Y-m-d'); // Default to 7 days from now
        $totalAmount = $invitation->apartment->amount * $defaultDuration;

        $updates = [];
        
        if (is_null($invitation->lease_duration)) {
            $updates['lease_duration'] = $defaultDuration;
        }
        
        if (is_null($invitation->total_amount)) {
            $updates['total_amount'] = $totalAmount;
        }
        
        if (is_null($invitation->move_in_date)) {
            $updates['move_in_date'] = $defaultMoveInDate;
        }

        if (!empty($updates)) {
            $invitation->update($updates);
            echo "✅ Fixed Invitation {$invitation->id}: Duration={$defaultDuration}, Amount=₦" . number_format($totalAmount) . "\n";
            $fixed++;
        } else {
            echo "ℹ️  Invitation {$invitation->id}: Already has values\n";
        }

    } catch (Exception $e) {
        echo "❌ Error fixing Invitation {$invitation->id}: " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "\n=== Summary ===\n";
echo "Fixed: {$fixed} invitations\n";
echo "Errors: {$errors} invitations\n";
echo "Total processed: " . ($fixed + $errors) . "\n";

// Verify the fixes
echo "\n=== Verification ===\n";
$nullDurationCount = ApartmentInvitation::whereNull('lease_duration')->count();
$nullAmountCount = ApartmentInvitation::whereNull('total_amount')->count();

echo "Remaining invitations with NULL lease_duration: {$nullDurationCount}\n";
echo "Remaining invitations with NULL total_amount: {$nullAmountCount}\n";

if ($nullDurationCount == 0 && $nullAmountCount == 0) {
    echo "🎉 All invitations have been fixed!\n";
} else {
    echo "⚠️  Some invitations still need attention.\n";
}

echo "\n=== Fix Complete ===\n";