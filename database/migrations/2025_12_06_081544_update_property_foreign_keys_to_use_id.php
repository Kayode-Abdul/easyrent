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
     * This migration updates all property foreign key references from prop_id to id.
     * The properties table has both 'id' (auto-increment primary key) and 'prop_id' (legacy).
     * We're standardizing on 'id' as the primary key for all relationships.
     *
     * @return void
     */
    public function up()
    {
        // Step 1: Update apartments.property_id to match properties.id instead of properties.prop_id
        DB::statement('
            UPDATE apartments a
            INNER JOIN properties p ON a.property_id = p.prop_id
            SET a.property_id = p.id
        ');
        
        // Step 2: Update property_attributes.property_id to match properties.id instead of properties.prop_id
        if (Schema::hasTable('property_attributes')) {
            DB::statement('
                UPDATE property_attributes pa
                INNER JOIN properties p ON pa.property_id = p.prop_id
                SET pa.property_id = p.id
            ');
        }
        
        // Step 3: Drop existing foreign keys if they exist
        Schema::table('apartments', function (Blueprint $table) {
            // Check and drop foreign key if exists
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'apartments' 
                AND COLUMN_NAME = 'property_id' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            foreach ($foreignKeys as $fk) {
                $table->dropForeign($fk->CONSTRAINT_NAME);
            }
        });
        
        if (Schema::hasTable('property_attributes')) {
            Schema::table('property_attributes', function (Blueprint $table) {
                // Check and drop foreign key if exists
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'property_attributes' 
                    AND COLUMN_NAME = 'property_id' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                foreach ($foreignKeys as $fk) {
                    $table->dropForeign($fk->CONSTRAINT_NAME);
                }
            });
        }
        
        // Step 4: Add new foreign keys referencing properties.id
        Schema::table('apartments', function (Blueprint $table) {
            $table->foreign('property_id')
                  ->references('id')
                  ->on('properties')
                  ->onDelete('cascade');
        });
        
        if (Schema::hasTable('property_attributes')) {
            Schema::table('property_attributes', function (Blueprint $table) {
                $table->foreign('property_id')
                      ->references('id')
                      ->on('properties')
                      ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Step 1: Drop new foreign keys
        Schema::table('apartments', function (Blueprint $table) {
            $table->dropForeign(['property_id']);
        });
        
        if (Schema::hasTable('property_attributes')) {
            Schema::table('property_attributes', function (Blueprint $table) {
                $table->dropForeign(['property_id']);
            });
        }
        
        // Step 2: Revert apartments.property_id back to prop_id values
        DB::statement('
            UPDATE apartments a
            INNER JOIN properties p ON a.property_id = p.id
            SET a.property_id = p.prop_id
        ');
        
        // Step 3: Revert property_attributes.property_id back to prop_id values
        if (Schema::hasTable('property_attributes')) {
            DB::statement('
                UPDATE property_attributes pa
                INNER JOIN properties p ON pa.property_id = p.id
                SET pa.property_id = p.prop_id
            ');
        }
    }
};
