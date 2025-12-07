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
        Schema::create('apartment_invitations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('apartment_id');
            $table->unsignedBigInteger('landlord_id');
            $table->string('invitation_token', 64)->unique();
            $table->enum('status', ['active', 'used', 'expired', 'cancelled'])->default('active');
            $table->timestamp('expires_at')->nullable();
            $table->string('prospect_email')->nullable();
            $table->string('prospect_phone', 20)->nullable();
            $table->string('prospect_name')->nullable();
            $table->unsignedBigInteger('tenant_user_id')->nullable(); // For registered users
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('payment_initiated_at')->nullable();
            $table->timestamp('payment_completed_at')->nullable();
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->integer('lease_duration')->nullable(); // months
            $table->date('move_in_date')->nullable();
            $table->json('session_data')->nullable(); // Store session data for unregistered users
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('apartment_id')->references('id')->on('apartments')->onDelete('cascade');
            $table->foreign('landlord_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('tenant_user_id')->references('user_id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('invitation_token');
            $table->index('apartment_id');
            $table->index('status');
            $table->index('expires_at');
            $table->index('landlord_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('apartment_invitations');
    }
};