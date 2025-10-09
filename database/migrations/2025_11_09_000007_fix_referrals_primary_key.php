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
        if (Schema::hasTable('referrals')) {
            try {
                Schema::table('referrals', function (Blueprint $table) {
                    // Add primary key and make it auto-increment
                    $table->id()->change();
                });
            } catch (\Exception $e) {
                // Primary key might already exist, ignore
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to revert as we're just ensuring the column is properly configured
    }
};