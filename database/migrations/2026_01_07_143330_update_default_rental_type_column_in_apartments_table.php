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
        // Change default_rental_type from ENUM to VARCHAR to support all 8 rental types
        DB::statement("ALTER TABLE apartments MODIFY COLUMN default_rental_type VARCHAR(20) DEFAULT 'monthly'");
        
        // Add index if it doesn't exist
        Schema::table('apartments', function (Blueprint $table) {
            if (!$this->hasIndex('apartments', 'apartments_default_rental_type_index')) {
                $table->index('default_rental_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original ENUM (only if needed for rollback)
        DB::statement("ALTER TABLE apartments MODIFY COLUMN default_rental_type ENUM('hourly', 'daily', 'weekly', 'monthly', 'yearly') DEFAULT 'monthly'");
    }
    
    /**
     * Check if an index exists on a table
     */
    private function hasIndex($table, $index): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$index]);
        return !empty($indexes);
    }
};
