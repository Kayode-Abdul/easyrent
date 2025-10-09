<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'user_id';
    public $incrementing = false; // user_id is custom generated
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'username',
        'email',
        'role',
        'occupation',
        'phone',
        'address',
        'state',
        'lga',
        'admin',
        'date_created',
        'password',
        'marketer_status',
        'commission_rate',
        'bank_account_name',
        'bank_account_number',
        'bank_name',
        'bvn',
        'referral_code',
        'photo'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'admin', // hide admin field if sensitive
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function managedProperties()
    {
        return $this->hasMany(Property::class, 'agent_id');
    }

    // Apartments owned by this user (as landlord)
    public function apartments()
    {
        return $this->hasMany(Apartment::class, 'user_id', 'user_id');
    }

    // Apartments where this user is the tenant (leases)
    public function tenantLeases()
    {
        return $this->hasMany(Apartment::class, 'tenant_id', 'user_id');
    }

    public function isLandlord()
    {
        $id = self::getRoleId('landlord');
        $legacy = strtolower((string) $this->role);
        return $this->hasRole('landlord') || $legacy === 'landlord' || (is_numeric($this->role) && (int)$this->role === (int)$id);
    }

    public function isAgent()
    {
        $agentId = self::getRoleId('agent');
        $pmId = self::getRoleId('property_manager');
        $legacy = strtolower((string) $this->role);
        $isLegacyMatch = $legacy === 'agent' || $legacy === 'property_manager' || (is_numeric($this->role) && in_array((int)$this->role, array_filter([(int)$agentId, (int)$pmId, 4]), true));
        return $this->hasRole('agent') || $this->hasRole('property_manager') || $isLegacyMatch;
    }

    public function isTenant()
    {
        $id = self::getRoleId('tenant');
        $legacy = strtolower((string) $this->role);
        return $this->hasRole('tenant') || $legacy === 'tenant' || (is_numeric($this->role) && (int)$this->role === (int)$id);
    }

    public function scopeWithRole($query, $role)
    {
        if (is_array($role)) {
            $names = array_filter(array_map(fn($r) => is_numeric($r) ? null : $r, $role));
            $ids = array_values(array_filter(array_map(fn($r) => is_numeric($r) ? (int)$r : null, $role), fn($v) => !is_null($v)));
            return $query->where(function($q) use ($names, $ids){
                if (!empty($ids)) {
                    $q->orWhereIn('role', $ids)
                      ->orWhereHas('roles', function($r) use ($ids){ $r->whereIn('id', $ids); });
                }
                if (!empty($names)) {
                    $q->orWhereIn('role', $names)
                      ->orWhereHas('roles', function($r) use ($names){ $r->whereIn('name', $names); });
                }
            });
        }
        if (is_numeric($role)) {
            return $query->where('role', (int)$role)
                ->orWhereHas('roles', function($q) use ($role){ $q->where('id', (int)$role); });
        }
        return $query->where('role', $role)
            ->orWhereHas('roles', function($q) use ($role){ $q->where('name', $role); });
    }

    // Check if this user is a tenant of a given landlord
    public function isTenantOf($landlord)
    {
        // This user is tenant; landlord owns the apartment via user_id
        return $this->tenantLeases()->where('user_id', $landlord->user_id)->exists();
    }

    // Check if this user is a landlord of a given tenant
    public function isLandlordOf($tenant)
    {
        // This user is landlord; tenant occupies via tenant_id
        return $this->apartments()->where('tenant_id', $tenant->user_id)->exists();
    }

    public function activityLogs()
    {
        // Explicit keys prevent Laravel from guessing `user_user_id`
        return $this->hasMany(ActivityLog::class, 'user_id', 'user_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id', 'user_id');
    }
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id', 'user_id');
    }

    public function getReferralLink()
    {
        return url('/register?ref=' . $this->user_id);
    }

    public function agentRatings()
    {
        return $this->hasMany(\App\Models\AgentRating::class, 'agent_id', 'user_id');
    }

    public function givenRatings()
    {
        return $this->hasMany(\App\Models\AgentRating::class, 'user_id', 'user_id');
    }

    // Marketer-related relationships and methods
    public function marketerProfile()
    {
        return $this->hasOne(MarketerProfile::class, 'user_id', 'user_id');
    }

    public function referralCampaigns()
    {
        return $this->hasMany(ReferralCampaign::class, 'marketer_id', 'user_id');
    }

    public function referralRewards()
    {
        return $this->hasMany(ReferralReward::class, 'marketer_id', 'user_id');
    }

    public function commissionPayments()
    {
        return $this->hasMany(CommissionPayment::class, 'marketer_id', 'user_id');
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class, 'referrer_id', 'user_id');
    }

    public function referredUsers()
    {
        return $this->hasMany(Referral::class, 'referred_id', 'user_id');
    }

    // Role checking methods
    public function isMarketer()
    {
        // Check both legacy role field and modern roles relationship
        $marketerRoleId = self::getRoleId('marketer');
        return $this->role === $marketerRoleId || $this->hasRole('marketer');
    }

    /**
     * Get role ID by name with caching
     */
    public static function getRoleId(string $roleName): ?int
    {
        static $roleCache = [];
        
        if (!isset($roleCache[$roleName])) {
            $roleCache[$roleName] = DB::table('roles')->where('name', $roleName)->value('id');
        }
        
        return $roleCache[$roleName];
    }

    public function isActiveMarketer()
    {
        return $this->isMarketer() && $this->marketer_status === 'active';
    }

    public function isPendingMarketer()
    {
        return $this->isMarketer() && $this->marketer_status === 'pending';
    }

    // Enhanced referral link with campaign support
    public function getReferralLinkWithCampaign($campaignCode = null)
    {
        $url = url('/register?ref=' . $this->user_id);
        if ($campaignCode) {
            $url .= '&campaign=' . $campaignCode;
        }
        return $url;
    }

    // Generate unique referral code for marketer
    public function generateReferralCode()
    {
        if (!$this->referral_code) {
            do {
                $code = 'REF-' . strtoupper(\Illuminate\Support\Str::random(8));
            } while (User::where('referral_code', $code)->exists());
            
            $this->update(['referral_code' => $code]);
        }
        return $this->referral_code;
    }

    // Get marketer statistics
    public function getMarketerStats()
    {
        if (!$this->isMarketer()) {
            return null;
        }

        return [
            'total_referrals' => $this->referrals()->count(),
            'successful_referrals' => $this->referrals()->whereHas('referred', function($q) {
                $q->where('role', 2); // Landlords only
            })->count(),
            'total_commission' => $this->referralRewards()->where('status', 'paid')->sum('amount'),
            'pending_commission' => $this->referralRewards()->where('status', 'approved')->sum('amount'),
            'total_clicks' => $this->referralCampaigns()->sum('clicks'),
            'total_conversions' => $this->referralCampaigns()->sum('conversions'),
            'conversion_rate' => $this->calculateConversionRate()
        ];
    }

    private function calculateConversionRate()
    {
        $totalClicks = $this->referralCampaigns()->sum('clicks');
        $totalConversions = $this->referralCampaigns()->sum('conversions');
        
        return $totalClicks > 0 ? round(($totalConversions / $totalClicks) * 100, 2) : 0;
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }

    public function regionalScopes()
    {
        return $this->hasMany(RegionalScope::class, 'user_id', 'user_id');
    }

    /**
     * Get formatted regional scopes for the regional manager dashboard
     */
    public function getFormattedRegionalScopes()
    {
        $scopes = $this->regionalScopes()->get();
        $formattedScopes = collect();
        
        // If no scopes exist, return empty collection
        if ($scopes->isEmpty()) {
            return $formattedScopes;
        }
        
        // Group scopes by state
        $stateScopes = $scopes->where('scope_type', 'state');
        $lgaScopes = $scopes->where('scope_type', 'lga');
        
        foreach ($stateScopes as $stateScope) {
            $state = $stateScope->scope_value;
            
            // Find LGAs for this state
            $stateLgas = $lgaScopes->filter(function($lgaScope) use ($state) {
                return strpos($lgaScope->scope_value, $state . '::') === 0;
            });
            
            if ($stateLgas->count() > 0) {
                // Create scope objects for each LGA
                foreach ($stateLgas as $lgaScope) {
                    [$scopeState, $lga] = explode('::', $lgaScope->scope_value, 2);
                    $formattedScopes->push((object)[
                        'state' => $scopeState,
                        'lga' => $lga
                    ]);
                }
            } else {
                // State-wide scope (no specific LGA)
                $formattedScopes->push((object)[
                    'state' => $state,
                    'lga' => null
                ]);
            }
        }
        
        return $formattedScopes;
    }

    public function hasRole($roleName)
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    // Super Marketer role methods (Task 3.1)
    
    /**
     * Check if user is a Super Marketer
     */
    public function isSuperMarketer(): bool
    {
        return $this->hasRole('super_marketer');
    }

    /**
     * Get the referral chain for this user
     * Returns array with hierarchy information
     */
    public function getReferralChain(): array
    {
        $chain = [];
        
        // If this user is a Super Marketer, get their referred marketers and landlords
        if ($this->isSuperMarketer()) {
            $referralChains = ReferralChain::where('super_marketer_id', $this->user_id)
                ->with(['marketer', 'landlord'])
                ->get();
                
            foreach ($referralChains as $referralChain) {
                $chain[] = [
                    'type' => 'super_marketer_chain',
                    'super_marketer_id' => $this->user_id,
                    'marketer_id' => $referralChain->marketer_id,
                    'landlord_id' => $referralChain->landlord_id,
                    'chain_id' => $referralChain->id,
                    'status' => $referralChain->status,
                    'participants' => $referralChain->getParticipants()
                ];
            }
        }
        
        // If this user is a Marketer, check if they're part of a Super Marketer chain
        if ($this->isMarketer()) {
            $referralChain = ReferralChain::where('marketer_id', $this->user_id)
                ->with(['superMarketer', 'landlord'])
                ->first();
                
            if ($referralChain) {
                $chain[] = [
                    'type' => 'marketer_in_chain',
                    'super_marketer_id' => $referralChain->super_marketer_id,
                    'marketer_id' => $this->user_id,
                    'landlord_id' => $referralChain->landlord_id,
                    'chain_id' => $referralChain->id,
                    'status' => $referralChain->status,
                    'participants' => $referralChain->getParticipants()
                ];
            }
            
            // Also get direct referrals (landlords referred by this marketer)
            $directReferrals = $this->referrals()
                ->with('referred')
                ->whereHas('referred', function($q) {
                    $q->where('role', 2); // Landlords
                })
                ->get();
                
            foreach ($directReferrals as $referral) {
                $chain[] = [
                    'type' => 'direct_referral',
                    'marketer_id' => $this->user_id,
                    'landlord_id' => $referral->referred_id,
                    'referral_id' => $referral->id,
                    'status' => $referral->status ?? 'active'
                ];
            }
        }
        
        // If this user is a Landlord, check if they're part of any chain
        if ($this->isLandlord()) {
            $referralChain = ReferralChain::where('landlord_id', $this->user_id)
                ->with(['superMarketer', 'marketer'])
                ->first();
                
            if ($referralChain) {
                $chain[] = [
                    'type' => 'landlord_in_chain',
                    'super_marketer_id' => $referralChain->super_marketer_id,
                    'marketer_id' => $referralChain->marketer_id,
                    'landlord_id' => $this->user_id,
                    'chain_id' => $referralChain->id,
                    'status' => $referralChain->status,
                    'participants' => $referralChain->getParticipants()
                ];
            } else {
                // Check for direct referral relationship
                $directReferral = Referral::where('referred_id', $this->user_id)
                    ->with('referrer')
                    ->first();
                    
                if ($directReferral) {
                    $chain[] = [
                        'type' => 'direct_referral_landlord',
                        'marketer_id' => $directReferral->referrer_id,
                        'landlord_id' => $this->user_id,
                        'referral_id' => $directReferral->id,
                        'status' => $directReferral->status ?? 'active'
                    ];
                }
            }
        }
        
        return $chain;
    }

    /**
     * Check if user can refer marketers (Super Marketer validation)
     */
    public function canReferMarketer(): bool
    {
        // Must be a Super Marketer
        if (!$this->isSuperMarketer()) {
            return false;
        }
        
        // Must have active marketer status
        if (!$this->isActiveMarketer()) {
            return false;
        }
        
        // Additional business rules can be added here
        // For example: check if they have reached referral limits, 
        // account standing, etc.
        
        return true;
    }

    // Referral relationship methods (Task 3.2)
    
    /**
     * Get referrals made by this Super Marketer (marketers they referred)
     */
    public function superMarketerReferrals()
    {
        return $this->hasMany(ReferralChain::class, 'super_marketer_id', 'user_id');
    }

    /**
     * Get marketers referred by this Super Marketer
     */
    public function referredMarketers()
    {
        return $this->hasManyThrough(
            User::class,
            ReferralChain::class,
            'super_marketer_id', // Foreign key on referral_chains table
            'user_id',           // Foreign key on users table
            'user_id',           // Local key on users table (this Super Marketer)
            'marketer_id'        // Local key on referral_chains table
        )->whereHas('roles', function($q) {
            $q->where('name', 'marketer');
        });
    }

    /**
     * Get commission rate for this user in a specific region
     * Supports regional rate management
     */
    public function getCommissionRate(string $region = null): float
    {
        // If no region specified, try to get user's region from their profile or default
        if (!$region) {
            $region = $this->state ?? 'default';
        }

        // Get user's primary role for commission calculation
        $primaryRole = $this->getPrimaryRoleForCommission();
        
        if (!$primaryRole) {
            return 0.0;
        }

        // Look for active commission rate for this role and region
        $commissionRate = CommissionRate::active()
            ->forRegion($region)
            ->forRole($primaryRole->id)
            ->first();

        if ($commissionRate) {
            return (float) $commissionRate->commission_percentage;
        }

        // Fallback to default region if specific region not found
        if ($region !== 'default') {
            $defaultRate = CommissionRate::active()
                ->forRegion('default')
                ->forRole($primaryRole->id)
                ->first();

            if ($defaultRate) {
                return (float) $defaultRate->commission_percentage;
            }
        }

        // Final fallback to user's individual commission rate if set
        if ($this->commission_rate) {
            return (float) $this->commission_rate;
        }

        return 0.0;
    }

    /**
     * Get the primary role for commission calculation
     * Priority: Super Marketer > Marketer > Regional Manager > Other roles
     */
    private function getPrimaryRoleForCommission()
    {
        $userRoles = $this->roles;
        
        // Priority order for commission calculation
        $rolePriority = [
            'super_marketer' => 1,
            'marketer' => 2,
            'regional_manager' => 3,
            'property_manager' => 4,
            'admin' => 5
        ];

        $primaryRole = null;
        $highestPriority = 999;

        foreach ($userRoles as $role) {
            $priority = $rolePriority[$role->name] ?? 999;
            if ($priority < $highestPriority) {
                $highestPriority = $priority;
                $primaryRole = $role;
            }
        }

        return $primaryRole;
    }

    /**
     * Get all referral chains where this user is the Super Marketer
     */
    public function superMarketerChains()
    {
        return $this->hasMany(ReferralChain::class, 'super_marketer_id', 'user_id');
    }

    /**
     * Get referral chain where this user is the Marketer
     */
    public function marketerChain()
    {
        return $this->hasOne(ReferralChain::class, 'marketer_id', 'user_id');
    }

    /**
     * Get referral chain where this user is the Landlord
     */
    public function landlordChain()
    {
        return $this->hasOne(ReferralChain::class, 'landlord_id', 'user_id');
    }

    /**
     * Get Super Marketer who referred this user (if any)
     */
    public function referringSuperMarketer()
    {
        $chain = $this->marketerChain;
        return $chain ? $chain->superMarketer : null;
    }

    /**
     * Get Marketer who referred this landlord (if any)
     */
    public function referringMarketer()
    {
        // Check if landlord is in a referral chain
        $chain = $this->landlordChain;
        if ($chain) {
            return $chain->marketer;
        }

        // Check direct referral relationship
        $referral = Referral::where('referred_id', $this->user_id)->first();
        return $referral ? $referral->referrer : null;
    }

    /**
     * Get commission breakdown for this user in a specific region
     */
    public function getCommissionBreakdown(string $region = null): array
    {
        $region = $region ?? $this->state ?? 'default';
        $breakdown = [];

        if ($this->isSuperMarketer()) {
            $breakdown['super_marketer_rate'] = $this->getCommissionRate($region);
            
            // Get rates for referred marketers
            $marketerRole = Role::where('name', 'marketer')->first();
            if ($marketerRole) {
                $marketerRate = CommissionRate::active()
                    ->forRegion($region)
                    ->forRole($marketerRole->id)
                    ->first();
                $breakdown['marketer_rate'] = $marketerRate ? (float) $marketerRate->commission_percentage : 0.0;
            }
        } elseif ($this->isMarketer()) {
            $breakdown['marketer_rate'] = $this->getCommissionRate($region);
            
            // Check if this marketer has a referring Super Marketer
            $superMarketer = $this->referringSuperMarketer();
            if ($superMarketer) {
                $breakdown['super_marketer_rate'] = $superMarketer->getCommissionRate($region);
            }
        }

        // Add regional manager rate if applicable
        $regionalManagerRole = Role::where('name', 'regional_manager')->first();
        if ($regionalManagerRole) {
            $rmRate = CommissionRate::active()
                ->forRegion($region)
                ->forRole($regionalManagerRole->id)
                ->first();
            $breakdown['regional_manager_rate'] = $rmRate ? (float) $rmRate->commission_percentage : 0.0;
        }

        return $breakdown;
    }

    // Fraud Detection Methods
    
    /**
     * Check if user is flagged for review
     */
    public function isFlaggedForReview(): bool
    {
        return $this->flagged_for_review ?? false;
    }

    /**
     * Get fraud risk score
     */
    public function getFraudRiskScore(): int
    {
        return $this->fraud_risk_score ?? 0;
    }

    /**
     * Get flag reasons
     */
    public function getFlagReasons(): array
    {
        return $this->flag_reasons ? json_decode($this->flag_reasons, true) : [];
    }

    /**
     * Update fraud risk score
     */
    public function updateFraudRiskScore(int $score): void
    {
        $this->update([
            'fraud_risk_score' => min($score, 100),
            'last_fraud_check' => now()
        ]);
    }

    /**
     * Clear fraud flags
     */
    public function clearFraudFlags(): void
    {
        $this->update([
            'flagged_for_review' => false,
            'flag_reasons' => null,
            'flagged_at' => null,
            'fraud_risk_score' => 0
        ]);
    }
}
