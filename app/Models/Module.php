<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'title',
        'segment',
        'icon',
        'order',
        'estado',
    ];

    protected $casts = [
        'estado' => 'integer',
        'order'  => 'integer',
    ];

    public function parent()
    {
        return $this->belongsTo(Module::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Module::class, 'parent_id')
            ->where('estado', 1)
            ->orderBy('order');
    }

    public function permissions()
    {
        return $this->hasMany(RoleModulePermission::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }

    public function scopeRaiz($query)
    {
        return $query->whereNull('parent_id');
    }
}
