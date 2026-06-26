<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_visit_id',
        'nombre',
        'puesto',
        'email',
        'telefono',
    ];

    // Relaciones
    public function clientVisit()
    {
        return $this->belongsTo(ClientVisit::class);
    }
}
