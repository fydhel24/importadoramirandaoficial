<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleReportesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Permisos de reportes
        Permission::create(['name' => 'report.ventas', 'descripcion' => 'Reporte de ventas', 'group' => 'Reportes']);
        Permission::create(['name' => 'report.inventario', 'descripcion' => 'Reporte de inventario', 'group' => 'Reportes']);
        Permission::create(['name' => 'reporte.pedidos', 'descripcion' => 'Reporte de pedidos', 'group' => 'Reportes']);
        Permission::create(['name' => 'reporte.pedidos_producto', 'descripcion' => 'Reporte de pedidos de productos', 'group' => 'Reportes']);
        Permission::create(['name' => 'envios.index', 'descripcion' => 'Envio de productos', 'group' => 'Envios de productos']);

        // Sincronizar roles con los nuevos permisos
        $admin = Role::findByName('Admin');

        // Sincronizar permisos con el rol de Admin
        $permissions = [

            'report.ventas',
            'report.inventario',
            'reporte.pedidos',
            'reporte.pedidos_producto',
            'envios.index',
        ];

        foreach ($permissions as $permission) {
            Permission::findByName($permission)->syncRoles([$admin]);
        }
    }
}
