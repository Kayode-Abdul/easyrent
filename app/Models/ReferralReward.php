<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'marketer_id',
        'referral_id',
        'reward_type',
        'amount',
        'description',
        'status',
        'processed_at',
        'processed_by',
        'payment_reference',
        'reward_details'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'reward_details' => 'array'
    ];

    // Reward type constants
    const TYPE_COMMISSION = 'commission';
    const TYPE_BONUS = 'bonus';
    const TYPE_MILESTONE = 'milestone';

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the marketer that owns the reward
     */
    public function marketer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marketer_id', 'user_id');
    }

    /**
     * Get the referral that generated this reward
     */
    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
    }

    /**
     * Get the user who processed this reward
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by', 'user_id');
    }

    /**
     * Check if reward is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if reward is approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if reward is paid
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Mark reward as approved
     */
    public function approve($processedBy = null): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'processed_at' => now(),
            'processed_by' => $processedBy ?? auth()->id()
        ]);
    }

    /**
     * Mark reward as paid
     */
    public function markAsPaid($paymentReference = null): void
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'payment_reference' => $paymentReference,
            'processed_at' => now()
        ]);
    }

    /**
     * Cancel reward
     */
    public function cancel($reason = null): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'processed_at' => now(),
            'reward_details' => array_merge($this->reward_details ?? [], [
                'cancellation_reason' => $reason
            ])
        ]);
    }

    /**
     * Scope for pending rewards
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for approved rewards
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope for paid rewards
     */
    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * Scope for rewards by marketer
     */
    public function scopeByMarketer($query, $marketerId)
    {
        return $query->where('marketer_id', $marketerId);
    }

    /**
     * Scope for rewards by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('reward_type', $type);
    }
}
