<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArtisanVerificationCode extends Model
{
    protected $fillable = [
        'task_id',
        'code',
        'landlord_id',
        'artisan_id',
        'tenant_id',
        'expires_at'
    ];

    public function task()
    {
        return $this->belongsTo(ArtisanTask::class , 'task_id');
    }

    public function landlord()
    {
        return $this->belongsTo(User::class , 'landlord_id', 'user_id');
    }

    public function artisan()
    {
        return $this->belongsTo(User::class , 'artisan_id', 'user_id');
    }

    public function tenant()
    {
        return $this->belongsTo(User::class , 'tenant_id', 'user_id');
    }
}