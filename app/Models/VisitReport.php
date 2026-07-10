<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VisitReport extends Model
{
    use HasFactory;

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
        'estado',
        'created_at',
        'updated_at',
    ];

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

    // Scopes
    public function scopeActivos($query)
    {
        return $query->whereIn('estado', [1, 2]);
    }
}
