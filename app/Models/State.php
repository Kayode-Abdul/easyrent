<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class State extends Model
{
    protected $fillable = ['name', 'country_name'];

    public function lgas(): HasMany
    {
        return $this->hasMany(Lga::class);
    }
}
