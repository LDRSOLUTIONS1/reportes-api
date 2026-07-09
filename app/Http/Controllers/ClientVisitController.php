<?php
// app/Http/Controllers/Api/ClientVisitController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ClientVisit;
use App\Models\VisitReport;
use Illuminate\Http\Request;

class ClientVisitController extends Controller
{
    /**
     * POST /api/visit-reports/{visitReport}/client-visit
     */
    public function store(Request $request, VisitReport $visitReport)
    {
        abort_if($visitReport->visit_type !== 'cliente_directo', 422, 'Este reporte no es de cliente directo.');
        abort_if($visitReport->clientVisit()->exists(), 422, 'Este reporte ya tiene datos de cliente.');

        $validated = $request->validate([
            'razon_social'       => 'required|string|max:255',
            'ubicaciones'        => 'nullable|string|max:255',
            'tamanio_flota'      => 'nullable|string|max:50',
            'giro'               => 'nullable|string|max:100',
            'rutas'              => 'nullable|string|max:255',
            'cobertura'          => 'nullable|string|max:100',
            'tipo_cliente'       => 'nullable|in:conquista,retencion,desarrollo',
            'edad_promedio_flota' => 'nullable|integer|min:0',
            // Contactos anidados
            'contacts'           => 'nullable|array|max:10',
            'contacts.*.nombre'  => 'required_with:contacts|string|max:150',
            'contacts.*.puesto'  => 'nullable|string|max:100',
            'contacts.*.email'   => 'nullable|email',
            'contacts.*.telefono' => 'nullable|string|max:20',
            // Flota anidada
            'fleet'              => 'nullable|array|max:5',
            'fleet.*.marca'      => 'required_with:fleet|string|max:100',
            'fleet.*.modelo'     => 'nullable|string|max:100',
            'fleet.*.capacidad_carga'   => 'nullable|numeric|min:0',
            'fleet.*.cantidad'          => 'nullable|integer|min:0',
            'fleet.*.porcentaje_flota'  => 'nullable|numeric|min:0|max:100',
            'fleet.*.comentarios_aplicacion' => 'nullable|string|max:255',
            // Historial de ventas
            'sales_history'       => 'nullable|array',
            'sales_history.*.anio'     => 'required_with:sales_history|integer|min:2000',
            'sales_history.*.cantidad' => 'required_with:sales_history|integer|min:0',
            // Eventos
            'events'              => 'nullable|array',
            'events.*.nombre_evento' => 'required_with:events|string|max:100',
            'events.*.tipo'          => 'required_with:events|in:asistio,candidato',
            // Requerimientos
            'requirements'             => 'nullable|array',
            'requirements.modelo_interes'   => 'nullable|string|max:100',
            'requirements.tipo_carroceria'  => 'nullable|string|max:100',
            'requirements.proyeccion_compra' => 'nullable|string|max:100',
            'requirements.financiamiento'   => 'nullable|in:credito_casa,arrendamiento,contado,otro',
            'requirements.tiempo_entrega'   => 'nullable|string|max:100',
            'requirements.lugar_entrega'    => 'nullable|string|max:100',
            'requirements.distribuidor'     => 'nullable|string|max:150',
            'requirements.demo'             => 'nullable|boolean',
            'requirements.otro'             => 'nullable|string|max:255',
        ]);

        $clientVisit = $visitReport->clientVisit()->create(
            collect($validated)->except(['contacts', 'fleet', 'sales_history', 'events', 'requirements'])->toArray()
        );

        if (!empty($validated['contacts'])) {
            $clientVisit->contacts()->createMany($validated['contacts']);
        }

        if (!empty($validated['fleet'])) {
            $clientVisit->fleetInfo()->createMany($validated['fleet']);
        }

        if (!empty($validated['sales_history'])) {
            $clientVisit->salesHistory()->createMany($validated['sales_history']);
        }

        if (!empty($validated['events'])) {
            $clientVisit->events()->createMany($validated['events']);
        }

        if (!empty($validated['requirements'])) {
            $clientVisit->requirements()->create($validated['requirements']);
        }

        return response()->json(
            $clientVisit->load(['contacts', 'fleetInfo', 'salesHistory', 'events', 'requirements']),
            201
        );
    }

    /**
     * PUT /api/visit-reports/{visitReport}/client-visit
     * Actualiza datos del cliente (reemplaza relaciones)
     */
    public function update(Request $request, VisitReport $visitReport)
    {
        $clientVisit = $visitReport->clientVisit;
        abort_if(! $clientVisit, 404, 'No se encontró la visita de cliente.');

        $validated = $request->validate([
            'razon_social'        => 'sometimes|string|max:255',
            'ubicaciones'         => 'nullable|string|max:255',
            'tamanio_flota'       => 'nullable|string|max:50',
            'giro'                => 'nullable|string|max:100',
            'rutas'               => 'nullable|string|max:255',
            'cobertura'           => 'nullable|string|max:100',
            'tipo_cliente'        => 'nullable|in:conquista,retencion,desarrollo',
            'edad_promedio_flota' => 'nullable|integer|min:0',
            'contacts'            => 'nullable|array',
            'fleet'               => 'nullable|array',
            'sales_history'       => 'nullable|array',
            'events'              => 'nullable|array',
            'requirements'        => 'nullable|array',
        ]);

        $clientVisit->update(
            collect($validated)->except(['contacts', 'fleet', 'sales_history', 'events', 'requirements'])->toArray()
        );

        // Sincronizar relaciones (borrar y recrear)
        if (isset($validated['contacts'])) {
            $clientVisit->contacts()->delete();
            $clientVisit->contacts()->createMany($validated['contacts']);
        }

        if (isset($validated['fleet'])) {
            $clientVisit->fleetInfo()->delete();
            $clientVisit->fleetInfo()->createMany($validated['fleet']);
        }

        if (isset($validated['sales_history'])) {
            $clientVisit->salesHistory()->delete();
            $clientVisit->salesHistory()->createMany($validated['sales_history']);
        }

        if (isset($validated['events'])) {
            $clientVisit->events()->delete();
            $clientVisit->events()->createMany($validated['events']);
        }

        if (isset($validated['requirements'])) {
            $clientVisit->requirements()->updateOrCreate(
                ['client_visit_id' => $clientVisit->id],
                $validated['requirements']
            );
        }

        return response()->json(
            $clientVisit->load(['contacts', 'fleetInfo', 'salesHistory', 'events', 'requirements'])
        );
    }
}
