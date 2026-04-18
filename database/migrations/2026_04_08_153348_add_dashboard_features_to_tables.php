<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add features to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'two_factor_enabled')) {
                $table->boolean('two_factor_enabled')->default(false)->after('password');
            }
            if (!Schema::hasColumn('users', 'notification_preferences')) {
                $table->json('notification_preferences')->nullable()->after('two_factor_enabled');
            }
        });

        // Add features to artisan_bids table
        Schema::table('artisan_bids', function (Blueprint $table) {
            if (!Schema::hasColumn('artisan_bids', 'is_read')) {
                $table->boolean('is_read')->default(false);
            }
        });

        // Add features to payments table
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'is_read')) {
                $table->boolean('is_read')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_enabled', 'notification_preferences']);
        });

        Schema::table('artisan_bids', function (Blueprint $table) {
            $table->dropColumn('is_read');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('is_read');
        });
    }
};
