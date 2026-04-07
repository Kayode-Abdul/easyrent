<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('artisan_category_id')->nullable()->after('role');
            $table->boolean('is_artisan_verified')->default(false)->after('artisan_category_id');
            $table->text('artisan_bio')->nullable()->after('is_artisan_verified');
            $table->string('city')->nullable()->after('lga');

        // Note: address, state, lga already exist in users table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['artisan_category_id', 'is_artisan_verified', 'artisan_bio', 'city']);
        });
    }
};