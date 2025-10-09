<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'transaction_id',
        'tenant_id',
        'landlord_id',
        'apartment_id',
        'property_id',
        'amount',
        'duration',
        'status',
        'payment_method',
        'payment_reference',
        'payment_meta',
        'paid_at',
        'due_date',
        'payment_date'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_meta' => 'array',
        'paid_at' => 'datetime',
        'due_date' => 'date'
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id', 'user_id');
    }

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_id', 'user_id');
    }

    public function apartment(): BelongsTo
    {
        return $this->belongsTo(Apartment::class, 'apartment_id', 'apartment_id');
    }

    public function getFormattedAmount(): string
    {
        return '₦' . number_format($this->amount, 2);
    }

    public function getFormattedStatus(): string
    {
        return ucfirst($this->status);
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_SUCCESS => 'badge-success',
            self::STATUS_FAILED => 'badge-danger',
            default => 'badge-warning'
        };
    }
    
    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id', 'prop_id');
    }
}
