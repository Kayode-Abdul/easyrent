<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Apartment;

echo "=== Testing Apartment Update ===\n\n";

$apartmentId = 1599327;

// Get the apartment
$apartment = Apartment::where('apartment_id', $apartmentId)->first();

if (!$apartment) {
    echo "Apartment not found!\n";
    exit(1);
}

echo "Before Update:\n";
echo "Apartment ID: {$apartment->apartment_id}\n";
echo "Amount: ₦" . number_format($apartment->amount, 2) . "\n\n";

// Try to update
echo "Attempting to update amount to 50000...\n";
$apartment->amount = 50000;
$saved = $apartment->save();

echo "Save result: " . ($saved ? "SUCCESS" : "FAILED") . "\n\n";

// Refresh from database
$apartment->refresh();

echo "After Update:\n";
echo "Apartment ID: {$apartment->apartment_id}\n";
echo "Amount: ₦" . number_format($apartment->amount, 2) . "\n";

echo "\n=== Test Complete ===\n";
