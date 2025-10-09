<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('landlord_id');
            $table->unsignedBigInteger('apartment_id');
            $table->decimal('amount', 12, 2);
            $table->integer('duration')->comment('Duration in months');
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->enum('payment_method', ['card', 'bank_transfer', 'ussd'])->nullable();
            $table->string('payment_reference')->nullable();
            $table->json('payment_meta')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('landlord_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('apartment_id')->references('apartment_id')->on('apartments')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
