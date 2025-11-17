<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::create(['name' => 'Admin']);
        $vendedor = Role::create(['name' => 'Vendedor']);

        // Permisos de Usuarios
        $this->createUserPermissions($admin);

        // Permisos de Roles
        $this->createRolePermissions($admin);

        // Permisos de Semanas
        $this->createSemanaPermissions($admin);

        // Permisos de Pedidos
        $this->createPedidoPermissions($admin);

        // Permisos de Informes
        $this->createInformePermissions($admin);

    }

    private function createUserPermissions($admin)
    {
        Permission::create(['name' => 'users.index', 'descripcion' => 'Ver listado de usuarios', 'group' => 'Usuarios'])->syncRoles([$admin]);
        Permission::create(['name' => 'users.create', 'descripcion' => 'Crear usuarios', 'group' => 'Usuarios'])->syncRoles([$admin]);
        Permission::create(['name' => 'users.show', 'descripcion' => 'Ver Usuarios', 'group' => 'Usuarios'])->syncRoles([$admin]);
        Permission::create(['name' => 'users.edit', 'descripcion' => 'Editar usuarios', 'group' => 'Usuarios'])->syncRoles([$admin]);
        Permission::create(['name' => 'users.destroy', 'descripcion' => 'Eliminar usuarios', 'group' => 'Usuarios'])->syncRoles([$admin]);
    }

    private function createRolePermissions($admin)
    {
        Permission::create(['name' => 'roles.index', 'descripcion' => 'Ver listado de roles', 'group' => 'Roles'])->syncRoles([$admin]);
        Permission::create(['name' => 'roles.create', 'descripcion' => 'Crear roles', 'group' => 'Roles'])->syncRoles([$admin]);
        Permission::create(['name' => 'roles.show', 'descripcion' => 'Ver roles', 'group' => 'Roles'])->syncRoles([$admin]);
        Permission::create(['name' => 'roles.edit', 'descripcion' => 'Editar roles', 'group' => 'Roles'])->syncRoles([$admin]);
        Permission::create(['name' => 'roles.destroy', 'descripcion' => 'Eliminar roles', 'group' => 'Roles'])->syncRoles([$admin]);
    }

    private function createSemanaPermissions($admin)
    {
        Permission::create(['name' => 'semanas.index', 'descripcion' => 'Ver listado de semanas', 'group' => 'Semanas'])->syncRoles([$admin]);
        Permission::create(['name' => 'semanas.create', 'descripcion' => 'Crear semanas', 'group' => 'Semanas'])->syncRoles([$admin]);
        Permission::create(['name' => 'semanas.show', 'descripcion' => 'Ver semanas', 'group' => 'Semanas'])->syncRoles([$admin]);
        Permission::create(['name' => 'semanas.edit', 'descripcion' => 'Editar Semanas', 'group' => 'Semanas'])->syncRoles([$admin]);
        Permission::create(['name' => 'semanas.destroy', 'descripcion' => 'Eliminar semanas', 'group' => 'Semanas'])->syncRoles([$admin]);
    }

    private function createPedidoPermissions($admin)
    {
        Permission::create(['name' => 'pedidos.index', 'descripcion' => 'Ver listado de pedidos', 'group' => 'Pedidos'])->syncRoles([$admin]);
        Permission::create(['name' => 'pedidos.create', 'descripcion' => 'Crear pedidos', 'group' => 'Pedidos'])->syncRoles([$admin]);
        Permission::create(['name' => 'pedidos.show', 'descripcion' => 'Ver pedidos', 'group' => 'Pedidos'])->syncRoles([$admin]);
        Permission::create(['name' => 'pedidos.edit', 'descripcion' => 'Editar pedidos', 'group' => 'Pedidos'])->syncRoles([$admin]);
        Permission::create(['name' => 'pedidos.destroy', 'descripcion' => 'Eliminar pedidos', 'group' => 'Pedidos'])->syncRoles([$admin]);
    }

    private function createInformePermissions($admin)
    {
        Permission::create(['name' => 'informes.index', 'descripcion' => 'Ver listado de informes', 'group' => 'Informes'])->syncRoles([$admin]);
        Permission::create(['name' => 'informes.create', 'descripcion' => 'Crear informes', 'group' => 'Informes'])->syncRoles([$admin]);
        Permission::create(['name' => 'informes.show', 'descripcion' => 'Ver informe', 'group' => 'Informes'])->syncRoles([$admin]);
        Permission::create(['name' => 'informes.edit', 'descripcion' => 'Editar informes', 'group' => 'Informes'])->syncRoles([$admin]);
        Permission::create(['name' => 'informes.destroy', 'descripcion' => 'Eliminar informes', 'group' => 'Informes'])->syncRoles([$admin]);
    }

}
