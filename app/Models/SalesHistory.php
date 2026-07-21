<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalesHistory extends Model
{
    use HasFactory;

    protected $table = 'sales_history';

    protected $fillable = [
        'client_visit_id',
        'anio',
        'cantidad',
    ];

    // Relaciones
    public function clientVisit()
    {
        return $this->belongsTo(ClientVisit::class);
    }
}
