<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleChangeNotification extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'admin_id',
        'user_id',
        'old_role',
        'new_role',
        'timestamp',
        'ip_address',
    ];
    
    /**
     * Get the admin who made the change.
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id', 'user_id');
    }
    
    /**
     * Get the user whose role was changed.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
