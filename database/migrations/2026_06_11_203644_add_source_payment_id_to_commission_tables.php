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
        Schema::table('referral_rewards', function (Blueprint $table) {
            $table->unsignedBigInteger('source_payment_id')->nullable()->after('referral_id');
            $table->foreign('source_payment_id')->references('id')->on('payments')->onDelete('set null');
        });

        Schema::table('commission_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('source_payment_id')->nullable()->after('marketer_id');
            $table->foreign('source_payment_id')->references('id')->on('payments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('referral_rewards', function (Blueprint $table) {
            $table->dropForeign(['source_payment_id']);
            $table->dropColumn('source_payment_id');
        });

        Schema::table('commission_payments', function (Blueprint $table) {
            $table->dropForeign(['source_payment_id']);
            $table->dropColumn('source_payment_id');
        });
    }
};
