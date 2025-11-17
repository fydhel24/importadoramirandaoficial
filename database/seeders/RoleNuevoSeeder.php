<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleNuevoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear permisos agrupados por categorías

        // Permisos de Orden
        Permission::create(['name' => 'orden.index', 'descripcion' => 'Ver listado de orden', 'group' => 'Orden']);
        Permission::create(['name' => 'orden.pedidos', 'descripcion' => 'Ver pedidos', 'group' => 'Orden']);
        Permission::create(['name' => 'orden.create', 'descripcion' => 'Crear orden', 'group' => 'Orden']);
        Permission::create(['name' => 'orden.edit', 'descripcion' => 'Editar orden', 'group' => 'Orden']);
        Permission::create(['name' => 'orden.destroy', 'descripcion' => 'Eliminar orden', 'group' => 'Orden']);

        // Permisos de Reporte
        Permission::create(['name' => 'reporte.index', 'descripcion' => 'Realizar reporte', 'group' => 'Reporte']);

        // Permisos de Productos
        Permission::create(['name' => 'productos.index', 'descripcion' => 'Ver listado de productos', 'group' => 'Productos']);
        Permission::create(['name' => 'productos.create', 'descripcion' => 'Crear productos', 'group' => 'Productos']);
        Permission::create(['name' => 'productos.show', 'descripcion' => 'Ver productos', 'group' => 'Productos']);
        Permission::create(['name' => 'productos.edit', 'descripcion' => 'Editar productos', 'group' => 'Productos']);
        Permission::create(['name' => 'productos.destroy', 'descripcion' => 'Eliminar productos', 'group' => 'Productos']);

        // Permisos de Inventarios
        Permission::create(['name' => 'inventarios.index', 'descripcion' => 'Ver listado de inventarios', 'group' => 'Inventarios']);
        Permission::create(['name' => 'inventarios.create', 'descripcion' => 'Crear inventarios', 'group' => 'Inventarios']);
        Permission::create(['name' => 'inventarios.show', 'descripcion' => 'Ver inventarios', 'group' => 'Inventarios']);
        Permission::create(['name' => 'inventarios.edit', 'descripcion' => 'Editar inventarios', 'group' => 'Inventarios']);
        Permission::create(['name' => 'inventarios.destroy', 'descripcion' => 'Eliminar inventarios', 'group' => 'Inventarios']);

        // Permisos de Cupos
        Permission::create(['name' => 'cupos.index', 'descripcion' => 'Ver listado de cupos', 'group' => 'Cupos']);
        Permission::create(['name' => 'cupos.create', 'descripcion' => 'Crear cupos', 'group' => 'Cupos']);
        Permission::create(['name' => 'cupos.show', 'descripcion' => 'Ver cupos', 'group' => 'Cupos']);
        Permission::create(['name' => 'cupos.edit', 'descripcion' => 'Editar cupos', 'group' => 'Cupos']);
        Permission::create(['name' => 'cupos.destroy', 'descripcion' => 'Eliminar cupos', 'group' => 'Cupos']);

        // Permisos de Sucursales
        Permission::create(['name' => 'sucursales.index', 'descripcion' => 'Ver listado de sucursales', 'group' => 'Sucursales']);
        Permission::create(['name' => 'sucursales.create', 'descripcion' => 'Crear sucursales', 'group' => 'Sucursales']);
        Permission::create(['name' => 'sucursales.show', 'descripcion' => 'Ver sucursales', 'group' => 'Sucursales']);
        Permission::create(['name' => 'sucursales.edit', 'descripcion' => 'Editar sucursales', 'group' => 'Sucursales']);
        Permission::create(['name' => 'sucursales.destroy', 'descripcion' => 'Eliminar sucursales', 'group' => 'Sucursales']);

        // Permisos de Marcas
        Permission::create(['name' => 'marcas.index', 'descripcion' => 'Ver listado de marcas', 'group' => 'Marcas']);
        Permission::create(['name' => 'marcas.create', 'descripcion' => 'Crear marcas', 'group' => 'Marcas']);
        Permission::create(['name' => 'marcas.show', 'descripcion' => 'Ver marcas', 'group' => 'Marcas']);
        Permission::create(['name' => 'marcas.edit', 'descripcion' => 'Editar marcas', 'group' => 'Marcas']);
        Permission::create(['name' => 'marcas.destroy', 'descripcion' => 'Eliminar marcas', 'group' => 'Marcas']);

        // Permisos de Categorías
        Permission::create(['name' => 'categorias.index', 'descripcion' => 'Ver listado de categorias', 'group' => 'Categorías']);
        Permission::create(['name' => 'categorias.create', 'descripcion' => 'Crear categorias', 'group' => 'Categorías']);
        Permission::create(['name' => 'categorias.show', 'descripcion' => 'Ver categorias', 'group' => 'Categorías']);
        Permission::create(['name' => 'categorias.edit', 'descripcion' => 'Editar categorias', 'group' => 'Categorías']);
        Permission::create(['name' => 'categorias.destroy', 'descripcion' => 'Eliminar categorias', 'group' => 'Categorías']);

        // Permisos de Tipos
        Permission::create(['name' => 'tipos.index', 'descripcion' => 'Ver listado de tipos', 'group' => 'Tipos']);
        Permission::create(['name' => 'tipos.create', 'descripcion' => 'Crear tipos', 'group' => 'Tipos']);
        Permission::create(['name' => 'tipos.show', 'descripcion' => 'Ver tipos', 'group' => 'Tipos']);
        Permission::create(['name' => 'tipos.edit', 'descripcion' => 'Editar tipos', 'group' => 'Tipos']);
        Permission::create(['name' => 'tipos.destroy', 'descripcion' => 'Eliminar tipos', 'group' => 'Tipos']);

        // Sincronizar roles con los nuevos permisos
        $admin = Role::findByName('Admin');

        // Sincronizar permisos con el rol de Admin
        $permissions = [
            'inventarios.index',
            'inventarios.create',
            'inventarios.show',
            'inventarios.edit',
            'inventarios.destroy',
            'cupos.index',
            'cupos.create',
            'cupos.show',
            'cupos.edit',
            'cupos.destroy',
            'sucursales.index',
            'sucursales.create',
            'sucursales.show',
            'sucursales.edit',
            'sucursales.destroy',
            'marcas.index',
            'marcas.create',
            'marcas.show',
            'marcas.edit',
            'marcas.destroy',
            'categorias.index',
            'categorias.create',
            'categorias.show',
            'categorias.edit',
            'categorias.destroy',
            'tipos.index',
            'tipos.create',
            'tipos.show',
            'tipos.edit',
            'tipos.destroy',
            'orden.index',
            'orden.pedidos',
            'orden.create',
            'orden.edit',
            'orden.destroy',
            'reporte.index',
            'productos.index',
            'productos.create',
            'productos.show',
            'productos.edit',
            'productos.destroy'
        ];

        foreach ($permissions as $permission) {
            Permission::findByName($permission)->syncRoles([$admin]);
        }
    }
}
