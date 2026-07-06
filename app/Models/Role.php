<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'estado',
    ];


    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'user_roles'
        );
    }

    public function modulePermissions()
    {
        return $this->hasMany(RoleModulePermission::class);
    }
}
