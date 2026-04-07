<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Apartment;

echo "=== Apartment Amount Update Test ===\n\n";

// Get all apartments with their amounts
$apartments = Apartment::select('apartment_id', 'amount', 'apartment_type', 'updated_at')
    ->orderBy('apartment_id')
    ->get();

echo "Total apartments: " . $apartments->count() . "\n\n";

foreach ($apartments as $apt) {
    echo "Apartment ID: {$apt->apartment_id}\n";
    echo "Type: {$apt->apartment_type}\n";
    echo "Amount: ₦" . number_format($apt->amount, 2) . "\n";
    echo "Last Updated: {$apt->updated_at}\n";
    echo "---\n";
}

echo "\n=== Test Complete ===\n";
