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
        // First, check if the column is already nullable
        $columns = DB::select("SHOW COLUMNS FROM payments WHERE Field = 'tenant_id'");
        
        if (!empty($columns) && $columns[0]->Null === 'NO') {
            // Drop foreign key constraint first
            try {
                Schema::table('payments', function (Blueprint $table) {
                    $table->dropForeign(['tenant_id']);
                });
            } catch (Exception $e) {
                // Foreign key might not exist or have different name
                DB::statement('ALTER TABLE payments DROP FOREIGN KEY IF EXISTS payments_tenant_id_foreign');
            }
            
            // Make tenant_id nullable using raw SQL to avoid Laravel issues
            DB::statement('ALTER TABLE payments MODIFY tenant_id BIGINT UNSIGNED NULL');
            
            // Re-add foreign key constraint
            Schema::table('payments', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('user_id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key constraint
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });
        
        // Make tenant_id non-nullable again
        DB::statement('ALTER TABLE payments MODIFY tenant_id BIGINT UNSIGNED NOT NULL');
        
        // Re-add foreign key constraint
        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }
};