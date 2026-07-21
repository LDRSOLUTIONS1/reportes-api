<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events';

    protected $fillable = [
        'client_visit_id',
        'nombre_evento',
        'tipo',
    ];

    // Relaciones
    public function clientVisit()
    {
        return $this->belongsTo(ClientVisit::class);
    }
}
