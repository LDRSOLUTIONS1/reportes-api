<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClientRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_visit_id',
        'modelo_interes',
        'tipo_carroceria',
        'proyeccion_compra',
        'financiamiento',
        'tiempo_entrega',
        'lugar_entrega',
        'distribuidor',
        'demo',
        'otro',
    ];

    protected $casts = [
        'demo' => 'boolean',
    ];

    // Relaciones
    public function clientVisit()
    {
        return $this->belongsTo(ClientVisit::class);
    }
}
