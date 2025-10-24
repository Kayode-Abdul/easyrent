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
        Schema::table('users', function (Blueprint $table) {
            // Marketer-specific fields
            $table->enum('marketer_status', ['pending', 'active', 'suspended', 'inactive'])->nullable()->after('admin');
            $table->decimal('commission_rate', 5, 2)->nullable()->after('marketer_status');
            $table->string('bank_account_name')->nullable()->after('commission_rate');
            $table->string('bank_account_number', 50)->nullable()->after('bank_account_name');
            $table->string('bank_name')->nullable()->after('bank_account_number');
            $table->string('bvn', 11)->nullable()->after('bank_name');
            $table->string('referral_code', 20)->unique()->nullable()->after('bvn');
            
            // Indexes for performance
            $table->index(['role', 'marketer_status']);
            $table->index('referral_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'marketer_status']);
            $table->dropIndex(['referral_code']);
            $table->dropColumn([
                'marketer_status',
                'commission_rate',
                'bank_account_name',
                'bank_account_number',
                'bank_name',
                'bvn',
                'referral_code'
            ]);
        });
    }
};
