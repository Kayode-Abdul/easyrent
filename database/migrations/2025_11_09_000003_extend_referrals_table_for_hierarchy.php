<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            // Add hierarchy tracking fields
            $table->tinyInteger('referral_level')->default(1)->after('referred_id');
            $table->unsignedBigInteger('parent_referral_id')->nullable()->after('referral_level');
            $table->enum('commission_tier', ['super_marketer', 'marketer', 'direct'])->default('direct')->after('parent_referral_id');
            $table->json('regional_rate_snapshot')->nullable()->after('commission_tier');
            $table->string('referral_code', 50)->nullable()->after('regional_rate_snapshot');
            $table->enum('referral_status', ['pending', 'active', 'completed', 'cancelled'])->default('pending')->after('referral_code');
            
            // Add indexes for performance
            $table->index('referral_level', 'idx_referral_level');
            $table->index('parent_referral_id', 'idx_parent_referral');
            $table->index('commission_tier', 'idx_commission_tier');
            $table->index('referral_status', 'idx_referral_status');
            $table->index('referral_code', 'idx_referral_code');
            
            // Foreign key for parent referral
            $table->foreign('parent_referral_id')->references('id')->on('referrals')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['parent_referral_id']);
            
            // Drop indexes
            $table->dropIndex('idx_referral_level');
            $table->dropIndex('idx_parent_referral');
            $table->dropIndex('idx_commission_tier');
            $table->dropIndex('idx_referral_status');
            $table->dropIndex('idx_referral_code');
            
            // Drop columns
            $table->dropColumn([
                'referral_level',
                'parent_referral_id',
                'commission_tier',
                'regional_rate_snapshot',
                'referral_code',
                'referral_status'
            ]);
        });
    }
};