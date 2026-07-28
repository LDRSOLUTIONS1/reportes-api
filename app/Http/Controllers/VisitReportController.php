<?php

namespace App\Http\Controllers;

use App\Models\VisitReport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ClientVisit;
use App\Models\DistributorVisit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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

    public function getEditVisit($id)
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

    public function store(Request $request)
    {
        $validated = $this->validateVisit($request);

        $validated['user_id'] = Auth::id();

        $visit = VisitReport::create($validated);

        if ($visit->visit_type === 'cliente_directo') {
            $clientData = $this->validateClientVisit($request);

            $logoPath = $this->uploadLogo($request);
            if ($logoPath) {
                $clientData['logo_path'] = $logoPath;
            }

            $clientVisit = $this->storeClientVisit($visit, $clientData);

            $contacts = $this->validateContacts($this->getContacts($request));
            $this->storeContacts($clientVisit, $contacts);

            $fleet = $this->validateFleetInfo($this->getFleetInfo($request));
            $this->storeFleetInfo($clientVisit, $fleet);

            $history = $this->validateSalesHistory($this->getSalesHistory($request));
            $this->storeSalesHistory($clientVisit, $history);

            $this->storeEvents($clientVisit, $request);

            $requirements = $this->validateRequirements($request);
            $this->storeRequirements($clientVisit, $requirements);
        }

        if ($visit->visit_type === 'distribuidor') {
            $distributorData = $this->validateDistributorVisit($request);
            $distributorVisit = $this->storeDistributorVisit($visit, $distributorData);

            $leads = $this->validateLeads($this->getLeads($request));
            $this->storeLeads($distributorVisit, $leads);

            $indicators = $this->validateCommercialIndicators($this->getCommercialIndicators($request));
            $this->storeCommercialIndicators($distributorVisit, $indicators);
        }

        $agreements = $this->validateFollowupAgreements($this->getFollowupAgreements($request));
        $this->storeFollowupAgreements($visit, $agreements);
        $trainingData = $this->validateTrainingData($request);
        $this->storeTrainingData($visit, $trainingData);
        $this->validateAttachments($request);
        $this->storeAttachments($visit, $request);

        return response()->json([
            'message' => 'Reporte de visita creado correctamente',
            'data'    => $visit
        ], 201);
    }

    private function storeClientVisit(VisitReport $visit, array $data)
    {
        return $visit->clientVisit()->create($data);
    }

    private function storeContacts(ClientVisit $clientVisit, array $contacts)
    {
        foreach ($contacts as $contact) {

            $clientVisit->contacts()->create([
                'nombre'    => $contact['nombre'],
                'puesto'    => $contact['puesto'],
                'email'     => $contact['email'] ?? null,
                'telefono'  => $contact['telefono'],
            ]);
        }
    }

    private function storeFleetInfo(ClientVisit $clientVisit, array $fleet)
    {
        foreach ($fleet as $item) {

            $clientVisit->fleetInfo()->create([
                'marca'                  => $item['marca'],
                'modelo'                 => $item['modelo'],
                'capacidad_carga'        => $item['capacidad_carga'],
                'cantidad'               => $item['cantidad'],
                'porcentaje_flota'       => $item['porcentaje_flota'] ?? null,
                'comentarios_aplicacion' => $item['comentarios_aplicacion'] ?? null,
            ]);
        }
    }

    private function storeSalesHistory(ClientVisit $clientVisit, array $history)
    {
        foreach ($history as $item) {

            $clientVisit->salesHistory()->create([
                'anio'      => $item['anio'],
                'cantidad'  => $item['cantidad'],
            ]);
        }
    }

    private function storeEvents(ClientVisit $clientVisit, Request $request)
    {
        $eventos = [];

        foreach (json_decode($request->eventos_asistio, true) ?? [] as $nombre => $checked) {

            if ($checked) {

                $eventos[] = [
                    'nombre_evento' => $nombre,
                    'tipo'          => 'asistio',
                ];
            }
        }

        if ($request->eventos_asistio_otro) {

            $eventos[] = [
                'nombre_evento' => $request->eventos_asistio_otro,
                'tipo'          => 'asistio',
            ];
        }

        foreach (json_decode($request->eventos_candidato, true) ?? [] as $nombre => $checked) {

            if ($checked) {

                $eventos[] = [
                    'nombre_evento' => $nombre,
                    'tipo'          => 'candidato',
                ];
            }
        }

        if ($request->eventos_candidato_otro) {

            $eventos[] = [
                'nombre_evento' => $request->eventos_candidato_otro,
                'tipo'          => 'candidato',
            ];
        }

        $clientVisit->events()->createMany($eventos);
    }

    private function storeRequirements(ClientVisit $clientVisit, array $data)
    {
        $data['demo'] = $data['demo'] === 'si';

        return $clientVisit->requirements()->create($data);
    }

    private function storeFollowupAgreements(VisitReport $visit, array $agreements)
    {
        foreach ($agreements as $agreement) {

            $visit->followupAgreements()->create([
                'acuerdo'           => $agreement['acuerdo'],
                'responsable'       => $agreement['responsable'],
                'fecha_compromiso'  => $agreement['fecha_compromiso'],
            ]);
        }
    }

    private function storeTrainingData(VisitReport $visit, array $data)
    {
        return $visit->trainingData()->create($data);
    }

    private function storeAttachments(
        VisitReport $visit,
        Request $request
    ) {
        if (!$request->hasFile('evidencias')) {
            return;
        }

        foreach ($request->file('evidencias') as $file) {

            $path = $file->store(
                'documents',
                'public'
            );

            $visit->attachments()->create([
                'filename' => $file->getClientOriginalName(),
                'path'     => $path,
                'tipo'     => str_starts_with(
                    $file->getMimeType(),
                    'image/'
                ) ? 'foto' : 'anexo',
            ]);
        }
    }

    private function storeDistributorVisit(VisitReport $visit, array $data)
    {
        return $visit->distributorVisit()->create($data);
    }

    private function storeLeads(
        DistributorVisit $distributorVisit,
        array $leads
    ) {
        foreach ($leads as $lead) {

            $distributorVisit->leads()->create([
                'cliente'           => $lead['cliente'],
                'modelo_interes'    => $lead['modelo_interes'] ?? null,
                'porcentaje_avance' => $lead['porcentaje_avance'] ?? null,
                'comentarios'       => $lead['comentarios'] ?? null,
            ]);
        }
    }

    private function storeCommercialIndicators(
        DistributorVisit $distributorVisit,
        array $indicators
    ) {
        foreach ($indicators as $indicator) {

            $distributorVisit->commercialIndicators()->create([
                'modelo'            => $indicator['modelo'],
                'bp_2025'           => $indicator['bp_2025'] ?? null,
                'whole_ytd'         => $indicator['whole_ytd'] ?? null,
                'porcentaje_avance' => $indicator['porcentaje_avance'] ?? null,
                'retail_ytd'        => $indicator['retail_ytd'] ?? null,
                'inventario'        => $indicator['inventario'] ?? null,
                'back_order'        => $indicator['back_order'] ?? null,
            ]);
        }
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
        $temas = json_decode($request->temas_revisados, true) ?? [];

        $resultado = [];

        foreach ($temas as $tema => $checked) {

            if (!$checked) {
                continue;
            }

            if ($tema === 'otros') {

                if ($request->filled('temas_revisados_otro')) {
                    $resultado[] = $request->temas_revisados_otro;
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

    public function update(Request $request, $id)
    {
        $visit = VisitReport::activos()->findOrFail($id);

        $validated = $this->validateVisit($request);
        $visit->update($validated);

        if ($visit->visit_type === 'cliente_directo') {
            $clientData = $this->validateClientVisit($request);

            $logoPath = $this->uploadLogo($request);
            if ($logoPath) {
                $clientData['logo_path'] = $logoPath;
            }

            $clientVisit = $this->updateClientVisit($visit, $clientData);

            $contacts = $this->validateContacts($this->getContacts($request));
            $this->updateContacts($clientVisit, $contacts);

            $fleet = $this->validateFleetInfo($this->getFleetInfo($request));
            $this->updateFleetInfo($clientVisit, $fleet);

            $history = $this->validateSalesHistory($this->getSalesHistory($request));
            $this->updateSalesHistory($clientVisit, $history);

            $this->updateEvents($clientVisit, $request);

            $requirements = $this->validateRequirements($request);
            $this->updateRequirements($clientVisit, $requirements);
        }

        if ($visit->visit_type === 'distribuidor') {
            $distributorData = $this->validateDistributorVisit($request);
            $distributorVisit = $this->updateDistributorVisit($visit, $distributorData);

            $leads = $this->validateLeads($this->getLeads($request));
            $this->updateLeads($distributorVisit, $leads);

            $indicators = $this->validateCommercialIndicators($this->getCommercialIndicators($request));
            $this->updateCommercialIndicators($distributorVisit, $indicators);
        }

        $agreements = $this->validateFollowupAgreements($this->getFollowupAgreements($request));
        $this->updateFollowupAgreements($visit, $agreements);
        $trainingData = $this->validateTrainingData($request);
        $this->updateTrainingData($visit, $trainingData);
        $this->validateAttachments($request);
        $this->updateAttachments($visit, $request);

        return response()->json([
            'message' => 'Reporte de visita actualizado correctamente',
            'data'    => $visit
        ], 200);
    }

    private function updateClientVisit(VisitReport $visit, array $data)
    {
        return $visit->clientVisit()->updateOrCreate(
            [
                'visit_report_id' => $visit->id,
            ],
            $data
        );
    }

    private function uploadLogo(Request $request)
    {
        if (!$request->hasFile('logo_file')) {
            return null;
        }

        return $request
            ->file('logo_file')
            ->store('documents', 'public');
    }

    private function updateContacts(ClientVisit $clientVisit, array $contacts)
    {
        $ids = collect($contacts)
            ->pluck('id')
            ->filter()
            ->toArray();

        $clientVisit->contacts()
            ->whereNotIn('id', $ids)
            ->delete();

        foreach ($contacts as $contact) {

            $clientVisit->contacts()->updateOrCreate(
                [
                    'id' => $contact['id'] ?? null
                ],
                [
                    'nombre' => $contact['nombre'],
                    'puesto' => $contact['puesto'],
                    'email' => $contact['email'] ?? null,
                    'telefono' => $contact['telefono'],
                ]
            );
        }
    }

    private function updateFleetInfo(ClientVisit $clientVisit, array $fleet)
    {
        $ids = collect($fleet)
            ->pluck('id')
            ->filter()
            ->toArray();

        $clientVisit->fleetInfo()
            ->whereNotIn('id', $ids)
            ->delete();

        foreach ($fleet as $item) {

            $clientVisit->fleetInfo()->updateOrCreate(
                [
                    'id' => $item['id'] ?? null,
                ],
                [
                    'marca' => $item['marca'],
                    'modelo' => $item['modelo'],
                    'capacidad_carga' => $item['capacidad_carga'],
                    'cantidad' => $item['cantidad'],
                    'porcentaje_flota' => $item['porcentaje_flota'] ?? null,
                    'comentarios_aplicacion' => $item['comentarios_aplicacion'] ?? null,
                ]
            );
        }
    }

    private function updateSalesHistory(ClientVisit $clientVisit, array $history)
    {
        $ids = collect($history)
            ->pluck('id')
            ->filter()
            ->toArray();

        $clientVisit->salesHistory()
            ->whereNotIn('id', $ids)
            ->delete();

        foreach ($history as $item) {

            $clientVisit->salesHistory()->updateOrCreate(
                [
                    'id' => $item['id'] ?? null
                ],
                [
                    'anio' => $item['anio'],
                    'cantidad' => $item['cantidad'],
                ]
            );
        }
    }

    private function updateEvents(ClientVisit $clientVisit, Request $request)
    {
        $clientVisit->events()->delete();

        $this->storeEvents($clientVisit, $request);
    }

    private function updateRequirements(ClientVisit $clientVisit, array $data)
    {
        $data['demo'] = $data['demo'] === 'si';

        return $clientVisit->requirements()->updateOrCreate(
            [
                'client_visit_id' => $clientVisit->id,
            ],
            $data
        );
    }

    private function updateFollowupAgreements(VisitReport $visit, array $agreements)
    {
        $ids = collect($agreements)
            ->pluck('id')
            ->filter()
            ->toArray();

        $visit->followupAgreements()
            ->whereNotIn('id', $ids)
            ->delete();

        foreach ($agreements as $agreement) {

            $visit->followupAgreements()->updateOrCreate(
                [
                    'id' => $agreement['id'] ?? null,
                ],
                [
                    'acuerdo' => $agreement['acuerdo'],
                    'responsable' => $agreement['responsable'],
                    'fecha_compromiso' => $agreement['fecha_compromiso'],
                ]
            );
        }
    }

    private function updateTrainingData(VisitReport $visit, array $data)
    {
        return $visit->trainingData()->updateOrCreate(
            [
                'visit_report_id' => $visit->id,
            ],
            $data
        );
    }

    private function updateAttachments(
        VisitReport $visit,
        Request $request
    ) {

        $existentes = $request->evidencias_existentes ?? [];

        $visit->attachments()
            ->whereNotIn('id', $existentes)
            ->get()
            ->each(function ($attachment) {

                Storage::disk('public')
                    ->delete($attachment->path);

                $attachment->delete();
            });

        $this->storeAttachments(
            $visit,
            $request
        );
    }

    private function updateDistributorVisit(VisitReport $visit, array $data)
    {
        return $visit->distributorVisit()->updateOrCreate(
            [
                'visit_report_id' => $visit->id,
            ],
            $data
        );
    }

    private function updateLeads(
        DistributorVisit $distributorVisit,
        array $leads
    ) {
        $ids = collect($leads)
            ->pluck('id')
            ->filter()
            ->toArray();

        $distributorVisit->leads()
            ->whereNotIn('id', $ids)
            ->delete();

        foreach ($leads as $lead) {

            $distributorVisit->leads()->updateOrCreate(
                [
                    'id' => $lead['id'] ?? null,
                ],
                [
                    'cliente' => $lead['cliente'],
                    'modelo_interes' => $lead['modelo_interes'] ?? null,
                    'porcentaje_avance' => $lead['porcentaje_avance'] ?? null,
                    'comentarios' => $lead['comentarios'] ?? null,
                ]
            );
        }
    }

    private function updateCommercialIndicators(
        DistributorVisit $distributorVisit,
        array $indicators
    ) {
        $ids = collect($indicators)
            ->pluck('id')
            ->filter()
            ->toArray();

        $distributorVisit->commercialIndicators()
            ->whereNotIn('id', $ids)
            ->delete();

        foreach ($indicators as $indicator) {

            $distributorVisit->commercialIndicators()->updateOrCreate(
                [
                    'id' => $indicator['id'] ?? null,
                ],
                [
                    'modelo' => $indicator['modelo'],
                    'bp_2025' => $indicator['bp_2025'] ?? null,
                    'whole_ytd' => $indicator['whole_ytd'] ?? null,
                    'porcentaje_avance' => $indicator['porcentaje_avance'] ?? null,
                    'retail_ytd' => $indicator['retail_ytd'] ?? null,
                    'inventario' => $indicator['inventario'] ?? null,
                    'back_order' => $indicator['back_order'] ?? null,
                ]
            );
        }
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
                'fleet_info' => 'array',
                'fleet_info.*.marca'                  => 'required|string|max:255',
                'fleet_info.*.modelo'                 => 'required|string|max:255',
                'fleet_info.*.capacidad_carga'        => 'required|numeric|min:0',
                'fleet_info.*.cantidad'               => 'required|integer|min:1',
                'fleet_info.*.porcentaje_flota'       => 'nullable|numeric|min:0|max:100',
                'fleet_info.*.comentarios_aplicacion' => 'nullable|string|max:255',
            ]
        )->validate()['fleet_info'];
    }

    private function validateSalesHistory(array $history)
    {
        return Validator::make(
            ['sales_history' => $history],
            [
                'sales_history'             => 'array',
                'sales_history.*.anio'      => 'required|integer|min:2000',
                'sales_history.*.cantidad'  => 'required|integer|min:0',
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
                'proyeccion_compra.required'   => 'La proyección de compra es obligatoria.',
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
                'followup_agreements.*.acuerdo'          => 'required|string|max:255',
                'followup_agreements.*.responsable'      => 'required|string|max:255',
                'followup_agreements.*.fecha_compromiso' => 'required|date',
            ],
            [
                'followup_agreements.*.acuerdo.required'            => 'El acuerdo es obligatorio.',
                'followup_agreements.*.responsable.required'        => 'El responsable es obligatorio.',
                'followup_agreements.*.fecha_compromiso.required'   => 'La fecha compromiso es obligatoria.',
                'followup_agreements.*.fecha_compromiso.date'       => 'La fecha compromiso no es válida.',
            ]
        )->validate()['followup_agreements'];
    }

    private function validateTrainingData(Request $request)
    {
        return $request->validate(
            [
                'tipo'            => 'required|string|max:255',
                'tema_principal'  => 'required|string|max:255',
                'num_personas'    => 'required|integer|min:1',
                'comentarios'     => 'required|string|max:255',
            ],
            [
                'tipo.required'             => 'El tipo es obligatorio.',
                'tema_principal.required'   => 'El tema principal es obligatorio.',
                'num_personas.required'     => 'El número de personas es obligatorio.',
                'num_personas.integer'      => 'El número de personas debe ser un número entero.',
                'num_personas.min'          => 'Debe ser al menos 1.',
                'comentarios.required'      => 'Los comentarios son obligatorios.',
            ]
        );
    }

    private function validateAttachments(Request $request)
    {
        return $request->validate(
            [
                'evidencias'            =>  'nullable|array|max:10',
                'evidencias.*'          =>  'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
                'evidencias_existentes' =>  'nullable|array',
            ],
            [
                'evidencias.array'      => 'Las evidencias deben ser un array.',
                'evidencias.*.file'     => 'Las evidencias deben ser archivos.',
                'evidencias.*.mimes'    => 'Las evidencias deben ser de tipo JPG, JPEG, PNG, PDF, DOC o DOCX.',
                'evidencias.*.max'      => 'Las evidencias no pueden superar los 10MB.',
            ]
        );;
    }

    private function validateDistributorVisit(Request $request)
    {
        $data = $request->validate([
            'distribuidor'             => 'required|string|max:255',
            'plaza'                    => 'required|string|max:255',
            'grupo'                    => 'required|string|max:255',
            'comentarios_adicionales'  => 'nullable|string',
            'temas_revisados_otro'     => 'nullable|string|max:255',
        ]);

        $data['participantes'] = $this->validateParticipantes(
            $this->getParticipantes($request)
        );

        $data['temas_revisados'] = $this->getTemasRevisados($request);

        return $data;
    }

    private function validateParticipantes(array $participantes)
    {
        return Validator::make(
            ['participantes' => $participantes],
            [
                'participantes'            => 'array',
                'participantes.*.nombre'   => 'required|string|max:255',
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
}
