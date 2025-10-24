<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'business_type',
        'years_of_experience',
        'preferred_commission_rate',
        'marketing_channels',
        'target_regions',
        'kyc_status',
        'kyc_documents',
        'bio',
        'website',
        'social_media_handles',
        'total_referrals',
        'total_commission_earned',
        'verified_at'
    ];

    protected $casts = [
        'target_regions' => 'array',
        'kyc_documents' => 'array',
        'preferred_commission_rate' => 'decimal:2',
        'total_commission_earned' => 'decimal:2',
        'verified_at' => 'datetime'
    ];

    // KYC Status constants
    const KYC_PENDING = 'pending';
    const KYC_APPROVED = 'approved';
    const KYC_REJECTED = 'rejected';

    /**
     * Get the user that owns the marketer profile
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the referral campaigns for this marketer
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(ReferralCampaign::class, 'marketer_id', 'user_id');
    }

    /**
     * Get the referral rewards for this marketer
     */
    public function rewards(): HasMany
    {
        return $this->hasMany(ReferralReward::class, 'marketer_id', 'user_id');
    }

    /**
     * Get the commission payments for this marketer
     */
    public function commissionPayments(): HasMany
    {
        return $this->hasMany(CommissionPayment::class, 'marketer_id', 'user_id');
    }

    /**
     * Check if KYC is approved
     */
    public function isKycApproved(): bool
    {
        return $this->kyc_status === self::KYC_APPROVED;
    }

    /**
     * Check if KYC is pending
     */
    public function isKycPending(): bool
    {
        return $this->kyc_status === self::KYC_PENDING;
    }

    /**
     * Check if marketer is verified
     */
    public function isVerified(): bool
    {
        return !is_null($this->verified_at) && $this->isKycApproved();
    }

    /**
     * Get pending commission amount
     */
    public function getPendingCommissionAttribute(): float
    {
        return $this->rewards()
            ->where('status', 'approved')
            ->sum('amount');
    }

    /**
     * Get conversion rate
     */
    public function getConversionRateAttribute(): float
    {
        $totalClicks = $this->campaigns()->sum('clicks_count');
        $totalConversions = $this->campaigns()->sum('conversions_count');
        
        return $totalClicks > 0 ? ($totalConversions / $totalClicks) * 100 : 0;
    }

    /**
     * Scope for verified marketers
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at')
                    ->where('kyc_status', self::KYC_APPROVED);
    }

    /**
     * Scope for active marketers
     */
    public function scopeActive($query)
    {
        return $query->whereHas('user', function($q) {
            $q->where('marketer_status', 'active');
        });
    }
}
