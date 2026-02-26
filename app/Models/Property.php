<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    protected $primaryKey = 'id'; // Use auto-increment id as primary key
    public $incrementing = true;
    public $timestamps = true; // Enable timestamps since migration has them

    protected $fillable = [
        'user_id',
        'property_id', // Business identifier (renamed from prop_id)
        'prop_type',
        'address',
        'state',
        'lga',
        'no_of_apartment',
        'agent_id',
        'status',
        'approved_at',
        'rejected_at',
        'size_value',
        'size_unit',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id', 'user_id');
    }

    protected $casts = [
        'created_at' => 'datetime',
        'prop_type' => 'integer'
    ];

    /**
     * Relationships
     */
    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class, 'property_id');
    }

    public function mainImage()
    {
        return $this->hasOne(PropertyImage::class, 'property_id')->where('is_main', true);
    }

    // Backward compatibility accessors for old column names
    protected $appends = [];

    /**
     * Accessor for prop_name (backward compatibility)
     * Maps to address since there's no separate name field
     */
    public function getPropNameAttribute(): ?string
    {
        return $this->address;
    }

    /**
     * Accessor for prop_description (backward compatibility)
     * Returns null since this field doesn't exist in current schema
     */
    public function getPropDescriptionAttribute(): ?string
    {
        return null;
    }

    /**
     * Accessor for prop_address (backward compatibility)
     * Maps to address field
     */
    public function getPropAddressAttribute(): ?string
    {
        return $this->address;
    }

    /**
     * Accessor for prop_state (backward compatibility)
     * Maps to state field
     */
    public function getPropStateAttribute(): ?string
    {
        return $this->state;
    }

    /**
     * Accessor for prop_lga (backward compatibility)
     * Maps to lga field
     */
    public function getPropLgaAttribute(): ?string
    {
        return $this->lga;
    }

    // Property type constants
    const TYPE_MANSION = 1;
    const TYPE_DUPLEX = 2;
    const TYPE_FLAT = 3;
    const TYPE_TERRACE = 4;
    const TYPE_WAREHOUSE = 5;
    const TYPE_LAND = 6;
    const TYPE_FARM = 7;
    const TYPE_STORE = 8;
    const TYPE_SHOP = 9;

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

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function apartments(): HasMany
    {
        return $this->hasMany(Apartment::class, 'property_id', 'property_id');
    }

    /**
     * Relationship to PropertyType lookup table
     */
    public function propertyType(): BelongsTo
    {
        return $this->belongsTo(PropertyType::class, 'prop_type', 'id');
    }

    // Helper methods
    public function getPropertyTypeName(): string
    {
        return match($this->prop_type) {
            self::TYPE_MANSION => 'Mansion',
            self::TYPE_DUPLEX => 'Duplex',
            self::TYPE_FLAT => 'Flat',
            self::TYPE_TERRACE => 'Terrace',
            self::TYPE_WAREHOUSE => 'Warehouse',
            self::TYPE_LAND => 'Land',
            self::TYPE_FARM => 'Farm',
            self::TYPE_STORE => 'Store',
            self::TYPE_SHOP => 'Shop',
            default => 'Unknown'
        };
    }

    /**
     * Get all property types
     */
    public static function getPropertyTypes(): array
    {
        return [
            self::TYPE_MANSION => 'Mansion',
            self::TYPE_DUPLEX => 'Duplex',
            self::TYPE_FLAT => 'Flat',
            self::TYPE_TERRACE => 'Terrace',
            self::TYPE_WAREHOUSE => 'Warehouse',
            self::TYPE_LAND => 'Land',
            self::TYPE_FARM => 'Farm',
            self::TYPE_STORE => 'Store',
            self::TYPE_SHOP => 'Shop',
        ];
    }

    /**
     * Check if property is commercial type
     */
    public function isCommercial(): bool
    {
        return in_array($this->prop_type, [
            self::TYPE_WAREHOUSE,
            self::TYPE_STORE,
            self::TYPE_SHOP,
        ]);
    }

    /**
     * Check if property is land/agricultural type
     */
    public function isLand(): bool
    {
        return in_array($this->prop_type, [
            self::TYPE_LAND,
            self::TYPE_FARM,
        ]);
    }

    /**
     * Check if property is residential type
     */
    public function isResidential(): bool
    {
        return in_array($this->prop_type, [
            self::TYPE_MANSION,
            self::TYPE_DUPLEX,
            self::TYPE_FLAT,
            self::TYPE_TERRACE,
        ]);
    }

    /**
     * Get property attributes
     */
    public function attributes(): HasMany
    {
        return $this->hasMany(PropertyAttribute::class, 'property_id', 'property_id');
    }

    /**
     * Get a specific property attribute value
     */
    public function getPropertyAttribute(string $key, $default = null)
    {
        $attribute = $this->attributes()->where('attribute_key', $key)->first();
        return $attribute ? $attribute->attribute_value : $default;
    }

    /**
     * Set a property attribute
     */
    public function setPropertyAttribute(string $key, $value): void
    {
        $this->attributes()->updateOrCreate(
            ['attribute_key' => $key],
            ['attribute_value' => $value]
        );
    }

    /**
     * Get formatted size
     */
    public function getFormattedSize(): ?string
    {
        if (!$this->size_value) {
            return null;
        }
        return number_format($this->size_value, 2) . ' ' . ($this->size_unit ?? 'sqm');
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