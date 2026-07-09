<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\RoleModulePermission;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Devuelve el menú ya filtrado según el rol del usuario autenticado,
     * en un formato listo para mapear a la navegación de MUI Toolpad
     * (segment, title, icon como string clave para tu iconMap.js).
     */
    public function menu(Request $request)
    {
        $user = $request->user();
        $roleId = $user->role_id;

        $modulosPermitidosIds = RoleModulePermission::where('role_id', $roleId)
            ->where('can_view', true)
            ->pluck('module_id');

        $modulos = Module::activos()
            ->raiz()
            ->whereIn('id', $modulosPermitidosIds)
            ->with(['children' => function ($query) use ($modulosPermitidosIds) {
                $query->whereIn('id', $modulosPermitidosIds);
            }])
            ->orderBy('order')
            ->get();

        $menu = $modulos->map(fn ($modulo) => $this->formatearModulo($modulo));

        return response()->json($menu);
    }

    /**
     * Devuelve, para el usuario autenticado, un mapa simple
     * { "brands": { can_view, can_create, can_edit, can_delete }, ... }
     * pensado para tu hook usePermisos y checks de botones.
     */
    public function misPermisos(Request $request)
    {
        $user = $request->user();
        $roleId = $user->role_id;

        $permisos = RoleModulePermission::where('role_id', $roleId)
            ->with('module:id,name')
            ->get()
            ->mapWithKeys(function ($permiso) {
                return [
                    $permiso->module->name => [
                        'can_view'   => $permiso->can_view,
                        'can_create' => $permiso->can_create,
                        'can_edit'   => $permiso->can_edit,
                        'can_delete' => $permiso->can_delete,
                    ],
                ];
            });

        return response()->json($permisos);
    }

    private function formatearModulo(Module $modulo): array
    {
        $item = [
            'id'      => $modulo->id,
            'segment' => $modulo->segment,
            'title'   => $modulo->title,
            'icon'    => $modulo->icon,
        ];

        if ($modulo->children->isNotEmpty()) {
            $item['children'] = $modulo->children
                ->map(fn ($hijo) => $this->formatearModulo($hijo))
                ->values();
        }

        return $item;
    }
}
