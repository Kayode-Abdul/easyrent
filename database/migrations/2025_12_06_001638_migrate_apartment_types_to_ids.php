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
        // Check if column exists and modify it if needed
        if (Schema::hasColumn('apartments', 'apartment_type_id')) {
            // Drop existing column and recreate with correct type
            Schema::table('apartments', function (Blueprint $table) {
                $table->dropIndex(['apartment_type_id']);
                $table->dropColumn('apartment_type_id');
            });
        }
        
        // Create apartment_type_id column with correct type
        Schema::table('apartments', function (Blueprint $table) {
            $table->unsignedBigInteger('apartment_type_id')->nullable()->after('apartment_type');
            $table->index('apartment_type_id');
        });

        // Migrate existing apartment_type text values to apartment_type_id
        $apartments = DB::table('apartments')->whereNotNull('apartment_type')->get();
        
        foreach ($apartments as $apartment) {
            $apartmentType = DB::table('apartment_types')
                ->where('name', $apartment->apartment_type)
                ->first();
            
            if ($apartmentType) {
                DB::table('apartments')
                    ->where('id', $apartment->id)
                    ->update(['apartment_type_id' => $apartmentType->id]);
            } else {
                // If type not found, log it and set to "Other"
                $otherType = DB::table('apartment_types')
                    ->where('name', 'Other')
                    ->first();
                
                if ($otherType) {
                    DB::table('apartments')
                        ->where('id', $apartment->id)
                        ->update(['apartment_type_id' => $otherType->id]);
                    
                    \Log::warning("Apartment ID {$apartment->id} had unknown type '{$apartment->apartment_type}', set to 'Other'");
                }
            }
        }

        // Add foreign key constraint
        Schema::table('apartments', function (Blueprint $table) {
            $table->foreign('apartment_type_id')
                ->references('id')
                ->on('apartment_types')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->dropForeign(['apartment_type_id']);
            $table->dropColumn('apartment_type_id');
        });
    }
};
