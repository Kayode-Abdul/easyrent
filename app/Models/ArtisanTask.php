<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArtisanTask extends Model
{
    protected $fillable = [
        'complaint_id',
        'landlord_id',
        'tenant_id',
        'budget_min',
        'budget_max',
        'duration',
        'description',
        'status',
        'request_setoff',
        'payment_status',
        'payment_reference',
        'platform_fee',
        'gateway_fee',
        'total_amount_paid'
    ];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function landlord()
    {
        return $this->belongsTo(User::class , 'landlord_id', 'user_id');
    }

    public function tenant()
    {
        return $this->belongsTo(User::class , 'tenant_id', 'user_id');
    }

    public function bids()
    {
        return $this->hasMany(ArtisanBid::class , 'task_id');
    }

    public function verificationCode()
    {
        return $this->hasOne(ArtisanVerificationCode::class , 'task_id');
    }
}