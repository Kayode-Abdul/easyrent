<?php
namespace App\Services\Referral;

use App\Models\Referral;
use App\Models\ReferralReward;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RewardCalculationService
{
    public function calculateForReferral(Referral $referral): ReferralReward
    {
        return DB::transaction(function () use ($referral) {
            $referrer = $referral->referrer; // marketer
            if (!$referrer) {
                throw new \RuntimeException('Referrer (marketer) not found for referral ID ' . $referral->id);
            }

            $baseAmount = $this->resolveBaseAmount($referral);
            $marketerPercent = $this->resolveMarketerPercent($referrer, $referral);
            $regionalManagerData = $this->resolveRegionalManager($referral);
            $rmUser = $regionalManagerData['user'] ?? null;
            $rmPercent = $regionalManagerData['percent'] ?? 0;

            $marketerAmount = round($baseAmount * ($marketerPercent / 100), 2);
            $rmAmount = round($baseAmount * ($rmPercent / 100), 2);

            return ReferralReward::create([
                'marketer_id' => $referrer->user_id,
                'referral_id' => $referral->id,
                'reward_type' => ReferralReward::TYPE_COMMISSION,
                'amount' => $marketerAmount,
                'description' => 'Commission for referral #' . $referral->id,
                'status' => ReferralReward::STATUS_PENDING,
                'reward_details' => [
                    'base_amount' => $baseAmount,
                    'marketer_percent' => $marketerPercent,
                    'regional_manager_id' => $rmUser?->user_id,
                    'regional_manager_percent' => $rmPercent,
                    'regional_manager_amount' => $rmAmount,
                ]
            ]);
        });
    }

    protected function resolveBaseAmount(Referral $referral): float
    {
        // Placeholder: could derive from referred user's plan or a config value
        return (float) config('referrals.base_amount', 5000);
    }

    protected function resolveMarketerPercent(User $marketer, Referral $referral): float
    {
        // Priority: marketer override -> user.commission_rate -> config default
        if (!empty($marketer->commission_rate)) {
            return (float)$marketer->commission_rate;
        }
        return (float) config('referrals.default_marketer_percent', 10);
    }

    protected function resolveRegionalManager(Referral $referral): array
    {
        // Placeholder logic: no RM assigned yet
        return [
            'user' => null,
            'percent' => (float) config('referrals.default_rm_percent', 0)
        ];
    }
}
