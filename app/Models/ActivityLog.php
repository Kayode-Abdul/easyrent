<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
    ];

    public function user()
    {
        // Use explicit foreign and owner keys to match non-standard PK
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
