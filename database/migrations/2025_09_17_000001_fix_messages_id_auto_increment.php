<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    public function up(): void
    {
        // Use Schema builder instead of raw SQL for better compatibility
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                // This will work for both MySQL and SQLite
                $table->id()->change();
            });
        }
    }

    public function down(): void
    {
        // No need to revert as we're just ensuring the column is properly configured
    }
};
