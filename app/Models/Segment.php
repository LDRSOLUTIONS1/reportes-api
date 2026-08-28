<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Segment extends Model
{
    protected $table = 'segments';

    protected $fillable = [
        'name',
        'description',
        'estado',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
