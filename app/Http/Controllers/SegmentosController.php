<?php

namespace App\Http\Controllers;

use App\Models\Segment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SegmentosController extends Controller
{
    public function index()
    {
        $segments = Segment::select(
            'id',
            'name',
            'description',
            'estado',
            'created_at',
        )
            ->activos()
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($segments, 200);
    }

    public function store(Request $request)
    {
        $validated = $this->validateSegments($request);

        $segment = Segment::create($validated);

        return response()->json([
            'message' => 'Segmento creado correctamente',
            'data'    => $segment
        ], 201);
    }


    public function show($id)
    {
        $segment = Segment::select(
            'id',
            'name',
            'description',
            'estado',
            'created_at',
        )
            ->where('id', $id)
            ->activos()
            ->firstOrFail();

        return response()->json($segment, 200);
    }

    public function update(Request $request, $id)
    {
        $segment = Segment::activos()
            ->findOrFail($id);

        $validated = $this->validateSegments($request, $id);

        $segment->update($validated);

        return response()->json([
            'message' => 'Segmento actualizado correctamente',
            'data'    => $segment
        ], 200);
    }

    public function validateSegments(Request $request, $id = null)
    {
        return $request->validate(
            [
                'name'          => 'required|string|max:255|unique:segments,name,' . $id,
                'description'   => 'nullable|string|max:255',
                'estado'        => 'nullable|in:0,1,2',
            ],
            [
                'name.required'         => 'El nombre es obligatorio',
                'name.max'              => 'El nombre no puede tener más de 255 caracteres',
                'name.unique'           => 'El nombre ya existe',
                'description.max'       => 'La descripcion no puede tener más de 255 caracteres',
                'estado.in'             => 'El estado debe ser 1 (Inactivo) o 2 (Activo).',
            ]
        );
    }
}
