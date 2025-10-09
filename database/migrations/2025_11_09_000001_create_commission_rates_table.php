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
        Schema::create('commission_rates', function (Blueprint $table) {
            $table->id();
            $table->string('region', 100)->index();
            $table->unsignedBigInteger('role_id');
            $table->decimal('commission_percentage', 5, 4); // Allows up to 99.9999%
            $table->timestamp('effective_from');
            $table->timestamp('effective_until')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes for performance
            $table->index(['region', 'role_id'], 'idx_region_role');
            $table->index(['effective_from', 'effective_until'], 'idx_effective_dates');
            $table->index(['is_active', 'effective_from'], 'idx_active_rates');

            // Foreign key constraints
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('created_by')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_rates');
    }
};