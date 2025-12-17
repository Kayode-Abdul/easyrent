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
        Schema::table('apartments', function (Blueprint $table) {
            // Add pricing_type column with default 'total' for backward compatibility
            $table->enum('pricing_type', ['total', 'monthly'])->default('total')->after('amount');
            
            // Add price_configuration JSON column for complex pricing rules
            $table->json('price_configuration')->nullable()->after('pricing_type');
            
            // Add index for pricing_type for performance
            $table->index('pricing_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->dropIndex(['pricing_type']);
            $table->dropColumn(['pricing_type', 'price_configuration']);
        });
    }
};
