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
        Schema::table('commission_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('commission_payments', 'currency_id')) {
                $table->unsignedBigInteger('currency_id')->nullable()->after('payment_reference');
                $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('commission_payments', function (Blueprint $table) {
            if (Schema::hasColumn('commission_payments', 'currency_id')) {
                $table->dropForeign(['currency_id']);
                $table->dropColumn('currency_id');
            }
        });
    }
};
