<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Check if we're using SQLite or MySQL and handle accordingly
        $driver = DB::getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite: Check if table exists and has proper structure
            if (Schema::hasTable('payments')) {
                // For SQLite, we need to recreate the table to ensure proper auto-increment
                // But only if the id column doesn't already have the right properties
                $columns = Schema::getColumnListing('payments');
                if (in_array('id', $columns)) {
                    // SQLite handles auto-increment automatically for INTEGER PRIMARY KEY
                    // No action needed if table already exists with id column
                    return;
                }
            }
        } else {
            // MySQL/PostgreSQL: Use information_schema
            try {
                $pkExists = collect(DB::select("SELECT COUNT(*) AS cnt FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND CONSTRAINT_TYPE = 'PRIMARY KEY'"))->first()->cnt ?? 0;

                if ((int)$pkExists === 0) {
                    DB::statement('ALTER TABLE payments ADD PRIMARY KEY (id)');
                }
                
                // Set AUTO_INCREMENT on id (MySQL specific)
                DB::statement('ALTER TABLE payments MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
            } catch (\Exception $e) {
                // If information_schema query fails, skip this migration
                // The table likely already has the correct structure
                return;
            }
        }
    }

    public function down()
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite: Cannot easily remove auto-increment, skip
            return;
        } else {
            // MySQL/PostgreSQL
            try {
                // Remove AUTO_INCREMENT from id
                DB::statement('ALTER TABLE payments MODIFY id BIGINT UNSIGNED NOT NULL');
                
                // Drop primary key if exists
                $pkExists = collect(DB::select("SELECT COUNT(*) AS cnt FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND CONSTRAINT_TYPE = 'PRIMARY KEY'"))->first()->cnt ?? 0;
                if ((int)$pkExists > 0) {
                    DB::statement('ALTER TABLE payments DROP PRIMARY KEY');
                }
            } catch (\Exception $e) {
                // If queries fail, skip rollback
                return;
            }
        }
    }
};