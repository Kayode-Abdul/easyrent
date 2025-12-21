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
     * @return void
     */
    public function up()
    {
        // Change payment_method from ENUM to VARCHAR to support all Paystack channel values
        // Paystack returns: card, bank, ussd, qr, mobile_money, bank_transfer, etc.
        DB::statement("ALTER TABLE `payments` MODIFY COLUMN `payment_method` VARCHAR(50) NULL DEFAULT 'card'");
        
        // Update existing enum values to match common patterns
        DB::table('payments')->where('payment_method', 'bank_transfer')->update(['payment_method' => 'bank_transfer']);
        DB::table('payments')->where('payment_method', 'ussd')->update(['payment_method' => 'ussd']);
        DB::table('payments')->where('payment_method', 'card')->update(['payment_method' => 'card']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert back to ENUM (note: this may cause data loss if new values were added)
        DB::statement("ALTER TABLE `payments` MODIFY COLUMN `payment_method` ENUM('card','bank_transfer','ussd') NULL DEFAULT 'card'");
    }
};
