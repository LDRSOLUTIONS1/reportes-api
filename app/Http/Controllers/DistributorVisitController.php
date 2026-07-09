<?php
// app/Http/Controllers/Api/DistributorVisitController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DistributorVisit;
use App\Models\VisitReport;
use Illuminate\Http\Request;

class DistributorVisitController extends Controller
{
    /**
     * POST /api/visit-reports/{visitReport}/distributor-visit
     */
    public function store(Request $request, VisitReport $visitReport)
    {
        abort_if($visitReport->visit_type !== 'distribuidor', 422, 'Este reporte no es de distribuidor.');
        abort_if($visitReport->distributorVisit()->exists(), 422, 'Este reporte ya tiene datos de distribuidor.');

        $validated = $request->validate([
            'distribuidor'           => 'required|string|max:255',
            'plaza'                  => 'nullable|string|max:100',
            'grupo'                  => 'nullable|string|max:100',
            'temas_revisados'        => 'nullable|array',
            'participantes'          => 'nullable|array',
            'comentarios_adicionales' => 'nullable|string',
            // Leads anidados
            'leads'                      => 'nullable|array',
            'leads.*.cliente'            => 'required_with:leads|string|max:255',
            'leads.*.modelo_interes'     => 'nullable|string|max:100',
            'leads.*.porcentaje_avance'  => 'nullable|integer|min:0|max:100',
            'leads.*.comentarios'        => 'nullable|string',
            // Indicadores anidados
            'indicators'                     => 'nullable|array',
            'indicators.*.modelo'            => 'required_with:indicators|string|max:100',
            'indicators.*.bp_2025'           => 'nullable|numeric',
            'indicators.*.whole_ytd'         => 'nullable|numeric',
            'indicators.*.porcentaje_avance' => 'nullable|numeric',
            'indicators.*.retail_ytd'        => 'nullable|numeric',
            'indicators.*.inventario'        => 'nullable|integer',
            'indicators.*.back_order'        => 'nullable|integer',
        ]);

        $distributorVisit = $visitReport->distributorVisit()->create(
            collect($validated)->except(['leads', 'indicators'])->toArray()
        );

        if (!empty($validated['leads'])) {
            $distributorVisit->leads()->createMany($validated['leads']);
        }

        if (!empty($validated['indicators'])) {
            $distributorVisit->commercialIndicators()->createMany($validated['indicators']);
        }

        return response()->json(
            $distributorVisit->load(['leads', 'commercialIndicators']),
            201
        );
    }

    /**
     * PUT /api/visit-reports/{visitReport}/distributor-visit
     */
    public function update(Request $request, VisitReport $visitReport)
    {
        $distributorVisit = $visitReport->distributorVisit;
        abort_if(! $distributorVisit, 404, 'No se encontró la visita de distribuidor.');

        $validated = $request->validate([
            'distribuidor'            => 'sometimes|string|max:255',
            'plaza'                   => 'nullable|string|max:100',
            'grupo'                   => 'nullable|string|max:100',
            'temas_revisados'         => 'nullable|array',
            'participantes'           => 'nullable|array',
            'comentarios_adicionales' => 'nullable|string',
            'leads'                   => 'nullable|array',
            'indicators'              => 'nullable|array',
        ]);

        $distributorVisit->update(
            collect($validated)->except(['leads', 'indicators'])->toArray()
        );

        if (isset($validated['leads'])) {
            $distributorVisit->leads()->delete();
            $distributorVisit->leads()->createMany($validated['leads']);
        }

        if (isset($validated['indicators'])) {
            $distributorVisit->commercialIndicators()->delete();
            $distributorVisit->commercialIndicators()->createMany($validated['indicators']);
        }

        return response()->json(
            $distributorVisit->load(['leads', 'commercialIndicators'])
        );
    }
}
