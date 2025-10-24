<?php

namespace App\Services\Fraud;

use App\Models\User;
use App\Models\Referral;
use App\Models\ReferralChain;
use App\Models\CommissionPayment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FraudDetectionService
{
    /**
     * Detect suspicious referral patterns for a user
     *
     * @param int $userId
     * @return array
     */
    public function detectSuspiciousReferrals(int $userId): array
    {
        $suspiciousPatterns = [];
        
        // Check for rapid referral creation
        $rapidReferrals = $this->checkRapidReferralCreation($userId);
        if ($rapidReferrals['is_suspicious']) {
            $suspiciousPatterns[] = $rapidReferrals;
        }
        
        // Check for unusual referral success rates
        $unusualSuccessRate = $this->checkUnusualSuccessRate($userId);
        if ($unusualSuccessRate['is_suspicious']) {
            $suspiciousPatterns[] = $unusualSuccessRate;
        }
        
        // Check for duplicate referral information
        $duplicateInfo = $this->checkDuplicateReferralInfo($userId);
        if ($duplicateInfo['is_suspicious']) {
            $suspiciousPatterns[] = $duplicateInfo;
        }
        
        // Check for referral timing patterns
        $timingPatterns = $this->checkReferralTimingPatterns($userId);
        if ($timingPatterns['is_suspicious']) {
            $suspiciousPatterns[] = $timingPatterns;
        }
        
        return $suspiciousPatterns;
    }
    
    /**
     * Validate referral authenticity
     *
     * @param int $referralId
     * @return bool
     */
    public function validateReferralAuthenticity(int $referralId): bool
    {
        $referral = Referral::find($referralId);
        
        if (!$referral) {
            return false;
        }
        
        // Check if referrer and referred are different users
        if ($referral->referrer_id === $referral->referred_id) {
            Log::warning('Self-referral detected', [
                'referral_id' => $referralId,
                'user_id' => $referral->referrer_id
            ]);
            return false;
        }
        
        // Check for circular referrals
        if ($this->detectCircularReferrals($referral->referrer_id, $referral->referred_id)) {
            Log::warning('Circular referral detected', [
                'referral_id' => $referralId,
                'referrer_id' => $referral->referrer_id,
                'referred_id' => $referral->referred_id
            ]);
            return false;
        }
        
        // Validate referral chain integrity
        if (!$this->validateReferralChainIntegrity($referralId)) {
            Log::warning('Referral chain integrity violation', [
                'referral_id' => $referralId
            ]);
            return false;
        }
        
        // Check for duplicate referrals
        if ($this->checkDuplicateReferral($referral->referrer_id, $referral->referred_id, $referralId)) {
            Log::warning('Duplicate referral detected', [
                'referral_id' => $referralId,
                'referrer_id' => $referral->referrer_id,
                'referred_id' => $referral->referred_id
            ]);
            return false;
        }
        
        return true;
    }
    
    /**
     * Detect circular referrals in a referral chain
     *
     * @param int $referrerId
     * @param int $referredId
     * @return bool
     */
    public function detectCircularReferrals(int $referrerId, int $referredId): bool
    {
        // Build referral chain upwards from referrer
        $visitedUsers = [];
        $currentUserId = $referrerId;
        
        while ($currentUserId && !in_array($currentUserId, $visitedUsers)) {
            $visitedUsers[] = $currentUserId;
            
            // If we find the referred user in the chain, it's circular
            if ($currentUserId === $referredId) {
                return true;
            }
            
            // Get the next user in the chain (who referred the current user)
            $parentReferral = Referral::where('referred_id', $currentUserId)
                ->where('status', 'active')
                ->first();
            
            $currentUserId = $parentReferral ? $parentReferral->referrer_id : null;
        }
        
        // Also check downwards from referred user
        $visitedUsers = [];
        $currentUserId = $referredId;
        
        while ($currentUserId && !in_array($currentUserId, $visitedUsers)) {
            $visitedUsers[] = $currentUserId;
            
            // If we find the referrer in the chain, it's circular
            if ($currentUserId === $referrerId) {
                return true;
            }
            
            // Get users referred by current user
            $childReferrals = Referral::where('referrer_id', $currentUserId)
                ->where('status', 'active')
                ->get();
            
            // For simplicity, check first child (in real scenario, might need to check all branches)
            $currentUserId = $childReferrals->first() ? $childReferrals->first()->referred_id : null;
        }
        
        return false;
    }
    
    /**
     * Flag suspicious accounts for manual review
     *
     * @param int $userId
     * @param array $reasons
     * @return bool
     */
    public function flagAccountForReview(int $userId, array $reasons): bool
    {
        $user = User::find($userId);
        
        if (!$user) {
            return false;
        }
        
        // Log the flagging
        Log::warning('Account flagged for manual review', [
            'user_id' => $userId,
            'email' => $user->email,
            'reasons' => $reasons,
            'flagged_at' => now()
        ]);
        
        // Update user status or add flag (assuming we have a flagged_for_review field)
        // This would require a migration to add the field
        try {
            DB::table('users')
                ->where('user_id', $userId)
                ->update([
                    'flagged_for_review' => true,
                    'flag_reasons' => json_encode($reasons),
                    'flagged_at' => now()
                ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to flag user for review', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Check for rapid referral creation (potential bot activity)
     *
     * @param int $userId
     * @return array
     */
    private function checkRapidReferralCreation(int $userId): array
    {
        $recentReferrals = Referral::where('referrer_id', $userId)
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->count();
        
        $threshold = 10; // More than 10 referrals in 24 hours is suspicious
        
        return [
            'type' => 'rapid_referral_creation',
            'is_suspicious' => $recentReferrals > $threshold,
            'details' => [
                'referrals_count' => $recentReferrals,
                'threshold' => $threshold,
                'timeframe' => '24 hours'
            ]
        ];
    }
    
    /**
     * Check for unusual referral success rates
     *
     * @param int $userId
     * @return array
     */
    private function checkUnusualSuccessRate(int $userId): array
    {
        $totalReferrals = Referral::where('referrer_id', $userId)->count();
        $successfulReferrals = Referral::where('referrer_id', $userId)
            ->where('status', 'completed')
            ->count();
        
        if ($totalReferrals === 0) {
            return [
                'type' => 'unusual_success_rate',
                'is_suspicious' => false,
                'details' => ['message' => 'No referrals to analyze']
            ];
        }
        
        $successRate = ($successfulReferrals / $totalReferrals) * 100;
        $suspiciousThreshold = 95; // Success rate above 95% with significant volume is suspicious
        
        return [
            'type' => 'unusual_success_rate',
            'is_suspicious' => $successRate > $suspiciousThreshold && $totalReferrals > 5,
            'details' => [
                'success_rate' => $successRate,
                'total_referrals' => $totalReferrals,
                'successful_referrals' => $successfulReferrals,
                'threshold' => $suspiciousThreshold
            ]
        ];
    }  
  
    /**
     * Check for duplicate referral information (same email, phone, etc.)
     *
     * @param int $userId
     * @return array
     */
    private function checkDuplicateReferralInfo(int $userId): array
    {
        $referrals = Referral::where('referrer_id', $userId)
            ->with('referredUser')
            ->get();
        
        $duplicateEmails = [];
        $duplicatePhones = [];
        $emailCounts = [];
        $phoneCounts = [];
        
        foreach ($referrals as $referral) {
            if ($referral->referredUser) {
                $email = $referral->referredUser->email;
                $phone = $referral->referredUser->phone ?? '';
                
                $emailCounts[$email] = ($emailCounts[$email] ?? 0) + 1;
                if (!empty($phone)) {
                    $phoneCounts[$phone] = ($phoneCounts[$phone] ?? 0) + 1;
                }
            }
        }
        
        foreach ($emailCounts as $email => $count) {
            if ($count > 1) {
                $duplicateEmails[] = ['email' => $email, 'count' => $count];
            }
        }
        
        foreach ($phoneCounts as $phone => $count) {
            if ($count > 1) {
                $duplicatePhones[] = ['phone' => $phone, 'count' => $count];
            }
        }
        
        $hasDuplicates = !empty($duplicateEmails) || !empty($duplicatePhones);
        
        return [
            'type' => 'duplicate_referral_info',
            'is_suspicious' => $hasDuplicates,
            'details' => [
                'duplicate_emails' => $duplicateEmails,
                'duplicate_phones' => $duplicatePhones
            ]
        ];
    }
    
    /**
     * Check for suspicious referral timing patterns
     *
     * @param int $userId
     * @return array
     */
    private function checkReferralTimingPatterns(int $userId): array
    {
        $referrals = Referral::where('referrer_id', $userId)
            ->orderBy('created_at')
            ->get(['created_at']);
        
        if ($referrals->count() < 3) {
            return [
                'type' => 'referral_timing_patterns',
                'is_suspicious' => false,
                'details' => ['message' => 'Insufficient data for timing analysis']
            ];
        }
        
        $intervals = [];
        for ($i = 1; $i < $referrals->count(); $i++) {
            $interval = $referrals[$i]->created_at->diffInMinutes($referrals[$i-1]->created_at);
            $intervals[] = $interval;
        }
        
        // Check for too regular patterns (e.g., exactly every X minutes)
        $intervalCounts = array_count_values($intervals);
        $maxCount = max($intervalCounts);
        $totalIntervals = count($intervals);
        
        // If more than 70% of intervals are the same, it's suspicious
        $regularityThreshold = 0.7;
        $isRegular = ($maxCount / $totalIntervals) > $regularityThreshold;
        
        // Check for very short intervals (bot-like behavior)
        $shortIntervals = array_filter($intervals, function($interval) {
            return $interval < 5; // Less than 5 minutes between referrals
        });
        
        $hasShortIntervals = count($shortIntervals) > ($totalIntervals * 0.5);
        
        return [
            'type' => 'referral_timing_patterns',
            'is_suspicious' => $isRegular || $hasShortIntervals,
            'details' => [
                'is_too_regular' => $isRegular,
                'has_short_intervals' => $hasShortIntervals,
                'intervals' => $intervals,
                'short_intervals_count' => count($shortIntervals)
            ]
        ];
    }
    
    /**
     * Validate referral chain integrity
     *
     * @param int $referralId
     * @return bool
     */
    private function validateReferralChainIntegrity(int $referralId): bool
    {
        $referral = Referral::find($referralId);
        
        if (!$referral) {
            return false;
        }
        
        // Check if referrer exists and has appropriate role
        $referrer = User::find($referral->referrer_id);
        if (!$referrer) {
            return false;
        }
        
        // Check if referred user exists
        $referred = User::find($referral->referred_id);
        if (!$referred) {
            return false;
        }
        
        // Validate role hierarchy (Super Marketer can refer Marketers, Marketers can refer Landlords)
        $referrerRoles = $referrer->roles->pluck('id')->toArray();
        $referredRoles = $referred->roles->pluck('id')->toArray();
        
        // Role 9 = Super Marketer, Role 3 = Marketer, Role 2 = Landlord
        $validHierarchies = [
            [9, 3], // Super Marketer -> Marketer
            [3, 2], // Marketer -> Landlord
            [9, 2]  // Super Marketer -> Landlord (direct)
        ];
        
        foreach ($validHierarchies as $hierarchy) {
            if (in_array($hierarchy[0], $referrerRoles) && in_array($hierarchy[1], $referredRoles)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check for duplicate referrals between same users
     *
     * @param int $referrerId
     * @param int $referredId
     * @param int $excludeReferralId
     * @return bool
     */
    private function checkDuplicateReferral(int $referrerId, int $referredId, int $excludeReferralId): bool
    {
        $existingReferral = Referral::where('referrer_id', $referrerId)
            ->where('referred_id', $referredId)
            ->where('id', '!=', $excludeReferralId)
            ->first();
        
        return $existingReferral !== null;
    }
    
    /**
     * Get fraud detection statistics for a user
     *
     * @param int $userId
     * @return array
     */
    public function getFraudStatistics(int $userId): array
    {
        $user = User::find($userId);
        
        if (!$user) {
            return [];
        }
        
        $totalReferrals = Referral::where('referrer_id', $userId)->count();
        $flaggedReferrals = Referral::where('referrer_id', $userId)
            ->where('is_flagged', true)
            ->count();
        
        $suspiciousPatterns = $this->detectSuspiciousReferrals($userId);
        
        return [
            'user_id' => $userId,
            'total_referrals' => $totalReferrals,
            'flagged_referrals' => $flaggedReferrals,
            'suspicious_patterns_count' => count($suspiciousPatterns),
            'suspicious_patterns' => $suspiciousPatterns,
            'fraud_risk_score' => $this->calculateFraudRiskScore($userId),
            'last_checked' => now()
        ];
    }
    
    /**
     * Calculate fraud risk score for a user (0-100)
     *
     * @param int $userId
     * @return int
     */
    private function calculateFraudRiskScore(int $userId): int
    {
        $suspiciousPatterns = $this->detectSuspiciousReferrals($userId);
        $score = 0;
        
        foreach ($suspiciousPatterns as $pattern) {
            switch ($pattern['type']) {
                case 'rapid_referral_creation':
                    $score += 30;
                    break;
                case 'unusual_success_rate':
                    $score += 25;
                    break;
                case 'duplicate_referral_info':
                    $score += 35;
                    break;
                case 'referral_timing_patterns':
                    $score += 20;
                    break;
            }
        }
        
        return min($score, 100); // Cap at 100
    }
}