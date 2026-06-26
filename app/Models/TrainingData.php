<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrainingData extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_report_id',
        'tipo',
        'tema_principal',
        'num_personas',
        'comentarios',
    ];

    // Relaciones
    public function visitReport()
    {
        return $this->belongsTo(VisitReport::class);
    }
}
