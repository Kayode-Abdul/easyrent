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
            if (!Schema::hasColumn('users', 'artisan_category_id')) {
                $table->unsignedBigInteger('artisan_category_id')->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'is_artisan_verified')) {
                $table->boolean('is_artisan_verified')->default(false)->after('artisan_category_id');
            }
            if (!Schema::hasColumn('users', 'artisan_bio')) {
                $table->text('artisan_bio')->nullable()->after('is_artisan_verified');
            }
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable()->after('lga');
            }
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