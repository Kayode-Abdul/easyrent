<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PropertyAndApartmentTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Seed property types
        $propertyTypes = [
            ['id' => 1, 'name' => 'Mansion', 'category' => 'residential', 'description' => 'Large residential property', 'is_active' => true],
            ['id' => 2, 'name' => 'Duplex', 'category' => 'residential', 'description' => 'Two-unit residential property', 'is_active' => true],
            ['id' => 3, 'name' => 'Flat', 'category' => 'residential', 'description' => 'Apartment building', 'is_active' => true],
            ['id' => 4, 'name' => 'Terrace', 'category' => 'residential', 'description' => 'Terraced house', 'is_active' => true],
            ['id' => 5, 'name' => 'Warehouse', 'category' => 'commercial', 'description' => 'Storage and distribution facility', 'is_active' => true],
            ['id' => 6, 'name' => 'Land', 'category' => 'land', 'description' => 'Undeveloped land', 'is_active' => true],
            ['id' => 7, 'name' => 'Farm', 'category' => 'land', 'description' => 'Agricultural land', 'is_active' => true],
            ['id' => 8, 'name' => 'Store', 'category' => 'commercial', 'description' => 'Retail store', 'is_active' => true],
            ['id' => 9, 'name' => 'Shop', 'category' => 'commercial', 'description' => 'Small retail shop', 'is_active' => true],
        ];

        foreach ($propertyTypes as $type) {
            DB::table('property_types')->updateOrInsert(
                ['id' => $type['id']],
                array_merge($type, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // Seed apartment types
        $apartmentTypes = [
            // Residential Units
            ['name' => 'Studio', 'category' => 'residential', 'description' => 'Single room apartment'],
            ['name' => '1 Bedroom', 'category' => 'residential', 'description' => 'One bedroom apartment'],
            ['name' => '2 Bedroom', 'category' => 'residential', 'description' => 'Two bedroom apartment'],
            ['name' => '3 Bedroom', 'category' => 'residential', 'description' => 'Three bedroom apartment'],
            ['name' => '4 Bedroom', 'category' => 'residential', 'description' => 'Four bedroom apartment'],
            ['name' => 'Penthouse', 'category' => 'residential', 'description' => 'Luxury top-floor apartment'],
            ['name' => 'Duplex Unit', 'category' => 'residential', 'description' => 'Two-level apartment unit'],
            
            // Commercial Units
            ['name' => 'Shop Unit', 'category' => 'commercial', 'description' => 'Small retail unit'],
            ['name' => 'Store Unit', 'category' => 'commercial', 'description' => 'Retail store unit'],
            ['name' => 'Office Unit', 'category' => 'commercial', 'description' => 'Office space unit'],
            ['name' => 'Restaurant Unit', 'category' => 'commercial', 'description' => 'Restaurant space unit'],
            ['name' => 'Warehouse Unit', 'category' => 'commercial', 'description' => 'Storage unit'],
            ['name' => 'Showroom', 'category' => 'commercial', 'description' => 'Display showroom unit'],
            
            // Other
            ['name' => 'Storage Unit', 'category' => 'other', 'description' => 'Storage space'],
            ['name' => 'Parking Space', 'category' => 'other', 'description' => 'Vehicle parking space'],
            ['name' => 'Other', 'category' => 'other', 'description' => 'Other type of unit'],
        ];

        foreach ($apartmentTypes as $type) {
            DB::table('apartment_types')->updateOrInsert(
                ['name' => $type['name']],
                array_merge($type, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('Property and Apartment types seeded successfully!');
    }
}
