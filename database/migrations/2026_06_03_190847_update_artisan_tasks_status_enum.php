<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE artisan_tasks MODIFY COLUMN status ENUM('open', 'awarded', 'assigned', 'completed', 'cancelled') DEFAULT 'open'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE artisan_tasks MODIFY COLUMN status ENUM('open', 'awarded', 'completed', 'cancelled') DEFAULT 'open'");
    }
};
