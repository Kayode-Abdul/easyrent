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
        Schema::table('referrals', function (Blueprint $table) {
            // Enhanced tracking fields
            $table->decimal('commission_amount', 10, 2)->default(0.00)->after('referred_id');
            $table->enum('commission_status', ['pending', 'approved', 'paid', 'cancelled'])->default('pending')->after('commission_amount');
            $table->timestamp('conversion_date')->nullable()->after('commission_status');
            $table->string('campaign_id', 50)->nullable()->after('conversion_date');
            $table->enum('referral_source', ['link', 'qr_code', 'direct'])->default('link')->after('campaign_id');
            $table->string('ip_address', 45)->nullable()->after('referral_source');
            $table->string('user_agent')->nullable()->after('ip_address');
            $table->json('tracking_data')->nullable()->after('user_agent');
            
            // Indexes for performance
            $table->index('commission_status');
            $table->index('campaign_id');
            $table->index('referral_source');
            $table->index('conversion_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('referrals', function (Blueprint $table) {
            $table->dropIndex(['commission_status']);
            $table->dropIndex(['campaign_id']);
            $table->dropIndex(['referral_source']);
            $table->dropIndex(['conversion_date']);
            $table->dropColumn([
                'commission_amount',
                'commission_status',
                'conversion_date',
                'campaign_id',
                'referral_source',
                'ip_address',
                'user_agent',
                'tracking_data'
            ]);
        });
    }
};
