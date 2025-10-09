<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReferralReward;

class BackfillRewardDetails extends Command
{
    protected $signature = 'rewards:backfill-details';
    protected $description = 'Backfill reward_details JSON with base, percents and derived RM fields if missing';

    public function handle(): int
    {
        $count = 0;
        ReferralReward::whereNull('reward_details')->chunkById(200, function ($chunk) use (&$count) {
            foreach ($chunk as $reward) {
                $reward->reward_details = [
                    'base_amount' => (float) ($reward->amount > 0 ? round($reward->amount / max(0.01, config('referrals.default_marketer_percent',10)) * 100,2) : 0),
                    'marketer_percent' => config('referrals.default_marketer_percent',10),
                    'regional_manager_id' => null,
                    'regional_manager_percent' => config('referrals.default_rm_percent',5),
                    'regional_manager_amount' => 0,
                ];
                $reward->save();
                $count++;
            }
        });
        $this->info("Backfilled $count rewards");
        return 0;
    }
}
