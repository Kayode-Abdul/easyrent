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
        Schema::create('artisan_verification_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->foreign('task_id')->references('id')->on('artisan_tasks')->onDelete('cascade');
            $table->string('code')->unique();
            $table->unsignedBigInteger('landlord_id');
            $table->unsignedBigInteger('artisan_id')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('landlord_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('artisan_id')->references('user_id')->on('users')->onDelete('set null');
            $table->foreign('tenant_id')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artisan_verification_codes');
    }
};