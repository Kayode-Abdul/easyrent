<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleAssignmentAudit extends Model
{
    protected $fillable = [
        'actor_id','user_id','role_id','legacy_role','action','reason','meta'
    ];

    protected $casts = [
        'meta' => 'array'
    ];

    public function actor(){
        return $this->belongsTo(User::class, 'actor_id', 'user_id');
    }
    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
    public function role(){
        return $this->belongsTo(Role::class, 'role_id');
    }
}
