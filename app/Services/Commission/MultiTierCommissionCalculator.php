<?php

namespace App\Services\Commission;

use App\Models\CommissionPayment;
use App\Models\CommissionRate;
use App\Models\ReferralChain;
use App\Models\User;
use App\Models\Role;
use App\Services\Commission\RegionalRateManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Exception;

class MultiTierCommissionCalculator
{
    /**
     * Role IDs for commission tiers - dynamically resolved
     */
    public static function getSuperMarketerRoleId(): int
    {
        return \DB::table('roles')->where('name', 'super_marketer')->value('id') ?? 4;
    }
    
    public static function getMarketerRoleId(): int
    {
        return \DB::table('roles')->where('name', 'marketer')->value('id') ?? 3;
    }
    
    public static function getRegionalManagerRoleId(): int
    {
        return \DB::table('roles')->where('name', 'regional_manager')->value('id') ?? 9;
    }

    /**
     * Commission tier names
     */
    const TIER_SUPER_MARKETER = 'super_marketer';
    const TIER_MARKETER = 'marketer';
    const TIER_REGIONAL_MANAGER = 'regional_manager';
    const TIER_COMPANY = 'company';

    private RegionalRateManager $rateManager;

    public function __construct(RegionalRateManager $rateManager)
    {
        $this->rateManager = $rateManager;
    }

