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
        Schema::create('complaint_updates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('complaint_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('update_type', ['comment', 'status_change', 'assignment', 'escalation', 'priority_change'])->default('comment');
            $table->text('message');
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->boolean('is_internal')->default(false); // Internal notes vs public updates
            $table->json('metadata')->nullable(); // For additional context
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('complaint_id')->references('id')->on('complaints')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index(['complaint_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['update_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_updates');
    }
};