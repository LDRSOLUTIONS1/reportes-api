<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FollowupAgreementDate extends Model
{
    use HasFactory;

    protected $table = 'followup_agreement_dates';

    protected $fillable = [
        'followup_agreement_id',
        'fecha_compromiso',
        'motivo_reprogramacion',
        'user_id',
        'numero_reprogramacion',
        'estado',
    ];

    protected $casts = [
        'fecha_compromiso' => 'date',
        'numero_reprogramacion' => 'integer',
    ];

    // Relación con el acuerdo
    public function followupAgreement()
    {
        return $this->belongsTo(FollowupAgreement::class);
    }

    // Usuario que realizó la reprogramación
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
