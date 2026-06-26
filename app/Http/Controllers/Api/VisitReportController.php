<?php
// app/Http/Controllers/Api/VisitReportController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VisitReport;
use Illuminate\Http\Request;

class VisitReportController extends Controller
{
    /**
     * GET /api/visit-reports
     * Lista con filtros: tipo, status, fecha, segmento, user_id
     */
    public function index(Request $request)
    {
        $query = VisitReport::with([
            'user:id,name,role,segmento',
            'clientVisit:id,visit_report_id,razon_social,tipo_cliente',
            'distributorVisit:id,visit_report_id,distribuidor,plaza',
        ]);

        if ($request->filled('visit_type')) {
            $query->where('visit_type', $request->visit_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('segmento')) {
            $query->where('segmento', $request->segmento);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_inicio', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_inicio', '<=', $request->fecha_fin);
        }

        // Gte regional solo ve sus propios reportes
        if ($request->user()->role === 'gte_regional') {
            $query->where('user_id', $request->user()->id);
        }

        $reports = $query->orderByDesc('fecha_inicio')->paginate(15);

        return response()->json($reports);
    }

    /**
     * POST /api/visit-reports
     * Crea solo el encabezado del reporte
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'visit_type'        => 'required|in:cliente_directo,distribuidor',
            'tipo_visita'       => 'required|in:presentacion_comercial,capacitacion_operativa,capacitacion_producto,acompanamiento_comercial,operativa,otro',
            'objetivo'          => 'nullable|string|max:500',
            'logros_estrategia' => 'nullable|string',
            'segmento'          => 'nullable|string|max:100',
            'fecha_inicio'      => 'required|date',
            'fecha_fin'         => 'nullable|date|after_or_equal:fecha_inicio',
            'status'            => 'in:borrador,enviado,revisado',
        ]);

        $report = $request->user()->visitReports()->create($validated);

        return response()->json($report, 201);
    }

    /**
     * GET /api/visit-reports/{id}
     * Detalle completo según tipo de visita
     */
    public function show(Request $request, VisitReport $visitReport)
    {
        $this->authorizeAccess($request->user(), $visitReport);

        if ($visitReport->visit_type === 'cliente_directo') {
            $visitReport->load([
                'user:id,name,role,segmento',
                'clientVisit.contacts',
                'clientVisit.fleetInfo',
                'clientVisit.salesHistory',
                'clientVisit.events',
                'clientVisit.requirements',
                'followupAgreements',
                'trainingData',
                'attachments',
            ]);
        } else {
            $visitReport->load([
                'user:id,name,role,segmento',
                'distributorVisit.leads',
                'distributorVisit.commercialIndicators',
                'followupAgreements',
                'trainingData',
                'attachments',
            ]);
        }

        return response()->json($visitReport);
    }

    /**
     * PUT /api/visit-reports/{id}
     * Actualiza encabezado del reporte
     */
    public function update(Request $request, VisitReport $visitReport)
    {
        $this->authorizeAccess($request->user(), $visitReport);

        $validated = $request->validate([
            'tipo_visita'       => 'in:presentacion_comercial,capacitacion_operativa,capacitacion_producto,acompanamiento_comercial,operativa,otro',
            'objetivo'          => 'nullable|string|max:500',
            'logros_estrategia' => 'nullable|string',
            'segmento'          => 'nullable|string|max:100',
            'fecha_inicio'      => 'date',
            'fecha_fin'         => 'nullable|date|after_or_equal:fecha_inicio',
            'status'            => 'in:borrador,enviado,revisado',
        ]);

        $visitReport->update($validated);

        return response()->json($visitReport);
    }

    /**
     * DELETE /api/visit-reports/{id}
     */
    public function destroy(Request $request, VisitReport $visitReport)
    {
        $this->authorizeAccess($request->user(), $visitReport);

        $visitReport->delete();

        return response()->json(['message' => 'Reporte eliminado correctamente.']);
    }

    /**
     * PATCH /api/visit-reports/{id}/submit
     * Enviar reporte (cambiar status a 'enviado')
     */
    public function submit(Request $request, VisitReport $visitReport)
    {
        $this->authorizeAccess($request->user(), $visitReport);

        $visitReport->update(['status' => 'enviado']);

        return response()->json(['message' => 'Reporte enviado correctamente.', 'report' => $visitReport]);
    }

    // Helper: solo admin ve todo; gte_regional solo los suyos
    private function authorizeAccess($user, VisitReport $report): void
    {
        if ($user->role !== 'admin' && $report->user_id !== $user->id) {
            abort(403, 'No tienes permiso para acceder a este reporte.');
        }
    }
}
