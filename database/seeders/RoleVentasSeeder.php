<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleVentasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear permisos agrupados por categorías
        // Permisos de control ventas
        Permission::create(['name' => 'control.index', 'descripcion' => 'Ver Sucursales disponibles', 'group' => 'Venta Sucursal']);
        Permission::create(['name' => 'control.productos', 'descripcion' => 'Ver producto de Sucursal', 'group' => 'Venta Sucursal']);
        Permission::create(['name' => 'control.inventario.form', 'descripcion' => 'Agregar inventario', 'group' => 'Venta Sucursal']);

        // Sincronizar roles con los nuevos permisos
        $admin = Role::findByName('Admin');

        // Sincronizar permisos con el rol de Admin
        $permissions = [
            
            'control.index',
            'control.productos',
            'control.inventario.form',
        ];

        foreach ($permissions as $permission) {
            Permission::findByName($permission)->syncRoles([$admin]);
        }
    }
}
