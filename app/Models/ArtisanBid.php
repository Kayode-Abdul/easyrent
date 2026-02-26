<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArtisanBid extends Model
{
    protected $fillable = [
        'task_id',
        'artisan_id',
        'amount',
        'duration',
        'proposal',
        'status'
    ];

    public function task()
    {
        return $this->belongsTo(ArtisanTask::class , 'task_id');
    }

    public function artisan()
    {
        return $this->belongsTo(User::class , 'artisan_id', 'user_id');
    }
}