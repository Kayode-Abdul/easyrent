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
        Schema::table('commission_rates', function (Blueprint $table) {
            // Add property management status field
            if (!Schema::hasColumn('commission_rates', 'property_management_status')) {
                $table->enum('property_management_status', ['managed', 'unmanaged'])
                    ->after('commission_percentage')
                    ->default('unmanaged');
            }
            
            // Add hierarchy status field
            if (!Schema::hasColumn('commission_rates', 'hierarchy_status')) {
                $table->enum('hierarchy_status', ['with_super_marketer', 'without_super_marketer'])
                    ->after('property_management_status')
                    ->default('without_super_marketer');
            }
            
            // Add specific role rates
            if (!Schema::hasColumn('commission_rates', 'super_marketer_rate')) {
                $table->decimal('super_marketer_rate', 5, 3)
                    ->nullable()
                    ->after('hierarchy_status');
            }
            
            if (!Schema::hasColumn('commission_rates', 'marketer_rate')) {
                $table->decimal('marketer_rate', 5, 3)
                    ->nullable()
                    ->after('super_marketer_rate');
            }
            
            if (!Schema::hasColumn('commission_rates', 'regional_manager_rate')) {
                $table->decimal('regional_manager_rate', 5, 3)
                    ->nullable()
                    ->after('marketer_rate');
            }
            
            if (!Schema::hasColumn('commission_rates', 'company_rate')) {
                $table->decimal('company_rate', 5, 3)
                    ->nullable()
                    ->after('regional_manager_rate');
            }
            
            if (!Schema::hasColumn('commission_rates', 'total_commission_rate')) {
                $table->decimal('total_commission_rate', 5, 3)
                    ->after('company_rate')
                    ->default(0);
            }
            
            if (!Schema::hasColumn('commission_rates', 'description')) {
                $table->string('description')
                    ->nullable()
                    ->after('total_commission_rate');
            }
            
            if (!Schema::hasColumn('commission_rates', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')
                    ->nullable()
                    ->after('created_by');
            }
            
            if (!Schema::hasColumn('commission_rates', 'last_updated_at')) {
                $table->timestamp('last_updated_at')
                    ->nullable()
                    ->after('updated_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commission_rates', function (Blueprint $table) {
            $columns = [
                'property_management_status',
                'hierarchy_status',
                'super_marketer_rate',
                'marketer_rate',
                'regional_manager_rate',
                'company_rate',
                'total_commission_rate',
                'description',
                'updated_by',
                'last_updated_at'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('commission_rates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
