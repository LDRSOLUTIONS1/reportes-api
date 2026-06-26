<?php
// app/Http/Controllers/Api/VisitAttachmentController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VisitAttachment;
use App\Models\VisitReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VisitAttachmentController extends Controller
{
    /**
     * POST /api/visit-reports/{visitReport}/attachments
     */
    public function store(Request $request, VisitReport $visitReport)
    {
        $request->validate([
            'files'        => 'required|array|max:10',
            'files.*'      => 'file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB
            'tipo'         => 'nullable|in:foto,anexo',
        ]);

        $attachments = [];

        foreach ($request->file('files') as $file) {
            $path = $file->store("visitas/{$visitReport->id}", 'public');

            $attachments[] = $visitReport->attachments()->create([
                'filename' => $file->getClientOriginalName(),
                'path'     => $path,
                'tipo'     => $request->input('tipo', 'foto'),
            ]);
        }

        return response()->json($attachments, 201);
    }

    /**
     * DELETE /api/attachments/{attachment}
     */
    public function destroy(VisitAttachment $attachment)
    {
        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();

        return response()->json(['message' => 'Archivo eliminado.']);
    }
}
