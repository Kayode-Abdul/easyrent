<?php

namespace App\Services\Marketer;

use App\Models\User;
use App\Models\Payment;
use App\Models\Referral;
use App\Models\ReferralReward;
use App\Models\Role;
use App\Models\MarketerProfile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MarketerQualificationService
{
    /**
     * Evaluate all users for marketer qualification after a payment
     */
    public function evaluateQualificationAfterPayment(Payment $payment): array
    {
        $results = [];
        
        try {
            $landlord = User::where('user_id', $payment->landlord_id)->first();
            
            if (!$landlord) {
                return $results;
            }

            // Find all users who referred this landlord
            $referrers = $this->findReferrersForLandlord($landlord);
            
            foreach ($referrers as $referrer) {
                $result = $this->evaluateUserQualification($referrer, $payment);
                $results[] = $result;
            }

            Log::info('Marketer qualification evaluation completed', [
                'payment_id' => $payment->id,
                'landlord_id' => $landlord->user_id,
                'referrers_evaluated' => count($referrers),
                'promotions' => collect($results)->where('promoted', true)->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to evaluate marketer qualifications after payment', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
        }

        return $results;
    }

    /**
     * Find all users who referred a landlord
     */
    protected function findReferrersForLandlord(User $landlord): array
    {
        $referrers = [];

        // Find referrers through Referral model
        $referrals = Referral::where('referred_id', $landlord->user_id)
            ->with('referrer')
            ->get();

        foreach ($referrals as $referral) {
            if ($referral->referrer) {
                $referrers[] = $referral->referrer;
            }
        }

        // Also check legacy referred_by field
        if ($landlord->referred_by) {
            $legacyReferrer = User::where('user_id', $landlord->referred_by)->first();
            if ($legacyReferrer && !in_array($legacyReferrer->user_id, collect($referrers)->pluck('user_id')->toArray())) {
                $referrers[] = $legacyReferrer;
            }
        }

        return $referrers;
    }

    /**
     * Evaluate a specific user for marketer qualification
     */
    public function evaluateUserQualification(User $user, Payment $payment = null): array
    {
        $result = [
            'user_id' => $user->user_id,
            'was_marketer' => $user->isMarketer(),
            'qualified_before' => $user->qualifiesForMarketerStatus(),
            'promoted' => false,
            'commission_created' => false,
            'error' => null
        ];

        try {
            DB::beginTransaction();

            // Check qualification status
            $wasQualified = $user->qualifiesForMarketerStatus();
            $wasMarketer = $user->isMarketer();

            // Update referral tracking if payment provided
            if ($payment) {
                $this->updateReferralTracking($user, $payment);
            }

            // Evaluate for promotion
            $user->evaluateMarketerPromotion();
            
            // Refresh user to get updated status
            $user = $user->fresh();
            $isNowMarketer = $user->isMarketer();
            $isNowQualified = $user->qualifiesForMarketerStatus();

            $result['qualified_after'] = $isNowQualified;
            $result['is_marketer_after'] = $isNowMarketer;
            $result['promoted'] = !$wasMarketer && $isNowMarketer;

            // Create commission reward if newly promoted and payment exists
            if ($result['promoted'] && $payment) {
                $commissionCreated = $this->createCommissionReward($user, $payment);
                $result['commission_created'] = $commissionCreated;
            }

            DB::commit();

            Log::info('User marketer qualification evaluated', [
                'user_id' => $user->user_id,
                'payment_id' => $payment ? $payment->id : null,
                'was_qualified' => $wasQualified,
                'is_qualified' => $isNowQualified,
                'was_marketer' => $wasMarketer,
                'is_marketer' => $isNowMarketer,
                'promoted' => $result['promoted']
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $result['error'] = $e->getMessage();
            
            Log::error('Failed to evaluate user marketer qualification', [
                'user_id' => $user->user_id,
                'payment_id' => $payment ? $payment->id : null,
                'error' => $e->getMessage()
            ]);
        }

        return $result;
    }

    /**
     * Update referral tracking for payment
     */
    protected function updateReferralTracking(User $referrer, Payment $payment): void
    {
        $landlord = User::where('user_id', $payment->landlord_id)->first();
        
        if (!$landlord) {
            return;
        }

        // Find or create referral record
        $referral = Referral::where('referrer_id', $referrer->user_id)
            ->where('referred_id', $landlord->user_id)
            ->first();

        if ($referral) {
            // Update existing referral with payment information
            $referral->update([
                'property_id' => $payment->property_id,
                'commission_amount' => $payment->amount * 0.05, // 5% commission
                'commission_status' => 'pending',
                'conversion_date' => $payment->paid_at ?? now()
            ]);
        } else {
            // Create new referral record if it doesn't exist
            Referral::create([
                'referrer_id' => $referrer->user_id,
                'referred_id' => $landlord->user_id,
                'referral_code' => $referrer->referral_code,
                'referral_status' => 'active',
                'property_id' => $payment->property_id,
                'commission_amount' => $payment->amount * 0.05,
                'commission_status' => 'pending',
                'conversion_date' => $payment->paid_at ?? now(),
                'referral_source' => 'payment_tracking'
            ]);
        }
    }

    /**
     * Create commission reward for qualified marketer
     */
    protected function createCommissionReward(User $marketer, Payment $payment): bool
    {
        try {
            $commissionAmount = $payment->amount * 0.05; // 5% commission
            
            // Find the referral record
            $referral = Referral::where('referrer_id', $marketer->user_id)
                ->where('referred_id', $payment->landlord_id)
                ->first();

            if (!$referral) {
                Log::warning('No referral record found for commission reward', [
                    'marketer_id' => $marketer->user_id,
                    'landlord_id' => $payment->landlord_id,
                    'payment_id' => $payment->id
                ]);
                return false;
            }

            // Check if reward already exists
            $existingReward = ReferralReward::where('marketer_id', $marketer->user_id)
                ->where('referral_id', $referral->id)
                ->where('payment_id', $payment->id)
                ->first();

            if ($existingReward) {
                Log::info('Commission reward already exists', [
                    'reward_id' => $existingReward->id,
                    'marketer_id' => $marketer->user_id,
                    'payment_id' => $payment->id
                ]);
                return true;
            }

            // Create new reward
            ReferralReward::create([
                'marketer_id' => $marketer->user_id,
                'referral_id' => $referral->id,
                'amount' => $commissionAmount,
                'status' => 'approved',
                'reward_type' => 'referral_commission',
                'payment_id' => $payment->id,
                'earned_at' => now(),
                'reward_level' => 1 // Base level reward
            ]);

            Log::info('Commission reward created for newly promoted marketer', [
                'marketer_id' => $marketer->user_id,
                'referral_id' => $referral->id,
                'payment_id' => $payment->id,
                'commission_amount' => $commissionAmount
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to create commission reward', [
                'marketer_id' => $marketer->user_id,
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get qualification statistics for all users
     */
    public function getQualificationStatistics(): array
    {
        return [
            'total_users' => User::count(),
            'total_marketers' => User::whereHas('roles', function($q) {
                $q->where('name', 'marketer');
            })->count(),
            'qualified_non_marketers' => User::whereDoesntHave('roles', function($q) {
                $q->where('name', 'marketer');
            })->get()->filter(function($user) {
                return $user->qualifiesForMarketerStatus();
            })->count(),
            'total_referrals' => Referral::count(),
            'successful_referrals' => Referral::whereHas('referred', function($q) {
                $q->whereHas('apartments.payments', function($paymentQuery) {
                    $paymentQuery->where('status', 'completed');
                });
            })->count(),
            'pending_qualifications' => $this->getPendingQualifications()->count()
        ];
    }

    /**
     * Get users who are qualified but not yet promoted
     */
    public function getPendingQualifications()
    {
        return User::whereDoesntHave('roles', function($q) {
            $q->where('name', 'marketer');
        })->get()->filter(function($user) {
            return $user->qualifiesForMarketerStatus();
        });
    }

    /**
     * Batch process pending qualifications
     */
    public function processPendingQualifications(): array
    {
        $pendingUsers = $this->getPendingQualifications();
        $results = [];

        foreach ($pendingUsers as $user) {
            $result = $this->evaluateUserQualification($user);
            $results[] = $result;
        }

        Log::info('Batch processed pending marketer qualifications', [
            'total_processed' => count($results),
            'promotions' => collect($results)->where('promoted', true)->count()
        ]);

        return $results;
    }

    /**
     * Get detailed qualification report for a user
     */
    public function getUserQualificationReport(User $user): array
    {
        return [
            'user_info' => [
                'user_id' => $user->user_id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'is_marketer' => $user->isMarketer(),
                'marketer_status' => $user->marketer_status,
                'referral_code' => $user->referral_code
            ],
            'qualification_status' => $user->getMarketerQualificationStatus(),
            'referral_performance' => $this->getReferralPerformance($user),
            'commission_history' => $this->getCommissionHistory($user)
        ];
    }

    /**
     * Get referral performance for a user
     */
    protected function getReferralPerformance(User $user): array
    {
        $referrals = $user->referrals()->with(['referred.apartments.payments'])->get();
        
        return [
            'total_referrals' => $referrals->count(),
            'landlord_referrals' => $referrals->filter(function($referral) {
                return $referral->referred->hasRole('landlord') || 
                       $referral->referred->role == User::getRoleId('landlord');
            })->count(),
            'referrals_with_payments' => $referrals->filter(function($referral) {
                return $referral->referred->apartments()
                    ->whereHas('payments', function($query) {
                        $query->where('status', 'completed');
                    })->exists();
            })->count(),
            'total_commission_earned' => $user->referralRewards()
                ->where('status', 'paid')
                ->sum('amount'),
            'pending_commission' => $user->referralRewards()
                ->where('status', 'approved')
                ->sum('amount')
        ];
    }

    /**
     * Get commission history for a user
     */
    protected function getCommissionHistory(User $user): array
    {
        return $user->referralRewards()
            ->with(['referral.referred'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($reward) {
                return [
                    'id' => $reward->id,
                    'amount' => $reward->amount,
                    'status' => $reward->status,
                    'earned_at' => $reward->earned_at,
                    'referred_user' => $reward->referral ? [
                        'name' => $reward->referral->referred->first_name . ' ' . $reward->referral->referred->last_name,
                        'email' => $reward->referral->referred->email
                    ] : null
                ];
            })
            ->toArray();
    }
}