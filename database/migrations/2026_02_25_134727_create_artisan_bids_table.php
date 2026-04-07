<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('artisan_bids', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->foreign('task_id')->references('id')->on('artisan_tasks')->onDelete('cascade');
            $table->unsignedBigInteger('artisan_id');
            $table->foreign('artisan_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->string('duration')->nullable();
            $table->text('proposal')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artisan_bids');
    }
};