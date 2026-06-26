<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FollowupAgreement extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_report_id',
        'acuerdo',
        'responsable',
        'fecha_compromiso',
    ];

    protected $casts = [
        'fecha_compromiso' => 'date',
    ];

    // Relaciones
    public function visitReport()
    {
        return $this->belongsTo(VisitReport::class);
    }
}
