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
        Schema::create('referral_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('marketer_id');
            $table->unsignedBigInteger('referral_id');
            $table->enum('reward_type', ['commission', 'bonus', 'milestone'])->default('commission');
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'approved', 'paid', 'cancelled'])->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->string('payment_reference')->nullable();
            $table->json('reward_details')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('marketer_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('referral_id')->references('id')->on('referrals')->onDelete('cascade');
            $table->foreign('processed_by')->references('user_id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('status');
            $table->index('reward_type');
            $table->index(['marketer_id', 'status']);
            $table->index('processed_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('referral_rewards');
    }
};
