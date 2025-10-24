<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $table = 'role_user';
    protected $fillable = ['user_id', 'role_id'];
    public $timestamps = true;

    // Backward compatibility: virtual attribute 'role' maps to role_id
    public function getRoleAttribute()
    {
        return $this->role_id;
    }
    public function setRoleAttribute($value)
    {
        $this->attributes['role_id'] = $value;
    }
}
