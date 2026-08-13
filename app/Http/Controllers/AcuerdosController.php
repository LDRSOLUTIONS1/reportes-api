<?php

namespace App\Http\Controllers;

use App\Models\FollowupAgreement;
use Illuminate\Http\Request;

class AcuerdosController extends Controller
{
    public function index()
    {
        $acuerdos = FollowupAgreement::with([
            'visitReport:id,user_id,visit_type',
            'visitReport.clientVisit:id,visit_report_id,razon_social',
            'visitReport.distributorVisit:id,visit_report_id,distribuidor,plaza,grupo',
            'visitReport.user:id,name',
        ])->select(
                'id',
                'visit_report_id',
                'acuerdo',
                'responsable',
                'seguimiento',
                'fecha_compromiso',
                'status',
                'motivo_cancelacion',
                'completado_at',
                'estado',
            )
            ->activos()
            ->orderBy('id', 'desc')
            ->get();

        $acuerdos->each(function ($acuerdo) {
            if ($acuerdo->esta_vencido) {
                $acuerdo->status = 0;
            }
        });

        return response()->json($acuerdos, 200);
    }

    public function show($id)
    {
        $acuerdo = FollowupAgreement::with([
            'visitReport:id,user_id,visit_type',
            'visitReport.clientVisit:id,visit_report_id,razon_social',
            'visitReport.distributorVisit:id,visit_report_id,distribuidor,plaza,grupo',
            'visitReport.user:id,name',
        ])->select(
                'id',
                'visit_report_id',
                'acuerdo',
                'responsable',
                'seguimiento',
                'fecha_compromiso',
                'status',
                'motivo_cancelacion',
                'completado_at',
                'estado',
            )
            ->where('id', $id)
            ->activos()
            ->firstOrFail();

        if ($acuerdo->esta_vencido) {
            $acuerdo->status = 0;
        }
        return response()->json($acuerdo, 200);
    }
}
