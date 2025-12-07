<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BenefactorPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'benefactor_id',
        'tenant_id',
        'property_id',
        'apartment_id',
        'proforma_id',
        'amount',
        'payment_type',
        'status',
        'frequency',
        'next_payment_date',
        'payment_day_of_month',
        'is_paused',
        'paused_at',
        'pause_reason',
        'payment_reference',
        'transaction_id',
        'payment_metadata',
        'paid_at',
        'cancelled_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_metadata' => 'array',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'paused_at' => 'datetime',
        'next_payment_date' => 'date',
        'is_paused' => 'boolean',
    ];

    /**
     * Boot method to generate payment reference
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->payment_reference)) {
                $payment->payment_reference = 'BEN-' . strtoupper(Str::random(12));
            }
        });
    }

    /**
     * Get the benefactor who made this payment
     */
    public function benefactor()
    {
        return $this->belongsTo(Benefactor::class);
    }

    /**
     * Get the tenant receiving this payment
     */
    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    /**
     * Get the property associated with this payment
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the apartment associated with this payment
     */
    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    /**
     * Check if payment is recurring
     */
    public function isRecurring()
    {
        return $this->payment_type === 'recurring';
    }

    /**
     * Check if payment is one-time
     */
    public function isOneTime()
    {
        return $this->payment_type === 'one_time';
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payment is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Mark payment as completed
     */
    public function markAsCompleted($transactionId = null)
    {
        $this->update([
            'status' => 'completed',
            'paid_at' => now(),
            'transaction_id' => $transactionId,
        ]);

        // If recurring, set next payment date
        if ($this->isRecurring()) {
            $this->setNextPaymentDate();
        }

        // Create a regular payment record so it shows in landlord/tenant dashboards
        $landlordId = null;
        if ($this->proforma_id) {
            $proforma = \App\Models\ProfomaReceipt::find($this->proforma_id);
            if ($proforma) {
                $landlordId = $proforma->user_id;
            }
        }

        // Create payment record
        \App\Models\Payment::create([
            'transaction_id' => $transactionId ?? $this->payment_reference,
            'tenant_id' => $this->tenant_id,
            'landlord_id' => $landlordId,
            'apartment_id' => $this->apartment_id,
            'property_id' => $this->property_id,
            'amount' => $this->amount,
            'status' => 'completed',
            'payment_method' => 'benefactor',
            'payment_reference' => $this->payment_reference,
            'payment_meta' => [
                'benefactor_payment_id' => $this->id,
                'benefactor_name' => $this->benefactor->full_name,
                'benefactor_email' => $this->benefactor->email,
                'payment_type' => $this->payment_type,
                'frequency' => $this->frequency,
            ],
            'paid_at' => $this->paid_at,
            'payment_date' => $this->paid_at,
        ]);

        // Update proforma status if linked
        if ($this->proforma_id) {
            $proforma = \App\Models\ProfomaReceipt::find($this->proforma_id);
            if ($proforma) {
                $proforma->status = \App\Models\ProfomaReceipt::STATUS_PAID;
                $proforma->save();
                
                // Notify landlord
                \App\Models\Message::create([
                    'sender_id' => $this->tenant_id,
                    'receiver_id' => $proforma->user_id,
                    'subject' => 'Rent Paid by Benefactor',
                    'body' => "Great news! Your tenant's rent has been paid by a benefactor.\n\n"
                        . "Tenant: " . $this->tenant->first_name . " " . $this->tenant->last_name . "\n"
                        . "Amount: ₦" . number_format($this->amount, 2) . "\n"
                        . "Paid by: " . $this->benefactor->full_name . "\n"
                        . "Payment Reference: " . $this->payment_reference . "\n"
                        . "Payment Type: " . ucfirst(str_replace('_', ' ', $this->payment_type)) . "\n"
                        . "Paid at: " . $this->paid_at->format('M d, Y H:i')
                ]);
            }
        }
    }

    /**
     * Set next payment date based on frequency and payment day
     */
    public function setNextPaymentDate()
    {
        $nextDate = match($this->frequency) {
            'monthly' => now()->addMonth(),
            'quarterly' => now()->addMonths(3),
            'annually' => now()->addYear(),
            default => now()->addMonth(),
        };

        // If payment_day_of_month is set, adjust to that day
        if ($this->payment_day_of_month) {
            $day = min($this->payment_day_of_month, $nextDate->daysInMonth);
            $nextDate->day($day);
        }

        $this->update(['next_payment_date' => $nextDate]);
    }

    /**
     * Pause recurring payment
     */
    public function pause($reason = null)
    {
        $this->update([
            'is_paused' => true,
            'paused_at' => now(),
            'pause_reason' => $reason,
        ]);
    }

    /**
     * Resume paused payment
     */
    public function resume()
    {
        $this->update([
            'is_paused' => false,
            'paused_at' => null,
            'pause_reason' => null,
        ]);

        // Recalculate next payment date
        if ($this->isRecurring()) {
            $this->setNextPaymentDate();
        }
    }

    /**
     * Check if payment is paused
     */
    public function isPaused()
    {
        return $this->is_paused === true;
    }

    /**
     * Get the proforma associated with this payment
     */
    public function proforma()
    {
        return $this->belongsTo(\App\Models\ProfomaReceipt::class, 'proforma_id');
    }

    /**
     * Cancel recurring payment
     */
    public function cancel()
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }
}
