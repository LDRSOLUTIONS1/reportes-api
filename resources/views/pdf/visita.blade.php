<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <title>Reporte de Visita #{{ $visit->id }}</title>

    <style>
        @page {
            margin: 30px 38px 60px 38px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1C2530;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        table {
            border-collapse: collapse;
        }

        /* ===== HEADER ===== */
        .header {
            border-bottom: 3px solid {{ $esClienteDirecto ? '#2E6BE0' : '#7A5CDB' }};
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .header-table {
            width: 100%;
        }

        .header-table td {
            vertical-align: top;
        }

        .title {
            font-size: 19px;
            font-weight: bold;
            color: #1C2530;
        }

        .subtitle {
            color: #64748B;
            margin-top: 3px;
            font-size: 10px;
        }

        .type-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: bold;
            color: {{ $esClienteDirecto ? '#2E6BE0' : '#7A5CDB' }};
            background: {{ $esClienteDirecto ? '#2E6BE014' : '#7A5CDB14' }};
            border: 1px solid {{ $esClienteDirecto ? '#2E6BE033' : '#7A5CDB33' }};
            text-align: center;
        }

        .type-badge-cell {
            width: 120px;
            text-align: right;
        }

        /* ===== SECTION CARD ===== */
        .section {
            margin-top: 14px;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            padding: 12px 14px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #1C2530;
            padding-bottom: 8px;
            margin-bottom: 10px;
            border-bottom: 1px solid #EDF2F7;
        }

        .section-title .bar {
            display: inline-block;
            width: 4px;
            height: 11px;
            margin-right: 6px;
            border-radius: 2px;
            position: relative;
            top: 1px;
        }

        /* ===== INFO GRID (basado en tablas, seguro para saltos de página en dompdf) ===== */
        .grid-table {
            width: 100%;
        }

        .grid-table td {
            vertical-align: top;
            padding: 0 0 9px 0;
            width: 50%;
        }

        .grid-table td.pad-right {
            padding-right: 4%;
        }

        .grid-table td.pad-left {
            padding-left: 4%;
        }

        .info-label {
            font-size: 8px;
            font-weight: bold;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 2px;
        }

        .info-value {
            font-size: 10px;
            color: #1C2530;
        }

        .info-value.empty {
            color: #A0AEC0;
        }

        /* ===== INNER CARDS (contactos, flota, etc) ===== */
        .inner-card-table {
            width: 100%;
            margin-bottom: 8px;
        }

        .inner-card-table td {
            vertical-align: top;
            width: 50%;
            padding: 0;
        }

        .inner-card-table td.pad-right {
            padding-right: 3%;
        }

        .inner-card-table td.pad-left {
            padding-left: 3%;
        }

        .inner-card {
            border: 1px solid #E2E8F0;
            border-radius: 5px;
            padding: 9px 10px;
            background: #FAFBFC;
        }

        .inner-card-title {
            font-size: 10px;
            font-weight: bold;
            color: #1C2530;
        }

        .inner-card-sub {
            font-size: 9px;
            color: #64748B;
            margin-top: 1px;
        }

        /* ===== TABLES DE DATOS ===== */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        .data-table th {
            background: #F8FAFC;
            color: #475569;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            text-align: left;
            padding: 6px 8px;
            border-bottom: 2px solid #E2E8F0;
        }

        .data-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #EDF2F7;
            font-size: 9.5px;
        }

        /* ===== CHIPS / BADGES ===== */
        .chip {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 8px;
            font-weight: bold;
        }

        .chip-success {
            background: #E6F6EF;
            color: #1E9E5C;
        }

        .chip-error {
            background: #FDEBEA;
            color: #D64545;
        }

        .chip-warning {
            background: #FEF3E2;
            color: #B7791F;
        }

        .chip-default {
            background: #F1F3F5;
            color: #64748B;
        }

        /* ===== ACUERDO CARD ===== */
        .acuerdo-card {
            border: 1px solid #E2E8F0;
            border-left: 3px solid #D98C2B;
            border-radius: 5px;
            padding: 10px 12px;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .acuerdo-top-table {
            width: 100%;
            margin-bottom: 6px;
        }

        .acuerdo-top-table td {
            vertical-align: middle;
        }

        .acuerdo-name {
            font-size: 10.5px;
            font-weight: bold;
            color: #1C2530;
        }

        .acuerdo-status-cell {
            text-align: right;
            width: 90px;
        }

        .acuerdo-divider {
            border-top: 1px solid #EDF2F7;
            margin: 8px 0;
            font-size: 1px;
            line-height: 1px;
        }

        .motivo-box {
            margin-top: 8px;
            padding: 7px 9px;
            border-radius: 4px;
            background: #F8FAFC;
        }

        .motivo-label {
            font-size: 8px;
            font-weight: bold;
            color: #64748B;
            text-transform: uppercase;
        }

        .motivo-text {
            font-size: 9.5px;
            color: #1C2530;
            margin-top: 2px;
        }

        .reprog-header {
            font-size: 8.5px;
            font-weight: bold;
            color: #64748B;
            text-transform: uppercase;
            margin: 9px 0 6px 0;
        }

        .reprog-item {
            border-left: 2px solid #E2E8F0;
            padding-left: 8px;
            margin-bottom: 6px;
            page-break-inside: avoid;
        }

        .reprog-title {
            font-size: 9.5px;
            font-weight: bold;
            color: #1C2530;
        }

        .reprog-meta {
            font-size: 8px;
            color: #94A3B8;
            margin-top: 1px;
        }

        .reprog-motivo {
            font-size: 8.5px;
            color: #64748B;
            margin-top: 1px;
        }

        .vigente-tag {
            display: inline-block;
            margin-left: 5px;
            padding: 1px 6px;
            border-radius: 20px;
            font-size: 7px;
            font-weight: bold;
            color: #1E9E5C;
            background: #E6F6EF;
            position: relative;
            top: -1px;
        }

        /* ===== EVIDENCIAS (tabla de 3 columnas, segura para dompdf) ===== */
        .evidencia-table {
            width: 100%;
            margin-top: 4px;
        }

        .evidencia-table td {
            width: 33.33%;
            padding: 0 6px 10px 0;
            vertical-align: top;
        }

        .evidencia-table td:last-child {
            padding-right: 0;
        }

        .evidencia-frame {
            width: 100%;
            height: 110px;
            overflow: hidden;
            border-radius: 4px;
            border: 1px solid #E2E8F0;
            text-align: center;
        }

        /* dompdf no soporta object-fit: escalamos por ancho y recortamos
           el sobrante verticalmente con el overflow:hidden del contenedor */
        .evidencia-frame img {
            width: 100%;
            height: auto;
            display: block;
        }

        .evidencia-caption {
            font-size: 7.5px;
            color: #94A3B8;
            margin-top: 3px;
            text-align: center;
        }

        /* ===== UTIL ===== */
        .page-break {
            page-break-before: always;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        .footer {
            position: fixed;
            bottom: 8px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #94A3B8;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td>
                    <div class="title">Reporte de visita #{{ $visit->id }}</div>
                    <div class="subtitle">
                        {{ \Carbon\Carbon::parse($visit->fecha_inicio)->format('d/m/Y') }}
                        @if ($visit->fecha_fin && $visit->fecha_fin != $visit->fecha_inicio)
                            &ndash; {{ \Carbon\Carbon::parse($visit->fecha_fin)->format('d/m/Y') }}
                        @endif
                        &nbsp;&middot;&nbsp; {{ $visit->user?->name ?? 'Sin información' }}
                        ({{ $visit->user?->email ?? 'Sin información' }})
                    </div>
                </td>
                <td class="type-badge-cell">
                    <span class="type-badge">
                        {{ $esClienteDirecto ? 'Cliente directo' : 'Distribuidor' }}
                    </span>
                </td>
            </tr>
        </table>
    </div>


    {{-- INFORMACIÓN GENERAL --}}
    <div class="section">
        <div class="section-title">
            <span class="bar" style="background:#1C2530;"></span>Información general
        </div>

        <table class="grid-table">
            <tr>
                <td class="pad-right">
                    <div class="info-label">Segmento</div>
                    <div class="info-value {{ $visit->segmento ? '' : 'empty' }}">
                        {{ $visit->segmento ?? 'Sin dato' }}
                    </div>
                </td>
                <td class="pad-left">
                    <div class="info-label">Tipo de visita</div>
                    <div class="info-value">{{ $visit->tipo_visita }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="info-label">Objetivo</div>
                    <div class="info-value {{ $visit->objetivo ? '' : 'empty' }}">
                        {{ $visit->objetivo ?? 'Sin dato' }}
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="info-label">Logros / Estrategia</div>
                    <div class="info-value {{ $visit->logros_estrategia ? '' : 'empty' }}">
                        {{ $visit->logros_estrategia ?? 'Sin dato' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>


    {{-- CLIENTE DIRECTO --}}
    @if ($visit->clientVisit)

        <div class="section">
            <div class="section-title">
                <span class="bar" style="background:#2E6BE0;"></span>Información del cliente
            </div>

            <table class="grid-table">
                <tr>
                    <td class="pad-right">
                        <div class="info-label">Razón social</div>
                        <div class="info-value">{{ $visit->clientVisit->razon_social }}</div>
                    </td>
                    <td class="pad-left">
                        <div class="info-label">Tipo de cliente</div>
                        <div class="info-value">{{ $visit->clientVisit->tipo_cliente ?? 'Sin dato' }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="pad-right">
                        <div class="info-label">Ubicaciones</div>
                        <div class="info-value">{{ $visit->clientVisit->ubicaciones ?? 'Sin dato' }}</div>
                    </td>
                    <td class="pad-left">
                        <div class="info-label">Tamaño de flota</div>
                        <div class="info-value">{{ $visit->clientVisit->tamanio_flota ?? 'Sin dato' }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="pad-right">
                        <div class="info-label">Giro</div>
                        <div class="info-value">{{ $visit->clientVisit->giro ?? 'Sin dato' }}</div>
                    </td>
                    <td class="pad-left">
                        <div class="info-label">Rutas</div>
                        <div class="info-value">{{ $visit->clientVisit->rutas ?? 'Sin dato' }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="pad-right">
                        <div class="info-label">Cobertura</div>
                        <div class="info-value">{{ $visit->clientVisit->cobertura ?? 'Sin dato' }}</div>
                    </td>
                    <td class="pad-left">
                        <div class="info-label">Edad promedio de flota</div>
                        <div class="info-value">{{ $visit->clientVisit->edad_promedio_flota ?? 'Sin dato' }}</div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- CONTACTOS --}}
        @if ($visit->clientVisit->contacts && $visit->clientVisit->contacts->count())
            <div class="section">
                <div class="section-title">
                    <span class="bar" style="background:#2E6BE0;"></span>Contactos
                </div>

                @foreach ($visit->clientVisit->contacts->chunk(2) as $pair)
                    <table class="inner-card-table">
                        <tr>
                            @foreach ($pair as $contacto)
                                <td class="{{ $loop->first ? 'pad-right' : 'pad-left' }}">
                                    <div class="inner-card">
                                        <div class="inner-card-title">{{ $contacto->nombre }}</div>
                                        <div class="inner-card-sub">{{ $contacto->puesto ?? '—' }}</div>
                                        <div class="acuerdo-divider">&nbsp;</div>
                                        <div class="info-value">Email: {{ $contacto->email ?? '—' }}</div>
                                        <div class="info-value">Teléfono: {{ $contacto->telefono ?? '—' }}</div>
                                    </div>
                                </td>
                            @endforeach
                            @if ($pair->count() == 1)
                                <td class="pad-left">&nbsp;</td>
                            @endif
                        </tr>
                    </table>
                @endforeach
            </div>
        @endif

        {{-- FLOTA --}}
        @if ($visit->clientVisit->fleetInfo && $visit->clientVisit->fleetInfo->count())
            <div class="section">
                <div class="section-title">
                    <span class="bar" style="background:#2E6BE0;"></span>Información de flota
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Capacidad</th>
                            <th>Cantidad</th>
                            <th>% flota</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($visit->clientVisit->fleetInfo as $flota)
                            <tr>
                                <td>{{ $flota->marca }}</td>
                                <td>{{ $flota->modelo }}</td>
                                <td>{{ $flota->capacidad_carga }}</td>
                                <td>{{ $flota->cantidad }}</td>
                                <td>{{ $flota->porcentaje_flota }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- HISTORIAL DE VENTAS --}}
        @if ($visit->clientVisit->salesHistory && $visit->clientVisit->salesHistory->count())
            <div class="section">
                <div class="section-title">
                    <span class="bar" style="background:#2E6BE0;"></span>Historial de ventas
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Año</th>
                            <th>Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($visit->clientVisit->salesHistory as $venta)
                            <tr>
                                <td>{{ $venta->anio }}</td>
                                <td>{{ $venta->cantidad }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- REQUERIMIENTOS --}}
        @if ($visit->clientVisit->requirements)
            <div class="section">
                <div class="section-title">
                    <span class="bar" style="background:#2E6BE0;"></span>Requerimientos
                </div>

                <table class="grid-table">
                    <tr>
                        <td class="pad-right">
                            <div class="info-label">Modelo de interés</div>
                            <div class="info-value">{{ $visit->clientVisit->requirements->modelo_interes ?? 'Sin dato' }}</div>
                        </td>
                        <td class="pad-left">
                            <div class="info-label">Tipo de carrocería</div>
                            <div class="info-value">{{ $visit->clientVisit->requirements->tipo_carroceria ?? 'Sin dato' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="pad-right">
                            <div class="info-label">Proyección de compra</div>
                            <div class="info-value">{{ $visit->clientVisit->requirements->proyeccion_compra ?? 'Sin dato' }}</div>
                        </td>
                        <td class="pad-left">
                            <div class="info-label">Financiamiento</div>
                            <div class="info-value">{{ $visit->clientVisit->requirements->financiamiento ?? 'Sin dato' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="pad-right">
                            <div class="info-label">Tiempo de entrega</div>
                            <div class="info-value">{{ $visit->clientVisit->requirements->tiempo_entrega ?? 'Sin dato' }}</div>
                        </td>
                        <td class="pad-left">
                            <div class="info-label">Lugar de entrega</div>
                            <div class="info-value">{{ $visit->clientVisit->requirements->lugar_entrega ?? 'Sin dato' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="pad-right">
                            <div class="info-label">Distribuidor</div>
                            <div class="info-value">{{ $visit->clientVisit->requirements->distribuidor ?? 'Sin dato' }}</div>
                        </td>
                        <td class="pad-left">
                            <div class="info-label">Demo</div>
                            <div class="info-value">{{ $visit->clientVisit->requirements->demo ? 'Sí' : 'No' }}</div>
                        </td>
                    </tr>
                </table>
            </div>
        @endif

    @endif


    {{-- DISTRIBUIDOR --}}
    @if ($visit->distributorVisit)

        <div class="section">
            <div class="section-title">
                <span class="bar" style="background:#7A5CDB;"></span>Información del distribuidor
            </div>

            <table class="grid-table">
                <tr>
                    <td class="pad-right">
                        <div class="info-label">Distribuidor</div>
                        <div class="info-value">{{ $visit->distributorVisit->distribuidor }}</div>
                    </td>
                    <td class="pad-left">
                        <div class="info-label">Plaza</div>
                        <div class="info-value">{{ $visit->distributorVisit->plaza }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="pad-right">
                        <div class="info-label">Grupo</div>
                        <div class="info-value">{{ $visit->distributorVisit->grupo }}</div>
                    </td>
                    <td class="pad-left">&nbsp;</td>
                </tr>
            </table>

            @if ($visit->distributorVisit->comentarios_adicionales)
                <div class="motivo-box" style="margin-top:4px;">
                    <div class="motivo-label">Comentarios adicionales</div>
                    <div class="motivo-text">{{ $visit->distributorVisit->comentarios_adicionales }}</div>
                </div>
            @endif
        </div>

        {{-- LEADS --}}
        @if ($visit->distributorVisit->leads && $visit->distributorVisit->leads->count())
            <div class="section">
                <div class="section-title">
                    <span class="bar" style="background:#7A5CDB;"></span>Leads
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Modelo de interés</th>
                            <th>Avance</th>
                            <th>Comentarios</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($visit->distributorVisit->leads as $lead)
                            <tr>
                                <td>{{ $lead->cliente }}</td>
                                <td>{{ $lead->modelo_interes }}</td>
                                <td>{{ $lead->porcentaje_avance }}%</td>
                                <td>{{ $lead->comentarios ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- INDICADORES COMERCIALES --}}
        @if ($visit->distributorVisit->commercialIndicators && $visit->distributorVisit->commercialIndicators->count())
            <div class="section">
                <div class="section-title">
                    <span class="bar" style="background:#7A5CDB;"></span>Indicadores comerciales
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Modelo</th>
                            <th>BP 2025</th>
                            <th>Whole YTD</th>
                            <th>Retail YTD</th>
                            <th>Avance</th>
                            <th>Inventario</th>
                            <th>Back order</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($visit->distributorVisit->commercialIndicators as $indicador)
                            <tr>
                                <td>{{ $indicador->modelo }}</td>
                                <td>{{ $indicador->bp_2025 }}</td>
                                <td>{{ $indicador->whole_ytd }}</td>
                                <td>{{ $indicador->retail_ytd }}</td>
                                <td>{{ $indicador->porcentaje_avance }}%</td>
                                <td>{{ $indicador->inventario }}</td>
                                <td>{{ $indicador->back_order }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    @endif


    {{-- ACUERDOS --}}
    @if ($visit->followupAgreements->count())

        <div class="section">
            <div class="section-title">
                <span class="bar" style="background:#D98C2B;"></span>Acuerdos y seguimiento
            </div>

            @foreach ($visit->followupAgreements as $agreement)
                @php
                    $dates = $agreement->dates;
                    $original = $dates->firstWhere('numero_reprogramacion', 0)?->fecha_compromiso
                        ?? $agreement->fecha_compromiso;
                    $vigente = $dates->firstWhere('estado', 2) ?? $dates->last();
                    $fechaVigente = $vigente?->fecha_compromiso ?? $agreement->fecha_compromiso;
                    $reprogramaciones = $dates->count() > 1 ? $dates->count() - 1 : 0;

                    if ($agreement->status == 2) {
                        $chipClass = 'chip-success';
                        $chipLabel = 'Completado';
                    } elseif ($agreement->status == 3) {
                        $chipClass = 'chip-default';
                        $chipLabel = 'Cancelado';
                    } elseif ($agreement->esta_vencido) {
                        $chipClass = 'chip-error';
                        $chipLabel = 'Vencido';
                    } else {
                        $chipClass = 'chip-warning';
                        $chipLabel = 'Pendiente';
                    }
                @endphp

                <div class="acuerdo-card">
                    <table class="acuerdo-top-table">
                        <tr>
                            <td class="acuerdo-name">{{ $agreement->acuerdo }}</td>
                            <td class="acuerdo-status-cell">
                                <span class="chip {{ $chipClass }}">{{ $chipLabel }}</span>
                            </td>
                        </tr>
                    </table>

                    <div class="acuerdo-divider">&nbsp;</div>

                    <table class="grid-table">
                        <tr>
                            <td class="pad-right">
                                <div class="info-label">Responsable</div>
                                <div class="info-value">{{ $agreement->responsable }}</div>
                            </td>
                            <td class="pad-left">
                                <div class="info-label">Seguimiento</div>
                                <div class="info-value">{{ $agreement->seguimiento }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td class="pad-right">
                                <div class="info-label">Fecha compromiso original</div>
                                <div class="info-value">{{ \Carbon\Carbon::parse($original)->format('d/m/Y') }}</div>
                            </td>
                            <td class="pad-left">
                                <div class="info-label">Fecha vigente</div>
                                <div class="info-value">{{ \Carbon\Carbon::parse($fechaVigente)->format('d/m/Y') }}</div>
                            </td>
                        </tr>
                        @if ($agreement->status == 2 && $agreement->completado_at)
                            <tr>
                                <td class="pad-right">
                                    <div class="info-label">Completado el</div>
                                    <div class="info-value">
                                        {{ \Carbon\Carbon::parse($agreement->completado_at)->format('d/m/Y') }}
                                    </div>
                                </td>
                                <td class="pad-left">&nbsp;</td>
                            </tr>
                        @endif
                    </table>

                    @if ($agreement->status == 3 && $agreement->motivo_cancelacion)
                        <div class="motivo-box">
                            <div class="motivo-label">Motivo de cancelación</div>
                            <div class="motivo-text">{{ $agreement->motivo_cancelacion }}</div>
                        </div>
                    @endif

                    @if ($reprogramaciones > 0)
                        <div class="reprog-header">
                            Historial de reprogramaciones ({{ $reprogramaciones }})
                        </div>

                        @foreach ($dates->sortBy('numero_reprogramacion') as $date)
                            <div class="reprog-item">
                                <div class="reprog-title">
                                    {{ $date->numero_reprogramacion == 0 ? 'Fecha original' : 'Reprogramación ' . $date->numero_reprogramacion }}:
                                    {{ \Carbon\Carbon::parse($date->fecha_compromiso)->format('d/m/Y') }}
                                    @if ($date->estado == 2)
                                        <span class="vigente-tag">VIGENTE</span>
                                    @endif
                                </div>
                                @if ($date->motivo_reprogramacion)
                                    <div class="reprog-motivo">Motivo: {{ $date->motivo_reprogramacion }}</div>
                                @endif
                                <div class="reprog-meta">
                                    {{ $date->user?->name ?? 'Sin usuario' }} &middot;
                                    {{ \Carbon\Carbon::parse($date->created_at)->format('d/m/Y') }}
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            @endforeach
        </div>

    @endif


    {{-- CAPACITACIÓN --}}
    @if ($visit->trainingData)

        <div class="section">
            <div class="section-title">
                <span class="bar" style="background:#1E9E8B;"></span>Capacitación
            </div>

            <table class="grid-table">
                <tr>
                    <td class="pad-right">
                        <div class="info-label">Tipo</div>
                        <div class="info-value">{{ $visit->trainingData->tipo }}</div>
                    </td>
                    <td class="pad-left">
                        <div class="info-label">Número de personas</div>
                        <div class="info-value">{{ $visit->trainingData->num_personas }}</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="info-label">Tema principal</div>
                        <div class="info-value">{{ $visit->trainingData->tema_principal }}</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="info-label">Comentarios</div>
                        <div class="info-value {{ $visit->trainingData->comentarios ? '' : 'empty' }}">
                            {{ $visit->trainingData->comentarios ?? 'Sin dato' }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>

    @endif


    {{-- EVIDENCIAS --}}
    @if ($visit->attachments && $visit->attachments->count())

        <div class="section">
            <div class="section-title">
                <span class="bar" style="background:#52606D;"></span>Evidencias
            </div>

            @foreach ($visit->attachments->chunk(3) as $grupo)
                <table class="evidencia-table">
                    <tr>
                        @foreach ($grupo as $archivo)
                            <td>
                                <div class="evidencia-frame">
                                    <img src="{{ public_path('storage/' . $archivo->path) }}" alt="{{ $archivo->filename }}">
                                </div>
                            </td>
                        @endforeach
                        @for ($i = $grupo->count(); $i < 3; $i++)
                            <td>&nbsp;</td>
                        @endfor
                    </tr>
                </table>
            @endforeach
        </div>

    @endif


    {{-- FOOTER --}}
    <div class="footer">
        Reporte de visita #{{ $visit->id }} &middot; Generado el {{ now()->format('d/m/Y H:i') }}
    </div>

</body>

</html>
