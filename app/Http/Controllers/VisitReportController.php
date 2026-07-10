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

        $visit = VisitReport::create($validated);

        return response()->json([
            'message' => 'Reporte de visita creado correctamente',
            'data'    => $visit
        ], 201);
    }

    public function show($id)
    {
        $visit = VisitReport::select(
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

        $validated = $this->validateVisit($request, $id);

        $visit->update($validated);

        return response()->json([
            'message' => 'Reporte de visita actualizado correctamente',
            'data'    => $visit
        ], 200);
    }

    public function validateVisit(Request $request, $id = null)
    {
        return $request->validate(
            [
                'name' => 'required|string|max:255|unique:roles,name,' . $id,
                'estado' => 'nullable|in:0,1,2',
            ],
            [
                'name.required' => 'El nombre es obligatorio',
                'name.max'      => 'El nombre no puede tener más de 255 caracteres',
                'name.unique'   => 'El nombre ya existe',
                'estado.in'     => 'El estado debe ser 1 (Inactivo) o 2 (Activo).',
            ]
        );
    }
}
