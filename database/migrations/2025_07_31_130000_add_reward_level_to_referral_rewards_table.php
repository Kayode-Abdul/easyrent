<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('referral_rewards') && !Schema::hasColumn('referral_rewards', 'reward_level')) {
            Schema::table('referral_rewards', function (Blueprint $table) {
                $table->string('reward_level')->nullable()->after('amount');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('referral_rewards') && Schema::hasColumn('referral_rewards', 'reward_level')) {
            Schema::table('referral_rewards', function (Blueprint $table) {
                $table->dropColumn('reward_level');
            });
        }
    }
};