<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    // Many-to-many: users <-> roles through role_user pivot
    public function users()
    {
        // Pivot columns: role_user.role_id, role_user.user_id (users primary key is user_id)
        return $this->belongsToMany(User::class, 'role_user', 'role_id', 'user_id');
    }
}
