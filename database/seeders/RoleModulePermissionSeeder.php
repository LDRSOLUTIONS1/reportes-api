<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Role;
use App\Models\RoleModulePermission;
use Illuminate\Database\Seeder;

class RoleModulePermissionSeeder extends Seeder
{
    /**
     * Replica el objeto PERMISOS_POR_ROL_MENU que hoy tienes en roles.js:
     *   1 (Super Administrador): ve todos los módulos
     *   2 (Administrador), 3 (Consultor), 4 (Gerente de Marca): solo ven
     *   Inicio, Brands y Projects, igual que hoy.
     *
     * NOTA: tu lógica anterior solo controlaba visibilidad de menú
     * (un booleano), no CRUD granular. Aquí dejamos can_view=true según
     * ese mapeo y can_create/edit/delete en false por defecto para los
     * roles no-superadmin; los ajustas luego desde la pantalla de
     * administración de permisos. El Super Administrador (role_id 1)
     * queda con acceso total.
     */
    public function run(): void
    {
        $modulos = Module::orderBy('order')->get()->keyBy('order');

        $modulosVistosPorTodos = [1, 2, 3]; // Inicio, Brands, Projects (por "order")

        foreach (Role::all() as $role) {
            foreach ($modulos as $orden => $modulo) {
                if ($role->id === 1) {
                    // Super Administrador: acceso total
                    RoleModulePermission::updateOrCreate(
                        ['role_id' => $role->id, 'module_id' => $modulo->id],
                        [
                            'can_view'   => true,
                            'can_create' => true,
                            'can_edit'   => true,
                            'can_delete' => true,
                            'estado'     => 1,
                        ]
                    );
                    continue;
                }

                $puedeVer = in_array($orden, $modulosVistosPorTodos, true);

                RoleModulePermission::updateOrCreate(
                    ['role_id' => $role->id, 'module_id' => $modulo->id],
                    [
                        'can_view'   => $puedeVer,
                        'can_create' => false,
                        'can_edit'   => false,
                        'can_delete' => false,
                        'estado'     => 1,
                    ]
                );
            }
        }
    }
}
