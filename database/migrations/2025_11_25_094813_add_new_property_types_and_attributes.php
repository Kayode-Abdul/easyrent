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
        // Create property_attributes table for flexible attributes
        // Note: We're not adding a foreign key constraint because prop_id might not be properly indexed
        // The application will handle referential integrity
        Schema::create('property_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id')->comment('References prop_id in properties table');
            $table->string('attribute_key', 100);
            $table->text('attribute_value')->nullable();
            $table->timestamps();

            $table->index(['property_id', 'attribute_key']);
        });

        // Add size-related columns to properties table
        Schema::table('properties', function (Blueprint $table) {
            if (!Schema::hasColumn('properties', 'size_value')) {
                $table->decimal('size_value', 10, 2)->nullable()->after('no_of_apartment')
                    ->comment('Size in square meters, acres, etc.');
            }
            if (!Schema::hasColumn('properties', 'size_unit')) {
                $table->string('size_unit', 20)->nullable()->after('size_value')
                    ->comment('sqm, sqft, acres, hectares');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_attributes');
        
        Schema::table('properties', function (Blueprint $table) {
            if (Schema::hasColumn('properties', 'size_value')) {
                $table->dropColumn('size_value');
            }
            if (Schema::hasColumn('properties', 'size_unit')) {
                $table->dropColumn('size_unit');
            }
        });
    }
};
