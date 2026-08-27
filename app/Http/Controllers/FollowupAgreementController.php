<?php

namespace App\Http\Controllers;

use App\Models\FollowupAgreement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\AgreementReminderService;

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

    public function cancel(Request $request, $id)
    {
        $request->validate([
            'motivo_cancelacion' => 'required|string|max:255',
        ]);

        $agreement = FollowupAgreement::where('estado', 2)
            ->findOrFail($id);

        if ($agreement->status === 2) {
            return response()->json([
                'message' => 'Un acuerdo completado no puede cancelarse.'
            ], 422);
        }

        if ($agreement->status === 3) {
            return response()->json([
                'message' => 'El acuerdo ya está cancelado.'
            ], 422);
        }

        $agreement->update([
            'status' => 3,
            'motivo_cancelacion' => $request->motivo_cancelacion,
        ]);

        return response()->json([
            'message' => 'Acuerdo cancelado correctamente.',
            'data' => $agreement->fresh()
        ], 200);
    }

    public function reschedule(Request $request, $id)
    {
        $validated = $request->validate([
            'fecha_compromiso'      => 'required|date',
            'motivo_reprogramacion' => 'required|string|max:255',
        ]);

        $agreement = FollowupAgreement::findOrFail($id);

        if ((int) $agreement->estado !== 2) {
            return response()->json([
                'message' => 'El acuerdo no está activo y no puede reprogramarse.'
            ], 422);
        }

        if ((int) $agreement->status === 2) {
            return response()->json([
                'message' => 'Un acuerdo completado no puede reprogramarse.'
            ], 422);
        }

        if ((int) $agreement->status === 3) {
            return response()->json([
                'message' => 'Un acuerdo cancelado no puede reprogramarse.'
            ], 422);
        }

        $nextNumero = (
            $agreement->dates()->max('numero_reprogramacion') ?? 0
        ) + 1;

        $newDate = DB::transaction(function () use (
            $agreement,
            $validated,
            $nextNumero
        ) {
            $agreement->dates()
                ->where('estado', 2)
                ->update([
                    'estado' => 1,
                ]);

            $newDate = $agreement->dates()->create([
                'fecha_compromiso'      => $validated['fecha_compromiso'],
                'motivo_reprogramacion' => $validated['motivo_reprogramacion'],
                'user_id'               => Auth::id(),
                'numero_reprogramacion' => $nextNumero,
                'recordatorio_enviado_at' => null,
                'estado'                => 2,
            ]);

            $agreement->update([
                'fecha_compromiso' => $validated['fecha_compromiso'],
            ]);

            return $newDate;
        });

        AgreementReminderService::schedule($newDate);

        $agreement->load([
            'dates' => function ($query) {
                $query->with('user:id,name')
                    ->orderBy('numero_reprogramacion')
                    ->orderBy('id');
            }
        ]);

        $fechaOriginal = $agreement->dates
            ->firstWhere('numero_reprogramacion', 0);

        $fechaVigente = $agreement->dates
            ->firstWhere('estado', 2);

        return response()->json([
            'message' => 'Acuerdo reprogramado correctamente.',
            'data' => [
                ...$agreement->toArray(),
                'fecha_original' => $fechaOriginal?->fecha_compromiso,

                'fecha_vigente' => $fechaVigente?->fecha_compromiso,

                'numero_reprogramaciones' => $agreement->dates
                    ->where('numero_reprogramacion', '>', 0)
                    ->count(),

                'dates' => $agreement->dates,
            ],
        ], 200);
    }
}
