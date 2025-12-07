<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('apartment_invitations', function (Blueprint $table) {
            // First drop the existing foreign key constraint
            try {
                $table->dropForeign(['apartment_id']);
            } catch (Exception $e) {
                // Foreign key might not exist, continue
            }
        });
        
        Schema::table('apartment_invitations', function (Blueprint $table) {
            // Add the correct foreign key constraint
            $table->foreign('apartment_id')->references('apartment_id')->on('apartments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('apartment_invitations', function (Blueprint $table) {
            // Drop the corrected foreign key constraint
            $table->dropForeign(['apartment_id']);
        });
    }
};