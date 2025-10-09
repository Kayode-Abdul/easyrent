<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class CommissionRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'region',
        'role_id',
        'commission_percentage',
        'property_management_status',
        'hierarchy_status',
        'super_marketer_rate',
        'marketer_rate',
        'regional_manager_rate',
        'company_rate',
        'total_commission_rate',
        'description',
        'effective_from',
        'effective_until',
        'created_by',
        'updated_by',
        'last_updated_at',
        'is_active'
    ];

    protected $casts = [
        'commission_percentage' => 'decimal:4',
        'super_marketer_rate' => 'decimal:3',
        'marketer_rate' => 'decimal:3',
        'regional_manager_rate' => 'decimal:3',
        'company_rate' => 'decimal:3',
        'total_commission_rate' => 'decimal:3',
        'effective_from' => 'datetime',
        'effective_until' => 'datetime',
        'last_updated_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    /**
     * Get the role associated with this commission rate
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the user who created this rate
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    /**
     * Get the user who last updated this rate
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }

    /**
     * Scope to get only active rates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('effective_from', '<=', now())
                    ->where(function($q) {
                        $q->whereNull('effective_until')
                          ->orWhere('effective_until', '>', now());
                    });
    }

    /**
     * Scope to get rates for a specific region
     */
    public function scopeForRegion($query, string $region)
    {
        return $query->where('region', $region);
    }

    /**
     * Scope to get rates for a specific role
     */
    public function scopeForRole($query, int $roleId)
    {
        return $query->where('role_id', $roleId);
    }

    /**
     * Check if this rate is currently effective
     */
    public function isCurrentlyEffective(): bool
    {
        $now = now();
        return $this->is_active 
            && $this->effective_from <= $now 
            && ($this->effective_until === null || $this->effective_until > $now);
    }

    /**
     * Get the effective period as a human readable string
     */
    public function getEffectivePeriodAttribute(): string
    {
        $from = $this->effective_from->format('Y-m-d H:i');
        $until = $this->effective_until ? $this->effective_until->format('Y-m-d H:i') : 'Ongoing';
        
        return "{$from} - {$until}";
    }

    /**
     * Scope to get rates for specific property management status
     */
    public function scopeForPropertyManagement($query, string $status)
    {
        return $query->where('property_management_status', $status);
    }

    /**
     * Scope to get rates for specific hierarchy status
     */
    public function scopeForHierarchy($query, string $status)
    {
        return $query->where('hierarchy_status', $status);
    }

    /**
     * Get commission rate for a specific scenario
     */
    public static function getRateForScenario(
        string $region = 'default',
        string $propertyManagementStatus = 'unmanaged',
        string $hierarchyStatus = 'without_super_marketer'
    ): ?self {
        return self::where('region', $region)
            ->where('property_management_status', $propertyManagementStatus)
            ->where('hierarchy_status', $hierarchyStatus)
            ->active()
            ->first();
    }

    /**
     * Calculate commission breakdown for rent amount
     */
    public function calculateCommissionBreakdown(float $rentAmount): array
    {
        $breakdown = [
            'total_commission' => ($rentAmount * $this->total_commission_rate) / 100,
            'super_marketer_commission' => $this->super_marketer_rate ? ($rentAmount * $this->super_marketer_rate) / 100 : 0,
            'marketer_commission' => $this->marketer_rate ? ($rentAmount * $this->marketer_rate) / 100 : 0,
            'regional_manager_commission' => $this->regional_manager_rate ? ($rentAmount * $this->regional_manager_rate) / 100 : 0,
            'company_commission' => $this->company_rate ? ($rentAmount * $this->company_rate) / 100 : 0,
        ];

        $breakdown['rates'] = [
            'total_rate' => $this->total_commission_rate,
            'super_marketer_rate' => $this->super_marketer_rate,
            'marketer_rate' => $this->marketer_rate,
            'regional_manager_rate' => $this->regional_manager_rate,
            'company_rate' => $this->company_rate,
        ];

        return $breakdown;
    }

    /**
     * Get all available regions
     */
    public static function getAvailableRegions(): array
    {
        return self::distinct('region')->pluck('region')->toArray();
    }

    /**
     * Validate commission rates sum to total
     */
    public function validateRatesSum(): bool
    {
        $sum = ($this->super_marketer_rate ?? 0) + 
               ($this->marketer_rate ?? 0) + 
               ($this->regional_manager_rate ?? 0) + 
               ($this->company_rate ?? 0);
        
        return abs($sum - $this->total_commission_rate) < 0.001; // Allow for floating point precision
    }
}