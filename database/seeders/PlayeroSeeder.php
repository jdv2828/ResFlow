<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PlayeroSeeder extends Seeder
{
    public function run(): void
    {
        $permisosPlayero = [
            'puede_ver_ticket',
            'puede_leer_ticket',
            'puede_crear_ticket',
            'puede_editar_ticket',
            'puede_borrar_ticket',
            'puede_imprimir_ticket',
            'puede_ver_gestion_ticket',
            'puede_ver_lote',
        ];

        foreach (['playero_turno_1', 'playero_turno_2', 'playero_turno_3'] as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permisosPlayero);
        }
    }
}
