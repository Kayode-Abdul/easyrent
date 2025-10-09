<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Owner (users.user_id)
            $table->unsignedBigInteger('prop_id')->unique();
            $table->unsignedTinyInteger('prop_type');
            $table->string('address');
            $table->string('state');
            $table->string('lga');
            $table->unsignedInteger('no_of_apartment')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable(); // Agent (users.user_id)
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('agent_id')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('properties');
    }
};
