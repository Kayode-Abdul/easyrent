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
        // Add proforma_id to payment_invitations
        Schema::table('payment_invitations', function (Blueprint $table) {
            $table->unsignedBigInteger('proforma_id')->nullable()->after('benefactor_id');
            $table->foreign('proforma_id')->references('id')->on('profoma_receipt')->onDelete('cascade');
            $table->index('proforma_id');
        });

        // Add proforma_id to benefactor_payments
        Schema::table('benefactor_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('proforma_id')->nullable()->after('apartment_id');
            $table->foreign('proforma_id')->references('id')->on('profoma_receipt')->onDelete('set null');
            $table->index('proforma_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_invitations', function (Blueprint $table) {
            $table->dropForeign(['proforma_id']);
            $table->dropColumn('proforma_id');
        });

        Schema::table('benefactor_payments', function (Blueprint $table) {
            $table->dropForeign(['proforma_id']);
            $table->dropColumn('proforma_id');
        });
    }
};
