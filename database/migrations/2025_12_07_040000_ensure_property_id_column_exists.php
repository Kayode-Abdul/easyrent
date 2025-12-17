<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration ensures that prop_id is renamed to property_id
     * as per the standardization effort documented in PROP_ID_TO_PROPERTY_ID_MIGRATION.md
     */
    public function up(): void
    {
        // Check current column state
        $columns = Schema::getColumnListing('properties');
        
        if (in_array('prop_id', $columns) && !in_array('property_id', $columns)) {
            // The original column exists, rename it
            echo "Renaming prop_id to property_id in properties table...\n";
            
            Schema::table('properties', function (Blueprint $table) {
                $table->renameColumn('prop_id', 'property_id');
            });
            
            echo "✓ Successfully renamed prop_id to property_id\n";
            
        } elseif (in_array('property_id', $columns)) {
            // Already renamed
            echo "✓ property_id column already exists (migration already applied)\n";
            
        } elseif (!in_array('prop_id', $columns) && !in_array('property_id', $columns)) {
            // Neither exists - this shouldn't happen but handle it
            echo "⚠ Warning: Neither prop_id nor property_id exists. Adding property_id...\n";
            
            Schema::table('properties', function (Blueprint $table) {
                $table->unsignedBigInteger('property_id')->unique()->after('user_id');
            });
            
            echo "✓ Added property_id column\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columns = Schema::getColumnListing('properties');
        
        if (in_array('property_id', $columns) && !in_array('prop_id', $columns)) {
            Schema::table('properties', function (Blueprint $table) {
                $table->renameColumn('property_id', 'prop_id');
            });
            
            echo "Reverted property_id back to prop_id\n";
        }
    }
};
