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
        Schema::create('benefactor_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('benefactor_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('property_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('apartment_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount', 15, 2);
            $table->enum('payment_type', ['one_time', 'recurring'])->default('one_time');
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->enum('frequency', ['monthly', 'quarterly', 'annually'])->nullable(); // For recurring
            $table->date('next_payment_date')->nullable(); // For recurring
            $table->string('payment_reference')->unique()->nullable();
            $table->string('transaction_id')->nullable();
            $table->text('payment_metadata')->nullable(); // JSON data
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['benefactor_id', 'status']);
            $table->index(['tenant_id', 'payment_type']);
            $table->index('payment_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('benefactor_payments');
    }
};
