<?php

namespace App\Http\Controllers;

use App\Models\Nota;
use App\Models\NotaJefa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotaJefaController extends Controller
{
    // Constructor con el middleware para verificar permisos de acceso
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // Verificar que el usuario tenga el rol "Admin" y un correo específico
            if (
                auth()->user()->hasRole('Admin') &&
                (auth()->user()->email === 'yesenew@gmail.com' || auth()->user()->email === 'JHOELSURCO2@GMAIL.COM')
            ) {
                return $next($request);
            }

            // Si no tiene acceso, redirigir a una página con un mensaje
            return redirect()->route('notas.index')->with('success', 'No tienes permisos suficientes para acceder a esta página.');
        });
    }
    // Muestra la vista principal con las notas (index)
    public function index()
    {
        // Obtener todas las notas
        $notas = NotaJefa::with('user')->get();

        // Formatear las notas para que FullCalendar las entienda
        $events = $notas->map(function ($nota) {
            return [
                'id' => $nota->id,
                'title' => $nota->titulo,
                'start' => $nota->fecha,
                'description' => $nota->nota,
                'user' => $nota->user->name, // Incluir el nombre del usuario
                'allDay' => true, // Considerar eventos de todo el día
                'color' => $nota->color,
            ];
        });

        // Pasar las notas formateadas a la vista
        return view('notasjefa.index', compact('events'));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'nota' => 'required|string',
            'fecha' => 'required|date',
            'color' => 'required|string', // Aseguramos que el color es obligatorio
        ]);

        // Obtener el color seleccionado
        $color = $request->color;
        // Obtener el nombre del usuario que creó la nota
        $userName = Auth::user()->name;
        $nota = NotaJefa::create([
            'titulo' => $request->titulo,
            'nota' => $request->nota,
            'fecha' => $request->fecha,
            'color' => $color,  // Guardar el color seleccionado
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Nota creada exitosamente',
            'titulo' => $nota->titulo,
            'fecha' => $nota->fecha,
            'nota' => $nota->nota,
            'color' => $nota->color, // Incluir el color en la respuesta
            'user' => $userName,
        ]);
    }

    public function update(Request $request, $id)
    {
        // Validar los datos recibidos
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'nota' => 'required|string',
            'fecha' => 'required|date',
            'color' => 'required|string',
        ]);

        // Buscar la nota por ID
        $nota = NotaJefa::findOrFail($id);

        // Actualizar los campos de la nota
        $nota->titulo = $request->titulo;
        $nota->nota = $request->nota;
        $nota->fecha = $request->fecha;
        $nota->color = $request->color;
        $nota->save();

        // Devolver una respuesta JSON con un mensaje de éxito
        return response()->json([
            'message' => 'Nota actualizada exitosamente',
            'titulo' => $nota->titulo,
            'fecha' => $nota->fecha,
            'nota' => $nota->nota,
            'color' => $nota->color
        ]);
    }

    public function destroy($id)
    {
        // Buscar la nota por ID
        $nota = NotaJefa::findOrFail($id);

        // Eliminar la nota
        $nota->delete();

        // Responder con un mensaje de éxito
        return response()->json(['message' => 'Nota eliminada exitosamente']);
    }
}
