<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use App\Services\Payment\PaymentCalculationServiceInterface;

class Apartment extends Model
{
    public $timestamps = false;
    
    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'apartment_id';
    }

    protected $fillable = [
        'property_id',
        'apartment_id',
        'apartment_type',
        'apartment_type_id',
        'tenant_id',
        'user_id',
        'range_start',
        'range_end',
        'amount',
        'pricing_type',
        'price_configuration',
        'supported_rental_types',
        'hourly_rate',
        'daily_rate',
        'weekly_rate',
        'monthly_rate',
        'yearly_rate',
        'default_rental_type',
        'created_at',
        'occupied'
    ];

    protected $casts = [
        'range_start' => 'datetime',
        'range_end' => 'datetime',
        'created_at' => 'datetime',
        'amount' => 'decimal:2',
        'price_configuration' => 'array',
        'supported_rental_types' => 'array',
        'hourly_rate' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'weekly_rate' => 'decimal:2',
        'monthly_rate' => 'decimal:2',
        'yearly_rate' => 'decimal:2'
    ];

    public function property(): BelongsTo
    {
        // Relationship: apartments.property_id → properties.property_id
        return $this->belongsTo(Property::class, 'property_id', 'property_id');
    }

    public function tenant(): BelongsTo
    {
        // Fix: Use user_id as owner key for tenant relation
        return $this->belongsTo(User::class, 'tenant_id', 'user_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship to ApartmentType lookup table
     */
    public function apartmentType(): BelongsTo
    {
        return $this->belongsTo(ApartmentType::class, 'apartment_type_id', 'id');
    }

    /**
     * Accessor for apartment_type to maintain backward compatibility
     * Returns the type name from the lookup table if apartment_type_id is set
     */
    public function getApartmentTypeAttribute($value)
    {
        // If apartment_type_id is set, get the name from the relationship
        if ($this->apartment_type_id) {
            // Check if relationship is already loaded
            if ($this->relationLoaded('apartmentType')) {
                $relatedType = $this->getRelation('apartmentType');
                if ($relatedType) {
                    return $relatedType->name;
                }
            }
            
            // Load the relationship if not loaded
            $type = ApartmentType::find($this->apartment_type_id);
            return $type ? $type->name : $value;
        }
        
        // Fall back to the stored value (for backward compatibility)
        return $value;
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->range_end->isFuture();
    }

    public function getDurationInDays(): int
    {
        return $this->range_start->diffInDays($this->range_end);
    }

    public function getRemainingDays(): int
    {
        return now()->diffInDays($this->range_end, false);
    }

    public function isOwner(int $userId): bool
    {
        return $this->user_id === $userId;
    }

    public function getFormattedAmount(): string
    {
        return number_format($this->amount, 2);
    }

    public function getStatus(): string
    {
        if ($this->range_end->isPast()) {
            return 'Expired';
        }
        if ($this->range_start->isFuture()) {
            return 'Upcoming';
        }
        return 'Active';
    }

    /**
     * Get the pricing type for this apartment
     * Returns 'total' by default for backward compatibility
     */
    public function getPricingType(): string
    {
        return $this->pricing_type ?? 'total';
    }

    /**
     * Calculate the payment total for a given rental duration
     * Uses the centralized PaymentCalculationService
     */
    public function getCalculatedPaymentTotal(int $duration): float
    {
        return app(\App\Services\Payment\PaymentCalculationServiceInterface::class)
            ->calculatePaymentTotal($this->amount, $duration, $this->getPricingType())
            ->totalAmount;
    }

    /**
     * Validate pricing configuration data
     * Ensures the price_configuration JSON contains valid data
     */
    public function validatePricingConfiguration(): bool
    {
        // Validate that pricing_type is one of the allowed values
        if (!in_array($this->getPricingType(), ['total', 'monthly'])) {
            return false;
        }

        // Validate that amount is positive
        if ($this->amount !== null && $this->amount < 0) {
            return false;
        }

        // If price_configuration is set, validate its structure
        if (!empty($this->price_configuration) && is_array($this->price_configuration)) {
            $config = $this->price_configuration;
            
            // Validate that any numeric values are positive
            if (isset($config['base_amount']) && $config['base_amount'] < 0) {
                return false;
            }
            
            if (isset($config['multiplier']) && $config['multiplier'] < 0) {
                return false;
            }
        }

        return true;
    }

    // EasyRent Link relationships and methods
    public function invitations()
    {
        return $this->hasMany(ApartmentInvitation::class, 'apartment_id', 'apartment_id');
    }

    public function activeInvitation()
    {
        return $this->hasOne(ApartmentInvitation::class, 'apartment_id', 'apartment_id')
            ->where('status', 'active')
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    public function isVacant(): bool
    {
        return $this->tenant_id === null || $this->occupied == 0;
    }

    public function getEasyRentLink(): ?string
    {
        $invitation = $this->activeInvitation;
        return $invitation ? route('apartment.invite.show', $invitation->invitation_token) : null;
    }

    public function generateEasyRentLink(int $landlordId, array $options = []): string
    {
        // Deactivate existing invitations for this apartment
        $this->invitations()->where('status', 'active')->update(['status' => 'cancelled']);
        
        // Create new invitation
        $invitation = ApartmentInvitation::create([
            'apartment_id' => $this->apartment_id, // Use apartment_id field instead of id
            'landlord_id' => $landlordId,
            'expires_at' => $options['expires_at'] ?? now()->addDays(30),
            'status' => 'active'
        ]);
        
        return route('apartment.invite.show', $invitation->invitation_token);
    }

    public function hasActiveInvitation(): bool
    {
        return $this->activeInvitation !== null;
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'apartment_id', 'apartment_id');
    }

    // Complaint System Relationships
    
    /**
     * Complaints for this apartment
     */
    public function complaints()
    {
        return $this->hasMany(\App\Models\Complaint::class, 'apartment_id', 'apartment_id');
    }

    /**
     * Get open complaints for this apartment
     */
    public function openComplaints()
    {
        return $this->hasMany(\App\Models\Complaint::class, 'apartment_id', 'apartment_id')
                    ->whereIn('status', ['open', 'in_progress']);
    }

    /**
     * Check if apartment has any open complaints
     */
    public function hasOpenComplaints(): bool
    {
        return $this->openComplaints()->exists();
    }

    /**
     * Get complaint statistics for this apartment
     */
    public function getComplaintStats(): array
    {
        return [
            'total' => $this->complaints()->count(),
            'open' => $this->complaints()->open()->count(),
            'resolved' => $this->complaints()->resolved()->count(),
            'overdue' => $this->complaints()->overdue()->count(),
        ];
    }

    // Rental Duration Support Methods

    /**
     * Get supported rental types for this apartment
     */
    public function getSupportedRentalTypes(): array
    {
        return $this->supported_rental_types ?? ['monthly'];
    }

    /**
     * Check if apartment supports a specific rental type
     */
    public function supportsRentalType(string $type): bool
    {
        return in_array($type, $this->getSupportedRentalTypes());
    }

    /**
     * Get the rate for a specific rental type
     */
    public function getRateForType(string $type): ?float
    {
        switch ($type) {
            case 'hourly':
                return $this->hourly_rate;
            case 'daily':
                return $this->daily_rate;
            case 'weekly':
                return $this->weekly_rate;
            case 'monthly':
                return $this->monthly_rate ?? $this->amount; // Fallback to amount for backward compatibility
            case 'yearly':
                return $this->yearly_rate;
            default:
                return null;
        }
    }

    /**
     * Get all available rates for this apartment
     */
    public function getAllRates(): array
    {
        $rates = [];
        $supportedTypes = $this->getSupportedRentalTypes();
        
        foreach ($supportedTypes as $type) {
            $rate = $this->getRateForType($type);
            if ($rate !== null) {
                $rates[$type] = $rate;
            }
        }
        
        return $rates;
    }

    /**
     * Calculate total cost for a rental duration
     */
    public function calculateRentalCost(string $durationType, int $quantity): float
    {
        $rate = $this->getRateForType($durationType);
        
        if ($rate === null) {
            throw new \InvalidArgumentException("Rental type '{$durationType}' is not supported for this apartment");
        }
        
        return $rate * $quantity;
    }

    /**
     * Get the default rental type for this apartment
     */
    public function getDefaultRentalType(): string
    {
        return $this->default_rental_type ?? 'monthly';
    }

    /**
     * Set supported rental types and their rates
     */
    public function setRentalConfiguration(array $config): void
    {
        $supportedTypes = [];
        
        foreach ($config as $type => $rate) {
            if (in_array($type, ['hourly', 'daily', 'weekly', 'monthly', 'yearly']) && $rate > 0) {
                $supportedTypes[] = $type;
                $this->{$type . '_rate'} = $rate;
            }
        }
        
        $this->supported_rental_types = $supportedTypes;
        
        // Set default rental type to the first supported type if not already set
        if (!$this->default_rental_type || !in_array($this->default_rental_type, $supportedTypes)) {
            $this->default_rental_type = $supportedTypes[0] ?? 'monthly';
        }
    }

    /**
     * Get formatted rate display for a rental type
     */
    public function getFormattedRate(string $type): string
    {
        $rate = $this->getRateForType($type);
        
        if ($rate === null) {
            return 'Not available';
        }
        
        $period = match($type) {
            'hourly' => 'per hour',
            'daily' => 'per day',
            'weekly' => 'per week',
            'monthly' => 'per month',
            'yearly' => 'per year',
            default => ''
        };
        
        return '₦' . number_format($rate, 2) . ' ' . $period;
    }
}