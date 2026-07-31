<?php

namespace App\Http\Controllers;

use App\Models\FollowupAgreement;
use Illuminate\Http\Request;

class AcuerdosController extends Controller
{
    public function index()
    {
        $acuerdos = FollowupAgreement::select(
            'id',
            'visit_report_id',
            'acuerdo',
            'responsable',
            'fecha_compromiso',
            'estado',
            'created_at',
        )
            ->activos()
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($acuerdos, 200);
    }

    public function show($id)
    {
        $acuerdo = FollowupAgreement::select(
            'id',
            'visit_report_id',
            'acuerdo',
            'responsable',
            'fecha_compromiso',
            'estado',
            'created_at',
        )
            ->where('id', $id)
            ->activos()
            ->firstOrFail();

        return response()->json($acuerdo, 200);
    }
}
