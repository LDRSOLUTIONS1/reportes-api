<?php

namespace App\Http\Controllers;

use App\Models\VisitReport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VisitReportController extends Controller
{
    public function index()
    {
        $visits = VisitReport::select(
            'id',
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
        )
            ->activos()
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($visits, 200);
    }

    public function store(Request $request)
    {
        $validated = $this->validateVisit($request);

        $validated['user_id'] = auth()->id();

        $visit = VisitReport::create($validated);

        return response()->json([
            'message' => 'Reporte de visita creado correctamente',
            'data'    => $visit
        ], 201);
    }

    public function show($id)
    {
        $visit = VisitReport::select(
            'id',
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
        )
            ->where('id', $id)
            ->activos()
            ->firstOrFail();

        return response()->json($visit, 200);
    }

    public function update(Request $request, $id)
    {
        $visit = VisitReport::activos()
            ->findOrFail($id);

        $validated = $this->validateVisit($request);

        $visit->update($validated);

        return response()->json([
            'message' => 'Reporte de visita actualizado correctamente',
            'data'    => $visit
        ], 200);
    }

    public function validateVisit(Request $request)
    {
        return $request->validate(
            [
                'visit_type' => 'required|in:cliente_directo,distribuidor',
                'tipo_visita' => 'required|in:presentacion_comercial,capacitacion_operativa,capacitacion_producto,acompanamiento_comercial,operativa,otro',
                'objetivo' => 'nullable|string|max:255',
                'logros_estrategia' => 'nullable|string',
                'segmento' => 'nullable|string|max:255',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
                'estado' => 'nullable|in:0,1,2',
            ],
            [
                'visit_type.required' => 'Debe seleccionar el tipo de visita comercial.',
                'visit_type.in' => 'El tipo de visita comercial seleccionado no es válido.',
                'tipo_visita.required' => 'Debe seleccionar el tipo de visita.',
                'tipo_visita.in' => 'El tipo de visita seleccionado no es válido.',
                'objetivo.max' => 'El objetivo no puede tener más de 255 caracteres.',
                'segmento.max' => 'El segmento no puede tener más de 255 caracteres.',
                'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
                'fecha_inicio.date' => 'La fecha de inicio no es válida.',
                'fecha_fin.date' => 'La fecha de fin no es válida.',
                'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
                'estado.in' => 'El estado debe ser 0 (Eliminado), 1 (Inactivo) o 2 (Activo).',
            ]
        );
    }
}
