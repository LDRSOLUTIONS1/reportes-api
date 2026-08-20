<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class FollowupAgreement extends Model
{
    use HasFactory;

    protected $table = 'followup_agreements';

    protected $fillable = [
        'visit_report_id',
        'acuerdo',
        'responsable',
        'seguimiento',
        'fecha_compromiso',
        'status',
        'motivo_cancelacion',
        'completado_at',
        'estado',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'fecha_compromiso' => 'date',
        'completado_at' => 'datetime',
    ];

    protected $appends = ['esta_vencido'];

    // Relaciones
    public function visitReport()
    {
        return $this->belongsTo(VisitReport::class);
    }

    public function dates()
    {
        return $this->hasMany(FollowupAgreementDate::class)
            ->orderBy('numero_reprogramacion')
            ->orderBy('id');
    }

    public function currentDate()
    {
        return $this->hasOne(FollowupAgreementDate::class)
            ->where('estado', 2)
            ->latestOfMany('id');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->whereIn('estado', [1, 2]);
    }

    // Pendientes que YA vencieron, calculado en tiempo real
    public function scopeVencidos($query)
    {
        return $query
            ->where('status', 1)
            ->where('estado', 2)
            ->whereHas('dates', function ($q) {
                $q->where('estado', 2)
                    ->whereDate('fecha_compromiso', '<', Carbon::today());
            });
    }

    // Pendientes que todavía están a tiempo
    public function scopePendientesVigentes($query)
    {
        return $query
            ->where('status', 1)
            ->where('estado', 2)
            ->whereHas('dates', function ($q) {
                $q->where('estado', 2)
                    ->whereDate('fecha_compromiso', '>=', Carbon::today());
            });
    }

    // Accessor: para usar en Blade/API como $acuerdo->esta_vencido
    public function getEstaVencidoAttribute(): bool
    {
        if (
            (int) $this->status !== 1 ||
            (int) $this->estado !== 2
        ) {
            return false;
        }

        $fechaVigente = $this->dates()
            ->where('estado', 2)
            ->latest('id')
            ->first();

        if (!$fechaVigente || !$fechaVigente->fecha_compromiso) {
            return false;
        }

        return $fechaVigente->fecha_compromiso->lt(Carbon::today());
    }
}
