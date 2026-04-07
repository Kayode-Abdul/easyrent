<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('property_images', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('property_id')->nullable();
            $table->bigInteger('apartment_id')->nullable();
            $table->unsignedBigInteger('uploaded_by');
            $table->string('file_name');
            $table->string('file_path', 500);
            $table->string('original_name');
            $table->unsignedInteger('file_size');
            $table->string('mime_type', 100);
            $table->boolean('is_main')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
            $table->foreign('apartment_id')->references('id')->on('apartments')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');

            // Indexes for performance
            $table->index(['property_id', 'is_main']);
            $table->index(['apartment_id', 'is_main']);
            $table->index('uploaded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_images');
    }
};