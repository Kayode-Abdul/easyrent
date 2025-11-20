<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Benefactor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'full_name',
        'phone',
        'type',
        'relationship_type',
        'is_registered',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_registered' => 'boolean',
    ];

    /**
     * Get the user associated with this benefactor (if registered)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all payments made by this benefactor
     */
    public function payments()
    {
        return $this->hasMany(BenefactorPayment::class);
    }

    /**
     * Get active recurring payments
     */
    public function recurringPayments()
    {
        return $this->hasMany(BenefactorPayment::class)
            ->where('payment_type', 'recurring')
            ->where('status', 'completed');
    }

    /**
     * Get all tenants sponsored by this benefactor
     */
    public function tenants()
    {
        return $this->belongsToMany(User::class, 'benefactor_payments', 'benefactor_id', 'tenant_id')
            ->distinct();
    }

    /**
     * Check if benefactor is registered user
     */
    public function isRegistered()
    {
        return $this->type === 'registered' && $this->user_id !== null;
    }

    /**
     * Check if benefactor is guest
     */
    public function isGuest()
    {
        return $this->type === 'guest';
    }
}
