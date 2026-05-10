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
        Schema::table('users', function (Blueprint $table) {
            $table->text('artisan_bio')->nullable();
            $table->unsignedBigInteger('artisan_category_id')->nullable();
            $table->boolean('is_artisan_verified')->default(0);
            $table->string('city')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['artisan_bio', 'artisan_category_id', 'is_artisan_verified', 'city']);
        });
    }
};
