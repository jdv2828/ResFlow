<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    private const PERMISSIONS = [
        // GR permissions
        'puede_ver_centro_costo',
        'puede_leer_centro_costo',
        'puede_crear_centro_costo',
        'puede_editar_centro_costo',
        'puede_borrar_centro_costo',
        'puede_ver_recurso',
        'puede_leer_recurso',
        'puede_crear_recurso',
        'puede_editar_recurso',
        'puede_borrar_recurso',
        // Personal permissions
        'puede_ver_personal',
        'puede_leer_personal',
        'puede_crear_personal',
        'puede_editar_personal',
        'puede_borrar_personal',
        // Unchanged permissions
        'puede_ver_ticket',
        'puede_leer_ticket',
        'puede_crear_ticket',
        'puede_editar_ticket',
        'puede_borrar_ticket',
        'puede_imprimir_ticket',
        'puede_ver_roles_y_permisos',
        'puede_ver_gestion_ticket',
        'puede_ver_vehiculo',
        'puede_leer_vehiculo',
        'puede_crear_vehiculo',
        'puede_editar_vehiculo',
        'puede_borrar_vehiculo',
        'puede_ver_lote',
        'puede_crear_lote',
        'puede_editar_lote',
        'puede_borrar_lote',
        'puede_generar_lote',
        'puede_imprimir_lote',
        'puede_ver_informes',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }
}
