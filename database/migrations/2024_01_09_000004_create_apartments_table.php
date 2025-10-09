<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();
            // $table->unsignedBigInteger('apartment_id'); // it exist below as a unique identifier
            $table->unsignedBigInteger('property_id');
            $table->string('apartment_type')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable(); // users.user_id
            $table->unsignedBigInteger('user_id'); // owner (users.user_id)
            $table->dateTime('range_start')->nullable();
            $table->dateTime('range_end')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->boolean('occupied')->default(false);
            $table->unsignedBigInteger('apartment_id')->unique(); // Unique apartment identifier
            $table->timestamps();

            $table->foreign('property_id')->references('prop_id')->on('properties')->onDelete('cascade');
            $table->foreign('tenant_id')->references('user_id')->on('users')->onDelete('set null');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('apartments');
    }
};
