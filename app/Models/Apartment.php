<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Apartment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'property_id',
        'apartment_id',
        'apartment_type',
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
        // Fix: Use prop_id as owner key for property relation
        return $this->belongsTo(Property::class, 'property_id', 'prop_id');
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
}