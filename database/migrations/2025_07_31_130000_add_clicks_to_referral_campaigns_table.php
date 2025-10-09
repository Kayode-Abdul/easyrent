<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('referral_campaigns')) {
            return; // table will be created by another migration
        }
        Schema::table('referral_campaigns', function (Blueprint $table) {
            if (!Schema::hasColumn('referral_campaigns', 'clicks_count')) {
                $table->integer('clicks_count')->default(0)->after('status');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('referral_campaigns')) {
            return;
        }
        Schema::table('referral_campaigns', function (Blueprint $table) {
            if (Schema::hasColumn('referral_campaigns', 'clicks_count')) {
                $table->dropColumn('clicks_count');
            }
        });
    }
};
