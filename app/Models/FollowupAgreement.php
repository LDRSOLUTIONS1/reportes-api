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

    // Scopes
    public function scopeActivos($query)
    {
        return $query->whereIn('estado', [1, 2]);
    }

    // Pendientes que YA vencieron, calculado en tiempo real
    public function scopeVencidos($query)
    {
        return $query->where('status', 1)
            ->where('estado', 2)
            ->whereNotNull('fecha_compromiso')
            ->whereDate('fecha_compromiso', '<', Carbon::today());
    }

    // Pendientes que todavía están a tiempo
    public function scopePendientesVigentes($query)
    {
        return $query->where('status', 1)
            ->where('estado', 2)
            ->where(function ($q) {
                $q->whereNull('fecha_compromiso')
                    ->orWhereDate('fecha_compromiso', '>=', Carbon::today());
            });
    }

    // Accessor: para usar en Blade/API como $acuerdo->esta_vencido
    public function getEstaVencidoAttribute(): bool
    {
        return $this->status === 1
            && $this->estado === 2
            && $this->fecha_compromiso !== null
            && $this->fecha_compromiso->lt(Carbon::today());
    }
}
