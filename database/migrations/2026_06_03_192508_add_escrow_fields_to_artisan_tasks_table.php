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
    public function up(): void
    {
        Schema::table('artisan_tasks', function (Blueprint $table) {
            $table->string('payment_status')->default('pending')->after('status');
            $table->string('payment_reference')->nullable()->after('payment_status');
            $table->decimal('platform_fee', 15, 2)->nullable()->after('payment_reference');
            $table->decimal('gateway_fee', 15, 2)->nullable()->after('platform_fee');
            $table->decimal('total_amount_paid', 15, 2)->nullable()->after('gateway_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artisan_tasks', function (Blueprint $table) {
            $table->dropColumn([
                'payment_status',
                'payment_reference',
                'platform_fee',
                'gateway_fee',
                'total_amount_paid'
            ]);
        });
    }
};
