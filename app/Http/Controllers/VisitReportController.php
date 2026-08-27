<?php

namespace App\Http\Controllers;

use App\Models\VisitReport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ClientVisit;
use App\Models\User;
use App\Services\AgreementReminderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Barryvdh\DomPDF\Facade\Pdf;

class VisitReportController extends Controller
{
    /**
     * Rutas de archivos subidos durante el request actual.
     * Si la transacción falla, se usan para limpiar storage.
     */
    private array $uploadedFilesForCleanup = [];

    public function index()
    {
        $user = auth()->user();

        $query = VisitReport::with([
            'user:id,name,email',
            'clientVisit:id,visit_report_id,razon_social',
            'distributorVisit:id,visit_report_id,distribuidor,plaza,grupo',
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
            'status',
            'estado',
            'created_at',
        );

        // Super Administrador / Administrador
        if (in_array($user->role_id, [1, 2, 4])) {

            return response()->json(
                $query
                    ->activos()
                    ->orderBy('id', 'desc')
                    ->get(),
                200
            );
        }

        // El usuario tiene subordinados → es Manager
        $subordinateIds = User::where('manager_id', $user->id)
            ->pluck('id');

        if ($subordinateIds->isNotEmpty()) {

            $userIds = $subordinateIds
                ->push($user->id)
                ->unique();

            return response()->json(
                $query
                    ->whereIn('user_id', $userIds)
                    ->activos()
                    ->orderBy('id', 'desc')
                    ->get(),
                200
            );
        }

        // El usuario pertenece a un Manager
        if (!is_null($user->manager_id)) {

            return response()->json(
                $query
                    ->whereIn('user_id', [
                        $user->id,
                        $user->manager_id,
                    ])
                    ->activos()
                    ->orderBy('id', 'desc')
                    ->get(),
                200
            );
        }

        // Usuario sin subordinados y sin Manager
        return response()->json(
            $query
                ->where('user_id', $user->id)
                ->activos()
                ->orderBy('id', 'desc')
                ->get(),
            200
        );
    }

    public function show($id)
    {
        $visit = VisitReport::with([
            'user:id,name,email',
            'clientVisit:id,visit_report_id,razon_social,ubicaciones,tamanio_flota,giro,rutas,cobertura,tipo_cliente,edad_promedio_flota,logo_path',
            'clientVisit.contacts:id,client_visit_id,nombre,puesto,email,telefono',
            'clientVisit.fleetInfo:id,client_visit_id,marca,modelo,capacidad_carga,cantidad,porcentaje_flota,comentarios_aplicacion',
            'clientVisit.salesHistory:id,client_visit_id,anio,cantidad',
            'clientVisit.events:id,client_visit_id,nombre_evento,otro_evento,tipo',
            'clientVisit.requirements:id,client_visit_id,modelo_interes,tipo_carroceria,proyeccion_compra,financiamiento,tiempo_entrega,lugar_entrega,distribuidor,demo,otro',
            'distributorVisit:id,visit_report_id,distribuidor,plaza,grupo,temas_revisados,participantes,comentarios_adicionales',
            'distributorVisit.leads:id,distributor_visit_id,cliente,modelo_interes,porcentaje_avance,comentarios',
            'distributorVisit.commercialIndicators:id,distributor_visit_id,modelo,bp_2025,whole_ytd,porcentaje_avance,retail_ytd,inventario,back_order',
            'followupAgreements:id,visit_report_id,acuerdo,responsable,seguimiento,fecha_compromiso,status,motivo_cancelacion,completado_at,estado',
            'followupAgreements.dates' => function ($q) {
                $q->with('user:id,name')->orderBy('numero_reprogramacion')->orderBy('id');
            },

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

    public function getEditVisit($id)
    {
        $visit = VisitReport::with([
            'clientVisit:id,visit_report_id,razon_social,ubicaciones,tamanio_flota,giro,rutas,cobertura,tipo_cliente,edad_promedio_flota,logo_path',
            'clientVisit.contacts:id,client_visit_id,nombre,puesto,email,telefono',
            'clientVisit.fleetInfo:id,client_visit_id,marca,modelo,capacidad_carga,cantidad,porcentaje_flota,comentarios_aplicacion',
            'clientVisit.salesHistory:id,client_visit_id,anio,cantidad',
            'clientVisit.events:id,client_visit_id,nombre_evento,otro_evento,tipo',
            'clientVisit.requirements:id,client_visit_id,modelo_interes,tipo_carroceria,proyeccion_compra,financiamiento,tiempo_entrega,lugar_entrega,distribuidor,demo,otro',
            'distributorVisit:id,visit_report_id,distribuidor,plaza,grupo,temas_revisados,participantes,comentarios_adicionales',
            'distributorVisit.leads:id,distributor_visit_id,cliente,modelo_interes,porcentaje_avance,comentarios',
            'distributorVisit.commercialIndicators:id,distributor_visit_id,modelo,bp_2025,whole_ytd,porcentaje_avance,retail_ytd,inventario,back_order',
            'followupAgreements:id,visit_report_id,acuerdo,responsable,seguimiento,fecha_compromiso,status,motivo_cancelacion,completado_at,estado',
            'followupAgreements.dates' => function ($q) {
                $q->with('user:id,name')->orderBy('numero_reprogramacion')->orderBy('id');
            },

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

    public function store(Request $request)
    {
        $validated = $this->validateVisit($request);
        $validated['user_id'] = Auth::id();

        try {
            $visit = DB::transaction(function () use ($validated, $request) {
                $visit = VisitReport::create($validated);
                $this->saveVisitDetails($visit, $request);
                return $visit;
            });
        } catch (\Throwable $e) {
            $this->cleanupUploadedFiles();
            throw $e;
        }

        return response()->json([
            'message' => 'Reporte de visita creado correctamente',
            'data'    => $visit
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $visit = VisitReport::activos()->findOrFail($id);

        $validated = $this->validateVisit($request);

        try {
            DB::transaction(function () use ($visit, $validated, $request) {
                $visit->update($validated);
                $this->saveVisitDetails($visit, $request);
            });
        } catch (\Throwable $e) {
            $this->cleanupUploadedFiles();
            throw $e;
        }

        return response()->json([
            'message' => 'Reporte de visita actualizado correctamente',
            'data'    => $visit
        ], 200);
    }

    /**
     * Borra del storage cualquier archivo subido durante este request
     * si la transacción de BD terminó fallando (evita huérfanos en disco).
     */
    private function cleanupUploadedFiles()
    {
        foreach ($this->uploadedFilesForCleanup as $path) {
            Storage::disk('public')->delete($path);
        }

        $this->uploadedFilesForCleanup = [];
    }

    private function saveVisitDetails(VisitReport $visit, Request $request)
    {
        if ($visit->visit_type === 'cliente_directo') {
            $this->saveClientDirectoDetails($visit, $request);
        }

        if ($visit->visit_type === 'distribuidor') {
            $this->saveDistribuidorDetails($visit, $request);
        }

        $agreements = $this->validateFollowupAgreements(
            $this->getFollowupAgreements($request)
        );

        $this->syncFollowupAgreements(
            $visit,
            $agreements
        );

        $trainingData = $this->validateTrainingData($request);
        $visit->trainingData()->updateOrCreate(
            ['visit_report_id' => $visit->id],
            $trainingData
        );

        $this->validateAttachments($request);
        $this->syncAttachments($visit, $request);
    }

    private function syncFollowupAgreements(
        VisitReport $visit,
        array $agreements
    ) {
        foreach ($agreements as $agreement) {
            if (!empty($agreement['id'])) {

                $existing = $visit->followupAgreements()
                    ->where('id', $agreement['id'])
                    ->first();
                if ($existing) {

                    $existing->update([
                        'acuerdo'          => $agreement['acuerdo'],
                        'responsable'      => $agreement['responsable'],
                        'seguimiento'      => $agreement['seguimiento'],
                    ]);
                }
                continue;
            }

            $newAgreement = $visit->followupAgreements()->create([
                'acuerdo'          => $agreement['acuerdo'],
                'responsable'      => $agreement['responsable'],
                'seguimiento'      => $agreement['seguimiento'],
                'fecha_compromiso' => $agreement['fecha_compromiso'],
                'status'           => 1,
                'completado_at'    => null,
            ]);

            $date = $newAgreement->dates()->create([
                'fecha_compromiso'          => $agreement['fecha_compromiso'],
                'motivo_reprogramacion'     => null,
                'user_id'                   => Auth::id(),
                'numero_reprogramacion'     => 0,
                'recordatorio_enviado_at'   => null,
                'estado'                    => 2,
            ]);

            AgreementReminderService::schedule($date);
        }
    }

    private function saveClientDirectoDetails(VisitReport $visit, Request $request)
    {
        $clientData   = $this->validateClientVisit($request);
        $contacts     = $this->validateContacts($this->getContacts($request));
        $fleet        = $this->validateFleetInfo($this->getFleetInfo($request));
        $history      = $this->validateSalesHistory($this->getSalesHistory($request));
        $requirements = $this->validateRequirements($request);
        $requirements['demo'] = $requirements['demo'] === 'si';

        $logoPath = $this->uploadLogo($request);
        if ($logoPath) {
            if ($visit->clientVisit && $visit->clientVisit->logo_path) {
                Storage::disk('public')->delete($visit->clientVisit->logo_path);
            }

            $clientData['logo_path'] = $logoPath;
            $this->uploadedFilesForCleanup[] = $logoPath;
        }

        $clientVisit = $visit->clientVisit()->updateOrCreate(
            ['visit_report_id' => $visit->id],
            $clientData
        );

        $this->syncChildren($clientVisit->contacts(), $contacts, fn($c) => [
            'nombre'   => $c['nombre'],
            'puesto'   => $c['puesto'],
            'email'    => $c['email'] ?? null,
            'telefono' => $c['telefono'],
        ]);

        $this->syncChildren($clientVisit->fleetInfo(), $fleet, fn($f) => [
            'marca'                  => $f['marca'],
            'modelo'                 => $f['modelo'],
            'capacidad_carga'        => $f['capacidad_carga'],
            'cantidad'               => $f['cantidad'],
            'porcentaje_flota'       => $f['porcentaje_flota'] ?? null,
            'comentarios_aplicacion' => $f['comentarios_aplicacion'] ?? null,
        ]);

        $this->syncChildren($clientVisit->salesHistory(), $history, fn($h) => [
            'anio'     => $h['anio'],
            'cantidad' => $h['cantidad'],
        ]);

        $this->syncEvents($clientVisit, $request);

        $clientVisit->requirements()->updateOrCreate(
            ['client_visit_id' => $clientVisit->id],
            $requirements
        );
    }

    private function saveDistribuidorDetails(VisitReport $visit, Request $request)
    {
        $distributorData = $this->validateDistributorVisit($request);

        $distributorVisit = $visit->distributorVisit()->updateOrCreate(
            ['visit_report_id' => $visit->id],
            $distributorData
        );

        $leads = $this->validateLeads($this->getLeads($request));
        $this->syncChildren($distributorVisit->leads(), $leads, fn($l) => [
            'cliente'           => $l['cliente'],
            'modelo_interes'    => $l['modelo_interes'] ?? null,
            'porcentaje_avance' => $l['porcentaje_avance'] ?? null,
            'comentarios'       => $l['comentarios'] ?? null,
        ]);

        $indicators = $this->validateCommercialIndicators($this->getCommercialIndicators($request));
        $this->syncChildren($distributorVisit->commercialIndicators(), $indicators, fn($i) => [
            'modelo'            => $i['modelo'],
            'bp_2025'           => $i['bp_2025'] ?? null,
            'whole_ytd'         => $i['whole_ytd'] ?? null,
            'porcentaje_avance' => $i['porcentaje_avance'] ?? null,
            'retail_ytd'        => $i['retail_ytd'] ?? null,
            'inventario'        => $i['inventario'] ?? null,
            'back_order'        => $i['back_order'] ?? null,
        ]);
    }

    /**
     * Sincroniza una colección hija identificada por 'id':
     * borra los que ya no vienen en $items y crea/actualiza el resto.
     * Sirve tanto para "store" (no hay nada que borrar) como para "update".
     */
    private function syncChildren(HasMany $relation, array $items, callable $mapper)
    {
        $ids = collect($items)->pluck('id')->filter()->toArray();

        $relation->whereNotIn('id', $ids)->delete();

        foreach ($items as $item) {
            $relation->updateOrCreate(
                ['id' => $item['id'] ?? null],
                $mapper($item)
            );
        }
    }

    private function syncEvents(ClientVisit $clientVisit, Request $request)
    {
        $eventos = [];

        $eventosAsistio = json_decode(
            $request->input('eventos_asistio', '{}'),
            true
        ) ?? [];

        foreach ($eventosAsistio as $nombre => $checked) {
            if ($checked) {
                $eventos[] = [
                    'nombre_evento' => $nombre,
                    'otro_evento'   => null,
                    'tipo'          => 'asistio',
                ];
            }
        }

        if ($request->filled('eventos_asistio_otro')) {
            $eventos[] = [
                'nombre_evento' => 'otro',
                'otro_evento'   => $request->input('eventos_asistio_otro'),
                'tipo'          => 'asistio',
            ];
        }


        $eventosCandidato = json_decode(
            $request->input('eventos_candidato', '{}'),
            true
        ) ?? [];

        foreach ($eventosCandidato as $nombre => $checked) {
            if ($checked) {
                $eventos[] = [
                    'nombre_evento' => $nombre,
                    'otro_evento'   => null,
                    'tipo'          => 'candidato',
                ];
            }
        }

        if ($request->filled('eventos_candidato_otro')) {
            $eventos[] = [
                'nombre_evento' => 'otro',
                'otro_evento'   => $request->input('eventos_candidato_otro'),
                'tipo'          => 'candidato',
            ];
        }


        $clientVisit->events()->delete();

        if (!empty($eventos)) {
            $clientVisit->events()->createMany($eventos);
        }
    }

    private function syncAttachments(VisitReport $visit, Request $request)
    {
        $existentes = $request->evidencias_existentes ?? [];

        $visit->attachments()
            ->whereNotIn('id', $existentes)
            ->get()
            ->each(function ($attachment) {
                Storage::disk('public')->delete($attachment->path);
                $attachment->delete();
            });

        if (!$request->hasFile('evidencias')) {
            return;
        }

        foreach ($request->file('evidencias') as $file) {
            $path = $file->store('documents', 'public');
            $this->uploadedFilesForCleanup[] = $path;

            $visit->attachments()->create([
                'filename' => $file->getClientOriginalName(),
                'path'     => $path,
                'tipo'     => str_starts_with($file->getMimeType(), 'image/') ? 'foto' : 'anexo',
            ]);
        }
    }

    private function uploadLogo(Request $request)
    {
        if (!$request->hasFile('logo_file')) {
            return null;
        }

        return $request->file('logo_file')->store('documents', 'public');
    }

    private function getContacts(Request $request)
    {
        return json_decode($request->contactos, true) ?? [];
    }

    private function getFleetInfo(Request $request)
    {
        return json_decode($request->fleet_info, true) ?? [];
    }

    private function getSalesHistory(Request $request)
    {
        return json_decode($request->sales_history, true) ?? [];
    }

    private function getFollowupAgreements(Request $request)
    {
        return json_decode($request->followup_agreements, true) ?? [];
    }

    private function getParticipantes(Request $request)
    {
        return json_decode($request->participantes, true) ?? [];
    }

    private function getTemasRevisados(Request $request)
    {
        $temas = json_decode(
            $request->input('temas_revisados', '{}'),
            true
        ) ?? [];

        $resultado = [];

        foreach ($temas as $tema => $checked) {

            if (!$checked) {
                continue;
            }

            if ($tema === 'otros') {

                $otro = trim(
                    (string) $request->input('temas_revisados_otro', '')
                );

                if ($otro !== '') {
                    $resultado[] = $otro;
                }

                continue;
            }

            $resultado[] = $tema;
        }

        return $resultado;
    }

    private function getLeads(Request $request)
    {
        return json_decode($request->leads, true) ?? [];
    }

    private function getCommercialIndicators(Request $request)
    {
        return json_decode($request->commercial_indicators, true) ?? [];
    }

    public function validateVisit(Request $request)
    {
        return $request->validate(
            [
                'visit_type'        => 'required|in:cliente_directo,distribuidor',
                'tipo_visita'       => 'required|in:presentacion_comercial,capacitacion_operativa,capacitacion_producto,acompanamiento_comercial,operativa,capacitacion,otro',
                'objetivo'          => 'nullable|string|max:255',
                'logros_estrategia' => 'nullable|string',
                'segmento'          => 'nullable|string|max:255',
                'fecha_inicio'      => 'required|date',
                'fecha_fin'         => 'nullable|date|after_or_equal:fecha_inicio',
                'estado'            => 'nullable|in:0,1,2',
            ],
            [
                'visit_type.required'       => 'Debe seleccionar el tipo de visita comercial.',
                'visit_type.in'             => 'El tipo de visita comercial seleccionado no es válido.',
                'tipo_visita.required'      => 'Debe seleccionar el tipo de visita.',
                'tipo_visita.in'            => 'El tipo de visita seleccionado no es válido.',
                'objetivo.max'              => 'El objetivo no puede tener más de 255 caracteres.',
                'segmento.max'              => 'El segmento no puede tener más de 255 caracteres.',
                'fecha_inicio.required'     => 'La fecha de inicio es obligatoria.',
                'fecha_inicio.date'         => 'La fecha de inicio no es válida.',
                'fecha_fin.date'            => 'La fecha de fin no es válida.',
                'fecha_fin.after_or_equal'  => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
                'estado.in'                 => 'El estado debe ser 0 (Eliminado), 1 (Inactivo) o 2 (Activo).',
            ]
        );
    }

    private function validateClientVisit(Request $request)
    {
        return $request->validate(
            [
                'razon_social'         => 'required|string|max:255',
                'ubicaciones'          => 'nullable|string|max:255',
                'tamanio_flota'        => 'nullable|string|max:255',
                'giro'                 => 'nullable|string|max:255',
                'rutas'                => 'nullable|string|max:255',
                'cobertura'            => 'nullable|string|max:255',
                'tipo_cliente'         => 'nullable|string|max:255',
                'edad_promedio_flota'  => 'nullable|integer|min:0',
                'logo_file'            => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ],
            [
                'razon_social.required'        => 'La razón social es obligatoria.',
                'edad_promedio_flota.integer'  => 'La edad promedio de la flota debe ser un número.',
                'logo_file.image'              => 'El logo debe ser una imagen.',
                'logo_file.mimes'              => 'El logo debe ser JPG, JPEG o PNG.',
                'logo_file.max'                => 'El logo no puede ser mayor a 2MB.',
            ]
        );
    }

    private function validateContacts(array $contacts)
    {
        return Validator::make(
            ['contactos' => $contacts],
            [
                'contactos'            => 'array',
                'contactos.*.nombre'   => 'required|string|max:255',
                'contactos.*.puesto'   => 'required|string|max:255',
                'contactos.*.email'    => 'nullable|email|max:255',
                'contactos.*.telefono' => 'required|string|max:255',
            ]
        )->validate()['contactos'];
    }

    private function validateFleetInfo(array $fleet)
    {
        return Validator::make(
            ['fleet_info' => $fleet],
            [
                'fleet_info'                           => 'array',
                'fleet_info.*.marca'                   => 'required|string|max:255',
                'fleet_info.*.modelo'                  => 'required|string|max:255',
                'fleet_info.*.capacidad_carga'         => 'required|numeric|min:0',
                'fleet_info.*.cantidad'                => 'required|integer|min:1',
                'fleet_info.*.porcentaje_flota'        => 'nullable|numeric|min:0|max:100',
                'fleet_info.*.comentarios_aplicacion'  => 'nullable|string|max:255',
            ]
        )->validate()['fleet_info'];
    }

    private function validateSalesHistory(array $history)
    {
        return Validator::make(
            ['sales_history' => $history],
            [
                'sales_history'            => 'array',
                'sales_history.*.anio'     => 'required|integer|min:2000',
                'sales_history.*.cantidad' => 'required|integer|min:0',
            ]
        )->validate()['sales_history'];
    }

    private function validateRequirements(Request $request)
    {
        return $request->validate(
            [
                'modelo_interes'      => 'required|string|max:255',
                'tipo_carroceria'     => 'required|string|max:255',
                'proyeccion_compra'   => 'required|string|max:255',
                'financiamiento'      => 'required|string|max:255',
                'tiempo_entrega'      => 'required|string|max:255',
                'lugar_entrega'       => 'required|string|max:255',
                'distribuidor'        => 'required|string|max:255',
                'demo'                => 'required|in:si,no',
                'otro'                => 'nullable|string|max:255',
            ],
            [
                'modelo_interes.required'      => 'El modelo de interés es obligatorio.',
                'tipo_carroceria.required'     => 'El tipo de carrocería es obligatorio.',
                'proyeccion_compra.required'   => 'La proyección de compra   es obligatoria.',
                'financiamiento.required'      => 'El financiamiento es obligatorio.',
                'tiempo_entrega.required'      => 'El tiempo de entrega es obligatorio.',
                'lugar_entrega.required'       => 'El lugar de entrega es obligatorio.',
                'distribuidor.required'        => 'Debe seleccionar un distribuidor.',
                'demo.required'                => 'Debe seleccionar una opción.',
                'demo.in'                      => 'El valor de demo no es válido.',
            ]
        );
    }

    private function validateFollowupAgreements(array $agreements)
    {
        return Validator::make(
            ['followup_agreements' => $agreements],
            [
                'followup_agreements'                    => 'array',
                'followup_agreements.*.id'               => 'nullable|integer',
                'followup_agreements.*.acuerdo'          => 'required|string|max:255',
                'followup_agreements.*.responsable'      => 'required|string|max:255',
                'followup_agreements.*.seguimiento'      => 'required|string|max:255',
                'followup_agreements.*.fecha_compromiso' => 'required|date',
            ],
            [
                'followup_agreements.*.acuerdo.required'          => 'El acuerdo es obligatorio.',
                'followup_agreements.*.responsable.required'      => 'El responsable es obligatorio.',
                'followup_agreements.*.seguimiento.required'      => 'El seguimiento es obligatorio.',
                'followup_agreements.*.fecha_compromiso.required' => 'La fecha compromiso es obligatoria.',
                'followup_agreements.*.fecha_compromiso.date'     => 'La fecha compromiso no es válida.',
            ]
        )->validate()['followup_agreements'];
    }

    private function validateTrainingData(Request $request)
    {
        return $request->validate(
            [
                'tipo'           => 'required|string|max:255',
                'tema_principal' => 'required|string|max:255',
                'num_personas'   => 'required|integer|min:1',
                'comentarios'    => 'required|string|max:255',
            ],
            [
                'tipo.required'           => 'El tipo es obligatorio.',
                'tema_principal.required' => 'El tema principal es obligatorio.',
                'num_personas.required'  => 'El número de personas es obligatorio.',
                'num_personas.integer'   => 'El número de personas debe ser un número entero.',
                'num_personas.min'       => 'Debe ser al menos 1.',
                'comentarios.required'   => 'Los comentarios son obligatorios.',
            ]
        );
    }

    private function validateAttachments(Request $request)
    {
        return $request->validate(
            [
                'evidencias'            => 'nullable|array|max:10',
                'evidencias.*'          => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
                'evidencias_existentes' => 'nullable|array',
            ],
            [
                'evidencias.array'   => 'Las evidencias deben ser un array.',
                'evidencias.*.file'  => 'Las evidencias deben ser archivos.',
                'evidencias.*.mimes' => 'Las evidencias deben ser de tipo JPG, JPEG, PNG, PDF, DOC o DOCX.',
                'evidencias.*.max'   => 'Las evidencias no pueden superar los 10MB.',
            ]
        );
    }

    private function validateDistributorVisit(Request $request)
    {
        $data = $request->validate([
            'distribuidor'            => 'required|string|max:255',
            'plaza'                   => 'required|string|max:255',
            'grupo'                   => 'required|string|max:255',
            'comentarios_adicionales' => 'nullable|string',
            'temas_revisados_otro'    => 'nullable|string|max:255',
        ]);

        $data['participantes'] = $this->validateParticipantes($this->getParticipantes($request));
        $data['temas_revisados'] = $this->getTemasRevisados($request);

        return $data;
    }

    private function validateParticipantes(array $participantes)
    {
        return Validator::make(
            ['participantes' => $participantes],
            [
                'participantes'          => 'array',
                'participantes.*.nombre' => 'required|string|max:255',
            ]
        )->validate()['participantes'];
    }

    private function validateLeads(array $leads)
    {
        return Validator::make(
            ['leads' => $leads],
            [
                'leads'                     => 'array',
                'leads.*.cliente'           => 'required|string|max:255',
                'leads.*.modelo_interes'    => 'nullable|string|max:255',
                'leads.*.porcentaje_avance' => 'nullable|numeric|min:0|max:100',
                'leads.*.comentarios'       => 'nullable|string',
            ]
        )->validate()['leads'];
    }

    private function validateCommercialIndicators(array $indicators)
    {
        return Validator::make(
            ['commercial_indicators' => $indicators],
            [
                'commercial_indicators'                     => 'array',
                'commercial_indicators.*.modelo'            => 'required|string|max:255',
                'commercial_indicators.*.bp_2025'           => 'nullable|numeric|min:0',
                'commercial_indicators.*.whole_ytd'         => 'nullable|numeric|min:0',
                'commercial_indicators.*.porcentaje_avance' => 'nullable|numeric|min:0|max:100',
                'commercial_indicators.*.retail_ytd'        => 'nullable|numeric|min:0',
                'commercial_indicators.*.inventario'        => 'nullable|integer|min:0',
                'commercial_indicators.*.back_order'        => 'nullable|integer|min:0',
            ]
        )->validate()['commercial_indicators'];
    }

    public function pdf($id)
    {
        $visit = VisitReport::with([
            'user:id,name,email',
            'clientVisit:id,visit_report_id,razon_social,ubicaciones,tamanio_flota,giro,rutas,cobertura,tipo_cliente,edad_promedio_flota,logo_path',
            'clientVisit.contacts:id,client_visit_id,nombre,puesto,email,telefono',
            'clientVisit.fleetInfo:id,client_visit_id,marca,modelo,capacidad_carga,cantidad,porcentaje_flota,comentarios_aplicacion',
            'clientVisit.salesHistory:id,client_visit_id,anio,cantidad',
            'clientVisit.events:id,client_visit_id,nombre_evento,otro_evento,tipo',
            'clientVisit.requirements:id,client_visit_id,modelo_interes,tipo_carroceria,proyeccion_compra,financiamiento,tiempo_entrega,lugar_entrega,distribuidor,demo,otro',
            'distributorVisit:id,visit_report_id,distribuidor,plaza,grupo,temas_revisados,participantes,comentarios_adicionales',
            'distributorVisit.leads:id,distributor_visit_id,cliente,modelo_interes,porcentaje_avance,comentarios',
            'distributorVisit.commercialIndicators:id,distributor_visit_id,modelo,bp_2025,whole_ytd,porcentaje_avance,retail_ytd,inventario,back_order',
            'followupAgreements:id,visit_report_id,acuerdo,responsable,seguimiento,fecha_compromiso,status,motivo_cancelacion,completado_at,estado',
            'followupAgreements.dates' => function ($q) {
                $q->with('user:id,name')
                    ->orderBy('numero_reprogramacion')
                    ->orderBy('id');
            },
            'trainingData:id,visit_report_id,tipo,tema_principal,num_personas,comentarios',
            'attachments:id,visit_report_id,filename,path,tipo',
        ])
            ->select(
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

        $pdf = Pdf::loadView('pdf.visita', [
            'visit' => $visit,
            'esClienteDirecto' => $visit->visit_type === 'cliente_directo',
        ]);

        $pdf->setPaper('letter', 'portrait');

        return $pdf->download("visita-{$visit->id}.pdf");
    }
}
