<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->unsignedBigInteger('state_id')->nullable()->after('state');
            $table->unsignedBigInteger('lga_id')->nullable()->after('lga');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('country_name')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('lga_id')->nullable();
        });

        Schema::table('marketer_profiles', function (Blueprint $table) {
            $table->string('country_name')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('lga_id')->nullable();
        });

        Schema::table('regional_scopes', function (Blueprint $table) {
            $table->string('country_name')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('lga_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['state_id', 'lga_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['country_name', 'state_id', 'lga_id']);
        });

        Schema::table('marketer_profiles', function (Blueprint $table) {
            $table->dropColumn(['country_name', 'state_id', 'lga_id']);
        });

        Schema::table('regional_scopes', function (Blueprint $table) {
            $table->dropColumn(['country_name', 'state_id', 'lga_id']);
        });
    }
};
