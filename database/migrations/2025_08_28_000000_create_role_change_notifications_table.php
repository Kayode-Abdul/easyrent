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
        if (!Schema::hasTable('role_change_notifications')) {
            Schema::create('role_change_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('admin_id')->constrained('users', 'user_id')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
                $table->integer('old_role');
                $table->integer('new_role');
                $table->timestamp('timestamp')->nullable();
                $table->string('ip_address')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_change_notifications');
    }
};
