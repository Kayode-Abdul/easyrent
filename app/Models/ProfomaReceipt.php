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
    const STATUS_PAID = 4;

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
            case self::STATUS_PAID:
                return 'Paid';
            default:
                return (string) $this->status;
        }
    }

    /**
     * Get benefactor payment invitations for this proforma
     */
    public function benefactorInvitations()
    {
        return $this->hasMany(PaymentInvitation::class, 'proforma_id');
    }

    /**
     * Get benefactor payments for this proforma
     */
    public function benefactorPayments()
    {
        return $this->hasMany(BenefactorPayment::class, 'proforma_id');
    }

    /**
     * Check if proforma has been paid by benefactor
     */
    public function isPaidByBenefactor(): bool
    {
        return $this->benefactorPayments()
            ->where('status', 'completed')
            ->exists();
    }

    /**
     * Check if this proforma has been paid successfully
     */
    public function hasSuccessfulPayment(): bool
    {
        return Payment::where('transaction_id', $this->transaction_id)
            ->whereIn('status', [Payment::STATUS_SUCCESS, Payment::STATUS_COMPLETED])
            ->exists();
    }

    /**
     * Get the successful payment for this proforma
     */
    public function getSuccessfulPayment()
    {
        return Payment::where('transaction_id', $this->transaction_id)
            ->whereIn('status', [Payment::STATUS_SUCCESS, Payment::STATUS_COMPLETED])
            ->first();
    }

    /**
     * Get any payment attempts for this proforma
     */
    public function payments()
    {
        return Payment::where('transaction_id', $this->transaction_id)->get();
    }
}
