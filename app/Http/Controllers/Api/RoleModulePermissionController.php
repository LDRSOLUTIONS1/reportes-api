<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Role;
use App\Models\RoleModulePermission;
use Illuminate\Http\Request;

class RoleModulePermissionController extends Controller
{
    /**
     * Devuelve la matriz completa: todos los módulos activos + los
     * permisos que el rol dado tiene sobre cada uno (o flags en false
     * si aún no existe el registro). Ideal para pintar la tabla de
     * checkboxes en el front.
     */
    public function matrizPorRol(Role $role)
    {
        $modulos = Module::activos()->orderBy('order')->get();

        $permisosExistentes = RoleModulePermission::where('role_id', $role->id)
            ->get()
            ->keyBy('module_id');

        $matriz = $modulos->map(function ($modulo) use ($permisosExistentes) {
            $permiso = $permisosExistentes->get($modulo->id);

            return [
                'module_id'  => $modulo->id,
                'name'       => $modulo->name,
                'title'      => $modulo->title,
                'can_view'   => $permiso->can_view ?? false,
                'can_create' => $permiso->can_create ?? false,
                'can_edit'   => $permiso->can_edit ?? false,
                'can_delete' => $permiso->can_delete ?? false,
            ];
        });

        return response()->json([
            'role_id' => $role->id,
            'role'    => $role->name,
            'matriz'  => $matriz,
        ]);
    }

    /**
     * Guarda/actualiza en bloque la matriz de permisos de un rol.
     * Espera: { "permisos": [ { "module_id": 1, "can_view": true, ... }, ... ] }
     */
    public function actualizarMatriz(Request $request, Role $role)
    {
        $data = $request->validate([
            'permisos'               => ['required', 'array'],
            'permisos.*.module_id'   => ['required', 'exists:modules,id'],
            'permisos.*.can_view'    => ['boolean'],
            'permisos.*.can_create'  => ['boolean'],
            'permisos.*.can_edit'    => ['boolean'],
            'permisos.*.can_delete'  => ['boolean'],
        ]);

        foreach ($data['permisos'] as $permiso) {
            RoleModulePermission::updateOrCreate(
                [
                    'role_id'   => $role->id,
                    'module_id' => $permiso['module_id'],
                ],
                [
                    'can_view'   => $permiso['can_view'] ?? false,
                    'can_create' => $permiso['can_create'] ?? false,
                    'can_edit'   => $permiso['can_edit'] ?? false,
                    'can_delete' => $permiso['can_delete'] ?? false,
                    'estado'     => 1,
                ]
            );
        }

        return response()->json(['message' => 'Permisos actualizados correctamente']);
    }
}
