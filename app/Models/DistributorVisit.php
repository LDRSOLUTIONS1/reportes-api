<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DistributorVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_report_id',
        'distribuidor',
        'plaza',
        'grupo',
        'temas_revisados',
        'participantes',
        'comentarios_adicionales',
    ];

    protected $casts = [
        'temas_revisados' => 'array',
        'participantes'   => 'array',
    ];

    // Relaciones
    public function visitReport()
    {
        return $this->belongsTo(VisitReport::class);
    }

    public function leads()
    {
        return $this->hasMany(LeadsPipeline::class);
    }

    public function commercialIndicators()
    {
        return $this->hasMany(CommercialIndicator::class);
    }
}
