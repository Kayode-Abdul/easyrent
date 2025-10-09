<?php

namespace App\Services\Commission;

use App\Models\ReferralChain;
use App\Models\Referral;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ReferralChainService
{
    /**
     * Role IDs for validation
     */
    const SUPER_MARKETER_ROLE_ID = 9;
    const MARKETER_ROLE_ID = 7;
    const LANDLORD_ROLE_ID = 1;

    /**
     * Create a referral chain for multi-tier hierarchy
     *
     * @param int $superMarketerId
     * @param int $marketerId
     * @param int $landlordId
     * @param string $region
     * @return ReferralChain
     * @throws Exception
     */
    public function createReferralChain(
        ?int $superMarketerId,
        ?int $marketerId,
        int $landlordId,
        string $region = 'default'
    ): ReferralChain {
        try {
            DB::beginTransaction();

            // Validate referral eligibility
            $this->validateReferralEligibility($superMarketerId, $marketerId, $landlordId);

            // Check for circular referrals
            if ($this->detectCircularReferrals($superMarketerId, $marketerId, $landlordId)) {
                throw new Exception('Circular referral detected in chain');
            }

            // Check if chain already exists
            if ($this->chainExists($superMarketerId, $marketerId, $landlordId)) {
                throw new Exception('Referral chain already exists for these participants');
            }

            // Create the referral chain
            $chain = ReferralChain::create([
                'super_marketer_id' => $superMarketerId,
                'marketer_id' => $marketerId,
                'landlord_id' => $landlordId,
                'region' => $region,
                'status' => ReferralChain::STATUS_ACTIVE,
                'activated_at' => now()
            ]);

            // Create individual referral records
            $this->createReferralRecords($chain);

            DB::commit();

            Log::info('Referral chain created', [
                'chain_id' => $chain->id,
                'super_marketer_id' => $superMarketerId,
                'marketer_id' => $marketerId,
                'landlord_id' => $landlordId,
                'region' => $region
            ]);

            return $chain;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create referral chain', [
                'super_marketer_id' => $superMarketerId,
                'marketer_id' => $marketerId,
                'landlord_id' => $landlordId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get referral hierarchy for a specific referral
     *
     * @param int $referralId
     * @return array
     */
    public function getReferralHierarchy(int $referralId): array
    {
        $referral = Referral::with(['referrer', 'referred'])->find($referralId);
        
        if (!$referral) {
            return [];
        }

        $hierarchy = [];
        $current = $referral;

        // Build hierarchy by following parent referrals
        while ($current) {
            $hierarchy[] = [
                'referral_id' => $current->id,
                'referrer_id' => $current->referrer_id,
                'referred_id' => $current->referred_id,
                'referrer' => $current->referrer,
                'referred' => $current->referred,
                'level' => $current->referral_level ?? 1,
                'tier' => $current->commission_tier ?? 'direct'
            ];

            // Get parent referral if exists
            $current = $current->parent_referral_id ? 
                Referral::with(['referrer', 'referred'])->find($current->parent_referral_id) : 
                null;
        }

        return array_reverse($hierarchy); // Return top-down hierarchy
    }

    /**
     * Validate referral eligibility for all participants
     *
     * @param int|null $superMarketerId
     * @param int|null $marketerId
     * @param int $landlordId
     * @return bool
     * @throws Exception
     */
    public function validateReferralEligibility(?int $superMarketerId, ?int $marketerId, int $landlordId): bool
    {
        // Validate landlord exists and has correct role
        $landlord = User::find($landlordId);
        if (!$landlord) {
            throw new Exception("Landlord with ID {$landlordId} not found");
        }

        // Validate marketer if provided
        if ($marketerId) {
            $marketer = User::find($marketerId);
            if (!$marketer) {
                throw new Exception("Marketer with ID {$marketerId} not found");
            }

            if (!$this->userHasRole($marketerId, self::MARKETER_ROLE_ID)) {
                throw new Exception("User {$marketerId} does not have marketer role");
            }
        }

        // Validate super marketer if provided
        if ($superMarketerId) {
            $superMarketer = User::find($superMarketerId);
            if (!$superMarketer) {
                throw new Exception("Super Marketer with ID {$superMarketerId} not found");
            }

            if (!$this->userHasRole($superMarketerId, self::SUPER_MARKETER_ROLE_ID)) {
                throw new Exception("User {$superMarketerId} does not have super marketer role");
            }
        }

        // Validate chain structure
        if ($superMarketerId && !$marketerId) {
            throw new Exception("Super Marketer cannot directly refer landlord without marketer in chain");
        }

        return true;
    }

    /**
     * Detect circular referrals in the chain
     *
     * @param int|null $superMarketerId
     * @param int|null $marketerId
     * @param int $landlordId
     * @return bool
     */
    public function detectCircularReferrals(?int $superMarketerId, ?int $marketerId, int $landlordId): bool
    {
        $participants = array_filter([$superMarketerId, $marketerId, $landlordId]);
        
        // Check if any user appears multiple times
        if (count($participants) !== count(array_unique($participants))) {
            return true;
        }

        // Check for existing referral relationships that would create circles
        if ($superMarketerId && $marketerId) {
            // Check if marketer has already referred super marketer
            if ($this->hasReferralRelationship($marketerId, $superMarketerId)) {
                return true;
            }
        }

        if ($marketerId) {
            // Check if landlord has already referred marketer
            if ($this->hasReferralRelationship($landlordId, $marketerId)) {
                return true;
            }
        }

        if ($superMarketerId) {
            // Check if landlord has already referred super marketer
            if ($this->hasReferralRelationship($landlordId, $superMarketerId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all referral chains for a user
     *
     * @param int $userId
     * @param string|null $role
     * @return Collection
     */
    public function getUserReferralChains(int $userId, ?string $role = null): Collection
    {
        $query = ReferralChain::involvingUser($userId)
            ->with(['superMarketer', 'marketer', 'landlord']);

        if ($role) {
            switch ($role) {
                case 'super_marketer':
                    $query->where('super_marketer_id', $userId);
                    break;
                case 'marketer':
                    $query->where('marketer_id', $userId);
                    break;
                case 'landlord':
                    $query->where('landlord_id', $userId);
                    break;
            }
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Break a referral chain
     *
     * @param int $chainId
     * @param string $reason
     * @return bool
     */
    public function breakReferralChain(int $chainId, string $reason = ''): bool
    {
        try {
            $chain = ReferralChain::find($chainId);
            
            if (!$chain) {
                throw new Exception("Referral chain {$chainId} not found");
            }

            $chain->markAsBroken();

            // Update related referral records
            Referral::where('referrer_id', $chain->super_marketer_id)
                ->where('referred_id', $chain->marketer_id)
                ->update(['referral_status' => 'cancelled']);

            Referral::where('referrer_id', $chain->marketer_id)
                ->where('referred_id', $chain->landlord_id)
                ->update(['referral_status' => 'cancelled']);

            Log::info('Referral chain broken', [
                'chain_id' => $chainId,
                'reason' => $reason
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Failed to break referral chain', [
                'chain_id' => $chainId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get chain statistics for a region
     *
     * @param string $region
     * @return array
     */
    public function getRegionChainStatistics(string $region): array
    {
        $chains = ReferralChain::byRegion($region);

        return [
            'total_chains' => $chains->count(),
            'active_chains' => $chains->where('status', ReferralChain::STATUS_ACTIVE)->count(),
            'completed_chains' => $chains->where('status', ReferralChain::STATUS_COMPLETED)->count(),
            'broken_chains' => $chains->where('status', ReferralChain::STATUS_BROKEN)->count(),
            'suspended_chains' => $chains->where('status', ReferralChain::STATUS_SUSPENDED)->count(),
            'three_tier_chains' => $chains->whereNotNull('super_marketer_id')
                ->whereNotNull('marketer_id')->count(),
            'two_tier_chains' => $chains->whereNull('super_marketer_id')
                ->whereNotNull('marketer_id')->count(),
            'direct_chains' => $chains->whereNull('super_marketer_id')
                ->whereNull('marketer_id')->count(),
        ];
    }

    /**
     * Validate chain integrity
     *
     * @param int $chainId
     * @return bool
     */
    public function validateChainIntegrity(int $chainId): bool
    {
        $chain = ReferralChain::find($chainId);
        
        if (!$chain) {
            return false;
        }

        // Verify hash integrity
        if (!$chain->verifyIntegrity()) {
            Log::warning('Chain integrity verification failed', ['chain_id' => $chainId]);
            return false;
        }

        // Verify all participants still exist and have correct roles
        try {
            $this->validateReferralEligibility(
                $chain->super_marketer_id,
                $chain->marketer_id,
                $chain->landlord_id
            );
            return true;
        } catch (Exception $e) {
            Log::warning('Chain participant validation failed', [
                'chain_id' => $chainId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if a referral chain already exists
     *
     * @param int|null $superMarketerId
     * @param int|null $marketerId
     * @param int $landlordId
     * @return bool
     */
    private function chainExists(?int $superMarketerId, ?int $marketerId, int $landlordId): bool
    {
        return ReferralChain::where('super_marketer_id', $superMarketerId)
            ->where('marketer_id', $marketerId)
            ->where('landlord_id', $landlordId)
            ->exists();
    }

    /**
     * Check if user has a specific role
     *
     * @param int $userId
     * @param int $roleId
     * @return bool
     */
    private function userHasRole(int $userId, int $roleId): bool
    {
        return DB::table('role_user')
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->exists();
    }

    /**
     * Check if there's an existing referral relationship
     *
     * @param int $referrerId
     * @param int $referredId
     * @return bool
     */
    private function hasReferralRelationship(int $referrerId, int $referredId): bool
    {
        return Referral::where('referrer_id', $referrerId)
            ->where('referred_id', $referredId)
            ->exists();
    }

    /**
     * Create individual referral records for the chain
     *
     * @param ReferralChain $chain
     */
    private function createReferralRecords(ReferralChain $chain): void
    {
        $referrals = [];

        // Create Super Marketer -> Marketer referral if applicable
        if ($chain->super_marketer_id && $chain->marketer_id) {
            $referrals[] = [
                'referrer_id' => $chain->super_marketer_id,
                'referred_id' => $chain->marketer_id,
                'referral_level' => 1,
                'commission_tier' => 'super_marketer',
                'referral_status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // Create Marketer -> Landlord referral if applicable
        if ($chain->marketer_id) {
            $parentReferralId = null;
            if (!empty($referrals)) {
                // This will be set after the first referral is created
                $parentReferralId = 'PLACEHOLDER';
            }

            $referrals[] = [
                'referrer_id' => $chain->marketer_id,
                'referred_id' => $chain->landlord_id,
                'referral_level' => $chain->super_marketer_id ? 2 : 1,
                'parent_referral_id' => $parentReferralId,
                'commission_tier' => 'marketer',
                'referral_status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // Insert referrals and handle parent relationships
        if (!empty($referrals)) {
            $firstReferral = null;
            
            foreach ($referrals as $index => $referralData) {
                if ($referralData['parent_referral_id'] === 'PLACEHOLDER' && $firstReferral) {
                    $referralData['parent_referral_id'] = $firstReferral->id;
                }
                
                unset($referralData['parent_referral_id']); // Remove placeholder
                $referral = Referral::create($referralData);
                
                if ($index === 0) {
                    $firstReferral = $referral;
                }
            }
        }
    }
}