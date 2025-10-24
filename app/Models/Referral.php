<?php
	namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    protected $fillable = [
        'referrer_id',
        'referred_id',
        // Added enhanced tracking fields for seeding/usage
        'referral_code',
        'status',
        'commission_amount',
        'commission_status',
        'conversion_date',
        'campaign_id',
        'referral_source',
        'ip_address',
        'user_agent',
        'tracking_data',
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id', 'user_id');
    }

    public function referred()
    {
        return $this->belongsTo(User::class, 'referred_id', 'user_id');
    }

    // Added: link referral to its campaign by campaign_code
    public function campaign()
    {
        return $this->belongsTo(ReferralCampaign::class, 'campaign_id', 'campaign_code');
    }

    // Added: one reward per referral
    public function reward()
    {
        return $this->hasOne(ReferralReward::class);
    }

    // Fraud Detection Methods
    
    /**
     * Check if referral is flagged for fraud
     */
    public function isFlagged(): bool
    {
        return $this->is_flagged ?? false;
    }

    /**
     * Get fraud indicators
     */
    public function getFraudIndicators(): array
    {
        return $this->fraud_indicators ? json_decode($this->fraud_indicators, true) : [];
    }

    /**
     * Flag referral for fraud
     */
    public function flagForFraud(array $indicators): void
    {
        $this->update([
            'is_flagged' => true,
            'fraud_indicators' => json_encode($indicators),
            'fraud_checked_at' => now()
        ]);
    }

    /**
     * Mark referral as authenticity verified
     */
    public function markAsVerified(): void
    {
        $this->update([
            'authenticity_verified' => true,
            'fraud_checked_at' => now()
        ]);
    }

    /**
     * Clear fraud flags
     */
    public function clearFraudFlags(): void
    {
        $this->update([
            'is_flagged' => false,
            'fraud_indicators' => null,
            'fraud_checked_at' => now()
        ]);
    }

    /**
     * Get referral with user information for fraud analysis
     */
    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_id', 'user_id');
    }
}
