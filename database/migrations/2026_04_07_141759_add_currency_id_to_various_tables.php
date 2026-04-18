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
        if (!Schema::hasColumn('properties', 'currency_id')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->unsignedBigInteger('currency_id')->nullable()->after('property_id');
                $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
            });
        }

        if (!Schema::hasColumn('apartments', 'currency_id')) {
            Schema::table('apartments', function (Blueprint $table) {
                $table->unsignedBigInteger('currency_id')->nullable()->after('amount');
                $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
            });
        }

        if (!Schema::hasColumn('payments', 'currency_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->unsignedBigInteger('currency_id')->nullable()->after('amount');
                $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
            });
        }

        if (!Schema::hasColumn('profoma_receipt', 'currency_id')) {
            Schema::table('profoma_receipt', function (Blueprint $table) {
                $table->unsignedBigInteger('currency_id')->nullable()->after('amount');
                $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('profoma_receipt', 'currency_id')) {
            Schema::table('profoma_receipt', function (Blueprint $table) {
                $table->dropForeign(['currency_id']);
                $table->dropColumn('currency_id');
            });
        }

        if (Schema::hasColumn('payments', 'currency_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropForeign(['currency_id']);
                $table->dropColumn('currency_id');
            });
        }

        if (Schema::hasColumn('apartments', 'currency_id')) {
            Schema::table('apartments', function (Blueprint $table) {
                $table->dropForeign(['currency_id']);
                $table->dropColumn('currency_id');
            });
        }

        if (Schema::hasColumn('properties', 'currency_id')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->dropForeign(['currency_id']);
                $table->dropColumn('currency_id');
            });
        }
    }
};
