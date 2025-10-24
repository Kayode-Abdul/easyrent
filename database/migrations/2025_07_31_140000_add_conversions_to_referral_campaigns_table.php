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
            if (!Schema::hasColumn('referral_campaigns', 'conversions_count')) {
                $table->integer('conversions_count')->default(0)->after('clicks_count');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('referral_campaigns')) {
            return;
        }
        Schema::table('referral_campaigns', function (Blueprint $table) {
            if (Schema::hasColumn('referral_campaigns', 'conversions_count')) {
                $table->dropColumn('conversions_count');
            }
        });
    }
};
