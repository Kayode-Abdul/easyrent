<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoleChangeNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('role_change_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->comment('The ID of the admin who made the change');
            $table->unsignedBigInteger('user_id')->comment('The ID of the user whose role was changed');
            $table->integer('old_role')->comment('The previous role ID');
            $table->integer('new_role')->comment('The new role ID');
            $table->string('ip_address')->nullable();
            $table->timestamps();
            
            // Add foreign keys
            $table->foreign('admin_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('role_change_notifications');
    }
}
