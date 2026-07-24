<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VisitAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_report_id',
        'filename',
        'path',
        'tipo',
    ];

    protected $appends = ['url'];

    // Relaciones
    public function visitReport()
    {
        return $this->belongsTo(VisitReport::class);
    }

    // Helper: URL pública del archivo
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }
}
