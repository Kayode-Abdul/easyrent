<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Update the status enum to include 'success'
        DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('pending', 'completed', 'success', 'failed') DEFAULT 'pending'");
    }

    public function down()
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('pending', 'completed', 'failed') DEFAULT 'pending'");
    }
};