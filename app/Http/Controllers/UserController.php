<?php

namespace App\Http\Controllers;

use App\Models\Sucursale;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:users.index')->only('index');
        $this->middleware('can:users.create')->only('create', 'store');
        $this->middleware('can:users.edit')->only('edit', 'update');
        $this->middleware('can:users.destroy')->only('destroy');
    }

    /**
     * Mostrar la lista de usuarios
     */
    public function index()
    {
        $users = User::with('roles', 'sucursales')->get();  // Cargar roles y sucursales con los usuarios
        return view('users.index', compact('users'));
    }
    
    public function resetPassword(User $user)
    {
        // Establecer la contraseña igual al email
        $user->update([
            'password' => bcrypt($user->email), // La contraseña será igual al email cifrado
        ]);
    
        return redirect()->route('users.index')->with('success', 'Contraseña restablecida correctamente.');
    }

    /**
     * Mostrar el formulario para crear un nuevo usuario
     */
    public function create()
    {
        $roles = Role::all();  // Obtén todos los roles
        $sucursales = Sucursale::all();  // Obtén todas las sucursales
        return view('users.create', compact('roles', 'sucursales'));  // Enviar roles y sucursales a la vista
    }

    /**
     * Almacenar un nuevo usuario en la base de datos
     */
    public function store(Request $request)
{
    $request->validate([
        'name' => 'required',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:6',
        'role' => 'required',
        'sucursal' => 'required|exists:sucursales,id',
        'status' => 'required|in:active,inactive', // Validate the status field
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'status' => $request->status, // Assign the status
    ]);

    $user->assignRole($request->role);
    $user->sucursales()->sync([$request->sucursal]);

    return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');
}


    /**
     * Mostrar los detalles de un usuario
     */
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    /**
     * Mostrar el formulario para editar un usuario
     */
    public function edit(User $user)
    {
        $roles = Role::all();  // Obtén todos los roles
        $sucursales = Sucursale::all();  // Obtén todas las sucursales
        return view('users.edit', compact('user', 'roles', 'sucursales'));  // Enviar los datos a la vista
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required',
            'sucursal' => 'required|exists:sucursales,id',
            'status' => 'required|in:active,inactive', // Validate the status field
        ]);
    
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'status' => $request->status, // Update the status
        ]);
    
        $user->syncRoles([$request->role]);
        $user->sucursales()->sync([$request->sucursal]);
    
        return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente.');
    }
    /**
     * Eliminar un usuario
     */
    public function destroy(User $user)
    {
        // Eliminar al usuario
        $user->delete();
        return redirect()->route('users.index');
    }
    
    
    public function changePasswordView()
    {
        return view('users.change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ], [
            'old_password.required' => 'La contraseña actual es obligatoria.',
            'password.required' => 'La nueva contraseña es obligatoria.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
        ]);

        $user = auth()->user();
        if (!Hash::check($request->old_password, $user->password)) {
            return back()->withErrors(['old_password' => 'La contraseña actual es incorrecta.']);
        }

        if ($request->password === $request->old_password) {
            return back()->withErrors(['password' => 'La nueva contraseña no puede ser la misma que la actual.']);
        }

        $user->update([
            'password' => bcrypt($request->password),
        ]);

        return redirect()->route('home')->with('success', 'Contraseña cambiada exitosamente.');
    }
}
