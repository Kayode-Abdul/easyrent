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
            $table->timestamp('processing_started_at')->nullable();
            $table->decimal('processing_time_minutes', 8, 2)->nullable();
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
            $table->dropColumn(['processing_started_at', 'processing_time_minutes']);
        });
    }
};
