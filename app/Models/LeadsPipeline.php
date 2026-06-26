<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeadsPipeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'distributor_visit_id',
        'cliente',
        'modelo_interes',
        'porcentaje_avance',
        'comentarios',
    ];

    // Relaciones
    public function distributorVisit()
    {
        return $this->belongsTo(DistributorVisit::class);
    }
}
