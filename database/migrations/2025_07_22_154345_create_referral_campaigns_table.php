<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referral_campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('marketer_id');
            $table->string('campaign_name');
            $table->string('campaign_code', 50)->unique();
            $table->string('qr_code_path', 500)->nullable();
            $table->string('target_audience')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'paused', 'completed', 'cancelled'])->default('active');
            $table->integer('clicks_count')->default(0);
            $table->integer('conversions_count')->default(0);
            $table->decimal('total_commission', 10, 2)->default(0.00);
            $table->text('description')->nullable();
            $table->json('tracking_params')->nullable();
            $table->json('performance_metrics')->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('marketer_id')->references('user_id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index('campaign_code');
            $table->index('status');
            $table->index(['marketer_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('referral_campaigns');
    }
};