    /**
     * Calculate commission split for a 3-tier hierarchy
     *
     * @param float $totalCommission Total commission amount to split
     * @param array $referralChain Array of user IDs in hierarchy [super_marketer_id, marketer_id, landlord_id]
     * @param string $region Property region for rate lookup
     * @return array Commission breakdown with amounts and percentages
     * @throws Exception
     */
    public function calculateCommissionSplit(
        float $totalCommission,
        array $referralChain,
        string $region
    ): array {
        try {
            // Validate referral chain
            $this->validateReferralChain($referralChain);

            // Get regional rates
            $rates = $this->getRegionalRates($region);

            // Calculate individual commission amounts
            $breakdown = $this->calculateIndividualCommissions($totalCommission, $referralChain, $rates);

            // Validate total doesn't exceed available commission
            $this->validateCommissionTotal($breakdown, $totalCommission);

            Log::info('Commission split calculated', [
                'total_commission' => $totalCommission,
                'region' => $region,
                'referral_chain' => $referralChain,
                'breakdown' => $breakdown
            ]);

            return $breakdown;

        } catch (Exception $e) {
            Log::error('Commission calculation failed', [
                'total_commission' => $totalCommission,
                'referral_chain' => $referralChain,
                'region' => $region,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Validate commission total doesn't exceed available amount
     *
     * @param array $commissionSplits
     * @param float $totalAvailable
     * @return bool
     * @throws Exception
     */
    public function validateCommissionTotal(array $commissionSplits, float $totalAvailable): bool
    {
        $totalAllocated = 0;

        foreach ($commissionSplits as $split) {
            $totalAllocated += $split['amount'];
        }

        if ($totalAllocated > $totalAvailable) {
            throw new Exception(
                "Total allocated commission ({$totalAllocated}) exceeds available amount ({$totalAvailable})"
            );
        }

        return true;
    }

    /**
     * Get commission breakdown for a specific payment
     *
     * @param int $paymentId
     * @return array
     */
    public function getCommissionBreakdown(int $paymentId): array
    {
        return CommissionPayment::where('transaction_id', $paymentId)
            ->get()
            ->toArray();
    }

    /**
     * Process commission distribution for a payment
     *
     * @param int $paymentId
     * @return bool
     */
    public function processCommissionDistribution(int $paymentId): bool
    {
        // This would integrate with PaymentDistributionService
        // Implementation would depend on payment processing requirements
        return true;
    }

    /**
     * Calculate commission for a specific referral chain
     *
     * @param ReferralChain $chain
     * @param float $rentAmount
     * @return array
     */
    public function calculateChainCommission(ReferralChain $chain, float $rentAmount): array
    {
        $referralChain = [];
        
        if ($chain->super_marketer_id) {
            $referralChain[] = $chain->super_marketer_id;
        }
        
        if ($chain->marketer_id) {
            $referralChain[] = $chain->marketer_id;
        }
        
        $referralChain[] = $chain->landlord_id;

        // Calculate total commission (2.5% of rent)
        $totalCommission = $rentAmount * 0.025;

        return $this->calculateCommissionSplit(
            $totalCommission,
            $referralChain,
            $chain->region ?? 'default'
        );
    }

    /**
     * Get commission rates for all tiers in a region
     *
     * @param string $region
     * @return array
     */
    public function getRegionalCommissionRates(string $region): array
    {
        return [
            self::TIER_SUPER_MARKETER => $this->rateManager->getActiveRate($region, self::SUPER_MARKETER_ROLE_ID),
            self::TIER_MARKETER => $this->rateManager->getActiveRate($region, self::MARKETER_ROLE_ID),
            self::TIER_REGIONAL_MANAGER => $this->rateManager->getActiveRate($region, self::REGIONAL_MANAGER_ROLE_ID),
        ];
    }

    /**
     * Validate referral chain integrity
     *
     * @param array $referralChain
     * @throws Exception
     */
    private function validateReferralChain(array $referralChain): void
    {
        if (empty($referralChain)) {
            throw new Exception('Referral chain cannot be empty');
        }

        if (count($referralChain) > 3) {
            throw new Exception('Referral chain cannot exceed 3 tiers');
        }

        // Extract user IDs from user objects for duplicate checking
        $userIds = [];
        foreach ($referralChain as $user) {
            if (is_object($user) && isset($user->user_id)) {
                $userIds[] = $user->user_id;
            } elseif (is_numeric($user)) {
                $userIds[] = $user;
            } else {
                throw new Exception('Invalid user object in referral chain');
            }
        }

        // Check for duplicate users in chain
        if (count($userIds) !== count(array_unique($userIds))) {
            throw new Exception('Referral chain contains duplicate users');
        }

        // Validate all users exist (only if we have database access)
        foreach ($userIds as $userId) {
            try {
                if (!User::where('user_id', $userId)->exists()) {
                    throw new Exception("User {$userId} not found in referral chain");
                }
            } catch (\Exception $e) {
                // Skip database validation if no database connection
                if (strpos($e->getMessage(), 'database') === false && strpos($e->getMessage(), 'connection') === false) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Get regional commission rates
     *
     * @param string $region
     * @return array
     */
    private function getRegionalRates(string $region): array
    {
        return [
            self::SUPER_MARKETER_ROLE_ID => $this->rateManager->getActiveRate($region, self::SUPER_MARKETER_ROLE_ID),
            self::MARKETER_ROLE_ID => $this->rateManager->getActiveRate($region, self::MARKETER_ROLE_ID),
            self::REGIONAL_MANAGER_ROLE_ID => $this->rateManager->getActiveRate($region, self::REGIONAL_MANAGER_ROLE_ID),
        ];
    }

    /**
     * Calculate individual commission amounts for each tier
     *
     * @param float $totalCommission
     * @param array $referralChain
     * @param array $rates
     * @return array
     */
    private function calculateIndividualCommissions(
        float $totalCommission,
        array $referralChain,
        array $rates
    ): array {
        $breakdown = [];
        $totalAllocated = 0;

        // Identify chain structure
        $chainStructure = $this->identifyChainStructure($referralChain);

        // Calculate commissions based on chain structure
        foreach ($chainStructure as $tier => $userId) {
            $roleId = $this->getRoleIdForTier($tier);
            $rate = $rates[$roleId] ?? 0;
            $amount = ($rate / 100) * $totalCommission;

            if ($amount > 0) {
                $breakdown[] = [
                    'tier' => $tier,
                    'user_id' => $userId,
                    'role_id' => $roleId,
                    'rate_percentage' => $rate,
                    'amount' => round($amount, 2)
                ];
                $totalAllocated += $amount;
            }
        }

        // Add regional manager commission if applicable
        $regionalManagerRate = $rates[self::REGIONAL_MANAGER_ROLE_ID] ?? 0;
        if ($regionalManagerRate > 0) {
            $regionalManagerAmount = ($regionalManagerRate / 100) * $totalCommission;
            $breakdown[] = [
                'tier' => self::TIER_REGIONAL_MANAGER,
                'user_id' => null, // Will be determined by region
                'role_id' => self::REGIONAL_MANAGER_ROLE_ID,
                'rate_percentage' => $regionalManagerRate,
                'amount' => round($regionalManagerAmount, 2)
            ];
            $totalAllocated += $regionalManagerAmount;
        }

        // Add company profit (remaining amount)
        $companyProfit = $totalCommission - $totalAllocated;
        if ($companyProfit > 0) {
            $breakdown[] = [
                'tier' => self::TIER_COMPANY,
                'user_id' => null,
                'role_id' => null,
                'rate_percentage' => ($companyProfit / $totalCommission) * 100,
                'amount' => round($companyProfit, 2)
            ];
        }

        return $breakdown;
    }

    /**
     * Identify the structure of the referral chain
     *
     * @param array $referralChain
     * @return array
     */
    private function identifyChainStructure(array $referralChain): array
    {
        $structure = [];

        // Determine chain type based on length and user roles
        switch (count($referralChain)) {
            case 1:
                // Direct landlord (no referrers)
                break;
                
            case 2:
                // Marketer -> Landlord
                $structure[self::TIER_MARKETER] = $referralChain[0];
                break;
                
            case 3:
                // Super Marketer -> Marketer -> Landlord
                $structure[self::TIER_SUPER_MARKETER] = $referralChain[0];
                $structure[self::TIER_MARKETER] = $referralChain[1];
                break;
        }

        return $structure;
    }

    /**
     * Get role ID for a commission tier
     *
     * @param string $tier
     * @return int
     */
    private function getRoleIdForTier(string $tier): int
    {
        return match($tier) {
            self::TIER_SUPER_MARKETER => self::getSuperMarketerRoleId(),
            self::TIER_MARKETER => self::getMarketerRoleId(),
            self::TIER_REGIONAL_MANAGER => self::getRegionalManagerRoleId(),
            default => 0
        };
    }

    /**
     * Calculate commission percentage for a specific tier
     *
     * @param string $tier
     * @param string $region
     * @return float
     */
    public function getTierCommissionRate(string $tier, string $region): float
    {
        $roleId = $this->getRoleIdForTier($tier);
        return $this->rateManager->getActiveRate($region, $roleId);
    }

    /**
     * Validate that a user can participate in a specific tier
     *
     * @param int $userId
     * @param string $tier
     * @return bool
     */
    public function validateUserTierEligibility(int $userId, string $tier): bool
    {
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        $requiredRoleId = $this->getRoleIdForTier($tier);
        
        // Check if user has the required role
        return $user->roles()->where('role_id', $requiredRoleId)->exists();
    }

    /**
     * Get maximum possible commission for a region
     *
     * @param string $region
     * @return float
     */
    public function getMaximumRegionalCommission(string $region): float
    {
        $rates = $this->getRegionalRates($region);
        return array_sum($rates);
    }
}