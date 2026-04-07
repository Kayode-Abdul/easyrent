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
        Schema::table('payments', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['tenant_id']);
            
            // Make tenant_id nullable to support guest payments
            $table->unsignedBigInteger('tenant_id')->nullable()->change();
            
            // Re-add the foreign key constraint with nullable support
            $table->foreign('tenant_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['tenant_id']);
            
            // Make tenant_id non-nullable again
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
            
            // Re-add the foreign key constraint
            $table->foreign('tenant_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }
};