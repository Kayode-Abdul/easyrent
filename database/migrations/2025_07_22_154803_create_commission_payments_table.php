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
        Schema::create('commission_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('marketer_id');
            $table->string('payment_reference', 100)->unique();
            $table->decimal('total_amount', 10, 2);
            $table->enum('payment_method', ['bank_transfer', 'mobile_money', 'check'])->default('bank_transfer');
            $table->enum('payment_status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamp('payment_date')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->json('referral_ids')->nullable();
            $table->json('payment_details')->nullable();
            $table->text('notes')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamp('scheduled_date')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('marketer_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('processed_by')->references('user_id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('payment_status');
            $table->index('payment_method');
            $table->index(['marketer_id', 'payment_status']);
            $table->index('payment_date');
            $table->index('scheduled_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('commission_payments');
    }
};
