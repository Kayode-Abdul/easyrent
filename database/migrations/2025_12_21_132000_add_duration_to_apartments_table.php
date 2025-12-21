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
            if (!Schema::hasColumn('apartments', 'duration')) {
                // Store lease duration in decimal months (e.g. weekly=0.25, daily≈0.03, monthly=1)
                $table->decimal('duration', 8, 4)->nullable()->after('user_id');
                $table->index('duration');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            if (Schema::hasColumn('apartments', 'duration')) {
                $table->dropIndex(['duration']);
                $table->dropColumn('duration');
            }
        });
    }
};
