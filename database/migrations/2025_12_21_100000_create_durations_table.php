<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('durations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique(); // hourly, daily, weekly, monthly, etc.
            $table->string('name', 100); // Hourly, Daily, Weekly, Monthly, etc.
            $table->string('description', 255)->nullable(); // For tooltips/help text
            $table->decimal('duration_months', 8, 4); // Duration in months (e.g., 0.04 for hourly, 1 for monthly)
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0); // For ordering in dropdowns
            $table->string('display_format', 50)->nullable(); // How to display rates (per hour, per day, etc.)
            $table->json('calculation_rules')->nullable(); // Rules for rate calculations
            $table->timestamps();
            
            $table->index(['is_active', 'sort_order']);
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('durations');
    }
};
