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

    public function editarVisita($id)
    {
        $visit = VisitReport::with([
            'clientVisit:id,visit_report_id,razon_social,ubicaciones,tamanio_flota,giro,rutas,cobertura,tipo_cliente,edad_promedio_flota,logo_path',
            'clientVisit.contacts:id,client_visit_id,nombre,puesto,email,telefono',
            'clientVisit.fleetInfo:id,client_visit_id,marca,modelo,capacidad_carga,cantidad,porcentaje_flota,comentarios_aplicacion',
            'clientVisit.salesHistory:id,client_visit_id,anio,cantidad',
            'clientVisit.events:id,client_visit_id,nombre_evento,tipo',
            'clientVisit.requirements:id,client_visit_id,modelo_interes,tipo_carroceria,proyeccion_compra,financiamiento,tiempo_entrega,lugar_entrega,distribuidor,demo,otro',
            'distributorVisit:id,visit_report_id,distribuidor,plaza,grupo,temas_revisados,participantes,comentarios_adicionales',
            'distributorVisit.leads:id,distributor_visit_id,cliente,modelo_interes,porcentaje_avance,comentarios',
            'distributorVisit.commercialIndicators:id,distributor_visit_id,modelo,bp_2025,whole_ytd,porcentaje_avance,retail_ytd,inventario,back_order',
            'followupAgreements:id,visit_report_id,acuerdo,responsable,fecha_compromiso',
            'trainingData:id,visit_report_id,tipo,tema_principal,num_personas,comentarios',
            'attachments:id,visit_report_id,filename,path,tipo',
        ])->select(
            'id',
            'user_id',
            'visit_type',
            'tipo_visita',
            'objetivo',
            'logros_estrategia',
            'segmento',
            'fecha_inicio',
            'fecha_fin',
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
                'tipo_visita' => 'required|in:presentacion_comercial,capacitacion_operativa,capacitacion_producto,acompanamiento_comercial,operativa,capacitacion_otro',
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
