<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClientVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_report_id',
        'razon_social',
        'ubicaciones',
        'tamanio_flota',
        'giro',
        'rutas',
        'cobertura',
        'tipo_cliente',
        'edad_promedio_flota',
        'logo_path',
    ];

    // Relaciones
    public function visitReport()
    {
        return $this->belongsTo(VisitReport::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function fleetInfo()
    {
        return $this->hasMany(FleetInfo::class);
    }

    public function salesHistory()
    {
        return $this->hasMany(SalesHistory::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function requirements()
    {
        return $this->hasOne(ClientRequirement::class);
    }
}
