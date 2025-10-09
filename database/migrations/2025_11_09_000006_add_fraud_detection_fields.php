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
        // Add fraud detection fields to users table (only if they don't exist)
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'flagged_for_review')) {
                $table->boolean('flagged_for_review')->default(false)->after('region');
            }
            if (!Schema::hasColumn('users', 'flag_reasons')) {
                $table->json('flag_reasons')->nullable()->after('flagged_for_review');
            }
            if (!Schema::hasColumn('users', 'flagged_at')) {
                $table->timestamp('flagged_at')->nullable()->after('flag_reasons');
            }
            if (!Schema::hasColumn('users', 'fraud_risk_score')) {
                $table->integer('fraud_risk_score')->default(0)->after('flagged_at');
            }
            if (!Schema::hasColumn('users', 'last_fraud_check')) {
                $table->timestamp('last_fraud_check')->nullable()->after('fraud_risk_score');
            }
        });
        
        // Add indexes if they don't exist
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->index(['flagged_for_review']);
                $table->index(['fraud_risk_score']);
            });
        } catch (\Exception $e) {
            // Indexes might already exist
        }
        
        // Add fraud detection fields to referrals table
        Schema::table('referrals', function (Blueprint $table) {
            if (!Schema::hasColumn('referrals', 'is_flagged')) {
                $table->boolean('is_flagged')->default(false)->after('regional_rate_snapshot');
            }
            if (!Schema::hasColumn('referrals', 'fraud_indicators')) {
                $table->json('fraud_indicators')->nullable()->after('is_flagged');
            }
            if (!Schema::hasColumn('referrals', 'fraud_checked_at')) {
                $table->timestamp('fraud_checked_at')->nullable()->after('fraud_indicators');
            }
            if (!Schema::hasColumn('referrals', 'authenticity_verified')) {
                $table->boolean('authenticity_verified')->default(false)->after('fraud_checked_at');
            }
        });
        
        // Add indexes if they don't exist
        try {
            Schema::table('referrals', function (Blueprint $table) {
                $table->index(['is_flagged']);
                $table->index(['authenticity_verified']);
            });
        } catch (\Exception $e) {
            // Indexes might already exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['flagged_for_review']);
            $table->dropIndex(['fraud_risk_score']);
            $table->dropColumn([
                'flagged_for_review',
                'flag_reasons',
                'flagged_at',
                'fraud_risk_score',
                'last_fraud_check'
            ]);
        });
        
        Schema::table('referrals', function (Blueprint $table) {
            $table->dropIndex(['is_flagged']);
            $table->dropIndex(['authenticity_verified']);
            $table->dropColumn([
                'is_flagged',
                'fraud_indicators',
                'fraud_checked_at',
                'authenticity_verified'
            ]);
        });
    }
};