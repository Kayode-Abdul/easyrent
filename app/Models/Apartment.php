<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

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
        'created_at',
        'occupied'
    ];

    protected $casts = [
        'range_start' => 'datetime',
        'range_end' => 'datetime',
        'created_at' => 'datetime',
        'amount' => 'decimal:2'
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
        if ($this->apartment_type_id && $this->relationLoaded('apartmentType')) {
            return $this->apartmentType->name;
        }
        
        // If apartment_type_id is set but relationship not loaded, load it
        if ($this->apartment_type_id) {
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
}