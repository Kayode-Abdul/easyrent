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
        Schema::table('apartments', function (Blueprint $table) {
            // Add supported rental duration types
            $table->json('supported_rental_types')->nullable()->after('price_configuration')
                ->comment('JSON array of supported rental types: hourly, daily, weekly, monthly, yearly');
            
            // Add duration-specific pricing
            $table->decimal('hourly_rate', 10, 2)->nullable()->after('supported_rental_types');
            $table->decimal('daily_rate', 10, 2)->nullable()->after('hourly_rate');
            $table->decimal('weekly_rate', 10, 2)->nullable()->after('daily_rate');
            $table->decimal('monthly_rate', 10, 2)->nullable()->after('weekly_rate');
            $table->decimal('yearly_rate', 10, 2)->nullable()->after('monthly_rate');
            
            // Add default rental type
            $table->enum('default_rental_type', ['hourly', 'daily', 'weekly', 'monthly', 'yearly'])
                ->default('monthly')->after('yearly_rate');
            
            // Add indexes for performance
            $table->index('default_rental_type');
        });
        
        // Update existing apartments to support monthly rentals by default
        DB::table('apartments')->update([
            'supported_rental_types' => json_encode(['monthly']),
            'monthly_rate' => DB::raw('amount'),
            'default_rental_type' => 'monthly'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->dropIndex(['default_rental_type']);
            $table->dropColumn([
                'supported_rental_types',
                'hourly_rate',
                'daily_rate', 
                'weekly_rate',
                'monthly_rate',
                'yearly_rate',
                'default_rental_type'
            ]);
        });
    }
};