<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
      public function __construct()
    {
        $this->middleware('can:roles.index')->only('index');
        $this->middleware('can:roles.create')->only('create', 'store');
        $this->middleware('can:roles.edit')->only('edit', 'update');
        $this->middleware('can:roles.destroy')->only('destroy');
    }

    public function index()
    {
        $roles = Role::all();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        // Obtener todos los permisos
        $permissions = Permission::all();

        // Agrupar permisos por grupo
        $groupedPermissions = $permissions->groupBy('group');

        return view('roles.create', compact('groupedPermissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'array', // Permite múltiples permisos
        ]);

        // Crea el rol
        $role = Role::create(['name' => $request->name]);

        // Sincroniza permisos
        if ($request->permissions) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('roles.index')->with('success', 'Rol creado exitosamente.');
    }

    public function edit(Role $role)
    {
        // Obtener todos los permisos
        $permissions = Permission::all();

        // Agrupar permisos por grupo
        $groupedPermissions = $permissions->groupBy('group');

        return view('roles.edit', compact('role', 'groupedPermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'array',
        ]);

        // Actualiza el nombre del rol
        $role->update(['name' => $request->name]);

        // Sincroniza permisos
        if ($request->permissions) {
            $role->syncPermissions($request->permissions);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()->route('roles.index')->with('success', 'Rol actualizado exitosamente.');
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role eliminado exitosamente.');
    }
}
