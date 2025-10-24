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
        Schema::create('referral_chains', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('super_marketer_id')->nullable();
            $table->unsignedBigInteger('marketer_id')->nullable();
            $table->unsignedBigInteger('landlord_id');
            $table->string('chain_hash', 64)->unique(); // SHA-256 hash for chain integrity
            $table->enum('status', ['active', 'completed', 'broken', 'suspended'])->default('active');
            $table->json('commission_breakdown')->nullable(); // Store calculated commission splits
            $table->decimal('total_commission_percentage', 5, 4)->nullable();
            $table->string('region', 100)->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('super_marketer_id', 'idx_super_marketer');
            $table->index('marketer_id', 'idx_marketer');
            $table->index('landlord_id', 'idx_landlord');
            $table->index('status', 'idx_chain_status');
            $table->index('region', 'idx_chain_region');
            $table->index(['status', 'activated_at'], 'idx_active_chains');

            // Foreign key constraints
            $table->foreign('super_marketer_id')->references('user_id')->on('users')->onDelete('set null');
            $table->foreign('marketer_id')->references('user_id')->on('users')->onDelete('set null');
            $table->foreign('landlord_id')->references('user_id')->on('users')->onDelete('cascade');

            // Composite unique constraint to prevent duplicate chains
            $table->unique(['super_marketer_id', 'marketer_id', 'landlord_id'], 'unique_referral_chain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_chains');
    }
};