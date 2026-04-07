<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Apartment;

echo "🏠 Apartment Pricing Type Manager\n";
echo "================================\n\n";

// Function to display apartment info
function displayApartmentInfo($apartment) {
    echo "Apartment ID: {$apartment->apartment_id}\n";
    echo "Property ID: {$apartment->property_id}\n";
    echo "Amount: ₦" . number_format($apartment->amount, 2) . "\n";
    echo "Current Pricing Type: {$apartment->pricing_type}\n";
    echo "Created: {$apartment->created_at}\n";
    echo "Updated: {$apartment->updated_at}\n";
    echo str_repeat("-", 50) . "\n";
}

// Get command line arguments
$action = $argv[1] ?? null;
$apartmentId = $argv[2] ?? null;
$newPricingType = $argv[3] ?? null;

if (!$action) {
    echo "Usage:\n";
    echo "  php update_apartment_pricing_type.php list                    # List all apartments\n";
    echo "  php update_apartment_pricing_type.php show <apartment_id>     # Show specific apartment\n";
    echo "  php update_apartment_pricing_type.php update <apartment_id> <pricing_type>\n";
    echo "\n";
    echo "Pricing Types:\n";
    echo "  total   - Fixed amount (no multiplication by months)\n";
    echo "  monthly - Per month amount (multiplies by duration)\n";
    echo "\n";
    echo "Examples:\n";
    echo "  php update_apartment_pricing_type.php list\n";
    echo "  php update_apartment_pricing_type.php show 123\n";
    echo "  php update_apartment_pricing_type.php update 123 total\n";
    echo "  php update_apartment_pricing_type.php update 123 monthly\n";
    exit(1);
}

