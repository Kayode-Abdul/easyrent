<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfomaReceipt extends Model
{
    use HasFactory;

    protected $table = 'profoma_receipt';

    protected $fillable = [
        'user_id',
        'tenant_id',
        'status',
        'transaction_id',
        'apartment_id',
        'amount',
        'duration',
        'security_deposit',
        'water',
        'internet',
        'generator',
        'other_charges_desc',
        'other_charges_amount',
        'total',
    ];

    // Status constants
    const STATUS_REJECTED = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_NEW = 2;

    public function apartment()
    {
        return $this->belongsTo(Apartment::class, 'apartment_id');
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id', 'user_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Human-readable status label, including legacy value 3
    public function getStatusLabelAttribute(): string
    {
        switch ((int) $this->status) {
            case self::STATUS_REJECTED:
                return 'Rejected';
            case self::STATUS_CONFIRMED:
                return 'Confirmed';
            case self::STATUS_NEW:
                return 'New';
            case 3:
                return 'Draft'; // legacy/new-unsent by landlord
            default:
                return (string) $this->status;
        }
    }
}
