<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'prop_id', 'prop_type', 'address', 'state', 'lga', 'created_at', 'no_of_apartment', 'agent_id', 'status', 'approved_at'];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id', 'user_id');
    }

    protected $casts = [
        'created_at' => 'datetime',
        'prop_type' => 'integer'
    ];

    // Property type constants
    const TYPE_MANSION = 1;
    const TYPE_DUPLEX = 2;
    const TYPE_FLAT = 3;
    const TYPE_TERRACE = 4;

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Back-compat: some code expects `$property->user`
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'property_amenity');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function apartments(): HasMany
    {
        return $this->hasMany(Apartment::class, 'property_id', 'prop_id');
    }

    // Helper methods
    public function getPropertyTypeName(): string
    {
        return match($this->prop_type) {
            self::TYPE_MANSION => 'Mansion',
            self::TYPE_DUPLEX => 'Duplex',
            self::TYPE_FLAT => 'Flat',
            self::TYPE_TERRACE => 'Terrace',
            default => 'Unknown'
        };
    }

    public function getFullAddress(): string
    {
        return "{$this->address}, {$this->lga}, {$this->state}";
    }

    public function isOwner(int $userId): bool
    {
        return $this->user_id === $userId;
    }

    public function hasActiveApartments(): bool
    {
        return $this->apartments()
            ->where('range_end', '>', now())
            ->exists();
    }

    // Status helper
    public function isPending(): bool
    {
        return ($this->status ?? null) === 'pending';
    }

    public function isApproved(): bool
    {
        return ($this->status ?? null) === 'approved';
    }

    // Query scopes
    public function scopePending($q)
    {
        return $q->where('status', 'pending');
    }

    public function scopeApproved($q)
    {
        return $q->where('status', 'approved');
    }
}
