<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class VisitReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'visit_type',
        'tipo_visita',
        'objetivo',
        'logros_estrategia',
        'segmento',
        'fecha_inicio',
        'fecha_fin',
        'status',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clientVisit()
    {
        return $this->hasOne(ClientVisit::class);
    }

    public function distributorVisit()
    {
        return $this->hasOne(DistributorVisit::class);
    }

    public function followupAgreements()
    {
        return $this->hasMany(FollowupAgreement::class);
    }

    public function trainingData()
    {
        return $this->hasOne(TrainingData::class);
    }

    public function attachments()
    {
        return $this->hasMany(VisitAttachment::class);
    }

    // Helper: retorna la visita específica según tipo
    public function getVisitDetailAttribute()
    {
        return $this->visit_type === 'cliente_directo'
            ? $this->clientVisit
            : $this->distributorVisit;
    }

    // Scopes útiles
    public function scopeClienteDirecto($query)
    {
        return $query->where('visit_type', 'cliente_directo');
    }

    public function scopeDistribuidor($query)
    {
        return $query->where('visit_type', 'distribuidor');
    }

    public function scopeEnviados($query)
    {
        return $query->where('status', 'enviado');
    }
}
