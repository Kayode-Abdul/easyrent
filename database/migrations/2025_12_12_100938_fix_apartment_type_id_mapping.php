<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create mapping between old apartment_type names and new apartment_type_id
        $typeMapping = [
            '1-Bedroom' => '1 Bedroom',
            '2-Bedroom' => '2 Bedroom', 
            '3-Bedroom' => '3 Bedroom',
            '4-Bedroom' => '4 Bedroom',
            '5-Bedroom' => '4 Bedroom', // Map to closest available
            'Studio' => 'Studio',
            'Penthouse' => 'Penthouse',
            'Duplex' => 'Duplex Unit',
            'Shop' => 'Shop Unit',
            'Store' => 'Store Unit',
            'Office' => 'Office Unit',
            'Restaurant' => 'Restaurant Unit',
            'Warehouse' => 'Warehouse Unit',
            'Showroom' => 'Showroom',
            'Storage' => 'Storage Unit',
            'Parking' => 'Parking Space',
        ];

        // Get all apartments with apartment_type but no apartment_type_id
        $apartments = DB::table('apartments')
            ->whereNotNull('apartment_type')
            ->whereNull('apartment_type_id')
            ->get();

        foreach ($apartments as $apartment) {
            $oldType = $apartment->apartment_type;
            
            // Try direct mapping first
            $newTypeName = $typeMapping[$oldType] ?? null;
            
            // If no direct mapping, try to find a close match
            if (!$newTypeName) {
                // Remove hyphens and try again
                $normalizedType = str_replace('-', ' ', $oldType);
                $newTypeName = $normalizedType;
            }
            
            // Find the apartment type ID
            $apartmentType = DB::table('apartment_types')
                ->where('name', $newTypeName)
                ->first();
            
            if ($apartmentType) {
                DB::table('apartments')
                    ->where('id', $apartment->id)
                    ->update(['apartment_type_id' => $apartmentType->id]);
                    
                echo "Mapped apartment {$apartment->id}: '{$oldType}' -> '{$newTypeName}' (ID: {$apartmentType->id})\n";
            } else {
                // If still not found, set to "Other"
                $otherType = DB::table('apartment_types')
                    ->where('name', 'Other')
                    ->first();
                
                if ($otherType) {
                    DB::table('apartments')
                        ->where('id', $apartment->id)
                        ->update(['apartment_type_id' => $otherType->id]);
                        
                    echo "Apartment {$apartment->id}: '{$oldType}' -> 'Other' (no match found)\n";
                }
            }
        }
        
        echo "Apartment type ID mapping completed.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset apartment_type_id to null for apartments that were updated
        DB::table('apartments')->update(['apartment_type_id' => null]);
    }
};