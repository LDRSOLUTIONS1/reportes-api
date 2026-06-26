<?php
// app/Http/Controllers/Api/FollowupAgreementController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FollowupAgreement;
use App\Models\VisitReport;
use Illuminate\Http\Request;

class FollowupAgreementController extends Controller
{
    /**
     * GET /api/visit-reports/{visitReport}/followup-agreements
     */
    public function index(VisitReport $visitReport)
    {
        return response()->json($visitReport->followupAgreements);
    }

    /**
     * POST /api/visit-reports/{visitReport}/followup-agreements
     */
    public function store(Request $request, VisitReport $visitReport)
    {
        $validated = $request->validate([
            'acuerdo'          => 'required|string|max:500',
            'responsable'      => 'nullable|string|max:150',
            'fecha_compromiso' => 'nullable|date',
        ]);

        $agreement = $visitReport->followupAgreements()->create($validated);

        return response()->json($agreement, 201);
    }

    /**
     * PUT /api/followup-agreements/{followupAgreement}
     */
    public function update(Request $request, FollowupAgreement $followupAgreement)
    {
        $validated = $request->validate([
            'acuerdo'          => 'sometimes|string|max:500',
            'responsable'      => 'nullable|string|max:150',
            'fecha_compromiso' => 'nullable|date',
        ]);

        $followupAgreement->update($validated);

        return response()->json($followupAgreement);
    }

    /**
     * DELETE /api/followup-agreements/{followupAgreement}
     */
    public function destroy(FollowupAgreement $followupAgreement)
    {
        $followupAgreement->delete();

        return response()->json(['message' => 'Acuerdo eliminado.']);
    }
}
