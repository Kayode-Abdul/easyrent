<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create property types table
        Schema::create('property_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category'); // residential, commercial, land
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Create apartment types table
        Schema::create('apartment_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category'); // residential, commercial, other
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed property types
        DB::table('property_types')->insert([
            ['id' => 1, 'name' => 'Mansion', 'category' => 'residential', 'description' => 'Large residential property', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Duplex', 'category' => 'residential', 'description' => 'Two-unit residential property', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Flat', 'category' => 'residential', 'description' => 'Apartment building', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'Terrace', 'category' => 'residential', 'description' => 'Terraced house', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'name' => 'Warehouse', 'category' => 'commercial', 'description' => 'Storage and distribution facility', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'name' => 'Land', 'category' => 'land', 'description' => 'Undeveloped land', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'name' => 'Farm', 'category' => 'land', 'description' => 'Agricultural land', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'name' => 'Store', 'category' => 'commercial', 'description' => 'Retail store', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'name' => 'Shop', 'category' => 'commercial', 'description' => 'Small retail shop', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed apartment types
        DB::table('apartment_types')->insert([
            // Residential Units
            ['name' => 'Studio', 'category' => 'residential', 'description' => 'Single room apartment', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '1 Bedroom', 'category' => 'residential', 'description' => 'One bedroom apartment', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '2 Bedroom', 'category' => 'residential', 'description' => 'Two bedroom apartment', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '3 Bedroom', 'category' => 'residential', 'description' => 'Three bedroom apartment', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '4 Bedroom', 'category' => 'residential', 'description' => 'Four bedroom apartment', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Penthouse', 'category' => 'residential', 'description' => 'Luxury top-floor apartment', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Duplex Unit', 'category' => 'residential', 'description' => 'Two-level apartment unit', 'created_at' => now(), 'updated_at' => now()],
            
            // Commercial Units
            ['name' => 'Shop Unit', 'category' => 'commercial', 'description' => 'Small retail unit', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Store Unit', 'category' => 'commercial', 'description' => 'Retail store unit', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Office Unit', 'category' => 'commercial', 'description' => 'Office space unit', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Restaurant Unit', 'category' => 'commercial', 'description' => 'Restaurant space unit', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Warehouse Unit', 'category' => 'commercial', 'description' => 'Storage unit', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Showroom', 'category' => 'commercial', 'description' => 'Display showroom unit', 'created_at' => now(), 'updated_at' => now()],
            
            // Other
            ['name' => 'Storage Unit', 'category' => 'other', 'description' => 'Storage space', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Parking Space', 'category' => 'other', 'description' => 'Vehicle parking space', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Other', 'category' => 'other', 'description' => 'Other type of unit', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartment_types');
        Schema::dropIfExists('property_types');
    }
};