<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Duration extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'duration_months',
        'is_active',
        'sort_order',
        'display_format',
        'calculation_rules',
    ];

    protected $casts = [
        'duration_months' => 'decimal:4',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'calculation_rules' => 'array',
    ];

    /**
     * Get active durations ordered by sort_order
     */
    public static function getActiveOrdered()
    {
        return self::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get duration by code
     */
    public static function getByCode(string $code)
    {
        return self::where('code', $code)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get duration options for dropdown/select
     */
    public static function getForDropdown()
    {
        return self::getActiveOrdered()
            ->pluck('name', 'duration_months')
            ->toArray();
    }

    /**
     * Get rental type options for checkboxes
     */
    public static function getRentalTypes()
    {
        return self::getActiveOrdered()
            ->map(function ($duration) {
                return [
                    'code' => $duration->code,
                    'name' => $duration->name,
                    'description' => $duration->description,
                    'display_format' => $duration->display_format,
                ];
            })
            ->toArray();
    }

    /**
     * Calculate rate based on duration
     */
    public function calculateRate(float $baseRate, ?float $customRate = null): float
    {
        if ($customRate) {
            return $customRate;
        }

        $rules = $this->calculation_rules ?? [];
        $multiplier = $rules['multiplier'] ?? 1;
        
        return $baseRate * $multiplier;
    }

    /**
     * Get display format string
     */
    public function getDisplayFormat(): string
    {
        return $this->display_format ?? "per {$this->name}";
    }
}
