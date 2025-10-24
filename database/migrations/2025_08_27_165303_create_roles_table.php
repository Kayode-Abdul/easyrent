<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('display_name')->nullable();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        } else {
            if (!Schema::hasColumn('roles','name')) Schema::table('roles', function(Blueprint $t){ $t->string('name')->unique()->nullable();});
            if (!Schema::hasColumn('roles','display_name')) Schema::table('roles', function(Blueprint $t){ $t->string('display_name')->nullable();});
            if (!Schema::hasColumn('roles','description')) Schema::table('roles', function(Blueprint $t){ $t->string('description')->nullable();});
        }
    }

    public function down()
    {
        Schema::dropIfExists('roles');
    }
};
