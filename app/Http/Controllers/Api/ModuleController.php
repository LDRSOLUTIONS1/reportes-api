<?php

namespace App\Http\Controllers\Api;

use App\Models\Module;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ModuleController extends Controller
{
    public function index()
    {
        $modules  = Module::select(
            'id',
            'parent_id',
            'name',
            'title',
            'segment',
            'icon',
            'order',
            'estado',
            'created_at',
        )
            ->activos()
            ->orderBy('order')
            ->get();

        return response()->json($modules, 200);
    }

    public function store(Request $request)
    {
        $validated = $this->validateModules($request);

        $module  = Module::create($validated);

        return response()->json([
            'message' => 'Modulo creado correctamente',
            'data'    => $module
        ], 201);
    }

    public function show($id)
    {
        $module = Module::select(
            'id',
            'parent_id',
            'name',
            'title',
            'segment',
            'icon',
            'order',
            'estado',
            'created_at',
        )
            ->where('id', $id)
            ->activos()
            ->firstOrFail();

        return response()->json($module, 200);
    }

    public function update(Request $request, $id)
    {
        $module = Module::activos()
            ->firstOrFail($id);

        $validated = $this->validateModules($request, $id);

        $module->update($validated);

        return response()->json([
            'message' => 'Moudulo actualizado correctamente',
            'data'    => $module
        ], 200);
    }

    public function validateModules(Request $request, $id = null)
    {
        return $request->validate(
            [
                'parent_id' => 'nullable|exists:modules,id',
                'name' => 'required|string|max:255|unique:modules,name,' . $id,
                'title' => 'required|string|max:255',
                'segment' => 'required|string|max:255',
                'icon' => 'required|string|max:255',
                'order' => 'required|integer',
                'estado' => 'nullable|in:0,1,2',
            ],
            [
                'parent_id.exists' => 'El modulo padre no existe',
                'name.required' => 'El nombre es obligatorio',
                'name.max'      => 'El nombre no puede tener más de 255 caracteres',
                'name.unique'   => 'El nombre ya existe',
                'title.required' => 'El título es obligatorio',
                'title.max'      => 'El título no puede tener más de 255 caracteres',
                'segment.required' => 'El segmento es obligatorio',
                'segment.max'      => 'El segmento no puede tener más de 255 caracteres',
                'icon.required' => 'El icono es obligatorio',
                'icon.max'      => 'El icono no puede tener más de 255 caracteres',
                'estado.in'     => 'El estado debe ser 1 (Inactivo) o 2 (Activo).',
            ]
        );
    }
}
