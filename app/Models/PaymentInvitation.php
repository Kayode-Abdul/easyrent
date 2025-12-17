<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaymentInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'benefactor_email',
        'benefactor_id',
        'proforma_id',
        'amount',
        'token',
        'status',
        'approval_status',
        'expires_at',
        'accepted_at',
        'approved_at',
        'declined_at',
        'decline_reason',
        'invoice_details',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'approved_at' => 'datetime',
        'declined_at' => 'datetime',
        'invoice_details' => 'array',
    ];

    /**
     * Boot method to generate token
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invitation) {
            if (empty($invitation->token)) {
                $invitation->token = Str::random(64);
            }
            if (empty($invitation->expires_at)) {
                $invitation->expires_at = now()->addDays(7); // 7 days expiry
            }
        });
    }

    /**
     * Get the tenant who sent the invitation
     */
    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    /**
     * Get the benefactor (if accepted and registered)
     */
    public function benefactor()
    {
        return $this->belongsTo(Benefactor::class);
    }

    /**
     * Check if invitation is expired
     */
    public function isExpired()
    {
        return $this->expires_at->isPast() && $this->status === 'pending';
    }

    /**
     * Check if invitation is pending
     */
    public function isPending()
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    /**
     * Check if invitation is accepted
     */
    public function isAccepted()
    {
        return $this->status === 'accepted';
    }

    /**
     * Mark invitation as accepted
     */
    public function markAsAccepted($benefactorId = null)
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
            'benefactor_id' => $benefactorId,
        ]);
    }

    /**
     * Cancel invitation
     */
    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Approve invitation
     */
    public function approve()
    {
        $this->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    /**
     * Decline invitation
     */
    public function decline($reason = null)
    {
        $this->update([
            'approval_status' => 'declined',
            'declined_at' => now(),
            'decline_reason' => $reason,
        ]);
    }

    /**
     * Check if invitation is approved
     */
    public function isApproved()
    {
        return $this->approval_status === 'approved';
    }

    /**
     * Check if invitation is declined
     */
    public function isDeclined()
    {
        return $this->approval_status === 'declined';
    }

    /**
     * Check if invitation is pending approval
     */
    public function isPendingApproval()
    {
        return $this->approval_status === 'pending_approval';
    }

    /**
     * Get the proforma associated with this invitation
     */
    public function proforma()
    {
        return $this->belongsTo(\App\Models\ProfomaReceipt::class, 'proforma_id');
    }

    /**
     * Get payment link
     */
    public function getPaymentLink()
    {
        return route('benefactor.payment.show', ['token' => $this->token]);
    }

    /**
     * Get invitation token (alias for token field)
     */
    public function getInvitationTokenAttribute()
    {
        return $this->token;
    }
}
