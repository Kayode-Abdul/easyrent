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
        Schema::table('profoma_receipt', function (Blueprint $table) {
            // Add calculation_method column to track which calculation method was used
            $table->string('calculation_method')->nullable()->after('apartment_id');
            
            // Add calculation_log JSON column to store detailed calculation steps for audit purposes
            $table->json('calculation_log')->nullable()->after('calculation_method');
            
            // Add index for calculation_method for performance when querying by calculation type
            $table->index('calculation_method');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('profoma_receipt', function (Blueprint $table) {
            $table->dropIndex(['calculation_method']);
            $table->dropColumn(['calculation_method', 'calculation_log']);
        });
    }
};
