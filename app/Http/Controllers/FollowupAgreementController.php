<?php

namespace App\Http\Controllers;

use App\Models\FollowupAgreement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowupAgreementController extends Controller
{

    public function complete($id)
    {
        $agreement = FollowupAgreement::where('estado', 2)
            ->findOrFail($id);

        if ($agreement->status === 2) {
            return response()->json([
                'message' => 'El acuerdo ya está completado.'
            ], 422);
        }

        if ($agreement->status === 3) {
            return response()->json([
                'message' => 'Un acuerdo cancelado no puede completarse.'
            ], 422);
        }

        if ($agreement->status === 0) {
            return response()->json([
                'message' => 'Un acuerdo vencido no puede completarse.'
            ], 422);
        }
        $agreement->update([
            'status' => 2,
            'completado_at' => now(),
        ]);

        return response()->json([
            'message' => 'Acuerdo marcado como completado correctamente.',
            'data' => $agreement->fresh()
        ], 200);
    }
}
