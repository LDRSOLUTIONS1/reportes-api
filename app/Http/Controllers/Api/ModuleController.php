<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ModuleController extends Controller
{
    /**
     * Lista todos los módulos en forma de árbol (padres con sus hijos),
     * pensado para alimentar la pantalla de administración de módulos.
     */
    public function index()
    {
        $modulos = Module::with('children')
            ->raiz()
            ->orderBy('order')
            ->get();

        return response()->json($modulos);
    }

    /**
     * Lista plana de todos los módulos (útil para selects de "módulo padre"
     * y para la matriz de permisos).
     */
    public function flat()
    {
        $modulos = Module::orderBy('order')->get();

        return response()->json($modulos);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:100', 'unique:modules,name'],
            'title'     => ['required', 'string', 'max:255'],
            'segment'   => ['required', 'string', 'max:255'],
            'icon'      => ['nullable', 'string', 'max:100'],
            'parent_id' => ['nullable', 'exists:modules,id'],
            'order'     => ['nullable', 'integer'],
            'estado'    => ['nullable', 'boolean'],
        ]);

        $modulo = Module::create($data);

        return response()->json($modulo, 201);
    }

    public function update(Request $request, Module $module)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:100', Rule::unique('modules', 'name')->ignore($module->id)],
            'title'     => ['required', 'string', 'max:255'],
            'segment'   => ['required', 'string', 'max:255'],
            'icon'      => ['nullable', 'string', 'max:100'],
            'parent_id' => ['nullable', 'exists:modules,id', Rule::notIn([$module->id])],
            'order'     => ['nullable', 'integer'],
            'estado'    => ['nullable', 'boolean'],
        ]);

        $module->update($data);

        return response()->json($module);
    }

    /**
     * Baja/alta lógica: evita romper los permisos ya asignados en
     * role_module_permissions cuando un módulo se deshabilita.
     */
    public function toggleEstado(Module $module)
    {
        $module->update(['estado' => $module->estado ? 0 : 1]);

        return response()->json($module);
    }

    /**
     * Borrado definitivo. Al tener onDelete cascade, también elimina
     * los permisos asociados en role_module_permissions.
     */
    public function destroy(Module $module)
    {
        $module->delete();

        return response()->json(['message' => 'Módulo eliminado correctamente']);
    }
}
