<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class ReferralChain extends Model
{
    use HasFactory;

    protected $fillable = [
        'super_marketer_id',
        'marketer_id',
        'landlord_id',
        'chain_hash',
        'status',
        'commission_breakdown',
        'total_commission_percentage',
        'region',
        'activated_at',
        'completed_at'
    ];

    protected $casts = [
        'commission_breakdown' => 'array',
        'total_commission_percentage' => 'decimal:4',
        'activated_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_BROKEN = 'broken';
    const STATUS_SUSPENDED = 'suspended';

    /**
     * Boot method to generate chain hash
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($chain) {
            if (!$chain->chain_hash) {
                $chain->chain_hash = $chain->generateChainHash();
            }
        });
    }

    /**
     * Get the super marketer in this chain
     */
    public function superMarketer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'super_marketer_id', 'user_id');
    }

    /**
     * Get the marketer in this chain
     */
    public function marketer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marketer_id', 'user_id');
    }

    /**
     * Get the landlord in this chain
     */
    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_id', 'user_id');
    }

    /**
     * Generate unique chain hash for integrity verification
     */
    public function generateChainHash(): string
    {
        $chainData = [
            'super_marketer_id' => $this->super_marketer_id,
            'marketer_id' => $this->marketer_id,
            'landlord_id' => $this->landlord_id,
            'timestamp' => now()->timestamp
        ];

        return hash('sha256', json_encode($chainData));
    }

    /**
     * Verify chain integrity
     */
    public function verifyIntegrity(): bool
    {
        $expectedHash = $this->generateChainHash();
        return hash_equals($this->chain_hash, $expectedHash);
    }

    /**
     * Get all participants in the chain
     */
    public function getParticipants(): array
    {
        $participants = [];

        if ($this->super_marketer_id) {
            $participants['super_marketer'] = $this->superMarketer;
        }

        if ($this->marketer_id) {
            $participants['marketer'] = $this->marketer;
        }

        $participants['landlord'] = $this->landlord;

        return $participants;
    }

    /**
     * Get chain hierarchy as array of user IDs
     */
    public function getHierarchy(): array
    {
        $hierarchy = [];

        if ($this->super_marketer_id) {
            $hierarchy[] = $this->super_marketer_id;
        }

        if ($this->marketer_id) {
            $hierarchy[] = $this->marketer_id;
        }

        $hierarchy[] = $this->landlord_id;

        return $hierarchy;
    }

    /**
     * Check if chain is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if chain is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if chain is broken
     */
    public function isBroken(): bool
    {
        return $this->status === self::STATUS_BROKEN;
    }

    /**
     * Check if chain is suspended
     */
    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    /**
     * Activate the chain
     */
    public function activate(): void
    {
        $this->update([
            'status' => self::STATUS_ACTIVE,
            'activated_at' => now()
        ]);
    }

    /**
     * Complete the chain
     */
    public function complete(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now()
        ]);
    }

    /**
     * Mark chain as broken
     */
    public function markAsBroken(): void
    {
        $this->update([
            'status' => self::STATUS_BROKEN
        ]);
    }

    /**
     * Suspend the chain
     */
    public function suspend(): void
    {
        $this->update([
            'status' => self::STATUS_SUSPENDED
        ]);
    }

    /**
     * Get chain tier count
     */
    public function getTierCount(): int
    {
        $count = 1; // Always has landlord

        if ($this->marketer_id) {
            $count++;
        }

        if ($this->super_marketer_id) {
            $count++;
        }

        return $count;
    }

    /**
     * Scope for active chains
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for chains by region
     */
    public function scopeByRegion($query, string $region)
    {
        return $query->where('region', $region);
    }

    /**
     * Scope for chains involving a specific user
     */
    public function scopeInvolvingUser($query, int $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('super_marketer_id', $userId)
              ->orWhere('marketer_id', $userId)
              ->orWhere('landlord_id', $userId);
        });
    }

    /**
     * Get referrals associated with this chain through the chain participants
     * This is a custom relationship that finds referrals made by any user in this chain
     */
    public function referrals()
    {
        // Get all user IDs in this chain
        $userIds = collect([
            $this->super_marketer_id,
            $this->marketer_id,
            $this->landlord_id
        ])->filter()->values();

        // Return a relationship-like query
        return \App\Models\Referral::whereIn('referrer_id', $userIds);
    }

    /**
     * Get referrals as a proper Eloquent relationship (for whereHas queries)
     * This creates a relationship that can be used in whereHas clauses
     */
    public function chainReferrals()
    {
        return $this->hasManyThrough(
            \App\Models\Referral::class,
            User::class,
            'user_id', // Foreign key on users table
            'referrer_id', // Foreign key on referrals table
            'super_marketer_id', // Local key on referral_chains table
            'user_id' // Local key on users table
        )->orWhereHas('marketer', function($query) {
            $query->whereColumn('referrals.referrer_id', 'users.user_id');
        })->orWhereHas('landlord', function($query) {
            $query->whereColumn('referrals.referrer_id', 'users.user_id');
        });
    }

    /**
     * Get commission payments for this referral chain
     */
    public function commissionPayments()
    {
        return $this->hasMany(\App\Models\CommissionPayment::class, 'referral_chain_id');
    }
}