<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FleetInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_visit_id',
        'marca',
        'modelo',
        'capacidad_carga',
        'cantidad',
        'porcentaje_flota',
        'comentarios_aplicacion',
    ];

    protected $casts = [
        'capacidad_carga'  => 'decimal:2',
        'porcentaje_flota' => 'decimal:2',
    ];

    // Relaciones
    public function clientVisit()
    {
        return $this->belongsTo(ClientVisit::class);
    }
}
