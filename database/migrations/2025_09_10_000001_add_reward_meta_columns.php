<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('referral_rewards', function (Blueprint $table) {
            if (!Schema::hasColumn('referral_rewards', 'reward_details')) {
                $table->json('reward_details')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('referral_rewards', function (Blueprint $table) {
            if (Schema::hasColumn('referral_rewards', 'reward_details')) {
                $table->dropColumn('reward_details');
            }
        });
    }
};
