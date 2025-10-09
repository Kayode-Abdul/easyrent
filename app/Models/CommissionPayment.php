<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CommissionPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'marketer_id',
        'payment_reference',
        'total_amount',
        'payment_method',
        'payment_status',
        'payment_date',
        'processed_by',
        'referral_ids',
        'payment_details',
        'notes',
        'transaction_id',
        'scheduled_date',
        'referral_chain_id',
        'commission_tier',
        'parent_payment_id',
        'regional_rate_applied',
        'region'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'scheduled_date' => 'datetime',
        'referral_ids' => 'array',
        'payment_details' => 'array',
        'regional_rate_applied' => 'decimal:4'
    ];

    // Payment method constants
    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_MOBILE_MONEY = 'mobile_money';
    const METHOD_CHECK = 'check';

    // Payment status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    // Commission tier constants
    const TIER_SUPER_MARKETER = 'super_marketer';
    const TIER_MARKETER = 'marketer';
    const TIER_REGIONAL_MANAGER = 'regional_manager';

    /**
     * Boot method to generate payment reference
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($payment) {
            if (!$payment->payment_reference) {
                $payment->payment_reference = $payment->generatePaymentReference();
            }
        });
    }

    /**
     * Get the marketer that owns the payment
     */
    public function marketer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marketer_id', 'user_id');
    }

    /**
     * Get the user who processed this payment
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by', 'user_id');
    }

    /**
     * Get the referral rewards included in this payment
     */
    public function rewards(): HasMany
    {
        return $this->hasMany(ReferralReward::class, 'payment_reference', 'payment_reference');
    }

    /**
     * Get the referral chain this payment belongs to
     */
    public function referralChain(): BelongsTo
    {
        return $this->belongsTo(ReferralChain::class, 'referral_chain_id');
    }

    /**
     * Get the parent payment in the hierarchy
     */
    public function parentPayment(): BelongsTo
    {
        return $this->belongsTo(CommissionPayment::class, 'parent_payment_id');
    }

    /**
     * Get child payments in the hierarchy
     */
    public function childPayments(): HasMany
    {
        return $this->hasMany(CommissionPayment::class, 'parent_payment_id');
    }

    /**
     * Generate unique payment reference
     */
    public function generatePaymentReference(): string
    {
        do {
            $reference = 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        } while (self::where('payment_reference', $reference)->exists());
        
        return $reference;
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->payment_status === self::STATUS_PENDING;
    }

    /**
     * Check if payment is processing
     */
    public function isProcessing(): bool
    {
        return $this->payment_status === self::STATUS_PROCESSING;
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted(): bool
    {
        return $this->payment_status === self::STATUS_COMPLETED;
    }

    /**
     * Check if payment failed
     */
    public function isFailed(): bool
    {
        return $this->payment_status === self::STATUS_FAILED;
    }

    /**
     * Mark payment as processing
     */
    public function markAsProcessing($transactionId = null): void
    {
        $this->update([
            'payment_status' => self::STATUS_PROCESSING,
            'transaction_id' => $transactionId
        ]);
    }

    /**
     * Mark payment as completed
     */
    public function markAsCompleted($transactionId = null): void
    {
        $this->update([
            'payment_status' => self::STATUS_COMPLETED,
            'payment_date' => now(),
            'transaction_id' => $transactionId
        ]);

        // Update related rewards as paid
        ReferralReward::whereIn('id', $this->referral_ids ?? [])
            ->update([
                'status' => ReferralReward::STATUS_PAID,
                'payment_reference' => $this->payment_reference
            ]);
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed($reason = null): void
    {
        $details = $this->payment_details ?? [];
        $details['failure_reason'] = $reason;

        $this->update([
            'payment_status' => self::STATUS_FAILED,
            'payment_details' => $details
        ]);
    }

    /**
     * Get formatted payment method
     */
    public function getFormattedPaymentMethodAttribute(): string
    {
        return match($this->payment_method) {
            self::METHOD_BANK_TRANSFER => 'Bank Transfer',
            self::METHOD_MOBILE_MONEY => 'Mobile Money',
            self::METHOD_CHECK => 'Check',
            default => ucfirst(str_replace('_', ' ', $this->payment_method))
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->payment_status) {
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_PROCESSING => 'badge-info',
            self::STATUS_COMPLETED => 'badge-success',
            self::STATUS_FAILED => 'badge-danger',
            default => 'badge-secondary'
        };
    }

    /**
     * Scope for pending payments
     */
    public function scopePending($query)
    {
        return $query->where('payment_status', self::STATUS_PENDING);
    }

    /**
     * Scope for completed payments
     */
    public function scopeCompleted($query)
    {
        return $query->where('payment_status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for payments by marketer
     */
    public function scopeByMarketer($query, $marketerId)
    {
        return $query->where('marketer_id', $marketerId);
    }

    /**
     * Scope for payments by method
     */
    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope for payments by commission tier
     */
    public function scopeByTier($query, $tier)
    {
        return $query->where('commission_tier', $tier);
    }

    /**
     * Scope for payments by referral chain
     */
    public function scopeByReferralChain($query, $chainId)
    {
        return $query->where('referral_chain_id', $chainId);
    }

    /**
     * Scope for payments by region
     */
    public function scopeByRegion($query, $region)
    {
        return $query->where('region', $region);
    }

    /**
     * Check if payment is for Super Marketer tier
     */
    public function isSuperMarketerTier(): bool
    {
        return $this->commission_tier === self::TIER_SUPER_MARKETER;
    }

    /**
     * Check if payment is for Marketer tier
     */
    public function isMarketerTier(): bool
    {
        return $this->commission_tier === self::TIER_MARKETER;
    }

    /**
     * Check if payment is for Regional Manager tier
     */
    public function isRegionalManagerTier(): bool
    {
        return $this->commission_tier === self::TIER_REGIONAL_MANAGER;
    }

    /**
     * Get formatted commission tier
     */
    public function getFormattedCommissionTierAttribute(): string
    {
        return match($this->commission_tier) {
            self::TIER_SUPER_MARKETER => 'Super Marketer',
            self::TIER_MARKETER => 'Marketer',
            self::TIER_REGIONAL_MANAGER => 'Regional Manager',
            default => ucfirst(str_replace('_', ' ', $this->commission_tier))
        };
    }

    /**
     * Get the tier hierarchy level (1 = highest, 3 = lowest)
     */
    public function getTierHierarchyLevel(): int
    {
        return match($this->commission_tier) {
            self::TIER_SUPER_MARKETER => 1,
            self::TIER_MARKETER => 2,
            self::TIER_REGIONAL_MANAGER => 3,
            default => 999
        };
    }

    /**
     * Get all payments in the same referral chain
     */
    public function getChainPayments()
    {
        if (!$this->referral_chain_id) {
            return collect([$this]);
        }

        return self::where('referral_chain_id', $this->referral_chain_id)
                   ->orderBy('commission_tier')
                   ->get();
    }

    /**
     * Get the root payment in the hierarchy (no parent)
     */
    public function getRootPayment(): CommissionPayment
    {
        $current = $this;
        
        while ($current->parent_payment_id) {
            $current = $current->parentPayment;
            if (!$current) {
                break;
            }
        }
        
        return $current ?: $this;
    }

    /**
     * Get all descendant payments in the hierarchy
     */
    public function getDescendantPayments(): Collection
    {
        $descendants = collect();
        
        foreach ($this->childPayments as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getDescendantPayments());
        }
        
        return $descendants;
    }

    /**
     * Calculate total amount for entire payment hierarchy
     */
    public function getHierarchyTotalAmount(): float
    {
        $total = $this->total_amount;
        
        foreach ($this->getDescendantPayments() as $descendant) {
            $total += $descendant->total_amount;
        }
        
        return $total;
    }

    /**
     * Get payment hierarchy summary
     */
    public function getHierarchySummary(): array
    {
        $root = $this->getRootPayment();
        $allPayments = collect([$root])->merge($root->getDescendantPayments());
        
        return [
            'total_payments' => $allPayments->count(),
            'total_amount' => $allPayments->sum('total_amount'),
            'tiers' => $allPayments->groupBy('commission_tier')->map(function ($payments, $tier) {
                return [
                    'count' => $payments->count(),
                    'total_amount' => $payments->sum('total_amount'),
                    'status_breakdown' => $payments->groupBy('payment_status')->map->count()
                ];
            }),
            'status_summary' => $allPayments->groupBy('payment_status')->map->count()
        ];
    }
}
