<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Ensure the `id` column is a primary key and AUTO_INCREMENT
        $pkExists = collect(DB::select("SELECT COUNT(*) AS cnt FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND CONSTRAINT_TYPE = 'PRIMARY KEY'"))->first()->cnt ?? 0;

        if ((int)$pkExists === 0) {
            DB::statement('ALTER TABLE payments ADD PRIMARY KEY (id)');
        }
        
        // Set AUTO_INCREMENT on id
        DB::statement('ALTER TABLE payments MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
    }

    public function down()
    {
        // Remove AUTO_INCREMENT from id
        DB::statement('ALTER TABLE payments MODIFY id BIGINT UNSIGNED NOT NULL');
        
        // Drop primary key if exists
        $pkExists = collect(DB::select("SELECT COUNT(*) AS cnt FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND CONSTRAINT_TYPE = 'PRIMARY KEY'"))->first()->cnt ?? 0;
        if ((int)$pkExists > 0) {
            DB::statement('ALTER TABLE payments DROP PRIMARY KEY');
        }
    }
};