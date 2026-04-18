<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'symbol',
        'name',
        'is_active',
    ];

    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    public function apartments()
    {
        return $this->hasMany(Apartment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function profomaReceipts()
    {
        return $this->hasMany(ProfomaReceipt::class);
    }
}