try {
    switch ($action) {
        case 'list':
            echo "📋 All Apartments:\n\n";
            $apartments = Apartment::orderBy('apartment_id')->get();
            
            if ($apartments->isEmpty()) {
                echo "No apartments found.\n";
                break;
            }
            
            foreach ($apartments as $apartment) {
                displayApartmentInfo($apartment);
            }
            
            echo "\nTotal apartments: " . $apartments->count() . "\n";
            break;

        case 'show':
            if (!$apartmentId) {
                echo "❌ Error: Please provide apartment ID\n";
                echo "Usage: php update_apartment_pricing_type.php show <apartment_id>\n";
                exit(1);
            }
            
            $apartment = Apartment::where('apartment_id', $apartmentId)->first();
            
            if (!$apartment) {
                echo "❌ Error: Apartment with ID {$apartmentId} not found\n";
                exit(1);
            }
            
            echo "🏠 Apartment Details:\n\n";
            displayApartmentInfo($apartment);
            
            // Show recent payments for this apartment
            $payments = DB::table('payments')
                ->where('apartment_id', $apartmentId)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            
            if ($payments->count() > 0) {
                echo "\n💰 Recent Payments:\n";
                foreach ($payments as $payment) {
                    echo "  Payment ID: {$payment->id}\n";
                    echo "  Amount: ₦" . number_format($payment->amount, 2) . "\n";
                    echo "  Status: {$payment->status}\n";
                    echo "  Date: {$payment->created_at}\n";
                    echo "  ---\n";
                }
            }
            break;

        case 'update':
            if (!$apartmentId || !$newPricingType) {
                echo "❌ Error: Please provide apartment ID and pricing type\n";
                echo "Usage: php update_apartment_pricing_type.php update <apartment_id> <pricing_type>\n";
                echo "Pricing types: total, monthly\n";
                exit(1);
            }
            
            if (!in_array($newPricingType, ['total', 'monthly'])) {
                echo "❌ Error: Invalid pricing type '{$newPricingType}'\n";
                echo "Valid pricing types: total, monthly\n";
                exit(1);
            }
            
            $apartment = Apartment::where('apartment_id', $apartmentId)->first();
            
            if (!$apartment) {
                echo "❌ Error: Apartment with ID {$apartmentId} not found\n";
                exit(1);
            }
            
            echo "🏠 Current Apartment Details:\n\n";
            displayApartmentInfo($apartment);
            
            $oldPricingType = $apartment->pricing_type;
            
            if ($oldPricingType === $newPricingType) {
                echo "ℹ️  Apartment already has pricing type '{$newPricingType}'. No changes needed.\n";
                break;
            }
            
            // Confirm the change
            echo "\n⚠️  You are about to change:\n";
            echo "   Apartment ID: {$apartmentId}\n";
            echo "   From: {$oldPricingType}\n";
            echo "   To: {$newPricingType}\n\n";
            
            echo "This will affect how payment amounts are calculated:\n";
            if ($newPricingType === 'total') {
                echo "   - 'total': Amount will NOT be multiplied by duration\n";
                echo "   - Example: ₦500,000 for 12 months = ₦500,000 total\n";
            } else {
                echo "   - 'monthly': Amount WILL be multiplied by duration\n";
                echo "   - Example: ₦50,000 per month × 12 months = ₦600,000 total\n";
            }
            
            echo "\nType 'yes' to confirm: ";
            $handle = fopen("php://stdin", "r");
            $confirmation = trim(fgets($handle));
            fclose($handle);
            
            if (strtolower($confirmation) !== 'yes') {
                echo "❌ Update cancelled.\n";
                exit(0);
            }
            
            // Update the apartment
            $apartment->pricing_type = $newPricingType;
            $apartment->save();
            
            echo "\n✅ Successfully updated apartment pricing type!\n\n";
            echo "📋 Updated Apartment Details:\n\n";
            displayApartmentInfo($apartment->fresh());
            
            break;

        case 'bulk-update':
            echo "🔄 Bulk Update Mode\n\n";
            echo "Available options:\n";
            echo "1. Update all apartments to 'total' pricing\n";
            echo "2. Update all apartments to 'monthly' pricing\n";
            echo "3. Update apartments with amount > X to 'total'\n";
            echo "4. Update apartments with amount < X to 'monthly'\n";
            echo "\nSelect option (1-4): ";
            
            $handle = fopen("php://stdin", "r");
            $option = trim(fgets($handle));
            
            switch ($option) {
                case '1':
                    $count = Apartment::where('pricing_type', '!=', 'total')->update(['pricing_type' => 'total']);
                    echo "✅ Updated {$count} apartments to 'total' pricing.\n";
                    break;
                    
                case '2':
                    $count = Apartment::where('pricing_type', '!=', 'monthly')->update(['pricing_type' => 'monthly']);
                    echo "✅ Updated {$count} apartments to 'monthly' pricing.\n";
                    break;
                    
                case '3':
                    echo "Enter minimum amount (apartments with amount >= this will be set to 'total'): ";
                    $minAmount = trim(fgets($handle));
                    if (is_numeric($minAmount)) {
                        $count = Apartment::where('amount', '>=', $minAmount)->update(['pricing_type' => 'total']);
                        echo "✅ Updated {$count} apartments with amount >= ₦" . number_format($minAmount) . " to 'total' pricing.\n";
                    } else {
                        echo "❌ Invalid amount.\n";
                    }
                    break;
                    
                case '4':
                    echo "Enter maximum amount (apartments with amount <= this will be set to 'monthly'): ";
                    $maxAmount = trim(fgets($handle));
                    if (is_numeric($maxAmount)) {
                        $count = Apartment::where('amount', '<=', $maxAmount)->update(['pricing_type' => 'monthly']);
                        echo "✅ Updated {$count} apartments with amount <= ₦" . number_format($maxAmount) . " to 'monthly' pricing.\n";
                    } else {
                        echo "❌ Invalid amount.\n";
                    }
                    break;
                    
                default:
                    echo "❌ Invalid option.\n";
            }
            
            fclose($handle);
            break;

        default:
            echo "❌ Error: Unknown action '{$action}'\n";
            echo "Valid actions: list, show, update, bulk-update\n";
            exit(1);
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✨ Done!\n";