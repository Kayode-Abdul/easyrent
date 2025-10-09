<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolesAndRoleUserTables extends Migration
{
    public function up()
    {
        // Create roles table if it doesn't exist
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }

        // Create role_user pivot table if it doesn't exist
        if (!Schema::hasTable('role_user')) {
            Schema::create('role_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('role_id')->constrained()->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('role_user')) {
            Schema::dropIfExists('role_user');
        }
        if (Schema::hasTable('roles')) {
            Schema::dropIfExists('roles');
        }
    }
}
