<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class RoleIndexSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Permisos
        Permission::create(['name' => 'caja_sucursal.index', 'descripcion' => 'Reporte de cajas', 'group' => 'Cajas']);
        Permission::create(['name' => 'cajas.index', 'descripcion' => 'Apertura de caja', 'group' => 'Cajas']);
        Permission::create(['name' => 'envioscuaderno.index', 'descripcion' => 'Cuaderno', 'group' => 'Cuaderno']);
        Permission::create(['name' => 'envioscuaderno.indexSinLaPaz', 'descripcion' => 'Cuaderno sin La Paz', 'group' => 'Cuaderno']);
        Permission::create(['name' => 'envioscuaderno.indexSinLaPazYEnviados', 'descripcion' => 'Cuaderno sin La Paz y enviados', 'group' => 'Cuaderno']);
        Permission::create(['name' => 'envioscuaderno.sololapaz', 'descripcion' => 'Cuaderno solo La Paz', 'group' => 'Cuaderno']);
        Permission::create(['name' => 'report.stock', 'descripcion' => 'Ver y Administrar stock', 'group' => 'Administrar Stock']);
        Permission::create(['name' => 'proveedores.index', 'descripcion' => 'Administrar Proveedores', 'group' => 'Proveedores']);
        Permission::create(['name' => 'reportes.productos.form', 'descripcion' => 'Reporte por producto', 'group' => 'Reportes']);
        Permission::create(['name' => 'promociones.index', 'descripcion' => 'Promociones', 'group' => 'Promociones']);
        Permission::create(['name' => 'envios.historial', 'descripcion' => 'Historial de envios', 'group' => 'Envios de productos']);
        Permission::create(['name' => 'envios.solicitud', 'descripcion' => 'Solicitud de Envios', 'group' => 'Envios de productos']);
        Permission::create(['name' => 'report.user.ventas', 'descripcion' => 'Reporte ventas usuario', 'group' => 'Reportes']);

        // Sincronizar roles con los nuevos permisos
        $admin = Role::findByName('Admin');

        // Sincronizar permisos con el rol de Admin
        $permissions = [
            'caja_sucursal.index',
            'cajas.index',
            'envioscuaderno.index',
            'envioscuaderno.indexSinLaPaz',
            'envioscuaderno.indexSinLaPazYEnviados',
            'envioscuaderno.sololapaz',
            'report.stock',
            'proveedores.index',
            'reportes.productos.form',
            'promociones.index',
            'envios.historial',
            'envios.solicitud',
            'report.user.ventas',
        ];

        foreach ($permissions as $permission) {
            Permission::findByName($permission)->syncRoles([$admin]);
        }
    }
}
