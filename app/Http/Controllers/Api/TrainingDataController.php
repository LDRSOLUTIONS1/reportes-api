<?php
// app/Http/Controllers/Api/TrainingDataController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrainingData;
use App\Models\VisitReport;
use Illuminate\Http\Request;

class TrainingDataController extends Controller
{
    /**
     * POST /api/visit-reports/{visitReport}/training
     */
    public function store(Request $request, VisitReport $visitReport)
    {
        abort_if($visitReport->trainingData()->exists(), 422, 'Este reporte ya tiene datos de capacitación.');

        $validated = $request->validate([
            'tipo'           => 'nullable|in:tecnica,comercial,operativa',
            'tema_principal' => 'nullable|string|max:255',
            'num_personas'   => 'nullable|integer|min:0',
            'comentarios'    => 'nullable|string',
        ]);

        $training = $visitReport->trainingData()->create($validated);

        return response()->json($training, 201);
    }

    /**
     * PUT /api/visit-reports/{visitReport}/training
     */
    public function update(Request $request, VisitReport $visitReport)
    {
        $training = $visitReport->trainingData;
        abort_if(! $training, 404, 'No se encontraron datos de capacitación.');

        $validated = $request->validate([
            'tipo'           => 'nullable|in:tecnica,comercial,operativa',
            'tema_principal' => 'nullable|string|max:255',
            'num_personas'   => 'nullable|integer|min:0',
            'comentarios'    => 'nullable|string',
        ]);

        $training->update($validated);

        return response()->json($training);
    }
}
