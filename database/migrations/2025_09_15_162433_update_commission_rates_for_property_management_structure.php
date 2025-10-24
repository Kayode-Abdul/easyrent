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
        if (Schema::hasTable('commission_rates')) {
            Schema::table('commission_rates', function (Blueprint $table) {
                // Check if columns don't exist before adding them
                if (!Schema::hasColumn('commission_rates', 'property_management_status')) {
                    // Add property management status field
                    $table->enum('property_management_status', ['managed', 'unmanaged'])->after('region')->default('unmanaged');
                }
                
                if (!Schema::hasColumn('commission_rates', 'hierarchy_status')) {
                    // Add hierarchy status field
                    $table->enum('hierarchy_status', ['with_super_marketer', 'without_super_marketer'])->after('property_management_status')->default('without_super_marketer');
                }
                
                // Add specific role rates if they don't exist
                if (!Schema::hasColumn('commission_rates', 'super_marketer_rate')) {
                    $table->decimal('super_marketer_rate', 5, 3)->nullable()->after('hierarchy_status');
                }
                
                if (!Schema::hasColumn('commission_rates', 'marketer_rate')) {
                    $table->decimal('marketer_rate', 5, 3)->nullable()->after('super_marketer_rate');
                }
                
                if (!Schema::hasColumn('commission_rates', 'regional_manager_rate')) {
                    $table->decimal('regional_manager_rate', 5, 3)->nullable()->after('marketer_rate');
                }
                
                if (!Schema::hasColumn('commission_rates', 'company_rate')) {
                    $table->decimal('company_rate', 5, 3)->nullable()->after('regional_manager_rate');
                }
                
                if (!Schema::hasColumn('commission_rates', 'total_commission_rate')) {
                    $table->decimal('total_commission_rate', 5, 3)->after('company_rate');
                }
                
                if (!Schema::hasColumn('commission_rates', 'description')) {
                    // Add description field
                    $table->string('description')->nullable()->after('total_commission_rate');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('commission_rates')) {
            Schema::table('commission_rates', function (Blueprint $table) {
                $table->dropColumn([
                    'property_management_status',
                    'hierarchy_status',
                    'super_marketer_rate',
                    'marketer_rate',
                    'regional_manager_rate',
                    'company_rate',
                    'total_commission_rate',
                    'description'
                ]);
            });
        }
    }
};