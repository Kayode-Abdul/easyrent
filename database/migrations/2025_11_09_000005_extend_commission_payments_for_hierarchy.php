<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('commission_payments')) {
            // First add columns if they don't exist
            Schema::table('commission_payments', function (Blueprint $table) {
                if (!Schema::hasColumn('commission_payments', 'referral_chain_id')) {
                    $table->unsignedBigInteger('referral_chain_id')->nullable()->after('marketer_id');
                }
                
                if (!Schema::hasColumn('commission_payments', 'commission_tier')) {
                    $table->enum('commission_tier', ['super_marketer', 'marketer', 'regional_manager'])->after('referral_chain_id');
                }
                
                if (!Schema::hasColumn('commission_payments', 'parent_payment_id')) {
                    $table->unsignedBigInteger('parent_payment_id')->nullable()->after('commission_tier');
                }
                
                if (!Schema::hasColumn('commission_payments', 'regional_rate_applied')) {
                    $table->decimal('regional_rate_applied', 5, 4)->after('parent_payment_id');
                }
                
                if (!Schema::hasColumn('commission_payments', 'region')) {
                    $table->string('region', 100)->nullable()->after('regional_rate_applied');
                }
            });
            
            // Add indexes and foreign keys separately with try-catch blocks
            try {
                Schema::table('commission_payments', function (Blueprint $table) {
                    $table->index('commission_tier', 'idx_commission_tier');
                });
            } catch (\Exception $e) {
                // Index already exists, ignore
            }
            
            try {
                Schema::table('commission_payments', function (Blueprint $table) {
                    $table->index('referral_chain_id', 'idx_referral_chain');
                });
            } catch (\Exception $e) {
                // Index already exists, ignore
            }
            
            try {
                Schema::table('commission_payments', function (Blueprint $table) {
                    $table->index('parent_payment_id', 'idx_parent_payment');
                });
            } catch (\Exception $e) {
                // Index already exists, ignore
            }
            
            try {
                Schema::table('commission_payments', function (Blueprint $table) {
                    $table->index('region', 'idx_payment_region');
                });
            } catch (\Exception $e) {
                // Index already exists, ignore
            }
            
            try {
                Schema::table('commission_payments', function (Blueprint $table) {
                    $table->foreign('parent_payment_id')->references('id')->on('commission_payments')->onDelete('set null');
                });
            } catch (\Exception $e) {
                // Foreign key already exists, ignore
            }
            
            try {
                Schema::table('commission_payments', function (Blueprint $table) {
                    $table->foreign('referral_chain_id')->references('id')->on('referral_chains')->onDelete('set null');
                });
            } catch (\Exception $e) {
                // Foreign key already exists, ignore
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('commission_payments')) {
            // Try to drop foreign keys
            try {
                Schema::table('commission_payments', function (Blueprint $table) {
                    $table->dropForeign(['parent_payment_id']);
                });
            } catch (\Exception $e) {
                // Foreign key doesn't exist, ignore
            }
            
            try {
                Schema::table('commission_payments', function (Blueprint $table) {
                    $table->dropForeign(['referral_chain_id']);
                });
            } catch (\Exception $e) {
                // Foreign key doesn't exist, ignore
            }
            
            // Try to drop indexes
            try {
                Schema::table('commission_payments', function (Blueprint $table) {
                    $table->dropIndex('idx_commission_tier');
                });
            } catch (\Exception $e) {
                // Index doesn't exist, ignore
            }
            
            try {
                Schema::table('commission_payments', function (Blueprint $table) {
                    $table->dropIndex('idx_referral_chain');
                });
            } catch (\Exception $e) {
                // Index doesn't exist, ignore
            }
            
            try {
                Schema::table('commission_payments', function (Blueprint $table) {
                    $table->dropIndex('idx_parent_payment');
                });
            } catch (\Exception $e) {
                // Index doesn't exist, ignore
            }
            
            try {
                Schema::table('commission_payments', function (Blueprint $table) {
                    $table->dropIndex('idx_payment_region');
                });
            } catch (\Exception $e) {
                // Index doesn't exist, ignore
            }
            
            // Drop columns
            Schema::table('commission_payments', function (Blueprint $table) {
                $table->dropColumn([
                    'referral_chain_id',
                    'commission_tier',
                    'parent_payment_id',
                    'regional_rate_applied',
                    'region'
                ]);
            });
        }
    }
};