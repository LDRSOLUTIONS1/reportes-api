<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'role_id',
        'manager_id',
        'segment_id',
        'collaborator_number',
        'external_rh_id',
        'name',
        'email',
        'brand',
        'location_name',
        'password',
        'remember_token',
        'estado',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    //Usuario responsable de este usuario.
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    //Usuarios que están a cargo de este usuario.
    public function subordinates()
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function accessLogs()
    {
        return $this->hasMany(AccessLog::class);
    }

    public function followupAgreementDates()
    {
        return $this->hasMany(FollowupAgreementDate::class);
    }

    public function segment()
    {
        return $this->belongsTo(Segment::class);
    }

    public function scopeActivos($query)
    {
        return $query->whereIn('estado', [1, 2]);
    }
}
