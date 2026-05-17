<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArtisanRating extends Model
{
    protected $fillable = [
        'artisan_id',
        'user_id',
        'task_id',
        'rating',
        'comment',
    ];

    public function artisan()
    {
        return $this->belongsTo(User::class, 'artisan_id', 'user_id');
    }

    public function rater()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function task()
    {
        return $this->belongsTo(ArtisanTask::class, 'task_id');
    }
}
