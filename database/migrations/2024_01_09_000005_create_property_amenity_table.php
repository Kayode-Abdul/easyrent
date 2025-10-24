<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('property_amenity', function (Blueprint $table) {
            $table->unsignedBigInteger('property_id');
            $table->unsignedBigInteger('amenity_id');
            $table->primary(['property_id', 'amenity_id']);

            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
            $table->foreign('amenity_id')->references('id')->on('amenities')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('property_amenity');
    }
};
