<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class ReferralCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'marketer_id',
        'campaign_name',
        'campaign_code',
        'qr_code_path',
        'target_audience',
        'start_date',
        'end_date',
        'status',
        'clicks_count',
        'conversions_count',
        'total_commission',
        'description',
        'tracking_params',
        'performance_metrics'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_commission' => 'decimal:2',
        'tracking_params' => 'array',
        'performance_metrics' => 'array'
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_PAUSED = 'paused';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Boot method to generate campaign code
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($campaign) {
            if (!$campaign->campaign_code) {
                $campaign->campaign_code = $campaign->generateUniqueCampaignCode();
            }
        });
    }

    /**
     * Get the marketer that owns the campaign
     */
    public function marketer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marketer_id', 'user_id');
    }

    /**
     * Get the referrals for this campaign
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'campaign_id', 'campaign_code');
    }

    /**
     * Generate unique campaign code
     */
    public function generateUniqueCampaignCode(): string
    {
        do {
            $code = 'CAM-' . strtoupper(Str::random(8));
        } while (self::where('campaign_code', $code)->exists());
        
        return $code;
    }

    /**
     * Generate referral link for this campaign
     */
    public function getReferralLink(): string
    {
        return url('/register?ref=' . $this->marketer->referral_code . '&campaign=' . $this->campaign_code);
    }

    /**
     * Generate QR code for this campaign
     */
    public function generateQrCode(): string
    {
        $referralLink = $this->getReferralLink();
        $filename = 'qr-codes/campaign-' . $this->campaign_code . '.png';
        
        // Generate QR code
        $qrCode = QrCode::format('png')
            ->size(300)
            ->errorCorrection('M')
            ->generate($referralLink);
        
        // Store QR code
        Storage::disk('public')->put($filename, $qrCode);
        
        // Update campaign with QR code path
        $this->update(['qr_code_path' => 'storage/' . $filename]);
        
        return asset('storage/' . $filename);
    }

    /**
     * Get QR code URL
     */
    public function getQrCodeUrl(): ?string
    {
        if (!$this->qr_code_path && $this->campaign_type === 'qr_code') {
            $this->generateQrCode();
        }
        
        return $this->qr_code_path ? asset($this->qr_code_path) : null;
    }

    /**
     * Check if campaign is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if campaign is within date range
     */
    public function isWithinDateRange(): bool
    {
        $now = now()->toDateString();
        
        $afterStart = !$this->start_date || $now >= $this->start_date->toDateString();
        $beforeEnd = !$this->end_date || $now <= $this->end_date->toDateString();
        
        return $afterStart && $beforeEnd;
    }

    /**
     * Get conversion rate
     */
    public function getConversionRateAttribute(): float
    {
        return $this->clicks_count > 0 ? ($this->conversions_count / $this->clicks_count) * 100 : 0;
    }

    /**
     * Increment clicks count
     */
    public function incrementClicks(): void
    {
        $this->increment('clicks_count');
    }

    /**
     * Increment conversions count
     */
    public function incrementConversions(): void
    {
        $this->increment('conversions_count');
    }

    /**
     * Scope for active campaigns
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for campaigns within date range
     */
    public function scopeWithinDateRange($query)
    {
        $now = now()->toDateString();
        
        return $query->where(function($q) use ($now) {
            $q->whereNull('start_date')
              ->orWhere('start_date', '<=', $now);
        })->where(function($q) use ($now) {
            $q->whereNull('end_date')
              ->orWhere('end_date', '>=', $now);
        });
    }

    /**
     * Scope for campaigns by marketer
     */
    public function scopeByMarketer($query, $marketerId)
    {
        return $query->where('marketer_id', $marketerId);
    }
}
