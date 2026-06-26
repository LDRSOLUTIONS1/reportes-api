<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommercialIndicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'distributor_visit_id',
        'modelo',
        'bp_2025',
        'whole_ytd',
        'porcentaje_avance',
        'retail_ytd',
        'inventario',
        'back_order',
    ];

    protected $casts = [
        'bp_2025'           => 'decimal:2',
        'whole_ytd'         => 'decimal:2',
        'porcentaje_avance' => 'decimal:2',
        'retail_ytd'        => 'decimal:2',
    ];

    // Relaciones
    public function distributorVisit()
    {
        return $this->belongsTo(DistributorVisit::class);
    }
}
